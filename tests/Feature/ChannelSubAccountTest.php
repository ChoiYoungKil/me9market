<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\ChannelSubAccount;
use App\Models\User;
use App\Models\Vendor;
use App\Services\ChannelSubAccounts;
use App\Services\MemberPortal;
use App\Services\MemberRegistration;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ChannelSubAccountTest extends TestCase
{
    use RefreshDatabase;

    private function owner(string $key = 'one'): Admin
    {
        $vendor = Vendor::forceCreate(['name' => 'QA '.$key, 'email' => $key.'@vendor.test', 'mobile' => '01000000000', 'status' => 1]);

        return Admin::forceCreate(['name' => 'Owner '.$key, 'type' => 'vendor', 'vendor_id' => $vendor->id, 'email' => $key.'@admin.test', 'mobile' => '01000000000', 'password' => Hash::make('OwnerOnly1234'), 'status' => 1, 'confirm' => 'Yes']);
    }

    private function member(string $key = 'one'): User
    {
        return User::factory()->create(['username' => 'staff'.$key, 'member_number' => 'M-'.$key, 'email' => $key.'@member.test', 'name' => 'QA '.$key, 'password' => Hash::make('MemberOnly1234'), 'status' => 1]);
    }

    private function payload(Admin $owner, User $member, array $extra = []): array
    {
        return array_replace(['member_id' => $member->id, 'member_token' => app(ChannelSubAccounts::class)->proof($owner, $member), 'status' => 1,
            'started_at' => now()->subHour()->format('Y-m-d\TH:i'), 'ended_at' => now()->addHour()->format('Y-m-d\TH:i'), 'permissions' => ['shop', 'order']], $extra);
    }

    public function test_lookup_matches_registered_members_and_returns_no_password_or_contact_data(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $inactive = $this->member('inactive');
        $inactive->update(['status' => 0]);
        $this->actingAs($owner, 'admin');
        foreach (['member_no' => $member->member_number, 'login_id' => $member->username, 'name' => $member->name] as $type => $keyword) {
            $result = $this->postJson('/channel/settings/sub-accounts/member-lookup', ['search_type' => $type, 'keyword' => $keyword])->assertOk()->json('members');
            $this->assertCount(1, $result);
            $this->assertEquals($member->id, $result[0]['id']);
            $this->assertArrayNotHasKey('password', $result[0]);
            $this->assertArrayNotHasKey('mobile', $result[0]);
        }
        foreach ([$inactive->username, 'not-found'] as $keyword) {
            $this->postJson('/channel/settings/sub-accounts/member-lookup', ['search_type' => 'login_id', 'keyword' => $keyword])->assertOk()->assertJsonCount(0, 'members');
        }
    }

    public function test_lookup_is_required_and_member_credentials_are_not_changed_by_assignment(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $password = $member->password;
        $this->actingAs($owner, 'admin')->postJson('/channel/settings/sub-accounts/store', ['status' => 1, 'name' => 'fake', 'email' => 'fake@test.com', 'password' => 'Password1234'])->assertUnprocessable()->assertJsonValidationErrors('member_token');
        $token = $this->postJson('/channel/settings/sub-accounts/member-lookup', ['search_type' => 'member_no', 'keyword' => $member->member_number])->assertOk()->json('members.0.token');
        $this->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member, ['member_token' => $token]))->assertRedirect(route('channel.sub_accounts.list'));
        $account = ChannelSubAccount::with('admin')->sole();
        $this->assertEquals($member->id, $account->user_id);
        $this->assertEquals($owner->vendor_id, $account->admin->vendor_id);
        $this->assertSame($password, $member->fresh()->password);
        $this->assertSame('general', $member->fresh()->type);
        $this->assertStringEndsWith('@sub.me9.invalid', $account->admin->email);
        $this->assertFalse(Hash::check('MemberOnly1234', $account->admin->password));
        $this->postJson('/channel/settings/sub-accounts/store', $this->payload($owner, $member))->assertUnprocessable()->assertJsonValidationErrors('member_id');
        $this->assertDatabaseCount('channel_sub_accounts', 1);
    }

    public function test_legacy_member_without_stored_member_number_can_be_found_and_linked(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $member->forceFill(['member_number' => null])->save();
        $number = app(MemberRegistration::class)->memberNumber($member);
        $this->actingAs($owner, 'admin');
        foreach (['login_id' => $member->username, 'member_no' => $number] as $type => $keyword) {
            $this->postJson('/channel/settings/sub-accounts/member-lookup', ['search_type' => $type, 'keyword' => $keyword])->assertOk()->assertJsonPath('members.0.member_no', $number);
        }
        $this->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member))->assertRedirect();
        $this->assertDatabaseHas('channel_sub_accounts', ['user_id' => $member->id, 'member_no' => $number]);
        $this->assertNull($member->fresh()->member_number);
        $this->assertCount(1, app(MemberPortal::class)->subAccounts($member));
    }

    public function test_lookup_proof_cannot_be_reused_for_another_member_vendor_or_after_expiry(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $otherMember = $this->member('two');
        $otherOwner = $this->owner('two');
        $payload = $this->payload($owner, $member);
        $this->actingAs($owner, 'admin')->postJson('/channel/settings/sub-accounts/store', array_replace($payload, ['member_id' => $otherMember->id]))->assertUnprocessable();
        $this->actingAs($otherOwner, 'admin')->postJson('/channel/settings/sub-accounts/store', $payload)->assertUnprocessable();
        $this->travel(31)->minutes();
        $this->actingAs($owner, 'admin')->postJson('/channel/settings/sub-accounts/store', $payload)->assertUnprocessable();
        $this->assertDatabaseCount('channel_sub_accounts', 0);
    }

    public function test_update_preserves_identity_and_foreign_vendor_is_blocked(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $other = $this->owner('other');
        $this->actingAs($owner, 'admin')->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member))->assertRedirect();
        $account = ChannelSubAccount::sole();
        $adminId = $account->admin_id;
        $this->post('/channel/settings/sub-accounts/'.$account->id.'/update', $this->payload($owner, $member, ['permissions' => ['shop'], 'status' => 0]))->assertRedirect();
        $this->assertSame($adminId, $account->fresh()->admin_id);
        $this->assertSame(['shop'], $account->fresh()->permissions);
        $this->assertSame(0, (int) $account->fresh()->admin->status);
        $this->actingAs($other, 'admin')->post('/channel/settings/sub-accounts/'.$account->id.'/update', $this->payload($other, $member))->assertNotFound();
        $this->post('/channel/settings/sub-accounts/'.$account->id.'/delete')->assertNotFound();
    }

    public function test_member_login_and_existing_session_enforce_exact_start_end_times_and_permissions(): void
    {
        $this->travelTo(now()->setTime(14, 30, 0));
        $owner = $this->owner();
        $member = $this->member();
        $this->actingAs($owner, 'admin')->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member, ['started_at' => now()->addMinute()->format('Y-m-d\TH:i'), 'ended_at' => now()->addMinutes(2)->format('Y-m-d\TH:i'), 'permissions' => ['shop']]))->assertRedirect();
        $account = ChannelSubAccount::with('admin')->sole();
        Auth::guard('admin')->logout();
        $this->post('/channel/login', ['login_id' => $member->username, 'password' => 'MemberOnly1234'])->assertSessionHasErrors('login_id');
        $this->assertGuest('admin');
        $this->travel(1)->minutes();
        $this->post('/channel/login', ['login_id' => $member->username, 'password' => 'MemberOnly1234'])->assertRedirect(route('channel.index'));
        $this->get('/channel/shop/list')->assertOk()->assertDontSee('href="'.route('channel.product_own').'"', false);
        $this->get('/channel/product/own')->assertForbidden();
        $this->get('/channel/settings/sub-accounts')->assertForbidden();
        $this->post('/channel/complete-profile', [])->assertForbidden();
        $this->travel(61)->seconds();
        $this->get('/channel/shop/list')->assertForbidden();
        $this->assertCount(0, app(MemberPortal::class)->subAccounts($member));
        $this->assertSame('기간 만료', $account->fresh()->access_status);
    }

    public function test_inactive_member_revokes_an_existing_subadmin_session_and_duplicate_dates_are_rejected(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $this->actingAs($owner, 'admin')->postJson('/channel/settings/sub-accounts/store', $this->payload($owner, $member, ['ended_at' => now()->subHours(2)->format('Y-m-d\TH:i')]))->assertUnprocessable();
        $this->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member))->assertRedirect();
        $account = ChannelSubAccount::with('admin')->sole();
        $member->update(['status' => 0]);
        $this->actingAs($account->admin, 'admin')->get('/channel/shop/list')->assertForbidden();
        $this->assertCount(0, app(MemberPortal::class)->subAccounts($member));
    }

    public function test_reassigning_a_member_blocks_the_previous_members_existing_session(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $replacement = $this->member('replacement');
        $this->actingAs($owner, 'admin')->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member))->assertRedirect();
        $account = ChannelSubAccount::with('admin')->sole();
        $this->actingAs($member, 'web')->actingAs($account->admin, 'admin')->get('/channel/shop/list')->assertOk();
        $this->actingAs($owner, 'admin')->post('/channel/settings/sub-accounts/'.$account->id.'/update', $this->payload($owner, $replacement))->assertRedirect();
        $this->actingAs($account->admin, 'admin')->get('/channel/shop/list')->assertForbidden();
        $this->assertCount(0, app(MemberPortal::class)->subAccounts($member));
        Auth::guard('admin')->logout();
        Auth::guard('web')->logout();
        $this->post('/channel/login', ['login_id' => $replacement->username, 'password' => 'MemberOnly1234'])->assertRedirect(route('channel.index'));
        $this->get('/channel/shop/list')->assertOk();
    }

    public function test_invalid_form_value_types_redisplay_validation_errors_without_crashing(): void
    {
        $owner = $this->owner();
        $member = $this->member();
        $this->actingAs($owner, 'admin');
        foreach ([['member_token' => ['invalid'], 'form_key' => ['invalid']], ['permissions' => 'shop', 'form_key' => 'create'], ['started_at' => ['invalid'], 'form_key' => 'create']] as $invalid) {
            $this->from('/channel/settings/sub-accounts')->post('/channel/settings/sub-accounts/store', $this->payload($owner, $member, $invalid))->assertSessionHasErrors();
            $this->get('/channel/settings/sub-accounts')->assertOk();
        }
        $this->assertDatabaseCount('channel_sub_accounts', 0);
    }
}
