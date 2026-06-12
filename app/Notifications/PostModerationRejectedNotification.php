<?php

namespace App\Notifications;

use App\Enums\AppNotificationType;
use App\Models\Article;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class PostModerationRejectedNotification extends Notification
{
    use Queueable;

    public function __construct(public Article $article) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function databaseType(object $notifiable): string
    {
        return AppNotificationType::PostRejected->value;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $message = 'Bài «'.$this->article->title.'» bị từ chối';

        if (filled($this->article->moderation_note)) {
            $message .= ': '.$this->article->moderation_note;
        }

        return [
            'type' => AppNotificationType::PostRejected->value,
            'message' => $message,
            'url' => route('community.my-posts', ['tab' => 'rejected']),
            'article_title' => $this->article->title,
        ];
    }
}
