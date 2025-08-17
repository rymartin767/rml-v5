<x-layouts.app :title="__('Dashboard')">
    <div class="flex h-full w-full flex-1 flex-col gap-6 p-6">
        <!-- Welcome Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                Welcome back, {{ auth()->user()->name }}!
            </h1>
            <p class="text-gray-600 dark:text-gray-400">
                Here's what's happening with your events and activities today.
            </p>
        </div>

        <!-- Events Section -->
        <div class="bg-white dark:bg-gray-800 rounded-xl border border-neutral-200 dark:border-neutral-700 p-6">
            <livewire:dashboard.events />
        </div>
    </div>
</x-layouts.app>
