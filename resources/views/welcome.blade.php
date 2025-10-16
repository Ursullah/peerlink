<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>PeerLink - P2P Micro-Lending</title>
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
        <!-- Early dark mode script to prevent FOUC -->
        <script>
            if (
                localStorage.getItem('dark') === 'true' ||
                (!('dark' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)
            ) {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body x-data="theme" class="antialiased font-sans bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="min-h-screen">
        <header class="absolute inset-x-0 top-0 z-50">
            <nav class="flex items-center justify-between p-6 lg:px-8" aria-label="Global">
                <div class="flex lg:flex-1">
                    <a href="/" class="-m-1.5 p-1.5 text-2xl font-bold">
                        PeerLink
                    </a>
                </div>
                <div class="flex items-center gap-4 lg:flex lg:flex-1 lg:justify-end">
                    <!-- Theme Switcher Button -->
                    <button @click="toggle()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg x-show="!dark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-15.66l-.707.707M4.04 19.96l-.707.707M21 12h-1M4 12H3m15.66 8.66l-.707-.707M4.04 4.04l-.707-.707"/>
                        </svg>
                        <svg x-show="dark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold leading-6">Dashboard <span aria-hidden="true">&rarr;</span></a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold leading-6">Log in <span aria-hidden="true">&rarr;</span></a>
                    @endauth
                </div>
            </nav>
        </header>

        <main class="relative isolate px-6 pt-14 lg:px-8">
            <div class="mx-auto max-w-2xl py-32 sm:py-48 lg:py-56">
                <div class="text-center">
                    <h1 class="text-4xl font-bold tracking-tight sm:text-6xl text-gray-900 dark:text-white">Connecting Peers, Powering Dreams</h1>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">A transparent and trustworthy micro-lending platform. Borrow small amounts quickly or lend to others and earn interest. All powered by instant, secure payments.</p>
                    <div class="mt-10 flex items-center justify-center gap-x-6">
                        <a href="{{ route('register') }}" class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Get started</a>
                        <a href="#how-it-works" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Learn more <span aria-hidden="true">→</span></a>
                    </div>
                </div>
            </div>
        </main>
        
        <section id="how-it-works" class="py-24 sm:py-32 bg-white dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">How It Works</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">Everything you need to lend and borrow with confidence</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <!-- Wallet with plus icon -->
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75V5.25A2.25 2.25 0 0015 3h-6A2.25 2.25 0 006.75 5.25v1.5" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 7.5h16.5A2.25 2.25 0 0122.5 9.75v8.25A2.25 2.25 0 0120.25 20.25H3.75A2.25 2.25 0 011.5 18V9.75A2.25 2.25 0 013.75 7.5z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 11.25v3m1.5-1.5h-3" />
                                    </svg>
                                </div>
                                Digital Collateral &amp; Requests
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Borrowers top-up a wallet to use as digital collateral and create a loan request. This builds immediate trust.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <!-- Paper plane icon for instant funding -->
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 12l16.5-7.5-7.5 16.5-2.25-6.75-6.75-2.25z" />
                                    </svg>
                                </div>
                                Instant Funding
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Lenders browse active, approved loan requests. When a loan is funded, the money is instantly transferred to the borrower's wallet.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <!-- Handshake icon for repay & build reputation -->
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 15.75l-3.75-3.75m0 0l-3.75 3.75m3.75-3.75V21m0-6.75l3.75-3.75m0 0l3.75 3.75m-3.75-3.75V3" />
                                    </svg>
                                </div>
                                Repay &amp; Build Reputation
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Borrowers repay via STK Push. Successful, on-time repayments increase their reputation score, unlocking better terms in the future.</dd>
                        </div>
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <!-- Shield with checkmark for automated & secure -->
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3l7.5 4.5v6c0 5.25-7.5 9-7.5 9s-7.5-3.75-7.5-9v-6L12 3z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 12.75l1.5 1.5 3-3" />
                                    </svg>
                                </div>
                                Automated &amp; Secure
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">The platform automates collateral locking, fund transfers, and repayment tracking. Late payments are handled automatically to protect lenders.</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>
    </div>
</body>
</html>