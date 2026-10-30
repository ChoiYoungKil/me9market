<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ChannelDeliveryCharge;
use App\Models\Product;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelDeliveryManagementTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $key = 'one'): Admin
    {
        $vendor = Vendor::forceCreate(['name' => 'QA '.$key, 'email' => $key.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);

        return Admin::forceCreate(['name' => 'Owner '.$key, 'type' => 'vendor', 'vendor_id' => $vendor->id, 'email' => $key.'@admin.test', 'mobile' => '01000000000', 'password' => bcrypt('OwnerOnly1234'), 'status' => 1, 'confirm' => 'Yes']);
    }

    private function payload(array $extra = []): array
    {
        return array_replace(['status' => 1, 'name' => 'QA194 shipping', 'courier' => '한진택배', 'shipping_type' => 'free', 'payment_type' => 'prepaid', 'base_fee' => 3000, 'memo' => 'QA203 memo'], $extra);
    }

    private function product(Admin $owner, array $extra = []): Product
    {
        return Product::forceCreate(array_replace(['section_id' => 1, 'category_id' => 1, 'brand_id' => 1, 'vendor_id' => $owner->vendor_id, 'admin_id' => $owner->id, 'admin_type' => 'vendor', 'product_name' => 'QA193 product', 'product_code' => 'QA193-'.uniqid(), 'product_color' => 'Black', 'product_price' => 10000, 'product_discount' => 0, 'product_weight' => 100, 'status' => 1], $extra));
    }

    public function test_all_five_policy_types_round_trip_and_ignore_inactive_conditions(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner, 'admin');
        $cases = [
            ['shipping_type' => 'free', 'base_fee' => 9000, 'free_order_amount' => 30000],
            ['shipping_type' => 'conditional', 'amount_condition_enabled' => 1, 'free_order_amount' => 30000, 'quantity_condition_enabled' => 0, 'free_order_quantity' => 3],
            ['shipping_type' => 'fixed', 'fixed_fee' => 4000],
            ['shipping_type' => 'variable', 'calculation_rules' => ['basis' => 'quantity', 'repeat_every' => 2, 'repeat_fee' => 500, 'start_at_one' => 1]],
            ['shipping_type' => 'tiered', 'calculation_rules' => ['basis' => 'amount', 'tiers' => [['min' => 0, 'max' => 30000, 'fee' => 3000], ['min' => 30000, 'max' => null, 'fee' => 0]]]],
        ];
        foreach ($cases as $case) {
            $this->post('/channel/settings/delivery/store', $this->payload($case))->assertRedirect(route('channel.delivery.list'));
            $charge = ChannelDeliveryCharge::latest('id')->first();
            $this->assertSame($case['shipping_type'], $charge->shipping_type);
            $this->get(route('channel.delivery.show', $charge->id))->assertOk()->assertSee('QA194 shipping')->assertSee('QA203 memo');
            if ($charge->shipping_type === 'free') {
                $this->assertSame(0, $charge->base_fee);
            }
            if ($charge->shipping_type === 'conditional') {
                $this->assertNull($charge->free_order_quantity);
            }
            if ($charge->shipping_type === 'fixed') {
                $this->assertSame(4000, $charge->base_fee);
            }
        }
        $this->assertDatabaseCount('channel_delivery_charges', 5);
    }

    public function test_real_product_counts_copy_and_rename_preserve_existing_associations(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $charge = ChannelDeliveryCharge::create($this->payload() + ['vendor_id' => $owner->vendor_id, 'product_count' => 999]);
        $first = $this->product($owner, ['shipping_policy_id' => $charge->id, 'shipping_policy_name' => $charge->name]);
        $legacy = $this->product($owner, ['shipping_policy_name' => $charge->name]);
        $this->product($other, ['shipping_policy_id' => $charge->id]);
        $this->actingAs($owner, 'admin')->get(route('channel.delivery.list'))->assertOk()->assertViewHas('charges', fn ($rows) => (int) $rows->first()->linked_product_count === 2);
        $this->post(route('channel.delivery.copy', $charge->id))->assertRedirect();
        $copy = ChannelDeliveryCharge::latest('id')->first();
        $this->assertSame('QA194 shipping (복사)', $copy->name);
        $this->assertSame(0, $copy->product_count);
        $this->assertSame($charge->calculation_rules, $copy->calculation_rules);
        $this->get(route('channel.delivery.list'))->assertViewHas('charges', fn ($rows) => (int) $rows->first()->linked_product_count === 0);
        $this->post(route('channel.delivery.copy', $charge->id))->assertRedirect();
        $this->assertSame('QA194 shipping (복사 2)', ChannelDeliveryCharge::latest('id')->value('name'));
        $this->post(route('channel.delivery.update', $charge->id), $this->payload(['name' => 'QA203 renamed']))->assertRedirect();
        foreach ([$first, $legacy] as $product) {
            $this->assertSame($charge->id, $product->fresh()->shipping_policy_id);
            $this->assertSame('QA203 renamed', $product->fresh()->shipping_policy_name);
        }
        $this->postJson(route('channel.delivery.delete', $charge->id))->assertUnprocessable()->assertJsonValidationErrors('shipping');
        $this->post(route('channel.delivery.delete', $copy->id))->assertRedirect();
        $this->assertDatabaseMissing('channel_delivery_charges', ['id' => $copy->id]);
    }

    public function test_ambiguous_legacy_links_are_not_silently_reassigned_on_rename(): void
    {
        $owner = $this->owner();
        $charge = ChannelDeliveryCharge::create($this->payload() + ['vendor_id' => $owner->vendor_id]);
        ChannelDeliveryCharge::create($this->payload() + ['vendor_id' => $owner->vendor_id]);
        $legacy = $this->product($owner, ['shipping_policy_name' => $charge->name]);
        $this->actingAs($owner, 'admin')->postJson(route('channel.delivery.update', $charge->id), $this->payload(['name' => 'Renamed']))->assertUnprocessable()->assertJsonValidationErrors('name');
        $this->assertNull($legacy->fresh()->shipping_policy_id);
        $this->assertSame('QA194 shipping', $charge->fresh()->name);
    }

    public function test_foreign_vendor_cannot_view_modify_copy_or_delete_a_policy(): void
    {
        $owner = $this->owner();
        $other = $this->owner('other');
        $charge = ChannelDeliveryCharge::create($this->payload(['name' => 'PRIVATE shipping']) + ['vendor_id' => $other->vendor_id]);
        $this->actingAs($owner, 'admin');
        $this->get(route('channel.delivery.show', $charge->id))->assertNotFound();
        foreach (['update', 'copy', 'delete'] as $action) {
            $this->post(route('channel.delivery.'.$action, $charge->id), $this->payload())->assertNotFound();
        }
        $this->get(route('channel.delivery.list'))->assertOk()->assertDontSee('PRIVATE shipping');
        $this->assertDatabaseCount('channel_delivery_charges', 1);
    }

    public function test_preview_uses_checkout_calculator_for_thresholds_and_cod_without_saving(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $sample = ['preview_amount' => 29999, 'preview_quantity' => 1, 'preview_postcode' => '04524', 'preview_address' => '서울'];
        $conditional = $this->payload(['shipping_type' => 'conditional', 'free_order_amount' => 30000, 'free_order_quantity' => 3]);
        foreach ([[$conditional, [], 3000, 3000], [$conditional, ['preview_amount' => 30000], 0, 0], [$conditional, ['preview_quantity' => 3], 0, 0], [$this->payload(['shipping_type' => 'fixed', 'fixed_fee' => 4000, 'payment_type' => 'cod']), [], 4000, 0], [$this->payload(), [], 0, 0]] as [$payload,$changes,$fee,$payNow]) {
            $this->postJson(route('channel.delivery.preview'), $payload + array_replace($sample, $changes))->assertOk()->assertJsonPath('fee', $fee)->assertJsonPath('pay_now', $payNow);
        }
        $this->assertDatabaseCount('channel_delivery_charges', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_region_fees_and_restrictions_apply_in_preview_with_missing_and_invalid_ranges_rejected(): void
    {
        $this->actingAs($this->owner(), 'admin');
        $payload = $this->payload(['calculation_rules' => ['region_mode' => 'two', 'jeju_fee' => 5000, 'remote_fee' => 8000, 'mainland_fee' => 3000], 'jeju_postcodes_text' => '63000-63644', 'remote_postcodes_text' => '12345']) + ['preview_amount' => 10000, 'preview_quantity' => 1, 'preview_postcode' => '12345'];
        $this->postJson(route('channel.delivery.preview'), $payload)->assertOk()->assertJsonPath('fee', 5000);
        $payload['calculation_rules']['region_mode'] = 'three';
        $this->postJson(route('channel.delivery.preview'), $payload)->assertOk()->assertJsonPath('fee', 8000);
        $payload['calculation_rules']['block_remote'] = 1;
        $this->postJson(route('channel.delivery.preview'), $payload)->assertUnprocessable()->assertJsonValidationErrors('address');
        $payload['calculation_rules']['block_remote'] = 0;
        $payload['remote_postcodes_text'] = '';
        $this->postJson(route('channel.delivery.preview'), $payload)->assertUnprocessable()->assertJsonValidationErrors('calculation_rules.remote_postcodes');
        $payload['remote_postcodes_text'] = '12349-12340';
        $this->postJson(route('channel.delivery.preview'), $payload)->assertUnprocessable();
    }

    public function test_invalid_and_missing_conditions_are_rejected_and_malformed_old_input_renders_safely(): void
    {
        $this->actingAs($this->owner(), 'admin');
        foreach ([
            ['name' => ''], ['base_fee' => -1], ['shipping_type' => 'conditional', 'free_order_amount' => 0],
            ['shipping_type' => 'conditional', 'amount_condition_enabled' => 1, 'free_order_amount' => 0, 'free_order_quantity' => 3],
            ['calculation_rules' => ['jeju_fee' => ['bad']]],
            ['shipping_type' => 'tiered', 'calculation_rules' => ['basis' => 'amount', 'tiers' => []]],
            ['shipping_type' => 'tiered', 'calculation_rules' => ['basis' => 'amount', 'tiers' => [5 => ['min' => 0, 'max' => null, 'fee' => 0]]]],
            ['shipping_type' => 'variable', 'calculation_rules' => ['basis' => 'quantity', 'repeat_every' => 0, 'repeat_fee' => 500]],
        ] as $invalid) {
            $this->postJson(route('channel.delivery.store'), $this->payload($invalid))->assertUnprocessable();
        }
        $this->from(route('channel.delivery.list'))->post(route('channel.delivery.store'), $this->payload(['name' => ['bad'], 'form_key' => ['bad'], 'calculation_rules' => ['tiers' => ['bad']]]))->assertSessionHasErrors();
        $this->get(route('channel.delivery.list'))->assertOk();
        $this->assertDatabaseCount('channel_delivery_charges', 0);
    }

    public function test_inactive_rule_values_are_scrubbed_and_html_is_escaped(): void
    {
        $owner = $this->owner();
        $this->actingAs($owner, 'admin');
        $this->post(route('channel.delivery.store'), $this->payload(['memo' => '<script>bad()</script>', 'calculation_rules' => ['region_mode' => 'none', 'jeju_fee' => 5000, 'repeat_fee' => 999, 'repeat_every' => 2, 'unvalidated' => ['secret' => 'bad']]]))->assertRedirect();
        $charge = ChannelDeliveryCharge::first();
        $this->assertArrayNotHasKey('jeju_fee', $charge->calculation_rules);
        $this->assertArrayNotHasKey('repeat_fee', $charge->calculation_rules);
        $this->assertArrayNotHasKey('unvalidated', $charge->calculation_rules);
        $this->get(route('channel.delivery.show', $charge->id))->assertOk()->assertDontSee('<script>bad()</script>', false)->assertSee('&lt;script&gt;bad()&lt;/script&gt;', false);
    }
}
