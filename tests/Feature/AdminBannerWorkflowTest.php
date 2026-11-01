<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\Banner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

class AdminBannerWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private string $uploadRoot;

    protected function setUp(): void
    {
        parent::setUp();
        $this->uploadRoot = sys_get_temp_dir().'/me9-banner-test-'.\Illuminate\Support\Str::uuid();
        $this->app->usePublicPath($this->uploadRoot);
        $admin = new Admin;
        $admin->name = '배너 검수 관리자';
        $admin->type = 'superadmin';
        $admin->vendor_id = 0;
        $admin->mobile = '01000000000';
        $admin->email = 'banner-admin@example.test';
        $admin->password = bcrypt('test-password');
        $admin->status = 1;
        $admin->save();
        $this->actingAs($admin, 'admin');
    }

    protected function tearDown(): void
    {
        File::deleteDirectory($this->uploadRoot);
        parent::tearDown();
    }

    private function imagePath(Banner $banner): string
    {
        return $this->uploadRoot.'/front/images/banner_images/'.$banner->image;
    }

    private function createBanner(): Banner
    {
        $this->post('/admin/add-edit-banner', [
            'type' => 'Slider', 'title' => '등록 검수', 'link' => '/service', 'alt' => '검수 이미지',
            'image' => UploadedFile::fake()->image('slide.png', 120, 60),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/banners');
        return Banner::latest('id')->firstOrFail();
    }

    public function test_registration_saves_image_and_accepts_empty_optional_fields(): void
    {
        $this->post('/admin/add-edit-banner', [
            'type' => 'Slider', 'link' => '', 'title' => '', 'alt' => '',
            'image' => UploadedFile::fake()->image('banner.png', 120, 60),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/banners');
        $banner = Banner::sole();
        $this->assertSame('', $banner->title);
        $this->assertSame('', $banner->link);
        $this->assertSame('', $banner->alt);
        $this->assertEquals(1, $banner->status);
        $this->assertFileExists($this->imagePath($banner));
        $this->assertSame([1920, 720], array_slice(getimagesize($this->imagePath($banner)), 0, 2));
    }

    public function test_text_only_edit_keeps_image_and_active_or_stopped_status(): void
    {
        $banner = $this->createBanner();
        $original = $banner->image;
        foreach ([0, 1] as $status) {
            $banner->status = $status;
            $banner->save();
            $this->post('/admin/add-edit-banner/'.$banner->id, [
                'type' => 'Slider', 'title' => '내용 수정', 'link' => 'https://example.com/catalog', 'alt' => '수정 설명',
                'status' => 1 - $status,
            ])->assertSessionHasNoErrors()->assertRedirect('/admin/banners');
            $banner->refresh();
            $this->assertSame($original, $banner->image);
            $this->assertEquals($status, $banner->status);
            $this->assertSame('내용 수정', $banner->title);
            $this->assertSame('https://example.com/catalog', $banner->link);
            $this->assertSame('수정 설명', $banner->alt);
            $this->assertFileExists($this->imagePath($banner));
        }
    }

    public function test_image_replacement_saves_fixed_dimensions_and_removes_previous_file(): void
    {
        $banner = $this->createBanner();
        $oldPath = $this->imagePath($banner);
        $banner->status = 0;
        $banner->save();
        $this->post('/admin/add-edit-banner/'.$banner->id, [
            'type' => 'Fix', 'title' => '교체 검수', 'image' => UploadedFile::fake()->image('fixed.jpg', 160, 90),
        ])->assertSessionHasNoErrors()->assertRedirect('/admin/banners');
        $banner->refresh();
        $this->assertEquals(0, $banner->status);
        $this->assertSame('Fix', $banner->type);
        $this->assertSame('교체 검수', $banner->title);
        $this->assertFileDoesNotExist($oldPath);
        $this->assertFileExists($this->imagePath($banner));
        $this->assertSame([1920, 450], array_slice(getimagesize($this->imagePath($banner)), 0, 2));
    }

    public function test_invalid_uploads_and_unsafe_links_do_not_modify_existing_data(): void
    {
        $banner = $this->createBanner();
        $before = $banner->getAttributes();
        $invalidUploads = [
            UploadedFile::fake()->create('file.pdf', 10, 'application/pdf'),
            UploadedFile::fake()->image('large.png')->size(5121),
            UploadedFile::fake()->image('wide.png', 8193, 1),
        ];
        foreach ($invalidUploads as $upload) {
            $this->post('/admin/add-edit-banner/'.$banner->id, ['type' => 'Fix', 'title' => '반영 불가', 'image' => $upload])
                ->assertSessionHasErrors('image');
            $this->assertSame($before, $banner->fresh()->getAttributes());
        }
        foreach (['javascript:alert(1)', 'data:text/html,test', '//example.com', "https://example.com\t/javascript:test", '\\example.com'] as $link) {
            $this->post('/admin/add-edit-banner/'.$banner->id, ['type' => 'Slider', 'link' => $link])
                ->assertSessionHasErrors('link');
            $this->assertSame($before, $banner->fresh()->getAttributes());
        }
        $this->post('/admin/add-edit-banner', ['type' => 'Slider'])->assertSessionHasErrors('image');
        $this->assertSame(1, Banner::count());
        $this->assertCount(1, File::files(dirname($this->imagePath($banner))));
    }

    public function test_failed_database_save_keeps_original_image_and_cleans_new_file(): void
    {
        $banner = $this->createBanner();
        $oldPath = $this->imagePath($banner);
        $dispatcher = Banner::getEventDispatcher();
        Banner::setEventDispatcher(clone $dispatcher);
        Banner::saving(function () { throw new \RuntimeException('banner-save-failure'); });
        $this->withoutExceptionHandling();
        try {
            $this->post('/admin/add-edit-banner/'.$banner->id, [
                'type' => 'Fix', 'title' => '실패 검수', 'image' => UploadedFile::fake()->image('replacement.png'),
            ]);
            $this->fail('저장 실패 예외가 발생해야 합니다.');
        } catch (\RuntimeException $error) {
            $this->assertSame('banner-save-failure', $error->getMessage());
        } finally {
            Banner::setEventDispatcher($dispatcher);
        }
        $this->assertSame($banner->image, $banner->fresh()->image);
        $this->assertSame('등록 검수', $banner->fresh()->title);
        $this->assertFileExists($oldPath);
        $this->assertCount(1, File::files(dirname($oldPath)));
    }

    public function test_missing_banner_returns_not_found(): void
    {
        $this->get('/admin/add-edit-banner/999999')->assertNotFound();
        $this->post('/admin/add-edit-banner/999999', ['type' => 'Slider'])->assertNotFound();
    }
}
