<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\DeliveryAddress;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MypageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_access_mypage()
    {
        $response = $this->get('/mypage/main');

        $response->assertRedirect('/member/login');
    }

    public function test_authenticated_user_can_access_mypage_dashboard()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/mypage/main');

        $response->assertStatus(200);
    }

    public function test_user_profile_update()
    {
        $user = User::factory()->create([
            'email' => 'old@example.com',
            'mobile' => '010-1111-1111',
        ]);

        $response = $this->actingAs($user)->post('/mypage/profile', [
            'name' => 'New Name',
            'gender' => 'Male',
            'mobile_1' => '010',
            'mobile_2' => '2222',
            'mobile_3' => '2222',
            'email_1' => 'new',
            'email_2' => 'example.com',
            'zipcode' => '54321',
            'address1' => 'Seoul',
            'address2' => 'Gangnam',
        ]);

        $response->assertRedirect();
        
        $user = $user->fresh();
        $this->assertEquals('New Name', $user->name);
        $this->assertEquals('new@example.com', $user->email);
        $this->assertEquals('010-2222-2222', $user->mobile);
        $this->assertEquals('54321', $user->pincode);
        $this->assertEquals('Seoul', $user->address);
        $this->assertEquals('Gangnam', $user->city);
    }

    public function test_delivery_addresses_management_crud()
    {
        $user = User::factory()->create();

        // 1. Create (Add)
        $response = $this->actingAs($user)->post('/mypage/delivery/add', [
            'name' => 'Home Address',
            'zipcode' => '12345',
            'address1' => 'Seoul',
            'address2' => 'Mapo-gu',
            'is_default' => '1',
            'mobile' => '01012345678',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('delivery_addresses', [
            'user_id' => $user->id,
            'name' => 'Home Address',
            'address' => 'Seoul',
            'city' => 'Mapo-gu',
            'is_default' => 1,
        ]);

        $address = DeliveryAddress::where('user_id', $user->id)->first();

        // 2. Update
        $response = $this->actingAs($user)->post('/mypage/delivery/update', [
            'id' => $address->id,
            'name' => 'Updated Home Address',
            'zipcode' => '54321',
            'address1' => 'Seoul',
            'address2' => 'Yongsan-gu',
            'is_default' => '1',
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('delivery_addresses', [
            'id' => $address->id,
            'name' => 'Updated Home Address',
            'city' => 'Yongsan-gu',
        ]);

        // 3. Delete
        $response = $this->actingAs($user)->post('/mypage/delivery/delete', [
            'id' => $address->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseMissing('delivery_addresses', [
            'id' => $address->id,
        ]);
    }

    public function test_user_withdrawal_flow()
    {
        $user = User::factory()->create([
            'password' => bcrypt('correct-password'),
        ]);

        // Fail with wrong password
        $response = $this->actingAs($user)->post('/mypage/withdraw/submit', [
            'password' => 'wrong-password',
            'password_confirmation' => 'wrong-password',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error_message', '비밀번호가 일치하지 않습니다.');
        $this->assertEquals(1, $user->fresh()->status);

        // Succeed with correct password
        $response = $this->actingAs($user)->post('/mypage/withdraw/submit', [
            'password' => 'correct-password',
            'password_confirmation' => 'correct-password',
        ]);

        $response->assertRedirect(route('mypage.withdraw.success'));
        $this->assertEquals(0, $user->fresh()->status);
    }
}
