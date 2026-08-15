<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class TestEmail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:test-email {email?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test sending email via configured mailer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email') ?? 'muhammad.122140058@student.itera.ac.id';
        
        $this->info("Testing email configuration...");
        $this->info("Mailer: " . config('mail.default'));
        $this->info("From: " . config('mail.from.address'));
        $this->info("To: " . $email);
        
        try {
            Mail::raw('Ini adalah test email dari LGI Store. Jika Anda menerima email ini, berarti konfigurasi email sudah benar!', function ($message) use ($email) {
                $message->to($email)
                        ->subject('Test Email - LGI Store');
            });
            
            $this->info("✅ Email berhasil dikirim!");
            return 0;
        } catch (\Exception $e) {
            $this->error("❌ Gagal mengirim email:");
            $this->error($e->getMessage());
            return 1;
        }
    }
}
