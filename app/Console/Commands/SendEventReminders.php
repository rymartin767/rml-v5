<?php

namespace App\Console\Commands;

use App\Models\Event;
use App\Notifications\EventReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class SendEventReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'events:send-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send reminder notifications for upcoming events';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('Starting to send event reminders...');

        $now = Carbon::now();
        $remindersSent = 0;

        // Get events that need reminders sent
        $events = Event::query()
            ->whereNotNull('reminder')
            ->where('date', '>', $now)
            ->where('date', '<=', $now->copy()->addMinutes(30)) // Check next 30 minutes
            ->with('user')
            ->get();

        foreach ($events as $event) {
            $reminderTime = $event->date->subMinutes($event->reminder);

            // Check if it's time to send the reminder
            if ($now->between($reminderTime->subMinute(), $reminderTime->addMinute())) {
                try {
                    $event->user->notify(new EventReminderNotification($event));
                    $remindersSent++;

                    $this->info("Sent reminder for event: {$event->title} (ID: {$event->id})");
                } catch (\Exception $e) {
                    $this->error("Failed to send reminder for event {$event->id}: ".$e->getMessage());
                }
            }
        }

        $this->info("Completed! Sent {$remindersSent} reminders.");

        return Command::SUCCESS;
    }
}
