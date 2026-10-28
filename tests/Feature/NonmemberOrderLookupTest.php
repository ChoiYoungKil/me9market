<?php

namespace Tests\Feature;

use App\Models\Order;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NonmemberOrderLookupTest extends TestCase
{
    use RefreshDatabase;

    private function order(array $attributes = []): Order
    {
        $order = new Order;
        $order->forceFill(array_merge([
            'user_id' => 0,
            'name' => '수령인',
            'buyer_name' => '주문자',
            'buyer_mobile' => '010-7777-8888',
            'mobile' => '010-1111-2222',
            'email' => 'lookup@example.test',
            'address' => '검수용 주소',
            'city' => 'Seoul',
            'state' => 'Seoul',
            'country' => 'Korea',
            'pincode' => '04524',
            'shipping_charges' => 0,
            'coupon_code' => '',
            'coupon_amount' => 0,
            'order_status' => 'Payment Captured',
            'payment_method' => 'Card',
            'payment_gateway' => 'Test',
            'grand_total' => 12000,
        ], $attributes))->save();

        return $order;
    }

    private function payload(Order $order): array
    {
        return ['order_id' => 'Me9-'.str_pad($order->id, 8, '0', STR_PAD_LEFT),
            'phone_prefix' => '010', 'phone_middle' => '7777', 'phone_last' => '8888'];
    }

    public function test_lookup_page_is_public_and_does_not_create_orders(): void
    {
        $this->get('/nonmember/order/check')->assertOk()
            ->assertSee('주문코드')->assertSee('주문자 연락처')
            ->assertSee('name="phone_prefix"', false)->assertSee('name="phone_middle"', false)
            ->assertSee('name="phone_last"', false);
        $this->assertDatabaseCount('orders', 0);
        $this->get('/nonmember/order/details')->assertRedirect('/nonmember/order/check');
    }

    public function test_split_phone_uses_buyer_phone_and_opens_only_the_matched_order(): void
    {
        $order = $this->order(['name' => '조회 대상 수령인']);
        $this->order(['name' => '다른 주문 수령인']);
        $this->post('/nonmember/order/check', $this->payload($order))
            ->assertRedirect('/nonmember/order/details')->assertSessionHas('nonmember_order_id', $order->id);
        $this->get('/nonmember/order/details')->assertOk()->assertSee('조회 대상 수령인')
            ->assertDontSee('다른 주문 수령인')->assertHeader('Cache-Control', 'no-store, private');
        $this->assertGuest();
    }

    public function test_recipient_phone_is_not_accepted_in_place_of_buyer_phone(): void
    {
        $order = $this->order();
        $this->from('/nonmember/order/check')->post('/nonmember/order/check', array_merge($this->payload($order), [
            'phone_middle' => '1111', 'phone_last' => '2222',
        ]))->assertRedirect('/nonmember/order/check')->assertSessionHasErrors('order_id')
            ->assertSessionMissing('nonmember_order_id')
            ->assertSessionHasInput('phone_middle', '1111')->assertSessionHasInput('phone_last', '2222');
    }

    public function test_legacy_order_codes_and_phone_format_remain_usable(): void
    {
        $order = $this->order(['buyer_mobile' => null]);
        foreach ([(string) $order->id, 'Me9-000'.$order->id, 'me9-shop-000'.$order->id] as $code) {
            $this->post('/nonmember/order/check', ['order_id' => $code, 'phone' => '010-1111-2222'])
                ->assertRedirect('/nonmember/order/details')->assertSessionHas('nonmember_order_id', $order->id);
        }
    }

    public function test_failed_new_lookup_revokes_previous_verification_and_retains_input(): void
    {
        $order = $this->order();
        $this->withSession(['nonmember_order_id' => $order->id])->from('/nonmember/order/check')
            ->post('/nonmember/order/check', array_merge($this->payload($order), ['phone_last' => '9999']))
            ->assertSessionHasErrors('order_id')->assertSessionMissing('nonmember_order_id')
            ->assertSessionHasInput('order_id', $this->payload($order)['order_id']);
        $this->get('/nonmember/order/details')->assertRedirect('/nonmember/order/check');
    }

    public function test_malformed_codes_and_phone_values_cannot_reuse_verified_access(): void
    {
        $order = $this->order();
        foreach ([['order_id' => 'arbitrary-'.$order->id], ['order_id' => ['id' => $order->id]],
            ['phone_middle' => '77a7'], ['phone_last' => '123'], ['phone_prefix' => '999'],
            ['phone_middle' => '777']] as $invalid) {
            $field = array_key_first($invalid);
            $this->withSession(['nonmember_order_id' => $order->id])
                ->post('/nonmember/order/check', array_merge($this->payload($order), $invalid))
                ->assertSessionHasErrors($field)->assertSessionMissing('nonmember_order_id');
            $this->get('/nonmember/order/check')->assertOk()->assertSee('role="alert"', false);
        }
    }

    public function test_unknown_order_and_wrong_phone_return_the_same_lookup_error(): void
    {
        $order = $this->order();
        $message = '주문코드와 주문자 연락처가 일치하지 않습니다. 입력한 정보를 확인해 주세요.';
        foreach ([['order_id' => 'Me9-999999999'], ['phone_last' => '9999']] as $invalid) {
            $this->post('/nonmember/order/check', array_merge($this->payload($order), $invalid))
                ->assertSessionHasErrors(['order_id' => $message])->assertSessionMissing('nonmember_order_id');
        }
    }

    public function test_missing_required_values_and_deleted_order_are_handled(): void
    {
        $this->post('/nonmember/order/check', [])->assertSessionHasErrors(['order_id', 'phone']);
        $order = $this->order();
        $id = $order->id;
        $order->delete();
        $this->withSession(['nonmember_order_id' => $id])->get('/nonmember/order/details')
            ->assertRedirect('/nonmember/order/check')->assertSessionMissing('nonmember_order_id');
    }
}
