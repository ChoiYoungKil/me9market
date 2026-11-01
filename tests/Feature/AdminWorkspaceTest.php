<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\AdminActivity;
use App\Models\AdminNoticeComment;
use App\Models\Notice;
use App\Models\Order;
use App\Models\OrderClaim;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\User;
use App\Services\AdminNoticeContent;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class AdminWorkspaceTest extends TestCase
{
    use RefreshDatabase;

    private function administrator(string $type = 'admin'): Admin
    {
        return Admin::forceCreate(['type' => $type, 'vendor_id' => $type === 'vendor' ? 99 : 0,
            'name' => 'Workspace '.$type, 'email' => $type.'@workspace.example.test',
            'password' => Hash::make('WorkspaceTestOnly!'), 'mobile' => '01000000000', 'status' => 1, 'confirm' => 'Yes']);
    }

    private function product(int $vendor = 0): Product
    {
        return Product::create(['section_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'vendor_id' => $vendor,
            'admin_id' => 1, 'admin_type' => 'admin', 'product_name' => 'Workspace Product',
            'product_code' => 'WS'.uniqid(), 'product_color' => 'Black', 'product_price' => 10000,
            'product_discount' => 0, 'product_weight' => 1, 'status' => 1]);
    }

    private function orderItem(string $status = 'paid', int $vendor = 0, ?Order $order = null): OrdersProduct
    {
        if (! $order) {
            $user = User::factory()->create();
            $order = Order::forceCreate(['user_id' => $user->id, 'name' => 'Workspace Buyer', 'address' => 'QA address',
                'city' => 'Seoul', 'state' => 'Seoul', 'country' => 'Korea', 'pincode' => '04524',
                'mobile' => '01000000000', 'email' => $user->email, 'shipping_charges' => 0,
                'order_status' => 'Payment Captured', 'payment_method' => 'Card',
                'payment_gateway' => 'Me9 Mock Payment', 'grand_total' => 10000]);
        }
        $product = $this->product($vendor);

        return OrdersProduct::create(['order_id' => $order->id, 'user_id' => $order->user_id, 'vendor_id' => $vendor,
            'admin_id' => 1, 'product_id' => $product->id, 'product_code' => $product->product_code,
            'product_name' => $product->product_name, 'product_color' => 'Black', 'product_size' => '기본',
            'product_price' => 10000, 'selling_price' => 10000, 'supply_price' => 8000,
            'product_qty' => 1, 'line_total' => 10000, 'commission' => 0,
            'status_code' => $status, 'item_status' => OrderItemStatus::label($status), 'settlement_status' => 'pending']);
    }

    public function test_login_remembers_only_the_email_and_unchecked_login_forgets_it(): void
    {
        $admin = $this->administrator();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'WorkspaceTestOnly!', 'remember' => '1'])
            ->assertRedirect('/admin/dashboard')->assertCookie('me9_admin_login_id', $admin->email);
        auth('admin')->logout();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'WorkspaceTestOnly!'])
            ->assertRedirect('/admin/dashboard')->assertCookieExpired('me9_admin_login_id');
        auth('admin')->logout();
        $this->post('/admin/login', ['email' => $admin->email, 'password' => 'wrong', 'remember' => '1'])
            ->assertRedirect()->assertCookieMissing('me9_admin_login_id');
        $this->assertFalse(auth('admin')->check());
    }

    public function test_dashboard_reconciles_all_original_statuses_and_status_filter_matches(): void
    {
        $admin = $this->administrator();
        foreach (['delivered', 'cancelled', 'returned', 'exchanged', 'confirmed'] as $status) {
            $this->orderItem($status);
        }
        $replacement = $this->orderItem('shipping');
        $replacement->update(['is_exchange_replacement' => true]);
        $view = $this->actingAs($admin, 'admin')->get('/admin/dashboard')->assertOk();
        $this->assertSame(5, $view->viewData('totalItems'));
        $this->assertSame(5, array_sum($view->viewData('counts')));
        foreach (['delivered', 'cancelled', 'returned', 'exchanged', 'confirmed'] as $status) {
            $this->assertSame(1, $view->viewData('counts')[$status]);
            $this->get('/admin/orders?item_status='.$status)->assertOk()->assertViewHas('orders', fn ($orders) => $orders->total() === 1);
        }
    }

    public function test_product_pagination_preserves_filters_and_excel_treats_formula_as_text(): void
    {
        $admin = $this->administrator();
        for ($i = 0; $i < 11; $i++) {
            $this->product()->update(['product_name' => '=WS-filter-'.$i]);
        }
        $this->product()->update(['product_name' => 'Excluded']);
        $response = $this->actingAs($admin, 'admin')->get('/admin/products?keyword=WS-filter&per_page=10')->assertOk();
        $this->assertSame(11, $response->viewData('products')->total());
        $this->assertSame(10, $response->viewData('products')->count());
        $response->assertSee('keyword=WS-filter&amp;per_page=10&amp;page=2', false);
        $gallery = $this->get('/admin/products?keyword=WS-filter&view=gallery&per_page=10')->assertOk()
            ->assertSee('<h1>상품 갤러리</h1>', false)
            ->assertSee('admin-gallery', false)
            ->assertSee('keyword=WS-filter&amp;view=gallery&amp;per_page=10&amp;page=2', false);
        $dom = new \DOMDocument;
        @$dom->loadHTML('<?xml encoding="utf-8" ?>'.$gallery->getContent());
        $selected = (new \DOMXPath($dom))->query('//div[@id="l_menu"]//a[@aria-current="page"]');
        $this->assertCount(1, $selected);
        $this->assertSame('상품 갤러리', trim($selected->item(0)->textContent));
        $this->get('/admin/products?keyword=WS-filter&view=gallery&per_page=10&page=2')->assertOk()
            ->assertViewHas('products', fn ($products) => $products->currentPage() === 2 && $products->count() === 1);
        $file = $this->get('/admin/products?keyword=WS-filter&export=1')->assertOk()->streamedContent();
        $path = tempnam(sys_get_temp_dir(), 'admin-export-');
        file_put_contents($path, $file);
        try {
            $book = IOFactory::load($path);
            $this->assertSame(12, $book->getActiveSheet()->getHighestRow());
            $this->assertSame('s', $book->getActiveSheet()->getCell('B2')->getDataType());
            $this->assertStringStartsWith('=WS-filter-', $book->getActiveSheet()->getCell('B2')->getValue());
        } finally {
            @unlink($path);
        }
    }

    public function test_bulk_status_is_atomic_and_vendor_cannot_modify_global_products(): void
    {
        $admin = $this->administrator();
        $product = $this->product();
        $this->actingAs($admin, 'admin')->post('/admin/lists/products/bulk', ['ids' => [$product->id, 99999], 'status' => 0])
            ->assertSessionHasErrors('ids');
        $this->assertSame(1, (int) $product->fresh()->status);
        $this->post('/admin/lists/products/bulk', ['ids' => [$product->id], 'status' => 0])->assertSessionHasNoErrors();
        $this->assertSame(0, (int) $product->fresh()->status);
        $this->actingAs($this->administrator('vendor'), 'admin')->post('/admin/lists/products/bulk', ['ids' => [$product->id], 'status' => 1])->assertForbidden();
        $this->assertSame(0, (int) $product->fresh()->status);
    }

    public function test_notice_persists_editor_styles_and_attachment_and_audit_excludes_content_values(): void
    {
        $admin = $this->administrator();
        $response = $this->actingAs($admin, 'admin')->postJson('/admin/content/notices', [
            'title' => 'Private QA', 'content' => '<p style="color:#123456;font-size:18px"><strong>Private QA body</strong></p><script>alert(1)</script>',
            'status' => 0, 'attachment' => UploadedFile::fake()->createWithContent('qa.txt', 'QA download bytes'),
        ])->assertOk()->assertJsonPath('status', true);
        $notice = Notice::sole();
        try {
            $this->assertStringContainsString('color:#123456', $notice->content);
            $this->assertStringContainsString('font-size:18px', $notice->content);
            $this->assertStringNotContainsString('script', $notice->content);
            $this->get($response->json('redirect'))->assertOk()->assertSee('Private QA body')->assertSee('비공개');
            $this->get('/admin/content/notices/'.$notice->id.'/download')->assertDownload();
            $activity = AdminActivity::sole();
            $this->assertSame([$notice->id], $activity->metadata['ids']);
            $this->assertStringNotContainsString('Private QA body', json_encode($activity->metadata));
            $this->assertStringNotContainsString('QA download bytes', json_encode($activity->metadata));
        } finally {
            @unlink(public_path('admin/attachments/notices/'.$notice->attachment));
        }
    }

    public function test_empty_or_dangerous_only_notice_cannot_be_saved_and_failed_writes_are_not_audited(): void
    {
        $this->actingAs($this->administrator(), 'admin')->postJson('/admin/content/notices', [
            'title' => 'Invalid', 'content' => '<script>alert(1)</script><p><br></p>', 'status' => 0,
        ])->assertUnprocessable()->assertJsonValidationErrors('content');
        $this->assertDatabaseCount('notices', 0);
        $this->assertDatabaseCount('admin_activities', 0);
    }

    public function test_notice_comments_are_bound_to_the_correct_notice_and_pattern_routes_are_admin_only(): void
    {
        $notice = Notice::create(['title' => 'One', 'content' => 'One', 'status' => 0]);
        $other = Notice::create(['title' => 'Two', 'content' => 'Two', 'status' => 0]);
        $this->actingAs($this->administrator(), 'admin')->post('/admin/content/notices/'.$notice->id.'/comments', ['content' => 'Comment']);
        $comment = AdminNoticeComment::sole();
        $this->post('/admin/content/notices/'.$other->id.'/comments/'.$comment->id, ['content' => 'Tampered'])->assertNotFound();
        $this->assertSame('Comment', $comment->fresh()->content);
        $this->post('/admin/content/notices/'.$notice->id.'/comments/'.$comment->id, ['content' => 'Saved'])->assertRedirect();
        $this->assertSame('Saved', $comment->fresh()->content);
        $this->actingAs($this->administrator('vendor'), 'admin');
        foreach (['/admin/view', '/admin/sub02', '/admin/newpage', '/admin/content/notices/'.$notice->id] as $path) {
            $this->get($path)->assertForbidden();
        }
    }

    public function test_content_sanitizer_rejects_remote_and_script_images_and_preserves_safe_formatting(): void
    {
        $safe = '/admin/attachments/notices/12345678-1234-1234-1234-123456789abc.png';
        $clean = app(AdminNoticeContent::class)->clean('<p onclick="evil()" style="color:#aabbcc;position:fixed;background-image:url(https://evil.example)">Text</p>'
            .'<img src="javascript://localhost'.$safe.'" onerror="evil()"><img src="https://evil.example'.$safe.'"><img src="'.$safe.'">');
        $this->assertStringContainsString('color:#aabbcc', $clean);
        foreach (['onclick', 'onerror', 'javascript', 'evil.example', 'position', 'background-image'] as $forbidden) {
            $this->assertStringNotContainsString($forbidden, $clean);
        }
        $this->assertSame(1, substr_count($clean, '<img'));
    }

    public function test_ajax_order_panels_show_actual_shipping_and_hide_processing_on_finalized_items(): void
    {
        $item = $this->orderItem('confirmed');
        $this->actingAs($this->administrator(), 'admin')->get('/admin/orders/'.$item->order_id)->assertOk()->assertSee('Workspace Buyer');
        $path = '/admin/order-workspace/'.$item->order_id.'/panel/products';
        $this->withHeader('X-Requested-With', 'XMLHttpRequest')->get($path)->assertOk()->assertSee('배송정보를 변경할 수 없습니다')->assertDontSee('배송정보 적용');
        $this->get('/admin/order-workspace/'.$item->order_id.'/panel/unknown')->assertNotFound();
        $this->post('/admin/update-order-item-status', ['order_item_id' => $item->id, 'order_item_status' => 'shipping', 'item_courier_name' => 'QA', 'item_tracking_number' => 'QA'])
            ->assertSessionHasErrors();
        $this->assertSame('confirmed', $item->fresh()->normalized_status);
    }

    public function test_full_admin_can_request_and_complete_platform_vendor_zero_cancellation_without_switching_identity(): void
    {
        $item = $this->orderItem();
        $admin = $this->administrator();
        $base = '/admin/order-workspace/'.$item->order_id;
        $this->actingAs($admin, 'admin')->get($base.'/item/'.$item->id.'/cancel')->assertOk()->assertSee('value="'.$item->id.'"', false);
        $headers = ['X-Requested-With' => 'XMLHttpRequest'];
        $this->actingAs($admin, 'admin')->withHeaders($headers)->postJson($base.'/cancel', ['item_ids' => [$item->id], 'reason' => 'QA', 'detail_reason' => 'QA cancellation'])
            ->assertOk()->assertJsonPath('status', true);
        $this->assertSame('cancel_requested', $item->fresh()->normalized_status);
        $this->postJson($base.'/action', ['item_ids' => [$item->id], 'action' => 'cancel_approve'])->assertOk()->assertJsonPath('status', true);
        $this->assertSame('cancelled', $item->fresh()->normalized_status);
        $this->assertSame('completed', OrderClaim::sole()->status);
        $this->assertSame($admin->id, auth('admin')->id());
        $this->assertDatabaseHas('orders_logs', ['order_item_id' => $item->id, 'actor' => $admin->name]);
        $this->assertSame(2, AdminActivity::count());
        $this->postJson($base.'/action', ['item_ids' => [$item->id], 'action' => 'cancel_approve'])->assertUnprocessable();
        $this->assertSame(2, AdminActivity::count());
    }

    public function test_claim_processing_rejects_cross_order_and_mixed_vendor_payloads_atomically(): void
    {
        $one = $this->orderItem();
        $two = $this->orderItem();
        $mixed = $this->orderItem('paid', 99, $one->order);
        $this->actingAs($this->administrator(), 'admin')->withHeader('X-Requested-With', 'XMLHttpRequest');
        $this->postJson('/admin/order-workspace/'.$one->order_id.'/cancel', ['item_ids' => [$two->id], 'reason' => 'QA', 'detail_reason' => 'QA'])->assertNotFound();
        $this->postJson('/admin/order-workspace/'.$one->order_id.'/cancel', ['item_ids' => [$one->id, $mixed->id], 'reason' => 'QA', 'detail_reason' => 'QA'])->assertUnprocessable();
        $this->assertSame('paid', $one->fresh()->normalized_status);
        $this->assertSame('paid', $mixed->fresh()->normalized_status);
        $this->assertDatabaseCount('order_claims', 0);
    }

    public function test_admin_return_hold_release_and_exchange_replacement_use_the_shared_lifecycle(): void
    {
        $returned = $this->orderItem('delivered');
        $exchange = $this->orderItem('delivered', 0, $returned->order);
        $this->actingAs($this->administrator(), 'admin')->withHeader('X-Requested-With', 'XMLHttpRequest');
        $base = '/admin/order-workspace/'.$returned->order_id;
        $pickup = ['reason' => 'QA', 'detail_reason' => 'QA flow', 'pickup_method' => 'automatic',
            'return_address' => 'QA address', 'pickup_name' => 'QA', 'pickup_mobile' => '01000000000'];
        $this->postJson($base.'/return', ['item_ids' => [$returned->id]] + $pickup)->assertOk()->assertJsonPath('status', true);
        foreach (['return_receive', 'return_hold', 'return_release', 'return_complete'] as $action) {
            $this->postJson($base.'/action', ['item_ids' => [$returned->id], 'action' => $action, 'reason' => 'QA hold'])
                ->assertOk()->assertJsonPath('status', true);
        }
        $this->assertSame('returned', $returned->fresh()->normalized_status);
        $this->postJson($base.'/exchange', ['item_ids' => [$exchange->id]] + $pickup)->assertOk()->assertJsonPath('status', true);
        foreach (['exchange_approve', 'exchange_receive', 'exchange_hold_after', 'exchange_release_after', 'exchange_complete'] as $action) {
            $this->postJson($base.'/action', ['item_ids' => [$exchange->id], 'action' => $action, 'reason' => 'QA hold'])
                ->assertOk()->assertJsonPath('status', true);
        }
        $this->assertSame('exchanged', $exchange->fresh()->normalized_status);
        $replacement = $exchange->fresh()->exchangeReplacement;
        $this->assertNotNull($replacement);
        $this->assertSame('ready_to_ship', $replacement->normalized_status);
        $this->assertEquals(0, $replacement->line_total);
        $this->assertSame('excluded_exchange_replacement', $replacement->settlement_status);
        $this->postJson($base.'/action', ['item_ids' => [$exchange->id], 'action' => 'exchange_complete'])->assertUnprocessable();
        $this->assertSame(1, OrdersProduct::where('replacement_for_order_product_id', $exchange->id)->count());
    }

    public function test_activity_detail_records_actual_product_changes_and_is_admin_only(): void
    {
        $admin = $this->administrator();
        $product = $this->product();
        $this->actingAs($admin, 'admin')->post('/admin/lists/products/bulk', ['ids' => [$product->id], 'status' => 0])->assertRedirect();
        $activity = AdminActivity::sole();
        $this->assertSame('success', $activity->metadata['result']);
        $this->assertSame('일괄 처리', $activity->metadata['mode']);
        $change = $activity->metadata['changes'][0];
        $this->assertSame($product->id, $change['id']);
        $this->assertSame('Workspace Product', $change['name']);
        $field = collect($change['fields'])->firstWhere('field', 'status');
        $this->assertSame('활성', $field['before']);
        $this->assertSame('비활성', $field['after']);
        $this->get('/admin/activities/'.$activity->id)->assertOk()->assertSee('Workspace Product')->assertSee('활성')->assertSee('비활성');
        $this->get('/admin/activities?keyword=Workspace Product')->assertOk()->assertViewHas('entries', fn ($entries) => $entries->total() === 1);
        $this->actingAs($this->administrator('vendor'), 'admin')->get('/admin/activities/'.$activity->id)->assertForbidden();
    }

    public function test_notice_audit_explains_title_and_body_changes_without_storing_the_full_body(): void
    {
        $notice = Notice::create(['title' => 'Before QA', 'content' => 'Private old body', 'status' => 0]);
        $this->actingAs($this->administrator(), 'admin')->postJson('/admin/content/notices/'.$notice->id, ['title' => 'After QA', 'content' => 'Private new body', 'status' => 0])->assertOk();
        $activity = AdminActivity::sole();
        $fields = collect($activity->metadata['changes'][0]['fields'])->keyBy('field');
        $this->assertSame('Before QA', $fields['title']['before']);
        $this->assertSame('After QA', $fields['title']['after']);
        $this->assertSame('내용 수정', $fields['content']['after']);
        $this->assertStringNotContainsString('Private old body', json_encode($activity->metadata));
        $this->assertStringNotContainsString('Private new body', json_encode($activity->metadata));
        $this->get('/admin/activities/'.$activity->id)->assertOk()->assertSee('Before QA')->assertSee('After QA')->assertSee('내용 수정');
    }

    public function test_rolled_back_claim_changes_do_not_leak_into_the_next_successful_audit_record(): void
    {
        $one = $this->orderItem('paid');
        $two = $this->orderItem('delivered', 0, $one->order);
        $this->actingAs($this->administrator(), 'admin')->withHeader('X-Requested-With', 'XMLHttpRequest');
        $this->postJson('/admin/order-workspace/'.$one->order_id.'/cancel', ['item_ids' => [$one->id, $two->id], 'reason' => 'QA', 'detail_reason' => 'Rollback QA'])->assertUnprocessable();
        $this->assertSame('paid', $one->fresh()->normalized_status);
        $this->assertDatabaseCount('admin_activities', 0);
        $this->postJson('/admin/content/notices', ['title' => 'Next QA', 'content' => 'Body', 'status' => 0])->assertOk();
        $changes = AdminActivity::sole()->metadata['changes'];
        $this->assertCount(1, $changes);
        $this->assertSame('notices', $changes[0]['table']);
    }

    public function test_old_activity_records_explicitly_show_the_unavailable_before_and_after_values(): void
    {
        $admin = $this->administrator();
        $entry = AdminActivity::create(['admin_id' => $admin->id, 'actor' => $admin->name, 'action' => 'legacy', 'method' => 'POST', 'path' => 'admin/old', 'metadata' => ['ids' => [1], 'fields' => ['status']]]);
        $this->actingAs($admin, 'admin')->get('/admin/activities/'.$entry->id)->assertOk()->assertSee('변경 전·후 값이 수집되지 않았습니다')->assertSee('요청 기록');
    }
    public function test_default_refund_editor_sanitizes_markup_and_rejects_empty_content(): void
    {
        $this->actingAs($this->administrator(), 'admin');
        \App\Models\PlatformRefundPolicy::create(['id'=>99,'content'=>'Other record']);
        $this->post('/admin/refund-policies/default', ['content'=>'<p><strong>환불 안내</strong><script>alert(1)</script></p><ul><li>내용</li></ul>'])->assertRedirect();
        $content=\App\Models\PlatformRefundPolicy::currentContent();
        $this->assertStringContainsString('<strong>', $content);
        $this->assertStringNotContainsString('<script', $content);
        $this->post('/admin/refund-policies/default', ['content'=>'<p><br></p>'])->assertSessionHasErrors('content');
        $this->assertSame($content, \App\Models\PlatformRefundPolicy::currentContent());
    }
}
