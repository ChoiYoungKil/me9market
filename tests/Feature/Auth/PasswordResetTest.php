<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use App\Mail\TemporaryMemberPassword;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_find_id_screen_can_be_rendered()
    {
        $response = $this->get('/find/id');

        $response->assertStatus(200);
    }

    public function test_find_id_succeeds_with_correct_info()
    {
        $user = User::factory()->create([
            'member_number' => 'M9-260528-0001',
            'email' => 'testuser@example.com',
            'username' => 'testusername',
        ]);

        $response = $this->post('/find/id', [
            'member_number' => 'M9-260528-0001',
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('result', [
            'type' => 'success',
            'username' => 'testusername',
        ]);
    }

    public function test_find_id_fails_with_incorrect_info()
    {
        $user = User::factory()->create([
            'member_number' => 'M9-260528-0001',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/find/id', [
            'member_number' => 'wrong-number',
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('result', [
            'type' => 'fail',
            'message' => '일치하는 정보가 없습니다.',
        ]);
    }

    public function test_find_pw_screen_can_be_rendered()
    {
        $response = $this->get('/find/pw');

        $response->assertStatus(200);
    }

    public function test_find_pw_succeeds_with_correct_info()
    {
        Mail::fake();
        $user = User::factory()->create([
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/find/pw', [
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $originalPassword = $user->password;
        $response->assertStatus(200);

        $result = $response->original->getData()['result'];
        $response->assertSee('data-preview-preserve="true"', false);
        $this->assertEquals('success', $result['type']);
        $this->assertNotSame($originalPassword, $user->fresh()->password);
        Mail::assertSent(TemporaryMemberPassword::class, function ($mail) use ($user, $response) {
            $this->assertTrue(Hash::check($mail->temporaryPassword, $user->fresh()->password));
            $response->assertDontSee($mail->temporaryPassword);
            return $mail->hasTo($user->email);
        });
        $this->assertTrue($user->fresh()->temporary_password_expires_at->isFuture());
    }

    public function test_find_pw_fails_with_incorrect_info()
    {
        Mail::fake();
        $user = User::factory()->create([
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/find/pw', [
            'username' => 'wrongusername',
            'email' => 'testuser@example.com',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasErrors('email');
        Mail::assertNothingSent();
    }

    public function test_id_lookup_validates_malformed_input_and_does_not_reveal_disabled_member(): void
    {
        $this->postJson('/find/id', ['member_number'=>['bad'], 'email'=>'bad'])->assertUnprocessable()->assertJsonValidationErrors(['member_number','email']);
        $user = User::factory()->create(['status'=>0,'member_number'=>'M9-disabled','username'=>'hiddenmember']);
        $this->post('/find/id',['member_number'=>$user->member_number,'email'=>$user->email])->assertOk()->assertDontSee('hiddenmember')->assertSee('일치하는 정보가 없습니다.');
    }

    public function test_mail_failure_leaves_existing_password_unchanged(): void
    {
        $user = User::factory()->create(['username'=>'mailfailure']);
        $password = $user->password;
        Mail::shouldReceive('to')->once()->andThrow(new \RuntimeException('Test delivery unavailable'));
        $this->post('/find/pw',['username'=>$user->username,'email'=>$user->email])->assertRedirect()->assertSessionHasErrors('email');
        $this->assertSame($password,$user->fresh()->password);
        $this->assertNull($user->temporary_password_expires_at);
    }

    public function test_reissue_is_throttled_and_old_password_no_longer_authenticates(): void
    {
        Mail::fake();
        $user = User::factory()->create(['username'=>'recoverymember','password'=>Hash::make('OldMember36!')]);
        $payload=['username'=>$user->username,'email'=>$user->email];
        $this->post('/find/pw',$payload)->assertOk();
        $newHash=$user->fresh()->password;
        $this->post('/find/pw',$payload)->assertRedirect()->assertSessionHasErrors('email');
        $this->assertSame($newHash,$user->fresh()->password);
        Mail::assertSentCount(1);
        $this->post('/member/login',['login_id'=>$user->username,'password'=>'OldMember36!'])->assertRedirect();
        $this->assertGuest();
    }

    public function test_expired_temporary_password_is_rejected_and_password_change_clears_expiration(): void
    {
        $user = User::factory()->create(['username'=>'expiredmember','status'=>1,'password'=>Hash::make('Temporary36!'),
            'temporary_password_issued_at'=>now()->subMinutes(61),'temporary_password_expires_at'=>now()->subMinute()]);
        $this->post('/member/login',['login_id'=>$user->username,'password'=>'Temporary36!'])->assertRedirect()->assertSessionHas('error_message');
        $this->assertGuest();
        $this->post('/login',['email'=>$user->email,'password'=>'Temporary36!'])->assertRedirect();
        $this->assertGuest();
        $this->post('/shop-channel/login',['login_id'=>$user->username,'password'=>'Temporary36!'])->assertRedirect();
        $this->assertGuest();
        $user->temporary_password_expires_at=now()->addHour();$user->save();
        $this->post('/member/login',['login_id'=>$user->username,'password'=>'Temporary36!'])->assertRedirect();
        $this->assertAuthenticatedAs($user);
        $this->postJson(route('user.update.password'),['current_password'=>'Temporary36!','new_password'=>'RecoveredMember36!','confirm_password'=>'RecoveredMember36!'],['X-Requested-With'=>'XMLHttpRequest'])
            ->assertOk()->assertJson(['type'=>'success']);
        $this->assertNull($user->fresh()->temporary_password_expires_at);
        $this->assertNull($user->fresh()->temporary_password_issued_at);
    }

    public function test_local_test_mail_preview_is_session_bound_expiring_and_unavailable_in_production(): void
    {
        $this->app->instance('env','local');
        Mail::fake();Storage::fake('local');
        $user=User::factory()->create(['username'=>'previewmember','email'=>'preview@example.test']);
        $response=$this->post('/find/pw',['username'=>$user->username,'email'=>$user->email])->assertOk();
        $url=$response->viewData('result')['preview_url'];
        $this->get($url)->assertOk()->assertSee('로컬 검수 메일함')->assertSee('target="_blank"',false);
        $path='account-recovery-mail/'.basename($url).'.enc';
        Mail::assertSent(TemporaryMemberPassword::class, function($mail) use($path) {
            $this->assertStringNotContainsString($mail->temporaryPassword,Storage::disk('local')->get($path));return true;
        });
        $this->app->instance('env','production');$this->get($url)->assertNotFound();
        $this->app->instance('env','local');$this->travel(61)->minutes();$this->get($url)->assertNotFound();$this->travelBack();
        session()->flush();$this->get($url)->assertNotFound();
    }

    public function test_live_preview_poll_does_not_consume_recovery_errors_or_input(): void
    {
        $this->app->instance('env','local');
        $this->from('/find/pw')->post('/find/pw',['username'=>'missingmember','email'=>'missing@example.test'])->assertRedirect('/find/pw');
        $this->get('/__preview/version')->assertOk()->assertCookieMissing(config('session.cookie'));
        $this->get('/__preview/version')->assertOk();
        $this->get('/find/pw')->assertOk()->assertSee('일치하는 정보가 없습니다.')->assertSee('missingmember');
        $this->app->instance('env','production');
        $this->get('/__preview/version')->assertNotFound();
    }
}
