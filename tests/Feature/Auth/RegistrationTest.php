<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Vendor;
use App\Models\VendorsBusinessDetail;
use App\Models\VendorsBankDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get('/register/member');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register()
    {
        $response = $this->post('/register/member', [
            'agree_terms' => '1',
            'agree_privacy' => '1',
            'agree_third_party' => '1',
            'register_type' => 'general',
            'username' => 'newuser',
            'email_prefix' => 'newuser',
            'email_domain' => 'example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertJson([
            'status' => 'success',
        ]);
        
        $this->assertAuthenticated();
        
        $this->assertDatabaseHas('users', [
            'username' => 'newuser',
            'email' => 'newuser@example.com',
        ]);
    }

    public function test_register_step1_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register/step1');

        $response->assertStatus(200);
    }

    public function test_register_step1_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/register/step1/update', [
            'name' => 'Updated Name',
            'gender' => 'Male',
            'mobile_1' => '010',
            'mobile_2' => '1234',
            'mobile_3' => '5678',
            'zipcode' => '12345',
            'address1' => 'Seoul',
            'address2' => 'Gangnam-gu',
        ]);

        $response->assertRedirect();
        
        $user = $user->fresh();
        $this->assertEquals('Updated Name', $user->name);
        $this->assertEquals('010-1234-5678', $user->mobile);
        $this->assertEquals('12345', $user->pincode);
        $this->assertEquals('Seoul', $user->address);
        $this->assertEquals('Gangnam-gu', $user->city);
    }

    public function test_register_step2_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register/step2');

        $response->assertStatus(200);
    }

    public function test_register_step2_can_be_updated()
    {
        $user = User::factory()->create([
            'mobile' => '010-1234-5678',
        ]);

        $response = $this->actingAs($user)->post('/register/step2/update', [
            'shop_name' => 'My Shop',
            'shop_business_type' => 'Individual',
            'business_license_1' => '123',
            'business_license_2' => '45',
            'business_license_3' => '67890',
            'mobile_1' => '010',
            'mobile_2' => '1234',
            'mobile_3' => '5678',
            'email_1' => 'myshop',
            'email_2' => 'example.com',
            'zipcode' => '12345',
            'address1' => 'Seoul',
            'address2' => 'Gangnam-gu',
            'bank_name' => 'Kookmin Bank',
            'account_number' => '123456-78-901234',
            'account_holder_name' => 'Owner Name',
            'agree1' => '1',
        ]);

        $response->assertJson([
            'status' => 'success',
        ]);

        $user = $user->fresh();
        $this->assertNotNull($user->vendor_id);
        $this->assertEquals('company', $user->type);

        $this->assertDatabaseHas('vendors_business_details', [
            'vendor_id' => $user->vendor_id,
            'shop_name' => 'My Shop',
            'bank_name' => 'Kookmin Bank',
        ]);

        $this->assertDatabaseHas('vendors_bank_details', [
            'vendor_id' => $user->vendor_id,
            'bank_name' => 'Kookmin Bank',
            'account_number' => '123456-78-901234',
        ]);
    }

    public function test_register_step3_screen_can_be_rendered()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/register/step3');

        $response->assertStatus(200);
    }

    public function test_register_step3_can_be_updated()
    {
        $vendor = new Vendor;
        $vendor->name = 'Test Vendor';
        $vendor->mobile = '010-1234-5678';
        $vendor->email = 'vendor@example.com';
        $vendor->status = 0;
        $vendor->commission = 0;
        $vendor->confirm = 'No';
        $vendor->save();

        $businessDetail = new VendorsBusinessDetail;
        $businessDetail->vendor_id = $vendor->id;
        $businessDetail->shop_name = 'Test Shop';
        $businessDetail->shop_address = 'Seoul';
        $businessDetail->shop_mobile = '010-1234-5678';
        $businessDetail->shop_email = 'vendor@example.com';
        $businessDetail->save();

        $bankDetail = new VendorsBankDetail;
        $bankDetail->vendor_id = $vendor->id;
        $bankDetail->bank_name = 'Test Bank';
        $bankDetail->account_number = '123456';
        $bankDetail->account_holder_name = 'Test Owner';
        $bankDetail->bank_ifsc_code = '1234';
        $bankDetail->save();

        $user = User::factory()->create([
            'vendor_id' => $vendor->id,
            'type' => 'company',
            'mobile' => '010-1234-5678',
        ]);

        $response = $this->actingAs($user)->post('/register/step3/update', [
            'agree1' => '1',
        ]);

        $response->assertJson([
            'status' => 'success',
        ]);

        $user = $user->fresh();
        $this->assertEquals('vendor', $user->type);
    }
}
