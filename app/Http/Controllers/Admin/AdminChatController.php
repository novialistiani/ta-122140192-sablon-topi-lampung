<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ChatConversation;
use App\Models\ChatMessage;
use App\Models\User;
use App\Services\ChatBotService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminChatController extends Controller
{
    private $chatBotService;

    public function __construct(ChatBotService $chatBotService)
    {
        $this->chatBotService = $chatBotService;
    }

    /**
     * Display chatbot management page
     * Shows all conversations that need admin attention
     * Groups by user - shows one representative conversation per user
     * But displays aggregate data from ALL user's conversations
     */
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all'); // all, needs_response, handled
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 20;

        // Get ALL conversation IDs per user, then we'll aggregate messages
        $usersWithConversations = ChatConversation::whereNotNull('user_id')
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');

        // Get one conversation per user (prefer open ones, or latest by updated_at)
        $representativeConvIds = [];
        foreach ($usersWithConversations as $userId) {
            $conv = ChatConversation::where('user_id', $userId)
                ->orderByRaw("CASE WHEN status = 'open' THEN 0 ELSE 1 END")
                ->orderBy('updated_at', 'desc')
                ->first();
            if ($conv) {
                $representativeConvIds[] = $conv->id;
            }
        }

        $query = ChatConversation::with(['user', 'product', 'admin'])
            ->whereIn('id', $representativeConvIds)
            ->orderBy('updated_at', 'desc');

        // Apply filters
        if ($filter === 'needs_response') {
            $query->where('needs_admin_response', true);
        } elseif ($filter === 'handled') {
            $query->where('taken_over_by_admin', true);
        }

        // Apply search
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })->orWhereHas('product', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })->orWhere('subject', 'like', "%{$search}%");
        }

        $conversations = $query->paginate($perPage);
        
        // Add aggregate data for each conversation (from ALL user's conversations)
        foreach ($conversations as $conv) {
            // Get all conversation IDs for this user
            $userConvIds = ChatConversation::where('user_id', $conv->user_id)->pluck('id');
            
            // Count unread messages from ALL user's conversations
            $conv->unread_count = ChatMessage::whereIn('conversation_id', $userConvIds)
                ->where('sender_type', 'user')
                ->where('is_read_by_admin', false)
                ->count();
            
            // Get latest message from ALL user's conversations
            $conv->latestMessage = ChatMessage::whereIn('conversation_id', $userConvIds)
                ->orderBy('created_at', 'desc')
                ->first();
                
            // Count total messages from ALL user's conversations
            $conv->total_messages = ChatMessage::whereIn('conversation_id', $userConvIds)->count();
        }

        return view('admin.chatbot.index', compact('conversations', 'filter', 'search'));
    }

    /**
     * Get conversation detail - API endpoint
     * Gets ALL messages from ALL conversations of this user (unified view)
     */
    public function getConversation($conversationId)
    {
        $conversation = ChatConversation::with(['user', 'product', 'admin'])->findOrFail($conversationId);

        // Get ALL conversations from this user
        $userConversationIds = ChatConversation::where('user_id', $conversation->user_id)
            ->pluck('id');

        // Get ALL messages from ALL user's conversations, ordered by time
        $allMessages = ChatMessage::whereIn('conversation_id', $userConversationIds)
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark all unread messages from this user as read by admin
        ChatMessage::whereIn('conversation_id', $userConversationIds)
            ->where('sender_type', 'user')
            ->where('is_read_by_admin', false)
            ->update(['is_read_by_admin' => true]);

        // Attach messages to conversation for response
        $conversation->setRelation('messages', $allMessages);

        return response()->json([
            'success' => true,
            'conversation' => $conversation
        ]);
    }

    /**
     * Ambil alih konversasi dari bot/auto-response
     */
    public function takeOverConversation(Request $request, $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);
        $adminId = Auth::guard('admin')->id();

        try {
            $conversation->update([
                'taken_over_by_admin' => true,
                'taken_over_at' => now(),
                'admin_id' => $adminId,
                'is_admin_active' => true
            ]);

            // Log activity
            Log::info('Admin took over conversation', [
                'conversation_id' => $conversationId,
                'admin_id' => $adminId,
                'customer_id' => $conversation->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Anda telah mengambil alih konversasi',
                'conversation' => $conversation
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to take over conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil alih konversasi'
            ], 500);
        }
    }

    /**
     * Kirim pesan dari admin ke customer
     * Pesan akan disimpan ke conversation aktif user untuk menjaga unified history
     */
    public function sendAdminMessage(Request $request, $conversationId)
    {
        $request->validate([
            'message' => 'required|string|max:2000'
        ]);

        $conversation = ChatConversation::findOrFail($conversationId);
        $adminId = Auth::guard('admin')->id();

        // Ensure admin has access: auto take over if not claimed
        if (!$conversation->taken_over_by_admin || $conversation->admin_id === null) {
            $conversation->update([
                'taken_over_by_admin' => true,
                'admin_id' => $adminId,
                'is_admin_active' => true,
                'taken_over_at' => now(),
            ]);
        } elseif ($conversation->admin_id !== $adminId) {
            // Prevent replying to a conversation owned by another admin
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki akses untuk menjawab konversasi ini'
            ], 403);
        }

        try {
            // Find or get the most active conversation for this user (prefer open chatbot)
            $targetConversation = ChatConversation::where('user_id', $conversation->user_id)
                ->where('chat_source', 'chatbot')
                ->where('status', 'open')
                ->first();
            
            // If no open chatbot conversation, use the one with most recent messages
            if (!$targetConversation) {
                $targetConversation = ChatConversation::where('user_id', $conversation->user_id)
                    ->orderBy('updated_at', 'desc')
                    ->first();
            }
            
            // If still no conversation found, reopen the current one
            if (!$targetConversation) {
                $targetConversation = $conversation;
                $targetConversation->update(['status' => 'open']);
            }
            
            $targetConversationId = $targetConversation->id;

            // Create admin message in the target conversation
            $message = ChatMessage::create([
                'conversation_id' => $targetConversationId,
                'chat_conversation_id' => $targetConversationId,
                'sender_type' => 'admin',
                'message' => $request->message,
                'is_admin_reply' => true,
                'is_read_by_user' => false
            ]);

            // Update target conversation status
            $targetConversation->update([
                'taken_over_by_admin' => true,
                'admin_id' => $adminId,
                'is_admin_active' => true,
                'needs_admin_response' => false,
                'needs_response_since' => null,
                'status' => 'open',
                'updated_at' => now()
            ]);

            // Log activity
            Log::info('Admin sent message', [
                'original_conversation_id' => $conversationId,
                'target_conversation_id' => $targetConversationId,
                'message_id' => $message->id,
                'admin_id' => $adminId,
                'customer_id' => $conversation->user_id
            ]);

            // Note: Chat notifications are shown as badge on chat icon only, not in notification page
            // The is_read_by_user field is used to track unread messages
            // Commented out: notifyCustomerChatReply was sending to main notification system
            // $adminName = Auth::guard('admin')->user()->name ?? 'Admin';
            // app(NotificationService::class)->notifyCustomerChatReply(
            //     $conversationId,
            //     $conversation->user_id,
            //     $adminName
            // );

            return response()->json([
                'success' => true,
                'message' => 'Pesan terkirim',
                'data' => $message
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to send admin message', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim pesan'
            ], 500);
        }
    }

    /**
     * Tandai bahwa customer perlu jawaban langsung dari admin
     * Trigger ini bisa dari customer atau dari system
     */
    public function markNeedsAdminResponse(Request $request, $conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            $conversation->update([
                'needs_admin_response' => true,
                'needs_response_since' => now(),
            ]);

            // Create system notification message
            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => '⚠️ Customer meminta jawaban langsung dari admin',
                'metadata' => [
                    'system_notification' => true,
                    'type' => 'needs_response'
                ]
            ]);

            // TODO: Broadcast notification ke admin
            // TODO: Send notification email ke admin

            Log::info('Conversation marked as needing admin response', [
                'conversation_id' => $conversationId,
                'customer_id' => $conversation->user_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Admin telah diberitahu bahwa Anda memerlukan jawaban langsung'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to mark needs admin response', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal mengirim notifikasi ke admin'
            ], 500);
        }
    }

    /**
     * Close/resolve conversation
     */
    public function closeConversation(Request $request, $conversationId)
    {
        $request->validate([
            'resolution_notes' => 'nullable|string|max:500'
        ]);

        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            $conversation->update([
                'status' => 'closed',
                'taken_over_by_admin' => false,
                'is_admin_active' => false
            ]);

            // Create system message
            if ($request->resolution_notes) {
                ChatMessage::create([
                    'conversation_id' => $conversationId,
                    'sender_type' => 'system',
                    'message' => "Chat ditutup. Catatan: {$request->resolution_notes}",
                    'metadata' => ['type' => 'closure']
                ]);
            }

            Log::info('Conversation closed', [
                'conversation_id' => $conversationId,
                'resolution_notes' => $request->resolution_notes
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Konversasi berhasil ditutup'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to close conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal menutup konversasi'
            ], 500);
        }
    }

    /**
     * Release conversation back to bot
     */
    public function releaseConversation($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            $conversation->update([
                'taken_over_by_admin' => false,
                'is_admin_active' => false,
                'admin_id' => null,
                'taken_over_at' => null
            ]);

            ChatMessage::create([
                'conversation_id' => $conversationId,
                'sender_type' => 'system',
                'message' => 'Admin melepaskan konversasi kembali ke chatbot otomatis',
                'metadata' => ['type' => 'release']
            ]);

            Log::info('Conversation released back to bot', [
                'conversation_id' => $conversationId
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Konversasi dilepas kembali ke chatbot'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to release conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Gagal melepas konversasi'
            ], 500);
        }
    }

    /**
     * Get unread conversations count
     */
    public function getUnreadCount()
    {
        $unreadNeedsResponse = ChatConversation::where('needs_admin_response', true)
            ->where('taken_over_by_admin', false)
            ->count();

        $unreadMessages = ChatMessage::where('sender_type', 'user')
            ->where('is_read_by_admin', false)
            ->count();

        return response()->json([
            'needs_response' => $unreadNeedsResponse,
            'unread_messages' => $unreadMessages,
            'total' => $unreadNeedsResponse
        ]);
    }

    /**
     * Get conversations that need attention
     */
    public function getConversationsNeedingAttention()
    {
        $conversations = ChatConversation::where('needs_admin_response', true)
        ->where('taken_over_by_admin', false)
        ->with(['user', 'product', 'latestMessage'])
        ->orderBy('updated_at', 'desc')
        ->limit(10)
        ->get();

        return response()->json([
            'success' => true,
            'conversations' => $conversations,
            'count' => $conversations->count()
        ]);
    }

    /**
     * Mark conversation as read by admin
     */
    public function markConversationAsRead($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            $conversation->messages()
                ->where('sender_type', 'user')
                ->where('is_read_by_admin', false)
                ->update(['is_read_by_admin' => true]);

            return response()->json([
                'success' => true,
                'message' => 'Konversasi ditandai sudah dibaca'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menandai sebagai dibaca'
            ], 500);
        }
    }

    /**
     * Delete conversation and all messages
     */
    public function deleteConversation($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            // Delete all messages first
            $conversation->messages()->delete();
            
            // Delete conversation
            $conversation->delete();

            Log::info('Admin deleted conversation', [
                'conversation_id' => $conversationId,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat chat berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to delete conversation', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus riwayat chat'
            ], 500);
        }
    }

    /**
     * Clear chat history (messages only, keep conversation)
     */
    public function clearChatHistory($conversationId)
    {
        $conversation = ChatConversation::findOrFail($conversationId);

        try {
            // Delete all messages
            $conversation->messages()->delete();
            
            // Add system message
            ChatMessage::create([
                'conversation_id' => $conversation->id,
                'chat_conversation_id' => $conversation->id,
                'sender_type' => 'bot',
                'message' => 'Riwayat chat telah dihapus oleh admin.',
                'is_read_by_user' => false
            ]);

            Log::info('Admin cleared chat history', [
                'conversation_id' => $conversationId,
                'admin_id' => Auth::guard('admin')->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Riwayat chat berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to clear chat history', [
                'conversation_id' => $conversationId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus riwayat chat'
            ], 500);
        }
    }
}
