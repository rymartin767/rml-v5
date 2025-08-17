<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventTypes;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Hidden::make('user_id')
                    ->default(auth()->id()),

                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('Enter event title'),

                Textarea::make('description')
                    ->maxLength(1000)
                    ->placeholder('Optional event description')
                    ->rows(3),

                DateTimePicker::make('date')
                    ->required()
                    ->default(now())
                    ->seconds(false)
                    ->displayFormat('M j, Y g:i A')
                    ->placeholder('Select event date and time'),

                TextInput::make('location')
                    ->maxLength(255)
                    ->placeholder('Optional event location'),

                Select::make('event_type')
                    ->required()
                    ->options(EventTypes::class)
                    ->default('personal')
                    ->placeholder('Select event type'),

                Toggle::make('is_recurring')
                    ->label('Recurring Event')
                    ->default(false)
                    ->reactive(),

                Select::make('recurrence_pattern')
                    ->options([
                        'daily' => 'Daily',
                        'weekly' => 'Weekly',
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                    ])
                    ->visible(fn ($get) => $get('is_recurring'))
                    ->placeholder('Select recurrence pattern'),

                Select::make('reminder')
                    ->options([
                        15 => '15 minutes before',
                        30 => '30 minutes before',
                        60 => '1 hour before',
                        120 => '2 hours before',
                        1440 => '1 day before',
                    ])
                    ->placeholder('Optional reminder')
                    ->helperText('Set a reminder notification for this event'),
            ]);
    }
}
