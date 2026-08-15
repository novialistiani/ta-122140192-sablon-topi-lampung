<?php

namespace Tests\Feature\Chat;

use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function user_can_create_chat_conversation()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Pertanyaan tentang pengiriman',
            'status' => 'open',
        ]);

        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->user->id,
            'subject' => 'Pertanyaan tentang pengiriman',
        ]);
    }

    /** @test */
    public function user_can_send_chat_message()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Halo, saya ingin bertanya tentang produk',
            'sender_type' => 'customer',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'chat_conversation_id' => $conversation->id,
            'message' => 'Halo, saya ingin bertanya tentang produk',
        ]);
    }

    /** @test */
    public function admin_can_reply_to_chat_message()
    {
        $admin = User::factory()->create();
        
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Pertanyaan dari user',
            'sender_type' => 'customer',
        ]);

        $reply = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'message' => 'Jawaban dari admin',
            'sender_type' => 'admin',
        ]);

        $this->assertDatabaseHas('chat_messages', [
            'message' => 'Jawaban dari admin',
            'sender_type' => 'admin',
        ]);
    }

    /** @test */
    public function chat_conversation_can_be_closed()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $conversation->update(['status' => 'closed']);

        $this->assertEquals('closed', $conversation->fresh()->status);
    }

    /** @test */
    public function chat_conversation_messages_are_tracked()
    {
        $admin = User::factory()->create();
        
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Pesan 1',
            'sender_type' => 'customer',
        ]);

        ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $admin->id,
            'message' => 'Pesan 2',
            'sender_type' => 'admin',
        ]);

        $messages = ChatMessage::where('chat_conversation_id', $conversation->id)->get();

        $this->assertCount(2, $messages);
    }

    /** @test */
    public function chat_message_sender_type_is_validated()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $message = ChatMessage::create([
            'chat_conversation_id' => $conversation->id,
            'user_id' => $this->user->id,
            'message' => 'Test message',
            'sender_type' => 'customer',
        ]);

        $this->assertEquals('customer', $message->sender_type);
    }

    /** @test */
    public function chat_conversation_timestamp_is_recorded()
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Test',
            'status' => 'open',
        ]);

        $this->assertNotNull($conversation->created_at);
        $this->assertNotNull($conversation->updated_at);
    }

    /** @test */
    public function multiple_conversations_per_user()
    {
        ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Percakapan 1',
            'status' => 'open',
        ]);

        ChatConversation::create([
            'user_id' => $this->user->id,
            'subject' => 'Percakapan 2',
            'status' => 'open',
        ]);

        $conversations = ChatConversation::where('user_id', $this->user->id)->get();

        $this->assertCount(2, $conversations);
    }
}
