<!DOCTYPE html>
{{-- Use external theme.js via app.js --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="theme" :class="{ 'dark': dark }">

<head>
    <meta charset="utf-t">
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
            /* Adjusted duration */
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100 dark:bg-gray-900">

    {{-- START: NEW NAVIGATION BAR --}}
    <nav x-data="{ open: false }" class="fixed w-full top-0 z-50 bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex">
                    <div class="shrink-0 flex items-center">
                        <a href="{{ url('/') }}">
                            {{-- Using your logo code from guest.blade.php --}}
                            <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo"
                                class="h-10 w-auto sm:h-12 dark:invert hover:opacity-80 transition-opacity duration-200">
                        </a>
                    </div>
                </div>

                <div class="flex items-center sm:ms-6">
                    <div class="hidden sm:flex sm:items-center sm:space-x-8 sm:ms-10">
                        <a href="{{ route('login') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 
                                {{ request()->routeIs('login')
                                    ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }}
                                focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out text-sm font-medium leading-5">
                            {{ __('Log in') }}
                        </a>

                        <a href="{{ route('register') }}"
                            class="inline-flex items-center px-1 pt-1 border-b-2 
                                {{ request()->routeIs('register')
                                    ? 'border-indigo-400 dark:border-indigo-600 text-gray-900 dark:text-gray-100'
                                    : 'border-transparent text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:border-gray-300 dark:hover:border-gray-700' }}
                                focus:outline-none focus:text-gray-700 dark:focus:text-gray-300 focus:border-gray-300 dark:focus:border-gray-700 transition duration-150 ease-in-out text-sm font-medium leading-5">
                            {{ __('Register') }}
                        </a>
                    </div>

                    <button @click="toggle()"
                        class="theme-toggle relative flex items-center justify-center w-10 h-10 rounded-full
                               bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600
                               shadow-inner focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                               dark:focus:ring-offset-gray-800 transition-colors duration-300 ms-4"
                        title="Toggle theme">

                        <svg x-ref="sunIcon" x-show="!dark" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                            class="absolute w-6 h-6 text-yellow-500 transform transition-transform duration-700 ease-in-out"
                            x-transition:enter="transform transition ease-out duration-700"
                            x-transition:enter-start="opacity-0 -rotate-90 scale-50"
                            x-transition:enter-end="opacity-100 rotate-0 scale-100"
                            x-transition:leave="transform transition ease-in duration-500"
                            x-transition:leave-start="opacity-100 rotate-0 scale-100"
                            x-transition:leave-end="opacity-0 rotate-90 scale-50">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3v2.25m0 13.5V21m8.25-9H21m-17.25 0H3m15.364 6.364l-1.591-1.591M6.227 6.227l-1.59-1.59m12.727 12.727l1.59 1.59M6.227 17.773l-1.59 1.59M12 8.25a3.75 3.75 0 110 7.5 3.75 3.75 0 010-7.5z" />
                        </svg>

                        <svg x-ref="moonIcon" x-show="dark" xmlns="http://www.w3.org/2000/svg" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"
                            class="absolute w-6 h-6 text-indigo-200 transform transition-transform duration-700 ease-in-out"
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

                    <div class="-me-2 flex items-center sm:hidden ms-3">
                        <button @click="open = ! open"
                            class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
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

        <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('login') }}"
                    class="block w-full ps-3 pe-4 py-2 border-l-4 
                        {{ request()->routeIs('login')
                            ? 'border-indigo-400 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}
                        focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out text-base font-medium">
                    {{ __('Log in') }}
                </a>
                <a href="{{ route('register') }}"
                    class="block w-full ps-3 pe-4 py-2 border-l-4 
                        {{ request()->routeIs('register')
                            ? 'border-indigo-400 dark:border-indigo-600 text-indigo-700 dark:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/50'
                            : 'border-transparent text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 hover:border-gray-300 dark:hover:border-gray-600' }}
                        focus:outline-none focus:text-gray-800 dark:focus:text-gray-200 focus:bg-gray-50 dark:focus:bg-gray-700 focus:border-gray-300 dark:focus:border-gray-600 transition duration-150 ease-in-out text-base font-medium">
                    {{ __('Register') }}
                </a>
            </div>
        </div>
    </nav>
    {{-- END: NEW NAVIGATION BAR --}}


    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-10">
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800
                   shadow-md overflow-hidden sm:rounded-lg transition-all duration-300">
            {{ $slot }} {{-- Where login/register forms are injected --}}
        </div>
    </div>

</body>

</html>
