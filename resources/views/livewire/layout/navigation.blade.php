<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use function Livewire\Volt\on;

$logout = function (Logout $logout) {
    $logout();
    $this->redirectIntended(route('home', absolute: false), navigate: true);
};

on(['user-updated' => function() {
    $this->dispatch('$refresh');
}]);

?>

<nav class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center">
                    <a href="{{ route('home') }}" class="text-2xl font-bold text-indigo-600 dark:text-indigo-400">
                        LGI Store
                    </a>
                </div>

                <!-- Navigation Links -->
                <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                    <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')" wire:navigate>
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ml-6">
                <div>
                    <div>{{ Auth::user()->name }}</div>
                    <button wire:click="logout" class="text-sm text-red-600 hover:text-red-800">
                        {{ __('Log Out') }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</nav>
