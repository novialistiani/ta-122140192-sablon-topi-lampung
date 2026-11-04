<?php

namespace Tests\Feature\Chat;

use App\Models\Admin;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ChatStatisticsTest extends TestCase
{
    use RefreshDatabase;

    protected Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::factory()->create();
    }

    private function conversation(User $user, int $createdDaysAgo = 0, array $extra = []): ChatConversation
    {
        $conversation = ChatConversation::create(array_merge([
            'user_id' => $user->id,
            'status' => 'open',
            'chat_source' => 'chatbot',
            'subject' => 'Customer Chatbot',
        ], $extra));

        if ($createdDaysAgo > 0) {
            $conversation->forceFill(['created_at' => now()->subDays($createdDaysAgo)])->save();
        }

        return $conversation;
    }

    private function userMessage(ChatConversation $conversation, string $text, int $sentDaysAgo = 0): ChatMessage
    {
        $message = ChatMessage::create([
            'conversation_id' => $conversation->id,
            'chat_conversation_id' => $conversation->id,
            'user_id' => $conversation->user_id,
            'sender_type' => 'user',
            'message' => $text,
            'is_read_by_admin' => false,
        ]);

        if ($sentDaysAgo > 0) {
            DB::table('chat_messages')->where('id', $message->id)
                ->update(['created_at' => now()->subDays($sentDaysAgo)]);
        }

        return $message;
    }

    private function stats(array $query = [])
    {
        return $this->actingAs($this->admin, 'admin')
            ->get(route('chatbot.statistics', $query));
    }

    public function test_guest_cannot_view_chatbot_statistics(): void
    {
        $this->get(route('chatbot.statistics'))->assertRedirect();
    }

    public function test_admin_can_view_chatbot_statistics_page(): void
    {
        $this->stats()->assertOk();
    }

    public function test_old_conversation_with_recent_messages_is_counted(): void
    {
        $conversation = $this->conversation(User::factory()->create(), 90);
        $this->userMessage($conversation, 'berapa harga topi');

        $this->stats(['days' => 30])
            ->assertViewHas('totalConversations', 1)
            ->assertViewHas('totalUserMessages', 1);
    }

    public function test_messages_outside_the_range_are_excluded(): void
    {
        $conversation = $this->conversation(User::factory()->create(), 90);
        $this->userMessage($conversation, 'halo', 40);

        $this->stats(['days' => 7])
            ->assertViewHas('totalConversations', 0)
            ->assertViewHas('totalUserMessages', 0);
    }

    public function test_all_period_counts_every_conversation(): void
    {
        $first = $this->conversation(User::factory()->create(), 90);
        $second = $this->conversation(User::factory()->create());
        $this->userMessage($first, 'halo', 40);
        $this->userMessage($second, 'halo');

        $this->stats(['days' => 0])
            ->assertViewHas('totalConversations', 2)
            ->assertViewHas('totalUserMessages', 2);
    }

    public function test_most_discussed_topic_is_counted_from_keywords(): void
    {
        $conversation = $this->conversation(User::factory()->create());
        $this->userMessage($conversation, 'berapa harga topi');
        $this->userMessage($conversation, 'bisa custom desain logo?');

        $this->stats(['days' => 30])->assertViewHas('topics', function ($topics) {
            return ($topics['Harga'] ?? 0) === 1
                && ($topics['Custom Design'] ?? 0) === 1;
        });
    }

    public function test_escalated_conversation_is_reflected_in_escalation_rate(): void
    {
        $escalated = $this->conversation(User::factory()->create(), 0, ['needs_admin_response' => true]);
        $normal = $this->conversation(User::factory()->create());
        $this->userMessage($escalated, 'barang saya rusak');
        $this->userMessage($normal, 'halo');

        $this->stats(['days' => 30])
            ->assertViewHas('escalated', 1)
            ->assertViewHas('escalationRate', 50.0);
    }

    public function test_invalid_period_falls_back_to_30_days(): void
    {
        $this->stats(['days' => 5])->assertViewHas('days', 30);
    }
}