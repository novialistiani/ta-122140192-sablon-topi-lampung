<?php

namespace App\Http\Controllers;

use App\Models\NotificationLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ResendWebhookController extends Controller
{
    /**
     * Handle incoming webhooks from Resend
     * 
     * Webhook events:
     * - email.sent: Email was accepted by Resend
     * - email.delivered: Email was successfully delivered
     * - email.delivery_delayed: Delivery was delayed
     * - email.bounced: Email bounced
     * - email.complained: Email was marked as spam
     * - email.opened: Email was opened by recipient
     * - email.clicked: Link in email was clicked
     */
    public function handle(Request $request)
    {
        try {
            // Verify webhook signature for security
            if (!$this->verifySignature($request)) {
                Log::warning('Invalid Resend webhook signature', [
                    'ip' => $request->ip(),
                    'headers' => $request->headers->all()
                ]);
                return response()->json(['error' => 'Invalid signature'], 401);
            }

            $payload = $request->all();
            $eventType = $payload['type'] ?? null;
            
            Log::info('Resend webhook received', [
                'type' => $eventType,
                'payload' => $payload
            ]);

            // Route to appropriate handler based on event type
            switch ($eventType) {
                case 'email.sent':
                    $this->handleEmailSent($payload);
                    break;
                    
                case 'email.delivered':
                    $this->handleEmailDelivered($payload);
                    break;
                    
                case 'email.delivery_delayed':
                    $this->handleEmailDelayed($payload);
                    break;
                    
                case 'email.bounced':
                    $this->handleEmailBounced($payload);
                    break;
                    
                case 'email.complained':
                    $this->handleEmailComplained($payload);
                    break;
                    
                case 'email.opened':
                    $this->handleEmailOpened($payload);
                    break;
                    
                case 'email.clicked':
                    $this->handleEmailClicked($payload);
                    break;
                    
                default:
                    Log::warning('Unknown Resend webhook event type', ['type' => $eventType]);
            }

            return response()->json(['status' => 'success'], 200);
            
        } catch (\Exception $e) {
            Log::error('Error processing Resend webhook', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json(['error' => 'Internal server error'], 500);
        }
    }

    /**
     * Verify Resend webhook signature
     * 
     * @param Request $request
     * @return bool
     */
    protected function verifySignature(Request $request): bool
    {
        // Get signature from header
        $signature = $request->header('svix-signature') ?? $request->header('webhook-signature');
        
        // If no webhook secret configured, skip verification in development
        $webhookSecret = config('services.resend.webhook_secret');
        if (!$webhookSecret) {
            Log::warning('No Resend webhook secret configured - skipping verification');
            return true; // Allow in development, but log warning
        }

        if (!$signature) {
            return false;
        }

        // Get timestamp and signatures from header
        // Format: "v1,g=<signature1>,v1,g=<signature2>"
        $timestamp = $request->header('svix-timestamp');
        $body = $request->getContent();
        
        // Construct signed content
        $signedContent = $timestamp . '.' . $body;
        
        // Compute expected signature
        $expectedSignature = hash_hmac('sha256', $signedContent, $webhookSecret);
        
        // Extract signatures from header
        preg_match_all('/v1,g=([a-f0-9]+)/', $signature, $matches);
        $signatures = $matches[1] ?? [];
        
        // Check if any signature matches
        foreach ($signatures as $sig) {
            if (hash_equals($expectedSignature, $sig)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Handle email.sent event
     */
    protected function handleEmailSent(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            NotificationLog::where('resend_email_id', $emailId)
                ->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'metadata' => json_encode($payload)
                ]);
        }
    }

    /**
     * Handle email.delivered event
     */
    protected function handleEmailDelivered(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            NotificationLog::where('resend_email_id', $emailId)
                ->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                    'metadata' => json_encode($payload)
                ]);
            
            Log::info('Email delivered successfully', [
                'email_id' => $emailId,
                'to' => $payload['data']['to'] ?? null
            ]);
        }
    }

    /**
     * Handle email.delivery_delayed event
     */
    protected function handleEmailDelayed(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            NotificationLog::where('resend_email_id', $emailId)
                ->update([
                    'status' => 'delayed',
                    'metadata' => json_encode($payload)
                ]);
            
            Log::warning('Email delivery delayed', [
                'email_id' => $emailId,
                'reason' => $payload['data']['reason'] ?? 'Unknown'
            ]);
        }
    }

    /**
     * Handle email.bounced event
     */
    protected function handleEmailBounced(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            NotificationLog::where('resend_email_id', $emailId)
                ->update([
                    'status' => 'failed',
                    'error_message' => $payload['data']['bounce_type'] ?? 'Email bounced',
                    'metadata' => json_encode($payload)
                ]);
            
            Log::error('Email bounced', [
                'email_id' => $emailId,
                'bounce_type' => $payload['data']['bounce_type'] ?? null,
                'to' => $payload['data']['to'] ?? null
            ]);
        }
    }

    /**
     * Handle email.complained event (marked as spam)
     */
    protected function handleEmailComplained(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            NotificationLog::where('resend_email_id', $emailId)
                ->update([
                    'status' => 'failed',
                    'error_message' => 'Marked as spam',
                    'metadata' => json_encode($payload)
                ]);
            
            Log::warning('Email marked as spam', [
                'email_id' => $emailId,
                'to' => $payload['data']['to'] ?? null
            ]);
        }
    }

    /**
     * Handle email.opened event
     */
    protected function handleEmailOpened(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            $log = NotificationLog::where('resend_email_id', $emailId)->first();
            
            if ($log) {
                // Update opened status
                $metadata = json_decode($log->metadata, true) ?? [];
                $metadata['opened_at'] = now()->toDateTimeString();
                $metadata['open_count'] = ($metadata['open_count'] ?? 0) + 1;
                $metadata['last_webhook_event'] = $payload;
                
                $log->update([
                    'metadata' => json_encode($metadata)
                ]);
                
                Log::info('Email opened', [
                    'email_id' => $emailId,
                    'open_count' => $metadata['open_count']
                ]);
            }
        }
    }

    /**
     * Handle email.clicked event
     */
    protected function handleEmailClicked(array $payload): void
    {
        $emailId = $payload['data']['email_id'] ?? null;
        
        if ($emailId) {
            $log = NotificationLog::where('resend_email_id', $emailId)->first();
            
            if ($log) {
                // Update clicked status
                $metadata = json_decode($log->metadata, true) ?? [];
                $metadata['clicked_at'] = now()->toDateTimeString();
                $metadata['click_count'] = ($metadata['click_count'] ?? 0) + 1;
                $metadata['clicked_url'] = $payload['data']['click']['url'] ?? null;
                $metadata['last_webhook_event'] = $payload;
                
                $log->update([
                    'metadata' => json_encode($metadata)
                ]);
                
                Log::info('Email link clicked', [
                    'email_id' => $emailId,
                    'url' => $metadata['clicked_url'],
                    'click_count' => $metadata['click_count']
                ]);
            }
        }
    }
}
