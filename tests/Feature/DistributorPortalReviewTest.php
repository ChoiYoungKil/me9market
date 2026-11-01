<?php

namespace Tests\Feature;

use App\Models\Distributor;
use App\Models\Order;
use App\Models\OrdersProduct;
use App\Models\Product;
use App\Models\ShopChannel;
use App\Models\Vendor;
use App\Support\OrderItemStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class DistributorPortalReviewTest extends TestCase
{
    use RefreshDatabase;

    private function fixture(string $suffix = 'one', string $status = 'paid'): array
    {
        $v = Vendor::create(['name' => 'QA vendor '.$suffix, 'mobile' => '01000000000', 'email' => $suffix.'@example.test', 'status' => 1]);
        $d = Distributor::create(['vendor_id' => $v->id, 'name' => 'Manager '.$suffix, 'email' => 'manager-'.$suffix.'@example.test', 'password' => bcrypt('password'), 'status' => 1]);
        $s = ShopChannel::create(['vendor_id' => $v->id, 'channel_code' => 'dist-'.$suffix, 'channel_name' => 'Shop '.$suffix, 'status' => 1, 'keywords' => [], 'settlement_type' => 1, 'settlement_rate' => 0, 'copyright' => 'QA']);
        $p = Product::create(['section_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'admin_id' => 1, 'admin_type' => 'vendor', 'vendor_id' => $v->id, 'product_name' => 'Item '.$suffix, 'product_code' => 'QA-'.$suffix, 'product_price' => 10000, 'product_discount' => 0, 'product_color' => 'Black', 'product_weight' => 1, 'status' => 1]);
        $o = Order::forceCreate(['user_id' => 0, 'name' => 'Original recipient', 'address' => 'Original address', 'address_detail' => 'Original detail', 'city' => 'Seoul', 'state' => 'Seoul', 'country' => 'Korea', 'pincode' => '04524', 'mobile' => '01000000000', 'email' => 'qa@example.test', 'shipping_charges' => 0, 'order_status' => 'Payment Captured', 'payment_method' => 'Card', 'payment_gateway' => 'Me9 Mock Payment', 'grand_total' => 10000]);
        $i = OrdersProduct::create(['order_id' => $o->id, 'user_id' => 0, 'vendor_id' => $v->id, 'shop_channel_id' => $s->id, 'distributor_id' => $d->id, 'admin_id' => 1, 'product_id' => $p->id, 'product_name' => $p->product_name, 'product_code' => $p->product_code, 'product_color' => 'Black', 'product_size' => 'Basic', 'product_price' => 10000, 'product_qty' => 1, 'line_total' => 10000, 'status_code' => $status, 'item_status' => OrderItemStatus::label($status)]);

        return [$d, $i, $o];
    }

    private function csv(string $body): UploadedFile
    {
        return UploadedFile::fake()->createWithContent('invoice.csv', "\xEF\xBB\xBF주문상품ID,택배사,송장번호\n".$body);
    }

    public function test_login_checks_name_password_active_period_and_unauthenticated_access(): void
    {
        [$d] = $this->fixture();
        foreach (['/distributor/orders/pending', '/distributor/orders/completed', '/distributor/orders/1'] as $url) {
            $this->get($url)->assertRedirect(route('distributor.login'));
        }
        $this->post(route('distributor.login.submit'), ['email' => $d->email, 'name' => $d->name, 'password' => 'wrong'])->assertSessionHas('flash_message_error')->assertSessionMissing('distributor_id');
        $this->post(route('distributor.login.submit'), ['email' => $d->email, 'name' => 'Wrong', 'password' => 'password'])->assertSessionHas('flash_message_error')->assertSessionMissing('distributor_id');
        $d->update(['access_ended_at' => now()->subMinute()]);
        $this->post(route('distributor.login.submit'), ['email' => $d->email, 'name' => $d->name, 'password' => 'password'])->assertSessionMissing('distributor_id');
        $d->update(['access_ended_at' => now()->addDay()]);
        $this->post(route('distributor.login.submit'), ['email' => $d->email, 'name' => $d->name, 'password' => 'password'])->assertRedirect(route('distributor.orders.pending'))->assertSessionHas('distributor_id', $d->id);
    }

    public function test_pending_completed_filters_and_exports_use_same_scope_and_canonical_status(): void
    {
        [$d,$i] = $this->fixture();
        [$other,$hidden] = $this->fixture('hidden');
        $cancel = $i->replicate();
        $cancel->status_code = 'cancelled';
        $cancel->item_status = 'New';
        $cancel->tracking_number = 'old';
        $cancel->save();
        $shipping = $i->replicate();
        $shipping->status_code = 'shipping';
        $shipping->item_status = 'New';
        $shipping->tracking_number = '001234567890';
        $shipping->shipped_at = now();
        $shipping->save();
        $this->withSession(['distributor_id' => $d->id]);
        $r = $this->get('/distributor/orders/pending')->assertOk();
        $this->assertSame([$i->id], $r->viewData('orders')->pluck('id')->all());
        $r = $this->get('/distributor/orders/completed')->assertOk();
        $this->assertSame([$shipping->id], $r->viewData('orders')->pluck('id')->all());
        foreach (['product_name' => 'Item one', 'product_code' => 'QA-one', 'channel_name' => 'Shop one', 'receiver' => 'Original recipient', 'order_id' => 'Me9-Shop-'.str_pad($i->order_id, 7, '0', STR_PAD_LEFT)] as $field => $keyword) {
            $r = $this->get('/distributor/orders/pending?'.http_build_query(['search_type' => $field, 'keyword' => $keyword]))->assertOk();
            $this->assertSame(1, $r->viewData('orders')->total());
        }
        $invalid = $this->get('/distributor/orders/pending?search_type=order_id&keyword=invalid')->assertOk();
        $this->assertSame(0, $invalid->viewData('orders')->total());
        $this->get('/distributor/orders/pending?to=2026-11-01')->assertOk();
        $r = $this->get('/distributor/orders/pending?keyword=does-not-exist')->assertOk();
        $this->assertSame(0, $r->viewData('orders')->total());
        $this->getJson('/distributor/orders/pending?from=2026-11-02&to=2026-11-01')->assertUnprocessable();
        $r = $this->get('/distributor/orders/pending?export=1')->assertDownload();
        $file = tempnam(sys_get_temp_dir(), 'dist-export');
        file_put_contents($file, $r->streamedContent());
        $book = IOFactory::load($file);
        $sheet = $book->getActiveSheet();
        $this->assertSame(2, $sheet->getHighestRow());
        $this->assertSame((string) $i->id, $sheet->getCell('A2')->getValue());
        $this->assertSame('n', $sheet->getCell('H2')->getDataType());
        $this->assertSame('s', $sheet->getCell('J2')->getDataType());
        $this->assertSame('04524', $sheet->getCell('J2')->getValue());
        $book->disconnectWorksheets();
        unlink($file);
        $r = $this->get('/distributor/orders/completed?export=1')->assertDownload();
        $file = tempnam(sys_get_temp_dir(), 'dist-export');
        file_put_contents($file, $r->streamedContent());
        $book = IOFactory::load($file);
        $this->assertSame('001234567890', $book->getActiveSheet()->getCell('O2')->getValue());
        $book->disconnectWorksheets();
        unlink($file);
    }

    public function test_edit_uses_item_recipient_without_changing_order_or_sibling_and_lock_after_delivery(): void
    {
        [$d,$i,$o] = $this->fixture();
        $sibling = $i->replicate();
        $sibling->save();
        $this->withSession(['distributor_id' => $d->id]);
        $this->post(route('distributor.order.update', $i->id), ['receiver' => 'Corrected', 'zipcode' => '03925', 'address' => 'Corrected address', 'address_detail' => 'Room 100', 'status_code' => 'ready_to_ship'])->assertSessionHasNoErrors();
        $this->assertSame('Original address', $o->fresh()->address);
        $this->assertNull($sibling->fresh()->recipient_address);
        $this->assertSame('Corrected address', $i->fresh()->recipient_address);
        $this->get(route('distributor.order.details', $i->id))->assertOk()->assertSee('Room 100')->assertSee('Corrected');
        $this->postJson(route('distributor.order.update', $i->id), ['status_code' => 'shipping'])->assertUnprocessable();
        $this->post(route('distributor.order.update', $i->id), ['courier' => 'CJ', 'tracking_no' => '0000123', 'status_code' => 'shipping'])->assertSessionHasNoErrors();
        $this->post(route('distributor.order.update', $i->id), ['tracking_no' => '0000456', 'status_code' => 'shipping'])->assertSessionHasNoErrors();
        $this->post(route('distributor.order.update', $i->id), ['status_code' => 'delivered'])->assertSessionHasNoErrors();
        $this->postJson(route('distributor.order.update', $i->id), ['courier' => 'Forged', 'tracking_no' => 'FORGED', 'status_code' => 'delivered'])->assertUnprocessable();
        $this->get(route('distributor.order.details', $i->id).'?edit=1')->assertOk()->assertDontSee('name="tracking_no"', false)->assertSee('정보가 확정되었습니다');
        $this->assertSame('0000456', $i->fresh()->tracking_number);
        $this->assertDatabaseCount('orders_logs', 4);
    }

    public function test_invoice_import_is_atomic_for_foreign_ids_duplicate_ids_and_completed_orders(): void
    {
        [$d,$i] = $this->fixture();
        [, $other] = $this->fixture('other');
        $this->withSession(['distributor_id' => $d->id]);
        $url = route('distributor.orders.upload_invoice');
        foreach (["{$i->id},CJ,001234\n{$other->id},CJ,001235\n", "{$i->id},CJ,001234\n{$i->id},CJ,001235\n", "{$i->id},CJ,001234\n999999,,\n"] as $data) {
            $this->postJson($url, ['invoice_file' => $this->csv($data)])->assertUnprocessable();
            $this->assertSame('paid', $i->fresh()->status_code);
            $this->assertNull($i->fresh()->tracking_number);
        }
        $this->post($url, ['invoice_file' => $this->csv("{$i->id},CJ,001234567890\n")])->assertRedirect(route('distributor.orders.completed'))->assertSessionHasNoErrors();
        $this->assertSame('001234567890', $i->fresh()->tracking_number);
        $this->assertSame('shipping', $i->fresh()->status_code);
        $this->assertNotNull($i->fresh()->shipped_at);
        $this->postJson($url, ['invoice_file' => $this->csv("{$i->id},CJ,FORGED\n")])->assertUnprocessable();
        $this->assertSame('001234567890', $i->fresh()->tracking_number);
        $this->assertDatabaseCount('orders_logs', 1);
    }

    public function test_invoice_rejects_missing_duplicate_headers_formulas_and_unsupported_content(): void
    {
        [$d,$i] = $this->fixture();
        $this->withSession(['distributor_id' => $d->id]);
        $url = route('distributor.orders.upload_invoice');
        foreach (['wrong.csv' => "id,courier\n1,CJ", 'duplicate.csv' => "id,id,courier,tracking_no\n1,1,CJ,123", 'bad.xlsx' => 'not a zip', 'script.txt' => 'not allowed', 'formula.csv' => "id,courier,tracking_no\n{$i->id},CJ,=1+1", 'empty.csv' => "id,courier,tracking_no\n"] as $name => $content) {
            $this->postJson($url, ['invoice_file' => UploadedFile::fake()->createWithContent($name, $content)])->assertUnprocessable()->assertJsonValidationErrors('invoice_file');
        }
        $this->assertSame('paid', $i->fresh()->status_code);
    }

    public function test_sparse_xlsx_and_rich_text_header_preserves_tracking_string(): void
    {
        [$d,$i] = $this->fixture();
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->setCellValue('A1', 'order_item_id');
        $sheet->setCellValue('C1', 'courier_name');
        $sheet->setCellValue('E1', 'tracking_number');
        $sheet->setCellValue('A2', $i->id);
        $sheet->setCellValue('C2', 'CJ');
        $sheet->setCellValueExplicit('E2', '000987654321', 's');
        $path = tempnam(sys_get_temp_dir(), 'invoice');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        $this->withSession(['distributor_id' => $d->id])->post(route('distributor.orders.upload_invoice'), ['invoice_file' => new UploadedFile($path, 'invoice.xlsx', null, null, true)])->assertSessionHasNoErrors()->assertRedirect(route('distributor.orders.completed'));
        $this->assertSame('000987654321', $i->fresh()->tracking_number);
        unlink($path);
    }

    public function test_invoice_limits_and_xlsx_formulas_are_rejected_without_shipping(): void
    {
        [$d,$i] = $this->fixture();
        $this->withSession(['distributor_id' => $d->id]);
        $long = '';
        for ($n = 0; $n < 2001; $n++) {
            $long .= "{$i->id},CJ,123\n";
        }
        $this->postJson(route('distributor.orders.upload_invoice'), ['invoice_file' => $this->csv($long)])->assertUnprocessable()->assertJsonValidationErrors('invoice_file');
        $this->postJson(route('distributor.orders.upload_invoice'), ['invoice_file' => UploadedFile::fake()->create('oversize.csv', 5121)])->assertUnprocessable()->assertJsonValidationErrors('invoice_file');
        $book = new Spreadsheet;
        $sheet = $book->getActiveSheet();
        $sheet->fromArray([['id', 'courier', 'tracking_no'], [$i->id, 'CJ', '=1+1']]);
        $path = tempnam(sys_get_temp_dir(), 'formula');
        (new Xlsx($book))->save($path);
        $book->disconnectWorksheets();
        $this->postJson(route('distributor.orders.upload_invoice'),['invoice_file' => new UploadedFile($path,'formula.xlsx',null,null,true)])->assertUnprocessable()->assertJsonValidationErrors('invoice_file');
        unlink($path);
        $this->assertSame('paid',$i->fresh()->status_code);
        $this->assertDatabaseCount('orders_logs',0);
    }
}
