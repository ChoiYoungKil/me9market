<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PlatformRefundPolicy;
use App\Models\Product;
use App\Models\ShopCancelRefundPolicy;
use App\Models\ShopChannel;
use App\Models\ShopChannelProduct;
use App\Models\Vendor;
use App\Services\RefundPolicyContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelRefundPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $key = 'one'): Admin
    {
        $vendor = Vendor::forceCreate(['name' => 'QA '.$key, 'email' => $key.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);

        return Admin::forceCreate(['name' => 'Owner '.$key, 'type' => 'vendor', 'vendor_id' => $vendor->id, 'email' => $key.'@admin.test', 'mobile' => '01000000000', 'password' => bcrypt('OwnerOnly1234'), 'status' => 1, 'confirm' => 'Yes']);
    }

    private function payload(array $extra = []): array
    {
        return array_replace(['type' => 'default', 'status' => 'active', 'name' => 'QA206 안내', 'content' => "첫 번째 문단\n\n두 번째 문단"], $extra);
    }

    private function policy(Admin $owner, array $extra = []): ShopCancelRefundPolicy
    {
        return ShopCancelRefundPolicy::create($this->payload($extra) + ['vendor_id' => $owner->vendor_id]);
    }

    private function product(Admin $owner, int $policyId): Product
    {
        return Product::forceCreate(['section_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'vendor_id' => $owner->vendor_id, 'admin_id' => $owner->id, 'admin_type' => 'vendor', 'product_name' => 'QA205 product', 'product_code' => 'QA205-'.uniqid(), 'product_color' => 'Blue', 'product_price' => 10000, 'product_discount' => 0, 'product_weight' => 100, 'status' => 1, 'cancel_refund_policy_id' => $policyId]);
    }

    public function test_create_update_and_show_preserve_paragraphs_without_changing_platform_default(): void
    {
        $owner = $this->owner();
        PlatformRefundPolicy::forceCreate(['id' => 1, 'content' => 'Platform default']);
        $this->actingAs($owner, 'admin')->post(route('channel.refund.store'), $this->payload())->assertRedirect(route('channel.refund.list'));
        $policy = ShopCancelRefundPolicy::first();
        $this->assertSame($owner->vendor_id, $policy->vendor_id);
        $this->assertSame("첫 번째 문단\n\n두 번째 문단", $policy->content);
        $this->get(route('channel.refund.get', $policy->id))->assertOk()->assertSee('첫 번째 문단')->assertSee('두 번째 문단');
        $this->postJson(route('channel.refund.update', $policy->id), $this->payload(['type' => 'custom', 'status' => 'inactive', 'name' => 'QA207 수정', 'content' => "수정 내용\n두 번째 줄"]))->assertOk()->assertJsonPath('status', 'success');
        $this->assertDatabaseHas('shop_cancel_refund_policies', ['id' => $policy->id, 'name' => 'QA207 수정', 'type' => 'custom', 'status' => 'inactive', 'content' => "수정 내용\n두 번째 줄"]);
        $this->assertSame('Platform default', PlatformRefundPolicy::currentContent());
    }

    public function test_other_vendors_cannot_read_update_copy_or_delete_the_policy(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $policy = $this->policy($other, ['name' => 'PRIVATE policy']);
        $this->actingAs($owner, 'admin')->get(route('channel.refund.get', $policy->id))->assertNotFound();
        foreach (['update', 'copy', 'delete'] as $action) {
            $this->postJson(route('channel.refund.'.$action, $policy->id), $this->payload())->assertNotFound();
        }
        $this->get(route('channel.refund.list'))->assertOk()->assertDontSee('PRIVATE policy');
        $this->assertDatabaseCount('shop_cancel_refund_policies', 1);
    }

    public function test_live_product_counts_protect_associated_policies_and_copies_do_not_move_products(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $policy = $this->policy($owner, ['product_count' => 999]);
        $product = $this->product($owner, $policy->id);
        $this->product($other, $policy->id);
        $this->actingAs($owner, 'admin')->get(route('channel.refund.list'))->assertOk()->assertViewHas('policies', fn ($rows) => (int) $rows->first()->linked_product_count === 1);
        $this->getJson(route('channel.refund.get', $policy->id))->assertOk()->assertJsonPath('policy.product_count', 1);
        $this->postJson(route('channel.refund.delete', $policy->id))->assertUnprocessable()->assertJsonValidationErrors('policy');
        $this->postJson(route('channel.refund.copy', $policy->id))->assertOk()->assertJsonPath('policy.status', 'inactive');
        $copy = ShopCancelRefundPolicy::latest('id')->first();
        $this->assertSame(0, (int) $copy->product_count);
        $this->assertSame($policy->id, $product->fresh()->cancel_refund_policy_id);
        $this->get(route('channel.refund.list'))->assertViewHas('policies', fn ($rows) => (int) $rows->first()->linked_product_count === 0);
        $this->postJson(route('channel.refund.delete', $copy->id))->assertOk()->assertJsonPath('status', 'success');
        $this->assertModelMissing($copy);
        $this->assertModelExists($policy);
    }

    public function test_repeated_copies_have_distinct_bounded_names_and_preserve_content_and_type(): void
    {
        $owner = $this->owner();
        $policy = $this->policy($owner, ['name' => str_repeat('안', 255)]);
        $this->actingAs($owner, 'admin');
        foreach (range(1, 2) as $n) {
            $this->post(route('channel.refund.copy', $policy->id))->assertRedirect();
        }
        $copies = ShopCancelRefundPolicy::where('id', '!=', $policy->id)->get();
        $this->assertCount(2, $copies->pluck('name')->unique());
        foreach ($copies as $copy) {
            $this->assertLessThanOrEqual(255, mb_strlen($copy->name));
            $this->assertSame($policy->content, $copy->content);
            $this->assertSame('default', $copy->type);
            $this->assertSame('inactive', $copy->status);
        }
    }

    public function test_invalid_values_are_rejected_and_empty_failed_inputs_are_not_replaced_with_stored_content(): void
    {
        $owner = $this->owner();
        $policy = $this->policy($owner, ['content' => 'ORIGINAL CONTENT']);
        $this->actingAs($owner, 'admin');
        foreach ([['name' => ' '], ['type' => 'bad'], ['status' => 'bad'], ['content' => ['bad']], ['name' => ['bad']], ['content' => str_repeat('가', 15001)]] as $invalid) {
            $this->postJson(route('channel.refund.store'), $this->payload($invalid))->assertUnprocessable();
        }
        $this->from(route('channel.refund.list'))->post(route('channel.refund.update', $policy->id), $this->payload(['form_key' => 'edit-'.$policy->id, 'name' => '', 'content' => '']))->assertSessionHasErrors('name');
        $response = $this->get(route('channel.refund.list'))->assertOk();
        $document = new \DOMDocument;
        $document->loadHTML($response->getContent(), LIBXML_NOERROR | LIBXML_NOWARNING);
        $xpath = new \DOMXPath($document);
        $this->assertSame('', $xpath->evaluate('string(//dialog[@id="refund-edit-'.$policy->id.'"]//textarea[@name="content"])'));
        $this->assertSame('', $xpath->evaluate('string(//dialog[@id="refund-edit-'.$policy->id.'"]//input[@name="name"]/@value)'));
        $this->assertSame('ORIGINAL CONTENT', $policy->fresh()->content);
        $this->from(route('channel.refund.list'))->post(route('channel.refund.store'), $this->payload(['form_key' => ['bad'], 'name' => ['bad']]))->assertSessionHasErrors();
        $this->get(route('channel.refund.list'))->assertOk();
    }

    public function test_editor_html_is_sanitized_and_preserves_supported_formats(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner, 'admin');
        $html = '<h2>검수 제목</h2><p><strong>굵게</strong> <em>기울임</em> <u>밑줄</u></p><ol><li>첫 항목</li><li>둘째 항목</li></ol><p><a href="https://example.com" onclick="bad()">안전 링크</a><a href="javascript:bad()">잘못된 링크</a></p><script>bad()</script><img src="x" onerror="bad()">';
        $this->postJson(route('channel.refund.store'), $this->payload(['content' => $html]))->assertOk();
        $policy = ShopCancelRefundPolicy::first();
        foreach (['<script', 'onclick', 'javascript:', '<img', 'onerror'] as $unsafe) {
            $this->assertStringNotContainsString($unsafe, $policy->content);
        }
        $this->get(route('channel.refund.get', $policy->id))->assertOk()->assertSee('<strong>굵게</strong>', false)->assertSee('<ol>', false)->assertSee('href="https://example.com"', false)->assertDontSee('javascript:', false);
        $this->post(route('channel.refund.update', $policy->id), $this->payload(['content' => '<p><br></p>']))->assertRedirect();
        $this->assertSame('', $policy->fresh()->content);
        $this->get(route('channel.refund.get', $policy->id))->assertOk()->assertSee('등록된 내용이 없습니다.');
    }

    public function test_legacy_plain_text_keeps_newlines_and_literal_symbols_in_editor(): void
    {
        $content = app(RefundPolicyContent::class);
        $plain = "기존 안내 & 문의\n\n구매 < 2개";
        $this->assertSame($plain, $content->forStorage($plain));
        $this->assertSame('<p>기존 안내 &amp; 문의</p><p><br></p><p>구매 &lt; 2개</p>', $content->editorHtml($plain));
        $this->assertStringContainsString('<br>', $content->render($plain));
    }

    public function test_search_and_pagination_are_stable_and_keep_the_search_filter(): void
    {
        $owner = $this->owner();
        $first = null;
        $last = null;
        foreach (range(1, 21) as $n) {
            $row = $this->policy($owner, ['name' => 'QA205 '.$n]);
            $first ??= $row;
            $last = $row;
        }
        $this->actingAs($owner, 'admin')->get(route('channel.refund.list', ['keyword' => 'QA205']))->assertOk()->assertViewHas('policies', fn ($rows) => $rows->total() === 21 && $rows->count() === 20 && $rows->first()->id === $last->id && str_contains($rows->url(2), 'keyword=QA205'));
        $this->get(route('channel.refund.list', ['keyword' => 'QA205', 'page' => 2]))->assertOk()->assertViewHas('policies', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $first->id);
        $this->get(route('channel.refund.list', ['keyword' => 'missing']))->assertOk()->assertSee('검색 조건에 맞는 취소/환불안내가 없습니다.');
    }

    public function test_product_selector_offers_only_active_policies_owned_by_the_seller(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $active = $this->policy($owner);
        $this->policy($owner, ['status' => 'inactive', 'name' => 'Inactive']);
        $this->policy($other, ['name' => 'Foreign']);
        $this->actingAs($owner, 'admin')->get(route('channel.product.base.create'))->assertOk()->assertViewHas('cancelRefundPolicies', fn ($rows) => $rows->pluck('id')->all() === [$active->id]);
    }

    public function test_product_page_renders_policy_formats_and_uses_platform_default_when_inactive(): void
    {
        config(['shop_channel.seed_demo_data' => false]);
        $owner = $this->owner();
        $policy = $this->policy($owner, ['content' => '<p><strong>상품 안내 강조</strong></p><ol><li>취소 접수</li><li>문의 확인</li></ol><script>bad()</script>']);
        $product = $this->product($owner, $policy->id);
        $shop = ShopChannel::forceCreate(['vendor_id' => $owner->vendor_id, 'channel_code' => 'qa208-rich', 'channel_name' => 'QA208', 'copyright' => 'QA', 'keywords' => [], 'status' => 1, 'is_public' => 1]);
        $listing = ShopChannelProduct::forceCreate(['shop_channel_id' => $shop->id, 'product_id' => $product->id, 'product_type' => 'own', 'approval_status' => 'approved', 'status' => 1, 'constraint_type' => 'none', 'stock' => 10, 'product_price' => 10000, 'selling_price' => 12000, 'profit' => 2000]);
        $this->withSession(['shop_channel_id' => $shop->id])->get(route('shop.product_details', $listing->id))->assertOk()->assertSee('<strong>상품 안내 강조</strong>', false)->assertSee('<ol>', false)->assertDontSee('<script>bad()</script>', false);
        PlatformRefundPolicy::forceCreate(['id' => 1, 'content' => '<p><em>공통 기본 안내</em></p>']);
        $policy->update(['status' => 'inactive']);
        $this->get(route('shop.product_details', $listing->id))->assertOk()->assertSee('<em>공통 기본 안내</em>', false)->assertDontSee('상품 안내 강조');
    }
}
