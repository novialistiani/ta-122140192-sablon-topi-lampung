<?php

namespace Tests\Feature\Chat;

use App\Models\Admin;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatbotApiTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    private function send(string $message, array $extra = [], ?User $as = null)
    {
        if ($as) {
            $this->actingAs($as);
        }

        return $this->postJson(
            route('api.chatbot.send'),
            array_merge(['message' => $message], $extra)
        );
    }

    public function test_guest_gets_bot_response_without_saving_conversation(): void
    {
        $response = $this->send('halo');

        $response->assertOk();
        $this->assertTrue($response->json('success'));
        $this->assertStringContainsString('Selamat datang', $response->json('bot_response'));
        $this->assertNull($response->json('conversation_id'));
        $this->assertDatabaseCount('chat_conversations', 0);
    }

    public function test_logged_in_user_message_and_bot_reply_are_saved(): void
    {
        $response = $this->send('halo', [], $this->user);

        $response->assertOk();
        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->user->id,
            'chat_source' => 'chatbot',
            'status' => 'open',
        ]);
        $this->assertDatabaseHas('chat_messages', [
            'sender_type' => 'user',
            'message' => 'halo',
        ]);
        $this->assertDatabaseHas('chat_messages', ['sender_type' => 'bot']);
    }

    public function test_second_message_reuses_the_same_conversation(): void
    {
        $this->send('halo', [], $this->user);
        $this->send('terima kasih', [], $this->user);

        $this->assertEquals(1, ChatConversation::where('user_id', $this->user->id)->count());
    }

    public function test_empty_message_is_rejected_and_not_saved(): void
    {
        $response = $this->send('', [], $this->user);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_message_longer_than_500_characters_is_rejected(): void
    {
        $response = $this->send(str_repeat('a', 501), [], $this->user);

        $response->assertJson(['success' => false]);
        $this->assertDatabaseCount('chat_messages', 0);
    }

    public function test_unknown_message_returns_default_response(): void
    {
        $response = $this->send('xyzabc');

        $this->assertStringContainsString(
            'belum sepenuhnya memahami',
            $response->json('bot_response')
        );
    }

    public function test_complaint_keyword_escalates_to_admin(): void
    {
        $response = $this->send('saya mau komplain barangnya rusak', [], $this->user);

        $response->assertOk();
        $this->assertTrue($response->json('escalated'));
        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->user->id,
            'needs_admin_response' => true,
        ]);
    }

    public function test_bot_does_not_reply_when_admin_has_taken_over(): void
    {
        ChatConversation::create([
            'user_id' => $this->user->id,
            'status' => 'open',
            'chat_source' => 'chatbot',
            'subject' => 'Customer Chatbot',
            'taken_over_by_admin' => true,
        ]);

        $response = $this->send('halo', [], $this->user);

        $response->assertOk();
        $this->assertNull($response->json('bot_response'));
        $this->assertTrue($response->json('admin_handling'));
        $this->assertDatabaseMissing('chat_messages', ['sender_type' => 'bot']);
        $this->assertDatabaseHas('chat_conversations', [
            'user_id' => $this->user->id,
            'needs_admin_response' => true,
        ]);
    }

    public function test_category_question_lists_active_product_categories(): void
    {
        Product::factory()->create(['category' => 'topi', 'is_active' => true]);

        $response = $this->send('produk apa saja yang dijual');

        $this->assertStringContainsString('Topi', $response->json('bot_response'));
    }

    public function test_custom_design_question_returns_custom_product_count(): void
    {
        Product::factory()->create([
            'is_active' => true,
            'custom_design_allowed' => true,
        ]);

        $response = $this->send('bisa custom desain?');

        $this->assertStringContainsString('Custom Design', $response->json('bot_response'));
        $this->assertStringContainsString('ada 1 produk', $response->json('bot_response'));
    }

    public function test_shipping_question_answers_pickup_only(): void
    {
        $response = $this->send('bisa dikirim ke rumah?');

        $this->assertStringContainsString('pickup', $response->json('bot_response'));
    }

    public function test_promo_question_answers_honestly_that_no_promo_exists(): void
    {
        $response = $this->send('ada promo?');

        $this->assertStringContainsString('belum ada promo', $response->json('bot_response'));
    }

    public function test_product_context_returns_real_price_from_database(): void
    {
        $product = Product::factory()->create([
            'name' => 'Topi Uji Chatbot',
            'price' => 150000,
        ]);

        $response = $this->send('berapa harganya?', [
            'product_context' => ['id' => $product->id],
        ]);

        $this->assertStringContainsString('Topi Uji Chatbot', $response->json('bot_response'));
        $this->assertStringContainsString('150.000', $response->json('bot_response'));
    }

    public function test_cheap_recommendation_returns_product_cards(): void
    {
        Product::factory()->create([
            'is_active' => true,
            'stock' => 10,
            'price' => 50000,
        ]);

        $response = $this->send('rekomendasi produk murah');

        $response->assertOk();
        $this->assertNotEmpty($response->json('products'));
    }

    public function test_history_returns_saved_messages(): void
    {
        $this->send('halo', [], $this->user);

        $response = $this->actingAs($this->user)
            ->getJson(route('api.chatbot.history'));

        $response->assertOk();
        $this->assertCount(2, $response->json('messages'));
    }

    public function test_unread_count_and_mark_as_read(): void
    {
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'status' => 'open',
            'chat_source' => 'chatbot',
            'subject' => 'Customer Chatbot',
        ]);
        ChatMessage::create([
            'conversation_id' => $conversation->id,
            'chat_conversation_id' => $conversation->id,
            'sender_type' => 'admin',
            'message' => 'Balasan admin',
            'is_read_by_user' => false,
        ]);

        $this->actingAs($this->user);

        $this->getJson(route('api.chatbot.unread-count'))
            ->assertJson(['unread_count' => 1]);

        $this->postJson(route('api.chatbot.mark-read'))->assertOk();

        $this->getJson(route('api.chatbot.unread-count'))
            ->assertJson(['unread_count' => 0]);
    }

    public function test_admin_can_take_over_conversation(): void
    {
        $admin = Admin::factory()->create();
        $conversation = ChatConversation::create([
            'user_id' => $this->user->id,
            'status' => 'open',
            'chat_source' => 'chatbot',
            'subject' => 'Customer Chatbot',
        ]);

        $this->actingAs($admin, 'admin')
            ->post(route('chatbot.conversation.takeover', $conversation->id));

        $this->assertTrue((bool) $conversation->fresh()->taken_over_by_admin);
    }
}