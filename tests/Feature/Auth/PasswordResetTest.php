<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
        $user = User::factory()->create([
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/find/pw', [
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(200);
        
        $result = $response->original->getData()['result'];
        $this->assertEquals('success', $result['type']);
        $this->assertNotEmpty($result['temp_password']);
        
        $this->assertTrue(Hash::check($result['temp_password'], $user->fresh()->password));
    }

    public function test_find_pw_fails_with_incorrect_info()
    {
        $user = User::factory()->create([
            'username' => 'testusername',
            'email' => 'testuser@example.com',
        ]);

        $response = $this->post('/find/pw', [
            'username' => 'wrongusername',
            'email' => 'testuser@example.com',
        ]);

        $response->assertStatus(200);
        $response->assertViewHas('result', [
            'type' => 'fail',
            'message' => '일치하는 정보가 없습니다.',
        ]);
    }
}
