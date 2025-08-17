<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Seeder;

class EventSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first() ?? User::factory()->create();

        // Create some sample events
        Event::create([
            'user_id' => $user->id,
            'title' => 'Team Meeting',
            'description' => 'Weekly team sync meeting',
            'date' => now()->addDay()->setTime(9, 0),
            'location' => 'Conference Room A',
            'event_type' => 'work',
            'is_recurring' => true,
            'recurrence_pattern' => 'weekly',
            'reminder' => 30,
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Dinner with Friends',
            'description' => 'Catch up with old friends',
            'date' => now()->addDays(2)->setTime(19, 0),
            'location' => 'Local Restaurant',
            'event_type' => 'social',
            'is_recurring' => false,
            'reminder' => 60,
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Doctor Appointment',
            'description' => 'Annual checkup',
            'date' => now()->addDays(5)->setTime(14, 30),
            'location' => 'Medical Center',
            'event_type' => 'personal',
            'is_recurring' => false,
            'reminder' => 120,
        ]);

        Event::create([
            'user_id' => $user->id,
            'title' => 'Family Dinner',
            'description' => 'Sunday family dinner',
            'date' => now()->next('Sunday')->setTime(18, 0),
            'location' => 'Home',
            'event_type' => 'family',
            'is_recurring' => true,
            'recurrence_pattern' => 'weekly',
            'reminder' => 60,
        ]);
    }
}
