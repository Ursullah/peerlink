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
            /* Adjusted duration */
        }

        /* Animation styles (optional, but kept for consistency) */
        .rotate-animation {
            animation: smooth-rotate 0.8s ease-in-out;
        }

        @keyframes smooth-rotate {
            0% {
                transform: rotate(0deg) scale(1);
                opacity: 1;
            }

            50% {
                transform: rotate(180deg) scale(1.2);
                opacity: 0.7;
            }

            100% {
                transform: rotate(360deg) scale(1);
                opacity: 1;
            }
        }
    </style>
</head>

<body class="font-sans text-gray-900 antialiased bg-gray-100 dark:bg-gray-900">

    <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 py-4 lg:px-8" aria-label="Global">
        <div class="flex items-center gap-4">
            <a href="{{ url('/') }}" class="flex items-center">
                <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo"
                    class="h-16 w-auto sm:h-12 w-auto dark:invert hover:opacity-80 transition-opacity duration-200">
            </a>
        </div>

        <div class="flex items-center">
            {{-- Copied Theme Toggle Button from working welcome.blade.php --}}
            <button @click="toggle()"
                class="theme-toggle relative flex items-center justify-center w-10 h-10 rounded-full
                       bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600
                       shadow-inner focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500
                       dark:focus:ring-offset-gray-800 transition-colors duration-300"
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
        </div>
    </nav>

    <div class="min-h-screen flex flex-col items-center pt-6 sm:pt-10"> {{-- Adjusted padding --}}
        <div
            class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-gray-800
                   shadow-md overflow-hidden sm:rounded-lg transition-all duration-300">
            {{ $slot }} {{-- Where login/register forms are injected --}}
        </div>
    </div>

</body>

</html>
