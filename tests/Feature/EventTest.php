<?php

declare(strict_types=1);

use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('can create an event', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'title' => 'Test Event',
        'event_type' => 'personal',
        'date' => now()->addDay(),
    ]);

    expect($event->title)->toBe('Test Event');
    expect($event->event_type)->toBe('personal');
    expect($event->user_id)->toBe($this->user->id);
});

it('belongs to a user', function () {
    $event = Event::factory()->create(['user_id' => $this->user->id]);

    expect($event->user)->toBeInstanceOf(User::class);
    expect($event->user->id)->toBe($this->user->id);
});

it('can create and retrieve events', function () {
    // Create events directly
    $personalEvent = Event::create([
        'user_id' => $this->user->id,
        'title' => 'Personal Event',
        'date' => now()->addDay(),
        'event_type' => 'personal',
    ]);

    $workEvent = Event::create([
        'user_id' => $this->user->id,
        'title' => 'Work Event',
        'date' => now()->addDay(),
        'event_type' => 'work',
    ]);

    // Verify they exist in database
    $this->assertDatabaseHas('events', [
        'id' => $personalEvent->id,
        'title' => 'Personal Event',
        'event_type' => 'personal',
    ]);

    $this->assertDatabaseHas('events', [
        'id' => $workEvent->id,
        'title' => 'Work Event',
        'event_type' => 'work',
    ]);

    // Test basic retrieval
    $retrievedPersonal = Event::find($personalEvent->id);
    $retrievedWork = Event::find($workEvent->id);

    expect($retrievedPersonal->title)->toBe('Personal Event');
    expect($retrievedWork->title)->toBe('Work Event');
});

it('has formatted date attributes', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'date' => '2024-01-15 14:30:00',
    ]);

    expect($event->formatted_date)->toBe('Jan 15, 2024 2:30 PM');
    expect($event->formatted_time)->toBe('2:30 PM');
});

it('has event type colors', function () {
    $personalEvent = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type' => 'personal',
    ]);

    $workEvent = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type' => 'work',
    ]);

    $socialEvent = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type' => 'social',
    ]);

    $familyEvent = Event::factory()->create([
        'user_id' => $this->user->id,
        'event_type' => 'family',
    ]);

    expect($personalEvent->event_type_color)->toBe('blue');
    expect($workEvent->event_type_color)->toBe('green');
    expect($socialEvent->event_type_color)->toBe('purple');
    expect($familyEvent->event_type_color)->toBe('orange');
});

it('can handle recurring events', function () {
    $recurringEvent = Event::factory()->create([
        'user_id' => $this->user->id,
        'is_recurring' => true,
        'recurrence_pattern' => 'weekly',
    ]);

    expect($recurringEvent->is_recurring)->toBeTrue();
    expect($recurringEvent->recurrence_pattern)->toBe('weekly');
});

it('can handle events with reminders', function () {
    $eventWithReminder = Event::factory()->create([
        'user_id' => $this->user->id,
        'reminder' => 60, // 1 hour before
    ]);

    expect($eventWithReminder->reminder)->toBe(60);
});

it('casts date to datetime', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'date' => '2024-01-15 14:30:00',
    ]);

    expect($event->date)->toBeInstanceOf(Carbon::class);
    expect($event->date->format('Y-m-d H:i:s'))->toBe('2024-01-15 14:30:00');
});

it('casts boolean fields correctly', function () {
    $event = Event::factory()->create([
        'user_id' => $this->user->id,
        'is_recurring' => true,
    ]);

    expect($event->is_recurring)->toBeTrue();
    expect(is_bool($event->is_recurring))->toBeTrue();
});
