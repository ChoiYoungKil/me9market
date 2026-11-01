<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Intervention\Image\Facades\Image;

use App\Models\Banner;

class BannersController extends Controller
{
    public function banners() {
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'banners');

        $banners = Banner::get()->toArray();
        // dd($banners);

        
        return view('admin.banners.banners')->with(compact('banners'));
    }

    public function updateBannerStatus(Request $request) { // AJAX를 사용하여 배너 상태 업데이트    
        if ($request->ajax()) { // AJAX 호출인 경우
            $data = $request->all(); // AJAX 요청에서 전달된 데이터 배열 가져오기
            // dd($data);

            if ($data['status'] == 'Active') { // 상태값에 따라 0 또는 1로 전환
                $status = 0;
            } else {
                $status = 1;
            }


            Banner::where('id', $data['banner_id'])->update(['status' => $status]); 
            // echo '<pre>', var_dump($data), '</pre>';

            return response()->json([ // JSON Responses: https://laravel.com/docs/9.x/responses#json-responses
                'status'    => $status,
                'banner_id' => $data['banner_id']
            ]);
        }
    }

    public function deleteBanner($id) { // AJAX를 사용하여 서버 및 데이터베이스에서 배너 삭제    
        // `banners` 테이블에서 배너 이미지 레코드 가져오기
        $bannerImage = Banner::where('id', $id)->first();

        // 서버의 배너 이미지 경로 가져오기
        $banner_image_path = public_path('front/images/banner_images/');

        // 서버에서 물리적 파일 삭제
        if (file_exists($banner_image_path . $bannerImage->image)) {
            unlink($banner_image_path . $bannerImage->image);
        }

        // `banners` 테이블에서 배너 레코드 삭제
        Banner::where('id', $id)->delete();
        
        $message = '배너가 성공적으로 삭제되었습니다!';
        
        return redirect()->back()->with('success_message', $message);
    }

    public function addEditBanner(Request $request, $id = null) { // 배너 추가 또는 수정    
        // 사이드바 활성 페이지 설정을 위해 세션 사용
        Session::put('page', 'banners');


        if ($id == '') { // $id가 없으면 배너 추가
            $banner = new Banner;
            $title = '배너 이미지 추가';
            $message = '배너가 성공적으로 추가되었습니다!';
        } else { // $id가 있으면 배너 수정
            $banner = Banner::findOrFail($id);
            $title = '배너 이미지 수정';
            $message = '배너가 성공적으로 업데이트되었습니다!';
        }

        // 둘째, 요청 메소드가 'POST'인 경우, add_edit_banner.blade.php 페이지의 HTML <form>을 제출합니다 (배너 추가 또는 수정):
        if ($request->isMethod('post')) { // 추가 또는 수정 폼 제출 시
            $data = $request->validate([
                'type' => ['required', 'in:Slider,Fix'],
                'link' => ['nullable', 'string', 'max:2048', function ($attribute, $value, $fail) {
                    if (preg_match('/[\x00-\x20\\\\]/', $value) || str_starts_with($value, '//') ||
                        (preg_match('/^[^\/?#]*:/', $value) && !preg_match('/^https?:\/\//i', $value))) {
                        $fail('연결 주소는 사이트 내 경로 또는 http(s) 주소로 입력해 주세요.');
                    }
                }],
                'title' => ['nullable', 'string', 'max:255'],
                'alt' => ['nullable', 'string', 'max:255'],
                'image' => [$banner->image ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120', 'dimensions:max_width=8192,max_height=8192'],
            ], [
                'type.required' => '배너 유형을 선택해 주세요.',
                'type.in' => '올바른 배너 유형을 선택해 주세요.',
                'image.required' => '배너 이미지를 선택해 주세요.',
                'image.image' => '정상적인 이미지 파일을 선택해 주세요.',
                'image.mimes' => 'JPG, PNG, WEBP 이미지 파일을 선택해 주세요.',
                'image.max' => '배너 이미지는 5MB 이하로 선택해 주세요.',
                'image.dimensions' => '이미지 가로·세로는 각각 8,192px 이하로 선택해 주세요.',
            ]);
            // dd($data);

            $banner->type   = $data['type'];
            $banner->link   = $data['link'] ?? '';
            $banner->title  = $data['title'] ?? '';
            $banner->alt    = $data['alt'] ?? '';
            if (!$banner->exists) {
                $banner->status = 1;
            }

            
            if ($data['type'] == 'Slider') {
                $width  = '1920';
                $height = '720';
            } else if ($data['type'] == 'Fix') {
                $width  = '1920';
                $height = '450';
            }

            $oldImage = $banner->getOriginal('image');
            $imagePath = null;
            try {
                if ($request->hasFile('image')) {
                    $upload = $request->file('image');
                    $imageName = \Illuminate\Support\Str::uuid().'.'.$upload->extension();
                    $directory = public_path('front/images/banner_images');
                    File::ensureDirectoryExists($directory);
                    $imagePath = $directory.'/'.$imageName;
                    $image = Image::make($upload)->orientate();
                    try {
                        $image->resize($width, $height)->save($imagePath);
                    } finally {
                        $image->destroy();
                    }
                    $banner->image = $imageName;
                }
                DB::transaction(fn () => $banner->save());
            } catch (\Throwable $error) {
                if ($imagePath) {
                    File::delete($imagePath);
                }
                throw $error;
            }
            if ($imagePath && $oldImage && basename($oldImage) === $oldImage && !Banner::where('image', $oldImage)->exists()) {
                File::delete(public_path('front/images/banner_images/'.$oldImage));
            }

            return redirect('admin/banners')->with('success_message', $message); 
        }


        return view('admin.banners.add_edit_banner')->with(compact('banner', 'title'));
    }
}
