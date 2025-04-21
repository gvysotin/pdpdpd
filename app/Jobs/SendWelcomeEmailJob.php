<?php

namespace App\Jobs;

use Throwable;
use App\Mail\WelcomeEmail;
use App\Models\User;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;


class SendWelcomeEmailJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    // Количество попыток на выполнение Job
    public int $tries = 3;

    // Задержка между повторными попытками (в секундах)
    public int $backoff = 10;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public readonly User $user              
    )
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $user = $this->user->refresh(); // получить актуальные данные из БД

        Log::info("Attempt to send email to user with ID: {$this->user->id}");
        
        if ($user->welcome_email_sent_at === null) {
            // Отправляем письмо
            Mail::to($user->email)->send(new WelcomeEmail($user));

            // Обновляем только после успешной отправки
            $user->update([
                'welcome_email_sent_at' => now(),
            ]);
        }
    }

    // Этот метод вызывается, если Job проваливается после всех попыток
    public function failed(Throwable $exception): void
    {

        Log::error('Error sending welcome email to user ID', [
            'user_id' => $this->user->id,
            'error' => $exception->getMessage(),
        ]);

    }    
}
