<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Enums\EventTypes;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->schema([
            Section::make('Event Details')
                ->description('Basic event information and scheduling')
                ->schema([
                    Grid::make(2)
                        ->schema([
                            TextEntry::make('title')
                                ->label('Event Title')
                                ->weight('bold')
                                ->size('lg'),

                            TextEntry::make('event_type')
                                ->label('Event Type')
                                ->badge()
                                ->color(fn (string $state): string => EventTypes::from($state)->color()),
                        ])
                        ->columnSpanFull(),

                    TextEntry::make('description')
                        ->label('Description')
                        ->markdown()
                        ->placeholder('No description provided')
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('date')
                                ->label('Date & Time')
                                ->dateTime('M j, Y g:i A'),

                            TextEntry::make('location')
                                ->label('Location')
                                ->placeholder('No location specified'),
                        ])
                        ->columnSpanFull(),

                    Grid::make(2)
                        ->schema([
                            TextEntry::make('is_recurring')
                                ->label('Recurring Event'),

                            TextEntry::make('recurrence_pattern')
                                ->label('Recurrence Pattern')
                                ->placeholder('Not applicable'),

                            TextEntry::make('reminder')
                                ->label('Reminder')
                                ->formatStateUsing(function ($state): string {
                                    if ($state) {
                                        return "{$state} minutes before";
                                    }

                                    return 'No reminder set';
                                }),
                        ])
                        ->columnSpanFull(),
                ])
                ->columnSpanFull()
                ->collapsible(),
        ]);
    }
}
