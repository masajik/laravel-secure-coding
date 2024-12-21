<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_should_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'test@example.com']);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    /** @test */
    public function it_should_login_a_user()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure(['access_token', 'token_type']);
    }

    /** @test */
    public function it_should_logout_a_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->postJson('/api/logout');
        $response->assertStatus(200);
        $response->assertJson(['message' => 'Logged out successfully']);
    }

    /** @test */
    public function it_should_fail_registration_with_invalid_data()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'invalid-email',
            'password' => 'pass',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Sorry, we can\'t process your request.']);
    }

    /** @test */
    public function it_should_fail_login_with_invalid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'invalid-password'
        ]);

        $response->assertStatus(400);
        $response->assertJson(['message' => 'Your email is not registered or password is incorrect!']);
    }
}
