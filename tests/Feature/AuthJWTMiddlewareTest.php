<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthJWTMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $validToken;
    private $invalidToken;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        // Generate a valid JWT token
        $this->validToken = JWT::encode([
            'sub' => $user->id,
            'email' => $user->email,
            'exp' => time() + 3600
        ], env('JWT_SECRET_KEY'), env('JWT_ALGORITHM'));

        // Generate an invalid JWT token (wrong secret key)
        $this->invalidToken = JWT::encode([
            'sub' => $user->id,
            'email' => $user->email,
            'exp' => time() + 3600
        ], 'invalid_secret', env('JWT_ALGORITHM'));
    }

    /** @test */
    public function it_should_fail_authentication_with_no_token()
    {
        $response = $this->json('GET', '/api/tasks');

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Authentication failed!']);
    }

    /** @test */
    public function it_should_fail_authentication_with_invalid_token()
    {
        $response = $this->json('GET', '/api/tasks', [], ['Authorization' => 'Bearer ' . $this->invalidToken]);

        $response->assertStatus(401);
        $response->assertJson(['message' => 'Authentication failed!']);
    }

    /** @test */
    public function it_should_pass_authentication_with_valid_token()
    {
        $response = $this->json('GET', '/api/tasks', [], ['Authorization' => 'Bearer ' . $this->validToken]);

        $response->assertStatus(200);
        // Add more assertions if needed to verify the response content
    }
}
