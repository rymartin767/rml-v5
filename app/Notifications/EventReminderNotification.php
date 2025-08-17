<?php

namespace App\Notifications;

use App\Models\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EventReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public Event $event
    ) {}

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
        $eventTime = $this->event->date->format('g:i A');
        $eventDate = $this->event->date->format('l, F j, Y');

        return (new MailMessage)
            ->subject("Reminder: {$this->event->title} starts soon")
            ->greeting("Hello {$notifiable->name}!")
            ->line("This is a reminder that your event **{$this->event->title}** is starting soon.")
            ->line('**Event Details:**')
            ->line("📅 **Date:** {$eventDate}")
            ->line("🕐 **Time:** {$eventTime}")
            ->when($this->event->location, function ($message) {
                return $message->line("📍 **Location:** {$this->event->location}");
            })
            ->when($this->event->description, function ($message) {
                return $message->line("📝 **Description:** {$this->event->description}");
            })
            ->line('**Event Type:** '.ucfirst($this->event->event_type))
            ->when($this->event->is_recurring, function ($message) {
                return $message->line('🔄 This is a recurring event');
            })
            ->action('View Event Details', route('filament.admin.resources.events.view', $this->event))
            ->line('Thank you for using our application!');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event_id' => $this->event->id,
            'event_title' => $this->event->title,
            'event_date' => $this->event->date,
            'event_type' => $this->event->event_type,
            'reminder_minutes' => $this->event->reminder,
        ];
    }
}
