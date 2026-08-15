<?php

namespace Tests\Unit\Admin;

use App\Models\Admin;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminModelTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_can_be_created()
    {
        $admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
        ]);

        $this->assertDatabaseHas('admins', [
            'email' => 'admin@example.com',
        ]);
    }

    /** @test */
    public function admin_password_is_hashed()
    {
        $password = 'plain_password_123';
        $admin = Admin::create([
            'name' => 'Another Admin',
            'email' => 'another@example.com',
            'password' => bcrypt($password),
        ]);

        $this->assertNotEquals($password, $admin->password);
    }

    /** @test */
    public function admin_can_have_role()
    {
        $admin = Admin::create([
            'name' => 'Super Admin',
            'email' => 'super@example.com',
            'password' => bcrypt('password'),
            'role' => 'super_admin',
        ]);

        $this->assertEquals('super_admin', $admin->role);
    }

    /** @test */
    public function admin_can_have_status()
    {
        $admin = Admin::create([
            'name' => 'Active Admin',
            'email' => 'active@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active',
        ]);

        $this->assertEquals('active', $admin->status);
    }

    /** @test */
    public function admin_email_is_unique()
    {
        Admin::create([
            'name' => 'First Admin',
            'email' => 'unique@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);
        Admin::create([
            'name' => 'Second Admin',
            'email' => 'unique@example.com',
            'password' => bcrypt('password'),
        ]);
    }

    /** @test */
    public function admin_can_have_avatar()
    {
        $admin = Admin::create([
            'name' => 'Admin With Avatar',
            'email' => 'avatar@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'avatar' => '/storage/avatars/admin1.png',
        ]);

        $this->assertNotNull($admin->avatar);
        $this->assertStringContainsString('/storage/', $admin->avatar);
    }

    /** @test */
    public function admin_can_be_disabled()
    {
        $admin = Admin::create([
            'name' => 'Disabled Admin',
            'email' => 'disabled@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'inactive',
        ]);

        $this->assertEquals('inactive', $admin->status);
    }

    /** @test */
    public function admin_can_be_updated()
    {
        $admin = Admin::create([
            'name' => 'Original Name',
            'email' => 'update@example.com',
            'password' => bcrypt('password'),
        ]);

        $admin->update(['name' => 'Updated Name']);

        $this->assertEquals('Updated Name', $admin->fresh()->name);
    }

    /** @test */
    public function admin_can_be_deleted()
    {
        $admin = Admin::create([
            'name' => 'Delete Test Admin',
            'email' => 'delete@example.com',
            'password' => bcrypt('password'),
        ]);

        $adminId = $admin->id;
        $admin->delete();

        $this->assertDatabaseMissing('admins', ['id' => $adminId]);
    }

    /** @test */
    public function admin_has_fillable_attributes()
    {
        $admin = new Admin();
        $fillable = $admin->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
        $this->assertContains('role', $fillable);
    }
}
