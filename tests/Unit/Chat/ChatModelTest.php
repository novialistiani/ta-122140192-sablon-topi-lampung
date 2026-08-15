<?php

namespace Tests\Unit\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatModelTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function chat_conversation_can_be_created()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Pertanyaan tentang order',
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->user->id,
            'subject' => 'Pertanyaan tentang order',
        ]);
    }

    /** @test */
    public function chat_conversation_belongs_to_user()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test conversation',
            'status' => 'open',
        ]);

        $this->assertTrue($conversation->user->is($this->user));
    }

    /** @test */
    public function chat_message_can_be_created()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Halo, saya ingin bertanya tentang pesanan saya',
            'sender_type' => 'customer',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'message' => 'Halo, saya ingin bertanya tentang pesanan saya',
            'sender_type' => 'customer',
        ]);
    }

    /** @test */
    public function chat_message_belongs_to_conversation()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'conversation_id' => $conversation->id, // Primary key for relation
            'chat_conversation_id' => $conversation->id, // Backward compatibility
            'user_id' => $this->user->id,
            'message' => 'Test message',
            'sender_type' => 'customer',
        ]);

        $this->assertTrue($message->conversation->is($conversation));
    }

    /** @test */
    public function chat_conversation_status_can_be_open()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Open status',
            'status' => 'open',
        ]);

        $this->assertEquals('open', $conversation->status);
    }

    /** @test */
    public function chat_conversation_status_can_be_closed()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Close status',
            'status' => 'open',
        ]);

        $conversation->update(['status' => 'closed']);
        $this->assertEquals('closed', $conversation->fresh()->status);
    }

    /** @test */
    public function chat_message_sender_type_customer()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Customer message',
            'sender_type' => 'customer',
        ]);

        $this->assertEquals('customer', $message->sender_type);
    }

    /** @test */
    public function chat_message_sender_type_admin()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => null,
            'message' => 'Admin response',
            'sender_type' => 'admin',
        ]);

        $this->assertEquals('admin', $message->sender_type);
    }

    /** @test */
    public function chat_conversation_can_have_multiple_messages()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Multiple messages',
            'status' => 'open',
        ]);

        for ($i = 1; $i <= 5; $i++) {
            ChatMessage::create([
                'chat_conversation_id' => $conversation->id,
                'user_id' => $this->user->id,
                'message' => 'Message ' . $i,
                'sender_type' => 'customer',
            ]);
        }

        $messages = ChatMessage::where('chat_conversation_id', $conversation->id)->get();
        $this->assertEquals(5, $messages->count());
    }

    /** @test */
    public function chat_message_has_timestamp()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Timestamp test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Test message',
            'sender_type' => 'customer',
        ]);

        $this->assertNotNull($message->created_at);
    }
}
