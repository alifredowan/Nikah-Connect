<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendTestEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mail:test {email : The recipient email address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send a test email to verify mail configuration and SMTP connectivity';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $recipient = $this->argument('email');
        $mailer = config('mail.default');
        $host = config('mail.mailers.smtp.host');
        $port = config('mail.mailers.smtp.port');

        $this->info("Current Mail Driver: {$mailer}");
        if ($mailer === 'smtp') {
            $this->info("SMTP Host: {$host}:{$port}");
            $this->info('SMTP Username: '.(config('mail.mailers.smtp.username') ?: '(not set)'));
        }

        $this->info("Attempting to send test email to: {$recipient}...");

        try {
            Mail::raw('This is a test email from Nikah Connect to verify your mail delivery setup.', function ($message) use ($recipient) {
                $message->to($recipient)
                    ->subject('Nikah Connect - Mail Delivery Test');
            });

            if ($mailer === 'log') {
                $this->warn('Notice: MAIL_MAILER is set to "log". The email was written to storage/logs/laravel.log instead of sent via SMTP.');
            } else {
                $this->info("Success! Test email was successfully dispatched to {$recipient}.");
            }

            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('Failed to send email: '.$e->getMessage());
            $this->line('Please check your .env MAIL_* configuration.');

            return Command::FAILURE;
        }
    }
}
