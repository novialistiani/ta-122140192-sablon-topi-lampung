<?php

namespace Tests\Unit\Email;

use App\Models\EmailChangeRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmailChangeRequestModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function email_change_request_can_be_created()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'newemail@example.com',
            'token' => 'verification_token_123',
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('email_change_requests', [
            'new_email' => 'newemail@example.com',
            'status' => 'pending',
        ]);
    }

    /** @test */
    public function email_change_request_belongs_to_user()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'change@example.com',
            'token' => 'token_abc123',
            'status' => 'pending',
        ]);

        $this->assertTrue($request->user->is($this->user));
    }

    /** @test */
    public function email_change_request_can_be_pending()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'pending@example.com',
            'token' => 'token123',
            'status' => 'pending',
        ]);

        $this->assertEquals('pending', $request->status);
    }

    /** @test */
    public function email_change_request_can_be_verified()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'verify@example.com',
            'token' => 'verify_token',
            'status' => 'pending',
        ]);

        $request->update(['status' => 'verified']);
        $this->assertEquals('verified', $request->fresh()->status);
    }

    /** @test */
    public function email_change_request_can_be_expired()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'expire@example.com',
            'token' => 'expire_token',
            'status' => 'expired',
        ]);

        $this->assertEquals('expired', $request->status);
    }

    /** @test */
    public function email_change_request_has_unique_token()
    {
        $token = 'unique_token_xyz';

        EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'user1@example.com',
            'token' => $token,
            'status' => 'pending',
        ]);

        $found = EmailChangeRequest::where('token', $token)->first();
        $this->assertNotNull($found);
    }

    /** @test */
    public function email_change_request_stores_new_email()
    {
        $newEmail = 'brandnew@example.com';
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => $newEmail,
            'token' => 'brand_token',
            'status' => 'pending',
        ]);

        $this->assertEquals($newEmail, $request->new_email);
    }

    /** @test */
    public function user_can_have_multiple_email_change_requests()
    {
        EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'email1@example.com',
            'token' => 'token1',
            'status' => 'pending',
        ]);

        EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'email2@example.com',
            'token' => 'token2',
            'status' => 'expired',
        ]);

        $requests = EmailChangeRequest::where('user_id', $this->user->id)->get();
        $this->assertEquals(2, $requests->count());
    }

    /** @test */
    public function email_change_request_has_timestamps()
    {
        $request = EmailChangeRequest::create([
            'user_id' => $this->user->id,
            'new_email' => 'timestamp@example.com',
            'token' => 'timestamp_token',
            'status' => 'pending',
        ]);

        $this->assertNotNull($request->created_at);
        $this->assertNotNull($request->updated_at);
    }
}
