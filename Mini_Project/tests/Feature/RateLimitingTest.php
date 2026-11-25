<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed test data
        User::create([
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'password' => Hash::make('password123'),
            'role' => 'user',
        ]);
        User::create([
            'name' => 'VIP User',
            'email' => 'vip@example.com',
            'password' => Hash::make('password123'),
            'role' => 'vip',
        ]);
        Product::create(['name' => 'Test Product', 'brand' => 'Test', 'price' => 9.99, 'stock' => 10]);
    }

    // ========================
    // TEST: Unprotected endpoint always returns 200
    // ========================
    public function test_unprotected_endpoint_returns_products(): void
    {
        $response = $this->getJson('/api/products-unprotected');
        $response->assertStatus(200);
    }

    // ========================
    // TEST: Protected endpoint returns products for first request
    // ========================
    public function test_protected_endpoint_returns_products(): void
    {
        $response = $this->getJson('/api/products-protected');
        $response->assertStatus(200);
    }

    // ========================
    // TEST: Guest rate limiting (3 requests allowed, 4th blocked)
    // ========================
    public function test_guest_rate_limit_blocks_after_3_requests(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->getJson('/api/products-protected')->assertStatus(200);
        }
        // 4th request should be rate limited
        $response = $this->getJson('/api/products-protected');
        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Rate limit exceeded. Please slow down.',
            'tier' => 'guest',
        ]);
    }

    // ========================
    // TEST: Normal user gets 6 requests per minute
    // ========================
    public function test_normal_user_rate_limit_allows_6_requests(): void
    {
        $user = User::where('email', 'user@example.com')->first();

        for ($i = 0; $i < 6; $i++) {
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/products-protected')
                ->assertStatus(200);
        }
        // 7th request should be blocked
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/products-protected');
        $response->assertStatus(429);
        $response->assertJson([
            'tier' => 'user',
        ]);
    }

    // ========================
    // TEST: VIP user gets 20 requests per minute
    // ========================
    public function test_vip_user_rate_limit_allows_20_requests(): void
    {
        $user = User::where('email', 'vip@example.com')->first();

        for ($i = 0; $i < 20; $i++) {
            $this->actingAs($user, 'sanctum')
                ->getJson('/api/products-protected')
                ->assertStatus(200);
        }
        // 21st request should be blocked
        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/products-protected');
        $response->assertStatus(429);
        $response->assertJson([
            'tier' => 'vip',
        ]);
    }

    // ========================
    // TEST: Login with valid credentials returns token
    // ========================
    public function test_login_returns_token_with_valid_credentials(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'status',
            'token',
            'user' => ['id', 'name', 'email', 'role'],
        ]);
        $response->assertJson(['status' => 'success']);
    }

    // ========================
    // TEST: Login with wrong password returns 401
    // ========================
    public function test_login_returns_401_with_wrong_password(): void
    {
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpass',
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => 'error',
            'message' => 'Invalid credentials.',
        ]);
    }

    // ========================
    // TEST: Login brute-force protection (3 attempts, 4th blocked)
    // ========================
    public function test_login_rate_limit_blocks_after_3_attempts(): void
    {
        for ($i = 0; $i < 3; $i++) {
            $this->postJson('/api/login', [
                'email' => 'user@example.com',
                'password' => 'wrongpass',
            ])->assertStatus(401);
        }
        // 4th attempt should be rate limited
        $response = $this->postJson('/api/login', [
            'email' => 'user@example.com',
            'password' => 'wrongpass',
        ]);
        $response->assertStatus(429);
        $response->assertJson([
            'status' => 'blocked',
            'message' => 'Too many login attempts. Account temporarily throttled.',
        ]);
    }

    // ========================
    // TEST: /me endpoint returns authenticated user profile
    // ========================
    public function test_me_endpoint_returns_user_profile(): void
    {
        $user = User::where('email', 'user@example.com')->first();

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJson([
            'id' => $user->id,
            'name' => 'Normal User',
            'email' => 'user@example.com',
            'role' => 'user',
        ]);
    }

    // ========================
    // TEST: /me without token returns 401
    // ========================
    public function test_me_without_token_returns_401(): void
    {
        $response = $this->getJson('/api/me');
        $response->assertStatus(401);
    }
}
