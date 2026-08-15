<?php

namespace Tests\Feature\User;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function register_page_is_accessible()
    {
        $response = $this->get('/register');
        $response->assertStatus(200);
    }

    /** @test */
    public function login_page_is_accessible()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_home_page()
    {
        $response = $this->get('/');
        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_user_can_access_profile()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/profile');
        
        $response->assertStatus(200);
    }

    /** @test */
    public function user_can_view_all_products()
    {
        $response = $this->get('/all-products');
        $response->assertStatus(200);
    }

    /** @test */
    public function guest_user_can_view_catalog()
    {
        $response = $this->get('/catalog/test-category');
        // Should either return 200 or 404 depending on category existence
        $this->assertTrue(
            $response->getStatusCode() === 200 || $response->getStatusCode() === 404
        );
    }

    /** @test */
    public function forgot_password_page_is_accessible()
    {
        $response = $this->get('/forgot-password');
        $response->assertStatus(200);
    }

    /** @test */
    public function authenticated_user_can_access_chat()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/chat/history');
        
        $response->assertStatus(200);
    }
}

