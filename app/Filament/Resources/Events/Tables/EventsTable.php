<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->limit(50),

                TextColumn::make('event_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'personal' => 'info',
                        'work' => 'danger',
                        'social' => 'warning',
                        'family' => 'success',
                        default => 'gray',
                    }),

                TextColumn::make('date')
                    ->dateTime('M j, Y g:i A')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('location')
                    ->searchable()
                    ->limit(30)
                    ->placeholder('No location'),

                TextColumn::make('is_recurring')
                    ->badge()
                    ->label('Recurring'),

                TextColumn::make('reminder')
                    ->formatStateUsing(fn ($state) => $state ? "{$state} min" : 'None')
                    ->placeholder('No reminder'),
            ])
            ->filters([
                SelectFilter::make('event_type')
                    ->options([
                        'personal' => 'Personal',
                        'work' => 'Work',
                        'social' => 'Social',
                        'family' => 'Family',
                    ])
                    ->placeholder('All types'),

                Filter::make('upcoming')
                    ->query(fn (Builder $query): Builder => $query->upcoming())
                    ->label('Upcoming Events'),

                Filter::make('today')
                    ->query(fn (Builder $query): Builder => $query->today())
                    ->label('Today\'s Events'),

                Filter::make('this_month')
                    ->query(fn (Builder $query): Builder => $query->thisMonth())
                    ->label('This Month'),

                Filter::make('has_reminder')
                    ->query(fn (Builder $query): Builder => $query->whereNotNull('reminder'))
                    ->label('Has Reminder'),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('date', 'asc')
            ->searchable()
            ->paginated([10, 25, 50]);
    }
}
