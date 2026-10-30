<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\PointTransaction;
use App\Models\ShopChannel;
use App\Models\User;
use App\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChannelPointHistoryTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $key = 'one'): array
    {
        $vendor = Vendor::forceCreate(['name' => 'QA '.$key, 'email' => $key.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);
        $admin = Admin::forceCreate(['name' => 'Owner '.$key, 'type' => 'vendor', 'vendor_id' => $vendor->id, 'email' => $key.'@admin.test', 'mobile' => '01000000000', 'password' => bcrypt('OwnerOnly1234'), 'status' => 1, 'confirm' => 'Yes']);
        $shop = ShopChannel::forceCreate(['vendor_id' => $vendor->id, 'channel_code' => 'qa189-'.$key, 'channel_name' => 'QA189 '.$key, 'copyright' => 'QA', 'keywords' => '[]', 'status' => 1]);

        return [$admin, $shop];
    }

    private function entry(?ShopChannel $shop, string $type, int $points, string $at = '2026-10-31 12:00:00', array $extra = []): PointTransaction
    {
        $user = User::firstOrCreate(['email' => 'qa189@example.test'], ['name' => 'QA189 buyer', 'password' => bcrypt('BuyerOnly1234')]);

        return PointTransaction::forceCreate(array_replace(['user_id' => $user->id, 'shop_channel_id' => $shop?->id, 'type' => $type, 'points' => $points, 'description' => 'QA189 '.$type, 'created_at' => $at], $extra));
    }

    public function test_guest_is_redirected_and_empty_vendor_has_zero_totals_without_removed_actions(): void
    {
        $this->get('/channel/settings/points')->assertRedirect(route('channel.login'));
        [$admin] = $this->owner();
        $this->actingAs($admin, 'admin')->get('/channel/settings/points')->assertOk()
            ->assertViewHas('summary', fn ($summary) => $summary['balance'] === 0 && $summary['distributed'] === 0 && $summary['consumed'] === 0)
            ->assertSee('검색 조건에 맞는 포인트 내역이 없습니다.')
            ->assertDontSee('포인트 구매')->assertDontSee('환급 요청');
    }

    public function test_totals_include_closed_shops_but_exclude_other_vendors_and_me9_wallets(): void
    {
        [$admin, $shop] = $this->owner();
        [, $other] = $this->owner('other');
        $this->entry($shop, 'earn', 1000);
        $this->entry($shop, 'use', -400);
        $this->entry($shop, 'refund', 100);
        $this->entry($shop, 'convert_out', -200);
        $shop->update(['status' => 0, 'closure_status' => 'approved']);
        $this->entry($other, 'earn', 90000, extra: ['description' => 'PRIVATE other vendor']);
        $this->entry(null, 'convert_in', 200, extra: ['description' => 'PRIVATE Me9 wallet']);
        $this->actingAs($admin, 'admin')->get('/channel/settings/points')->assertOk()
            ->assertViewHas('summary', fn ($s) => $s === ['balance' => 500, 'distributed' => 1000, 'consumed' => 500, 'used' => 400, 'converted' => 200, 'restored' => 100])
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 4)
            ->assertSee('QA189 buyer')->assertDontSee('PRIVATE other vendor')->assertDontSee('PRIVATE Me9 wallet');
        $this->assertDatabaseCount('point_transactions', 6);
    }

    public function test_distribution_and_consumption_filters_reconcile_restorations_without_changing_global_totals(): void
    {
        [$admin, $shop] = $this->owner();
        $this->entry($shop, 'earn', 1000);
        $this->entry($shop, 'use', -400);
        $this->entry($shop, 'refund', 100);
        $this->entry($shop, 'convert_out', -200);
        $this->actingAs($admin, 'admin')->get('/channel/settings/points?history=earn')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 1 && $rows->first()->type === 'earn')
            ->assertViewHas('filteredPoints', 1000)->assertViewHas('summary', fn ($s) => $s['balance'] === 500);
        $this->get('/channel/settings/points?history=use')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 3 && $rows->pluck('type')->contains('refund'))
            ->assertViewHas('filteredPoints', -500)->assertSee('소진 취소');
        $this->get('/channel/settings/points?history=refund')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 1 && $rows->first()->type === 'refund');
    }

    public function test_date_bounds_include_the_whole_day_and_combine_with_the_history_filter(): void
    {
        [$admin, $shop] = $this->owner();
        $this->entry($shop, 'earn', 100, '2026-10-30 23:59:59');
        $first = $this->entry($shop, 'earn', 300, '2026-10-31 00:00:00');
        $last = $this->entry($shop, 'use', -50, '2026-10-31 23:59:59');
        $this->entry($shop, 'earn', 200, '2026-11-01 00:00:00');
        $this->actingAs($admin, 'admin')->get('/channel/settings/points?date_from=2026-10-31&date_to=2026-10-31')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->pluck('id')->all() === [$last->id, $first->id])
            ->assertViewHas('filteredPoints', 250)->assertViewHas('summary', fn ($s) => $s['balance'] === 550);
        $this->get('/channel/settings/points?history=earn&date_from=2026-10-31&date_to=2026-10-31')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->pluck('id')->all() === [$first->id]);
        $this->get('/channel/settings/points?date_from=2026-11-01')->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 1);
        $this->get('/channel/settings/points?date_to=2026-10-30')->assertOk()->assertViewHas('transactions', fn ($rows) => $rows->total() === 1);
    }

    public function test_malformed_filters_and_reversed_dates_are_rejected_and_the_form_renders_safely(): void
    {
        [$admin] = $this->owner();
        $this->actingAs($admin, 'admin');
        foreach ([['history' => ['bad']], ['history' => 'purchase'], ['date_from' => ['bad']], ['date_from' => '2026-02-30'], ['date_to' => 'tomorrow'], ['date_from' => '2026-11-01', 'date_to' => '2026-10-31']] as $query) {
            $this->getJson('/channel/settings/points?'.http_build_query($query))->assertUnprocessable();
        }
        $this->from('/channel/settings/points')->get('/channel/settings/points?date_from%5B%5D=bad')->assertRedirect('/channel/settings/points')->assertSessionHasErrors('date_from');
        $this->get('/channel/settings/points')->assertOk();
    }

    public function test_pagination_keeps_filters_and_uses_id_as_a_stable_tie_breaker(): void
    {
        [$admin, $shop] = $this->owner();
        $entries = collect(range(1, 21))->map(fn ($n) => $this->entry($shop, 'earn', 1));
        $query = 'history=earn&date_from=2026-10-31&date_to=2026-10-31';
        $this->actingAs($admin, 'admin')->get('/channel/settings/points?'.$query)->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->total() === 21 && $rows->count() === 20 && $rows->first()->id === $entries->last()->id && str_contains($rows->url(2), 'history=earn') && str_contains($rows->url(2), 'date_to=2026-10-31'))
            ->assertViewHas('filteredPoints', 21);
        $this->get('/channel/settings/points?'.$query.'&page=2')->assertOk()
            ->assertViewHas('transactions', fn ($rows) => $rows->count() === 1 && $rows->first()->id === $entries->first()->id);
    }

    public function test_descriptions_are_escaped_and_filtering_does_not_mutate_point_balances(): void
    {
        [$admin, $shop] = $this->owner();
        $this->entry($shop, 'earn', 500, extra: ['description' => '<script>alert(189)</script>']);
        $this->actingAs($admin, 'admin')->get('/channel/settings/points')->assertOk()
            ->assertDontSee('<script>alert(189)</script>', false)->assertSee('&lt;script&gt;alert(189)&lt;/script&gt;', false);
        $this->get('/channel/settings/points?history=use')->assertOk()->assertSee('검색 조건에 맞는 포인트 내역이 없습니다.');
        $this->assertDatabaseCount('point_transactions', 1);
        $this->assertSame(500, (int) PointTransaction::sum('points'));
    }
}
