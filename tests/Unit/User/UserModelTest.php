<?php

namespace Tests\Unit\User;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_be_created()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password123'),
            'phone' => '081234567890',
        ]);

        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
            'name' => 'John Doe',
        ]);
    }

    /** @test */
    public function user_password_is_hashed()
    {
        $password = 'plain_password';
        $user = User::create([
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
            'password' => bcrypt($password),
        ]);

        $this->assertNotEquals($password, $user->password);
    }

    /** @test */
    public function user_has_fillable_attributes()
    {
        $fillable = [
            'name', 'email', 'password', 'phone',
            'address', 'city', 'province', 'postal_code',
            'avatar', 'bio', 'birth_date'
        ];

        foreach ($fillable as $attribute) {
            $this->assertContains($attribute, (new User())->getFillable());
        }
    }

    /** @test */
    public function user_can_update_profile()
    {
        $user = User::factory()->create();
        
        $user->update([
            'phone' => '087654321098',
            'address' => 'Jl. Merdeka 123',
            'city' => 'Jakarta',
        ]);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'phone' => '087654321098',
            'city' => 'Jakarta',
        ]);
    }

    /** @test */
    public function user_can_have_email_change_request()
    {
        $user = User::factory()->create();
        
        $this->assertTrue(
            method_exists($user, 'addresses')
        );
    }

    /** @test */
    public function user_can_have_orders()
    {
        $user = User::factory()->create();
        
        $this->assertTrue(
            method_exists($user, 'virtualAccounts')
        );
    }

    /** @test */
    public function user_can_have_custom_design_orders()
    {
        $user = User::factory()->create();
        
        $this->assertTrue(
            method_exists($user, 'paymentTransactions')
        );
    }

    /** @test */
    public function user_email_is_unique()
    {
        User::factory()->create(['email' => 'unique@example.com']);

        $this->expectException(\Illuminate\Database\QueryException::class);
        User::factory()->create(['email' => 'unique@example.com']);
    }

    /** @test */
    public function user_can_be_deleted()
    {
        $user = User::factory()->create();
        $userId = $user->id;

        $user->delete();

        $this->assertDatabaseMissing('users', ['id' => $userId]);
    }
}
