<!DOCTYPE html>
{{-- Use external theme.js via app.js --}}
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="theme" :class="{ 'dark': dark }">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>PeerLink - P2P Micro-Lending</title>
    <link rel="icon" type="image/png" href="{{ asset('images/peerlink_logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />

    <script>
        if (localStorage.getItem('dark') === 'true' || (!('dark' in localStorage) && window.matchMedia(
                '(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark')
        } else {
            document.documentElement.classList.remove('dark')
        }
    </script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Add styles for animations --}}
    <style>
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

        .animate-pulse-slow {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: .5;
            }
        }

        /* Add hover effect for social icons */
        .social-link:hover svg {
            transform: scale(1.1);
            color: #4f46e5;
            /* Indigo-600 */
        }

        .dark .social-link:hover svg {
            color: #818cf8;
            /* Indigo-400 */
        }
    </style>
</head>

<body class="antialiased font-sans bg-gray-50 dark:bg-gray-900 text-gray-800 dark:text-gray-200">
    <div class="min-h-screen flex flex-col justify-between">
        {{-- Header --}}
        <header
            class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-100 dark:border-gray-800">
            <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 py-4 lg:px-8" aria-label="Global">
                {{-- Logo --}}
                <div class="flex items-center gap-4">
                    <a href="{{ url('/') }}" class="flex items-center">
                        <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo"
                            class="h-16 w-auto sm:h-12 w-auto dark:invert hover:opacity-80 transition-opacity duration-200">
                    </a>
                </div>
                {{-- Nav Links & Theme Toggle --}}
                <div class="flex items-center gap-4 sm:gap-6">
                    <a href="#how-it-works"
                        class="hidden sm:inline text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">How
                        it works</a>
                    <a href="#features"
                        class="hidden sm:inline text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">Features</a>
                    @auth
                        <a href="{{ url('/dashboard') }}"
                            class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}"
                            class="text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">Log
                            in</a>
                    @endauth
                    {{-- Theme Toggle Button --}}
                    <button @click="toggle()"
                        class="theme-toggle relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 shadow-inner focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800 transition-colors duration-300"
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
        </header>

        {{-- Main Hero --}}
        <main
            class="relative isolate flex-1 flex flex-col justify-center items-center overflow-hidden px-6 pt-14 lg:px-8">
            {{-- Background Blobs --}}
            <div class="absolute inset-x-0 -top-40 -z-10 transform-gpu overflow-hidden blur-3xl sm:-top-80"
                aria-hidden="true">
                <div class="relative left-[calc(50%-11rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 rotate-[30deg] bg-gradient-to-tr from-[#80ffa5] to-[#01c5ff] opacity-30 dark:opacity-20 sm:left-[calc(50%-30rem)] sm:w-[72.1875rem] animate-pulse-slow"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
                </div>
            </div>
            <div class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]"
                aria-hidden="true">
                <div class="relative left-[calc(50%+3rem)] aspect-[1155/678] w-[36.125rem] -translate-x-1/2 bg-gradient-to-tr from-[#80ffdd] to-[#a501ff] opacity-30 dark:opacity-15 sm:left-[calc(50%+36rem)] sm:w-[72.1875rem] animate-pulse-slow"
                    style="clip-path: polygon(74.1% 44.1%, 100% 61.6%, 97.5% 26.9%, 85.5% 0.1%, 80.7% 2%, 72.5% 32.5%, 60.2% 62.4%, 52.4% 68.1%, 47.5% 58.3%, 45.2% 34.5%, 27.5% 76.7%, 0.1% 64.9%, 17.9% 100%, 27.6% 76.8%, 76.1% 97.7%, 74.1% 44.1%)">
                </div>
            </div>

            {{-- Hero Content --}}
            <div class="relative z-10 max-w-2xl mx-auto py-32 sm:py-48 lg:py-56 text-center">
                <h1 class="text-4xl font-bold tracking-tight sm:text-6xl text-gray-900 dark:text-white">Connecting
                    Peers, Powering Dreams</h1>
                <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">A transparent and trustworthy
                    micro-lending platform. Borrow small amounts quickly or lend to others and earn interest. All
                    powered by instant, secure payments.</p>
                <div class="mt-10 flex items-center justify-center gap-x-6">
                    <a href="{{ route('register') }}"
                        class="rounded-md bg-indigo-600 px-3.5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">Get
                        started</a>
                    <a href="#how-it-works" class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Learn
                        more <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </main>

        {{-- How It Works Section --}}
        <section id="how-it-works" class="py-24 sm:py-32 bg-white dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">How It Works</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        Everything you need to lend and borrow with confidence</p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-4xl">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-2 lg:gap-y-16">

                        {{-- 1. Digital Collateral & Requests (NEW ICON) --}}
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div
                                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M21 12a2.25 2.25 0 0 1-2.25 2.25H5.25a2.25 2.25 0 0 1-2.25-2.25V5.25a2.25 2.25 0 0 1 2.25-2.25h13.5a2.25 2.25 0 0 1 2.25 2.25V12ZM21 12v-2.25A2.25 2.25 0 0 0 18.75 7.5H5.25A2.25 2.25 0 0 0 3 9.75v2.25Z" />
                                    </svg>
                                </div>Digital Collateral &amp; Requests
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Borrowers top-up a
                                wallet to use as digital collateral and create a loan request. This builds immediate
                                trust.</dd>
                        </div>

                        {{-- 2. Instant Funding (NEW ICON) --}}
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div
                                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                                    </svg>
                                </div>Instant Funding
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Lenders browse
                                active, approved loan requests. When a loan is funded, the money is instantly
                                transferred to the borrower's wallet.</dd>
                        </div>

                        {{-- 3. Repay & Build Reputation (NEW ICON) --}}
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div
                                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M11.48 3.499a.562.562 0 0 1 1.04 0l2.125 5.111a.563.563 0 0 0 .475.31h5.513c.498 0 .698.665.335 1.018l-4.209 3.055a.563.563 0 0 0-.17.618l1.54 5.348a.562.562 0 0 1-.84.62l-4.7-3.426a.563.563 0 0 0-.652 0l-4.7 3.426a.562.562 0 0 1-.84-.62l1.54-5.348a.563.563 0 0 0-.17-.618l-4.209-3.055a.563.563 0 0 1 .335-1.018h5.513a.563.563 0 0 0 .475-.31L11.48 3.5z" />
                                    </svg>
                                </div>Repay &amp; Build Reputation
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">Borrowers repay via
                                STK Push. Successful, on-time repayments increase their reputation score, unlocking
                                better terms in the future.</dd>
                        </div>

                        {{-- 4. Automated & Secure (NEW ICON) --}}
                        <div class="relative pl-16">
                            <dt class="text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <div
                                    class="absolute left-0 top-0 flex h-10 w-10 items-center justify-center rounded-lg bg-indigo-600">
                                    <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24"
                                        stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.333 9-6.031 9-11.623 0-1.663-.38-3.238-1.05-4.664M12 2.25a11.959 11.959 0 0 0-3.598 3.751" />
                                    </svg>
                                </div>Automated &amp; Secure
                            </dt>
                            <dd class="mt-2 text-base leading-7 text-gray-600 dark:text-gray-400">The platform
                                automates collateral locking, fund transfers, and repayment tracking. Late payments are
                                handled automatically to protect lenders.</dd>
                        </div>
                    </dl>
                </div>
            </div>
        </section>

        {{-- Features Section --}}
        <section id="features" class="py-24 sm:py-32 bg-gray-50 dark:bg-gray-900/50">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl lg:text-center">
                    <h2 class="text-base font-semibold leading-7 text-indigo-600 dark:text-indigo-400">Future
                        Enhancements</h2>
                    <p class="mt-2 text-3xl font-bold tracking-tight text-gray-900 dark:text-white sm:text-4xl">
                        Exciting Features Coming Soon</p>
                    <p class="mt-6 text-lg leading-8 text-gray-600 dark:text-gray-400">
                        We're constantly working to make PeerLink even better. Here's a glimpse of what's next:
                    </p>
                </div>
                <div class="mx-auto mt-16 max-w-2xl sm:mt-20 lg:mt-24 lg:max-w-none">
                    <dl class="grid max-w-xl grid-cols-1 gap-x-8 gap-y-10 lg:max-w-none lg:grid-cols-3 lg:gap-y-16">

                        {{-- Feature 1: Group Lending --}}
                        <div class="flex flex-col">
                            <dt
                                class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <svg class="h-6 w-6 flex-none text-indigo-600 dark:text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18 18.72a9.094 9.094 0 003.741-.479 1.684 1.684 0 00.124-3.054A9.094 9.094 0 0110.27 4.125a9.094 9.094 0 00-3.268.441 1.684 1.684 0 00-.123 3.054A9.094 9.094 0 0118 18.72zm-4.755-4.249a.684.684 0 00-.684-.684H9.316a.684.684 0 00-.684.684l-.001 3.256a.684.684 0 00.684.684h3.256a.684.684 0 00.684-.684l.001-3.256z" />
                                </svg>
                                Group Lending (Chama Mode)
                            </dt>
                            <dd
                                class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">Allow small groups (Chamas) to collectively guarantee loans for
                                    their members, reducing individual risk and promoting community finance.</p>
                            </dd>
                        </div>

                        {{-- Feature 2: Advanced Scoring --}}
                        <div class="flex flex-col">
                            <dt
                                class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <svg class="h-6 w-6 flex-none text-indigo-600 dark:text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M4.26 10.147a60.436 60.436 0 0 0-.491 6.347A48.627 48.627 0 0 1 12 20.904a48.627 48.627 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.57 50.57 0 0 0-2.658-.813A59.905 59.905 0 0 1 12 3.493a59.902 59.902 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.697 50.697 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15a.75.75 0 1 0 0-1.5.75.75 0 0 0 0 1.5Zm0 0v-3.675A55.378 55.378 0 0 1 12 8.443m-7.007 11.55A5.981 5.981 0 0 0 6.75 15.75v-1.5" />
                                </svg>
                                Smarter Credit Scoring
                            </dt>
                            <dd
                                class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">Implement a more sophisticated reputation and credit scoring
                                    system, potentially using machine learning, to provide fairer and more accurate risk
                                    assessments.</p>
                            </dd>
                        </div>

                        {{-- Feature 3: Gamification --}}
                        <div class="flex flex-col">
                            <dt
                                class="flex items-center gap-x-3 text-base font-semibold leading-7 text-gray-900 dark:text-white">
                                <svg class="h-6 w-6 flex-none text-indigo-600 dark:text-indigo-400" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M16.5 18.75h-9m9 0a3 3 0 013 3h-15a3 3 0 013-3m9 0v-4.5m-9 4.5v-4.5m1.379-6.39L12 3.75l1.121 4.11m-2.242 0H6.375M12 7.86l2.379-.51m-.75 3.39l1.5 1.5M8.625 10.74l1.5-1.5M12 21a9 9 0 100-18 9 9 0 000 18z" />
                                </svg>
                                Rewards & Gamification
                            </dt>
                            <dd
                                class="mt-4 flex flex-auto flex-col text-base leading-7 text-gray-600 dark:text-gray-400">
                                <p class="flex-auto">Introduce badges, points, or interest discounts for borrowers with
                                    excellent repayment history, encouraging responsible financial behaviour.</p>
                            </dd>
                        </div>

                    </dl>
                </div>
            </div>
        </section>

        {{-- Footer --}}
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 mt-16">
            <div class="mx-auto max-w-7xl px-6 py-12 lg:px-8">
                <div class="xl:grid xl:grid-cols-3 xl:gap-8">
                    {{-- Logo and Description --}}
                    <div class="space-y-4">
                        <a href="{{ url('/') }}" class="inline-block">
                            <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo"
                                class="h-10 w-auto dark:invert">
                            <span class="sr-only">PeerLink</span>
                        </a>
                        <p class="text-sm leading-6 text-gray-600 dark:text-gray-400">Empowering financial connections
                            through secure and transparent peer-to-peer lending.</p>
                        {{-- Social Media Icons --}}
                        <div class="flex space-x-6">
                            {{-- Add your actual social links here --}}
                            <a href="#"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 social-link">
                                <span class="sr-only">Facebook</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 social-link">
                                <span class="sr-only">X (Twitter)</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path
                                        d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
                                </svg>
                            </a>
                            <a href="#"
                                class="text-gray-400 hover:text-gray-500 dark:hover:text-gray-300 social-link">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M16.338 16.338H13.67V12.16c0-.995-.017-2.277-1.387-2.277-1.39 0-1.601 1.086-1.601 2.207v4.248H8.014V8.59h2.559v1.174h.037c.356-.675 1.227-1.387 2.526-1.387 2.703 0 3.203 1.778 3.203 4.092v4.711zM5.005 6.575a1.548 1.548 0 11-.003-3.096 1.548 1.548 0 01.003 3.096zm2.57 9.763H2.436V8.59h2.559zM17.638 0H4.362A4.362 4.362 0 000 4.362v15.276A4.362 4.362 0 004.362 24h13.276A4.362 4.362 0 0022 19.638V4.362A4.362 4.362 0 0017.638 0z"
                                        clip-rule="evenodd" />
                                </svg>
                            </a>
                            {{-- Add more social icons as needed --}}
                        </div>
                    </div>
                    {{-- Footer Links Grid --}}
                    <div class="mt-16 grid grid-cols-2 gap-8 xl:col-span-2 xl:mt-0">
                        <div class="md:grid md:grid-cols-2 md:gap-8">
                            <div>
                                <h3 class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Quick Links
                                </h3>
                                <ul role="list" class="mt-6 space-y-4">
                                    <li><a href="#how-it-works"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">How
                                            It Works</a></li>
                                    <li><a href="#features"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Features</a>
                                    </li>
                                    <li><a href="{{ route('register') }}"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Register</a>
                                    </li>
                                    <li><a href="{{ route('login') }}"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Log
                                            In</a></li>
                                </ul>
                            </div>
                            <div class="mt-10 md:mt-0">
                                <h3 class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Resources
                                </h3>
                                <ul role="list" class="mt-6 space-y-4">
                                    <li><a href="#"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">FAQ</a>
                                    </li>
                                    <li><a href="#"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Blog</a>
                                    </li>
                                    {{-- Add more resource links --}}
                                </ul>
                            </div>
                        </div>
                        <div class="md:grid md:grid-cols-2 md:gap-8">
                            <div>
                                <h3 class="text-sm font-semibold leading-6 text-gray-900 dark:text-white">Legal</h3>
                                <ul role="list" class="mt-6 space-y-4">
                                    <li><a href="#"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Privacy
                                            Policy</a></li>
                                    <li><a href="#"
                                            class="text-sm leading-6 text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">Terms
                                            of Service</a></li>
                                    {{-- Add more legal links --}}
                                </ul>
                            </div>
                            <div class="mt-10 md:mt-0">
                                {{-- Placeholder for another column if needed --}}
                            </div>
                        </div>
                    </div>
                </div>
                {{-- Copyright Notice --}}
                <div class="mt-16 border-t border-gray-900/10 dark:border-white/10 pt-8 sm:mt-20 lg:mt-24">
                    <p class="text-xs leading-5 text-gray-500 dark:text-gray-400">&copy; {{ date('Y') }} PeerLink,
                        Inc. All rights reserved.</p>
                </div>
            </div>
        </footer>
    </div>
</body>

</html>
