<!DOCTYPE html>
{{-- Use external theme.js via app.js --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="theme" :class="{ 'dark': dark }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script>
        if (localStorage.getItem('dark') === 'true' || (!('dark' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        html,
        body {
            transition: background-color 0.4s ease, color 0.4s ease;
        }

        .theme-toggle svg {
            transition: transform 0.7s ease-in-out, opacity 0.5s ease;
        }
    </style>
</head>

{{-- RESTORED YOUR ORIGINAL BACKGROUND COLORS --}}
<body class="font-sans text-gray-900 antialiased bg-gray-100 dark:bg-gray-900">

    {{-- START: STICKY NAVBAR (with original solid colors) --}}
    <nav x-data="{ open: false }"
        class="sticky top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 transition duration-300 ease-in-out">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    {{-- Logo Section (using your logo from welcome page) --}}
                    <div class="shrink-0 flex items-center">
                        <a href="{{ url('/') }}">
                            <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo"
                                class="h-10 w-auto sm:h-12 w-auto dark:invert hover:opacity-80 transition-opacity duration-200">
                        </a>
                    </div>

                    {{-- Main Navigation Links --}}
                    <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                        <x-nav-link :href="route('login')" :active="request()->routeIs('login')">
                            {{ __('Log in') }}
                        </x-nav-link>
                        <x-nav-link :href="route('register')" :active="request()->routeIs('register')">
                            {{ __('Register') }}
                        </x-nav-link>
                    </div>
                </div>

                <div class="flex items-center sm:ms-6">
                    {{-- Theme Toggle Button (WITH FULL ICON CODE) --}}
                    <button @click="toggle()" {{-- Use the method from external theme.js --}}
                        class="relative flex items-center justify-center w-10 h-10 rounded-full overflow-hidden
                               bg-gradient-to-tr from-yellow-100 via-gray-200 to-yellow-200
                               dark:from-indigo-800 dark:via-gray-700 dark:to-indigo-900
                               shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800
                               transition-all duration-700 ease-in-out transform hover:scale-105 mr-3"
                        title="Toggle Theme">

                        <span
                            class="absolute inset-0 bg-gradient-to-br from-yellow-300 to-orange-300 dark:from-indigo-500 dark:to-blue-700
                                   opacity-30 blur-lg transition-all duration-700 scale-150"></span>

                        {{-- Sun Icon (FULL CODE) --}}
                        <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8"
                            class="absolute w-6 h-6 text-yellow-500 transition-transform duration-700 rotate-0 scale-100"
                            x-transition:enter="transform transition ease-out duration-700"
                            x-transition:enter-start="opacity-0 -rotate-90 scale-50"
                            x-transition:enter-end="opacity-100 rotate-0 scale-100"
                            x-transition:leave="transform transition ease-in duration-500"
                            x-transition:leave-start="opacity-100 rotate-0 scale-100"
                            x-transition:leave-end="opacity-0 rotate-90 scale-50">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m0 13.5V21m8.25-9H21m-17.25 0H3m15.364 6.364l-1.591-1.591M6.227 6.227l-1.59-1.59m12.727 12.727l1.59 1.59M6.227 17.773l-1.59 1.59M12 8.25a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5z" />
                        </svg>

                        {{-- Moon Icon (FULL CODE) --}}
                        <svg x-show="dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            stroke="currentColor" stroke-width="1.8"
                            class="absolute w-6 h-6 text-indigo-200 transition-transform duration-700 rotate-180 scale-100"
                            x-transition:enter="transform transition ease-out duration-700"
                            x-transition:enter-start="opacity-0 rotate-90 scale-50"
                            x-transition:enter-end="opacity-100 rotate-180 scale-100"
                            x-transition:leave="transform transition ease-in duration-500"
                            x-transition:leave-start="opacity-100 rotate-180 scale-100"
                            x-transition:leave-end="opacity-0 -rotate-90 scale-50">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 12.79A9 9 0 1111.21 3a7.5 7.5 0 009.79 9.79z" />
                        </svg>
                    </button>

                    {{-- Hamburger Menu Button --}}
                    <div class="-me-2 flex items-center sm:hidden">
                        <button @click="open = !open"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-300 hover:text-gray-600 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden"
                                    stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Mobile Nav Links (with original solid colors) --}}
        <div :class="{ 'block': open, 'hidden': !open }"
            class="hidden sm:hidden bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
            <div class="pt-2 pb-3 space-y-1">
                <x-responsive-nav-link :href="route('login')" :active="request()->routeIs('login')">
                    {{ __('Log in') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('register')" :active="request()->routeIs('register')">
                    {{ __('Register') }}
                </x-responsive-nav-link>
            </div>
        </div>
    </nav>
    {{-- END: NAVBAR --}}


    {{-- ADDED PADDING (pt-24) TO PUSH CONTENT DOWN FROM STICKY NAV --}}
    <div class="min-h-screen flex flex-col items-center pt-24 sm:pt-28">
        
        {{-- RESTORED YOUR ORIGINAL SOLID CARD --}}
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800
                   shadow-md overflow-hidden sm:rounded-lg transition-all duration-300">
            {{ $slot }} {{-- Where login/register forms are injected --}}
        </div>
    </div>

</body>

</html>