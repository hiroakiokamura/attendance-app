<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EmailVerificationNotification extends Notification
{
    use Queueable;

    protected $verificationUrl;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $verificationUrl)
    {
        $this->verificationUrl = $verificationUrl;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('【' . config('app.name') . '】メールアドレス認証のお願い')
            ->greeting('こんにちは、' . $notifiable->name . 'さん')
            ->line('会員登録いただき、ありがとうございます。')
            ->line('メールアドレスの認証を完了するため、下記のボタンをクリックしてください。')
            ->action('メールアドレスを認証する', $this->verificationUrl)
            ->line('このリンクは24時間後に期限切れとなります。')
            ->line('もしこのメールに心当たりがない場合は、このメールを無視してください。')
            ->salutation('よろしくお願いいたします。');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}