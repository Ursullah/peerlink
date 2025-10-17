<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Early dark mode script to prevent FOUC -->
    <script>
        (function() {
            const storedDark = localStorage.getItem('dark');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (storedDark === 'true' || (!storedDark && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Avoid transitions during initial load
            document.documentElement.style.transition = 'none';
            window.addEventListener('load', () => {
                setTimeout(() => document.documentElement.style.transition = '', 0);
            });
        })();
    </script>

    <script>
        (function() {
            const storedDark = localStorage.getItem('dark');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            if (storedDark === 'true' || (!storedDark && prefersDark)) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }

            // Avoid transitions during initial page load
            document.documentElement.style.transition = 'none';
            window.addEventListener('load', () => {
                setTimeout(() => document.documentElement.style.transition = '', 0);
            });
        })();
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body x-data="theme" class="font-sans antialiased">

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        @include('layouts.navigation')

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-white dark:bg-gray-800 shadow">
                <div class="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                    {{-- Original Header Content --}}
                    <div>
                        {{ $header }}
                    </div>

                    {{-- ** START MODIFICATION ** --}}
                    {{-- Show Wallet Info & Buttons ONLY for LENDERS in the header --}}
                    @auth
                        @if (Auth::user()->role === 'lender')
                            <div class="flex items-center space-x-4">
                                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">
                                    Wallet: <strong class="text-indigo-600 dark:text-indigo-400">KES
                                        {{ number_format(Auth::user()->wallet?->balance / 100 ?? 0, 2) }}</strong>
                                </span>
                                <a href="{{ route('wallet.deposit.form') }}"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500">
                                    Top-Up
                                </a>
                                <a href="{{ route('wallet.withdraw.form') }}"
                                    class="inline-flex items-center px-3 py-1 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                    Withdraw
                                </a>
                            </div>
                        @endif
                    @endauth
                    {{-- ** END MODIFICATION ** --}}

                </div>
            </header>
        @endisset

        <!-- Page Content -->
        <main>
            {{ $slot }}
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</body>

</html>
