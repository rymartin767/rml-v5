<?php

namespace App\Livewire\Dashboard;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Events extends Component
{
    use WithPagination;

    public $showCreateModal = false;

    public $search = '';

    public $selectedMonth;

    public $selectedYear;

    protected $queryString = [
        'search' => ['except' => ''],
        'selectedMonth' => ['except' => ''],
        'selectedYear' => ['except' => ''],
    ];

    public function mount()
    {
        $this->selectedMonth = now()->month;
        $this->selectedYear = now()->year;
    }

    public function updatedSearch()
    {
        $this->resetPage();
    }

    public function updatedSelectedMonth()
    {
        $this->resetPage();
    }

    public function updatedSelectedYear()
    {
        $this->resetPage();
    }

    public function openCreateModal()
    {
        $this->showCreateModal = true;
    }

    public function closeCreateModal()
    {
        $this->showCreateModal = false;
    }

    public function getEventsProperty()
    {
        return Event::query()
            ->where('user_id', Auth::id())
            ->when($this->search, function ($query) {
                $query->where('title', 'like', '%'.$this->search.'%')
                    ->orWhere('description', 'like', '%'.$this->search.'%');
            })
            ->when($this->selectedMonth, function ($query) {
                $query->whereMonth('date', $this->selectedMonth);
            })
            ->when($this->selectedYear, function ($query) {
                $query->whereYear('date', $this->selectedYear);
            })
            ->orderBy('date', 'asc')
            ->paginate(10);
    }

    public function getTodaysEventsProperty()
    {
        return Event::query()
            ->where('user_id', Auth::id())
            ->today()
            ->orderBy('date', 'asc')
            ->get();
    }

    public function getUpcomingEventsProperty()
    {
        return Event::query()
            ->where('user_id', Auth::id())
            ->upcoming()
            ->limit(5)
            ->orderBy('date', 'asc')
            ->get();
    }

    public function getMonthNameProperty()
    {
        return now()->month($this->selectedMonth)->format('F');
    }

    public function getPreviousMonthProperty()
    {
        $date = now()->month($this->selectedMonth)->subMonth();

        return $date->month;
    }

    public function getNextMonthProperty()
    {
        $date = now()->month($this->selectedMonth)->addMonth();

        return $date->month;
    }

    public function previousMonth()
    {
        if ($this->selectedMonth == 1) {
            $this->selectedMonth = 12;
            $this->selectedYear--;
        } else {
            $this->selectedMonth--;
        }
        $this->resetPage();
    }

    public function nextMonth()
    {
        if ($this->selectedMonth == 12) {
            $this->selectedMonth = 1;
            $this->selectedYear++;
        } else {
            $this->selectedMonth++;
        }
        $this->resetPage();
    }

    public function render()
    {
        return <<<'HTML'
        <div class="space-y-6">
            <!-- Header with Search and Create Button -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex-1">
                    <flux:input
                        wire:model.live.debounce.300ms="search"
                        placeholder="Search events..."
                        class="max-w-md"
                    />
                </div>
                <flux:button wire:click="openCreateModal" variant="ghost">
                    Add Event
                </flux:button>
            </div>

            <!-- Today's Events Highlight -->
            @if($this->todaysEvents->count() > 0)
                <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                    <h3 class="text-lg font-semibold text-blue-900 dark:text-blue-100 mb-3">
                        Today's Events
                    </h3>
                    <div class="space-y-2">
                        @foreach($this->todaysEvents as $event)
                            <div class="flex items-center justify-between p-3 bg-white dark:bg-gray-800 rounded-md border border-blue-200 dark:border-blue-700">
                                <div class="flex items-center space-x-3">
                                    <div class="w-3 h-3 rounded-full bg-{{ $event->event_type_color }}-500"></div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->formatted_time }}</div>
                                    </div>
                                </div>
                                @if($event->location)
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->location }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Monthly Calendar View -->
            <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700">
                <!-- Calendar Header -->
                <div class="flex items-center justify-between p-4 border-b border-gray-200 dark:border-gray-700">
                    <flux:button
                        wire:click="previousMonth"
                        variant="ghost"
                        size="sm"
                    >
                        <flux:icon.chevron-left class="w-4 h-4" />
                    </flux:button>
                    
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-gray-100">
                        {{ $this->monthName }} {{ $this->selectedYear }}
                    </h2>
                    
                    <flux:button
                        wire:click="nextMonth"
                        variant="ghost"
                        size="sm"
                    >
                        <flux:icon.chevron-right class="w-4 h-4" />
                    </flux:button>
                </div>

                <!-- Events List -->
                <div class="p-4">
                    @if($this->events->count() > 0)
                        <div class="space-y-3">
                            @foreach($this->events as $event)
                                <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-gray-700 rounded-lg border border-gray-200 dark:border-gray-600">
                                    <div class="flex items-center space-x-4">
                                        <div class="text-center">
                                            <div class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                                                {{ $event->date->format('j') }}
                                            </div>
                                            <div class="text-sm text-gray-500 dark:text-gray-400">
                                                {{ $event->date->format('M') }}
                                            </div>
                                        </div>
                                        
                                        <div class="flex-1">
                                            <div class="flex items-center space-x-2 mb-1">
                                                <span class="px-2 py-1 text-xs font-medium rounded-full bg-{{ $event->event_type_color }}-100 text-{{ $event->event_type_color }}-800 dark:bg-{{ $event->event_type_color }}-900 dark:text-{{ $event->event_type_color }}-200">
                                                    {{ ucfirst($event->event_type) }}
                                                </span>
                                                @if($event->is_recurring)
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                        Recurring
                                                    </span>
                                                @endif
                                                @if($event->reminder)
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200">
                                                        Reminder
                                                    </span>
                                                @endif
                                            </div>
                                            
                                            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">
                                                {{ $event->title }}
                                            </h3>
                                            
                                            @if($event->description)
                                                <p class="text-gray-600 dark:text-gray-300 text-sm mb-2">
                                                    {{ Str::limit($event->description, 100) }}
                                                </p>
                                            @endif
                                            
                                            <div class="flex items-center space-x-4 text-sm text-gray-500 dark:text-gray-400">
                                                <div class="flex items-center space-x-1">
                                                    <flux:icon.clock class="w-4 h-4" />
                                                    <span>{{ $event->formatted_time }}</span>
                                                </div>
                                                @if($event->location)
                                                    <div class="flex items-center space-x-1">
                                                        <flux:icon.map-pin class="w-4 h-4" />
                                                        <span>{{ $event->location }}</span>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <div class="flex items-center space-x-2">
                                        <flux:button
                                            href="{{ route('filament.admin.resources.events.edit', $event) }}"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <flux:icon.pencil class="w-4 h-4" />
                                        </flux:button>
                                        <flux:button
                                            href="{{ route('filament.admin.resources.events.view', $event) }}"
                                            variant="ghost"
                                            size="sm"
                                        >
                                            <flux:icon.eye class="w-4 h-4" />
                                        </flux:button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        
                        <!-- Pagination -->
                        <div class="mt-6">
                            {{ $this->events->links() }}
                        </div>
                    @else
                        <div class="text-center py-12">
                            <flux:icon.calendar class="w-12 h-12 text-gray-400 mx-auto mb-4" />
                            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-2">
                                No events found
                            </h3>
                            <p class="text-gray-500 dark:text-gray-400 mb-4">
                                {{ $this->search ? 'No events match your search.' : "No events scheduled for {$this->monthName} {$this->selectedYear}." }}
                            </p>
                            <flux:button
                                wire:click="openCreateModal"
                                variant="primary"
                                size="sm"
                            >
                                <flux:icon.plus class="w-4 h-4 mr-2" />
                                Create your first event
                            </flux:button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Upcoming Events Sidebar -->
            @if($this->upcomingEvents->count() > 0)
                <div class="bg-white dark:bg-gray-800 rounded-lg border border-gray-200 dark:border-gray-700 p-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-3">
                        Upcoming Events
                    </h3>
                    <div class="space-y-2">
                        @foreach($this->upcomingEvents as $event)
                            <div class="flex items-center justify-between p-3 bg-gray-50 dark:bg-gray-700 rounded-md">
                                <div class="flex items-center space-x-3">
                                    <div class="w-2 h-2 rounded-full bg-{{ $event->event_type_color }}-500"></div>
                                    <div>
                                        <div class="font-medium text-gray-900 dark:text-gray-100">{{ $event->title }}</div>
                                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $event->formatted_date }}</div>
                                    </div>
                                </div>
                                <flux:button
                                    href="{{ route('filament.admin.resources.events.view', $event) }}"
                                    variant="ghost"
                                    size="sm"
                                >
                                    <flux:icon.arrow-right class="w-4 h-4" />
                                </flux:button>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Create Event Modal -->
            @if($showCreateModal)
                <div class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white dark:bg-gray-800">
                        <div class="mt-3">
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                    Create New Event
                                </h3>
                                <flux:button
                                    wire:click="closeCreateModal"
                                    variant="ghost"
                                    size="sm"
                                >
                                    <flux:icon.x-mark class="w-4 h-4" />
                                </flux:button>
                            </div>
                            
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-4">
                                Quick event creation is coming soon. For now, please use the Filament admin panel to create events.
                            </p>
                            
                            <div class="flex justify-end space-x-3">
                                <flux:button
                                    wire:click="closeCreateModal"
                                    variant="ghost"
                                >
                                    Cancel
                                </flux:button>
                                <flux:button
                                    href="{{ route('filament.admin.resources.events.create') }}"
                                    variant="primary"
                                >
                                    Go to Admin Panel
                                </flux:button>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
        HTML;
    }
}
