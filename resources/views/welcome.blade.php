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
    <div class="min-h-screen flex flex-col justify-between">
        <!-- Top Bar -->
        <header class="sticky top-0 z-50 bg-white/80 dark:bg-gray-900/80 backdrop-blur border-b border-gray-100 dark:border-gray-800">
            <nav class="max-w-7xl mx-auto flex items-center justify-between px-4 py-4 lg:px-8" aria-label="Global">
                <div class="flex items-center gap-4">
                    <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo" class="h-28 w-auto dark:invert">

                </div>
                <div class="flex items-center gap-6">
                    <a href="#how-it-works" class="text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">How it works</a>
                    <a href="#features" class="text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">Features</a>
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">Dashboard</a>
                    @else
                        <a href="{{ route('login') }}" class="text-sm font-semibold text-gray-700 dark:text-gray-200 hover:text-indigo-600 dark:hover:text-indigo-400 transition">Log in</a>
                    @endauth
                    <button @click="toggle()" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400 transition duration-150 ease-in-out">
                        <svg x-show="!dark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m8.66-15.66l-.707.707M4.04 19.96l-.707.707M21 12h-1M4 12H3m15.66 8.66l-.707-.707M4.04 4.04l-.707-.707"/>
                        </svg>
                        <svg x-show="dark" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                        </svg>
                    </button>
                </div>
            </nav>
        </header>

        <!-- Hero Section with Blob -->
        <main class="relative flex-1 flex flex-col justify-center items-center overflow-hidden">
            <div class="absolute inset-0 -z-10 flex items-center justify-center">
                <svg width="900" height="600" viewBox="0 0 900 600" fill="none" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                    <defs>
                        <radialGradient id="blobGradient" cx="50%" cy="50%" r="50%" fx="50%" fy="50%" gradientUnits="userSpaceOnUse">
                            <stop stop-color="#6366F1" stop-opacity="0.3" />
                            <stop offset="1" stop-color="#6366F1" stop-opacity="0" />
                        </radialGradient>
                    </defs>
                    <ellipse cx="450" cy="300" rx="400" ry="220" fill="url(#blobGradient)" />
                </svg>
            </div>
            <div class="relative z-10 max-w-2xl mx-auto py-32 sm:py-48 lg:py-56 text-center">
                <h1 class="text-5xl sm:text-6xl font-extrabold tracking-tight text-gray-900 dark:text-white mb-6">Empowering Peer-to-Peer Lending</h1>
                <p class="mt-4 text-lg sm:text-xl text-gray-600 dark:text-gray-300">Borrow instantly, lend securely, and build your financial future with trust and transparency. PeerLink connects people for a better tomorrow.</p>
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('register') }}" class="rounded-lg bg-indigo-600 px-6 py-3 text-lg font-semibold text-white shadow-lg hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 transition">Get Started</a>
                    <a href="#how-it-works" class="text-lg font-semibold leading-6 text-indigo-600 dark:text-indigo-400 hover:underline">Learn more <span aria-hidden="true">→</span></a>
                </div>
            </div>
        </main>

        <!-- How It Works Section -->
        <section id="how-it-works" class="relative z-10 py-24 sm:py-32 bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-800">
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

        <!-- Floating Social Links -->
<div class="w-full flex justify-center -mt-10 z-20 relative">
    <div class="bg-white dark:bg-gray-800 shadow-lg rounded-2xl px-8 py-6 flex gap-6 items-center border border-gray-100 dark:border-gray-700">
        <!-- Twitter -->
        <a href="https://twitter.com/" target="_blank" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.46 6c-.77.35-1.6.59-2.47.7a4.3 4.3 0 001.88-2.37 8.59 8.59 0 01-2.72 1.04A4.28 4.28 0 0016.11 4c-2.37 0-4.29 1.92-4.29 4.29 0 .34.04.67.11.99C7.69 8.99 4.07 7.13 1.64 4.16c-.37.64-.58 1.38-.58 2.17 0 1.5.76 2.82 1.92 3.6-.7-.02-1.36-.21-1.94-.53v.05c0 2.1 1.5 3.85 3.5 4.25-.36.1-.74.16-1.13.16-.28 0-.54-.03-.8-.08.54 1.7 2.1 2.94 3.95 2.97A8.6 8.6 0 012 19.54a12.13 12.13 0 006.56 1.92c7.88 0 12.2-6.53 12.2-12.2 0-.19 0-.38-.01-.57A8.7 8.7 0 0024 4.59a8.48 8.48 0 01-2.54.7z"/>
            </svg>
        </a>

        <!-- WhatsApp -->
        <a href="https://wa.me/254700000000" target="_blank" class="text-gray-400 hover:text-green-500 dark:hover:text-green-400 transition">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 32 32">
                <path d="M16.04 2.003C8.853 2.003 3 7.856 3 15.042c0 2.65.795 5.16 2.297 7.324L3.002 30l7.806-2.26a13.01 13.01 0 005.233 1.073c7.188 0 13.04-5.853 13.04-13.04 0-7.187-5.852-12.97-13.04-12.97zm0 23.37a10.33 10.33 0 01-5.272-1.442l-.376-.224-4.64 1.344 1.379-4.514-.247-.387a10.3 10.3 0 01-1.636-5.706c0-5.693 4.63-10.322 10.322-10.322 5.692 0 10.321 4.63 10.321 10.322 0 5.693-4.63 10.322-10.321 10.322zm5.69-7.775c-.31-.156-1.83-.903-2.116-1.006-.282-.104-.488-.156-.695.157-.204.31-.799.998-.978 1.203-.18.206-.361.232-.67.078-.31-.157-1.309-.482-2.495-1.538-.922-.822-1.542-1.84-1.722-2.15-.18-.31-.02-.477.137-.633.142-.141.31-.361.466-.54.155-.18.206-.31.31-.517.104-.206.052-.39-.026-.547-.078-.157-.694-1.676-.951-2.296-.25-.602-.505-.522-.695-.53h-.593c-.206 0-.54.078-.823.39-.282.31-1.08 1.056-1.08 2.574 0 1.519 1.107 2.986 1.263 3.193.156.206 2.182 3.334 5.29 4.68.74.319 1.317.51 1.767.653.74.236 1.412.203 1.944.124.593-.088 1.83-.747 2.09-1.468.258-.72.258-1.339.18-1.468-.078-.13-.28-.207-.593-.362z"/>
            </svg>
        </a>

        <!-- GitHub -->
        <a href="https://github.com/" target="_blank" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.475 2 2 6.484 2 12.021c0 4.428 2.865 8.184 6.839 9.504.5.092.682-.217.682-.482 0-.238-.009-.868-.013-1.703-2.782.605-3.37-1.342-3.37-1.342-.454-1.156-1.11-1.464-1.11-1.464-.908-.621.069-.608.069-.608 1.004.07 1.532 1.031 1.532 1.031.892 1.532 2.341 1.09 2.91.834.092-.648.35-1.09.636-1.34-2.22-.253-4.555-1.112-4.555-4.951 0-1.094.39-1.988 1.029-2.688-.103-.253-.447-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.536 9.536 0 0112 6.844a9.54 9.54 0 012.5.338c1.909-1.296 2.748-1.026 2.748-1.026.547 1.378.202 2.397.099 2.65.64.7 1.028 1.594 1.028 2.688 0 3.849-2.339 4.694-4.566 4.942.359.31.678.921.678 1.855 0 1.34-.012 2.419-.012 2.747 0 .268.18.579.688.48A10.025 10.025 0 0022 12.021C22 6.484 17.523 2 12 2z"/>
            </svg>
        </a>

        <!-- Instagram -->
        <a href="https://instagram.com/" target="_blank" class="text-gray-400 hover:text-pink-500 dark:hover:text-pink-400 transition">
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 24 24">
                <path d="M7.75 2h8.5A5.75 5.75 0 0122 7.75v8.5A5.75 5.75 0 0116.25 22h-8.5A5.75 5.75 0 012 16.25v-8.5A5.75 5.75 0 017.75 2zm0 1.5A4.25 4.25 0 003.5 7.75v8.5A4.25 4.25 0 007.75 20.5h8.5a4.25 4.25 0 004.25-4.25v-8.5A4.25 4.25 0 0016.25 3.5h-8.5zM12 7a5 5 0 110 10 5 5 0 010-10zm0 1.5a3.5 3.5 0 100 7 3.5 3.5 0 000-7zm5.25-.88a1.13 1.13 0 110 2.25 1.13 1.13 0 010-2.25z"/>
            </svg>
        </a>
    </div>
</div>


        <!-- Footer -->
        <footer class="bg-white dark:bg-gray-900 border-t border-gray-100 dark:border-gray-800 mt-16">
            <div class="max-w-7xl mx-auto px-4 py-6 flex flex-col items-center justify-center">
                <div class="flex items-center gap-2 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                    <img src="{{ asset('images/peerlink_logo.png') }}" alt="PeerLink Logo" class="h-28 w-auto dark:invert">
                </div>
                <div class="text-gray-400 text-sm mt-2">&copy; {{ date('Y') }} PeerLink. All rights reserved.</div>
            </div>
        </footer>
                    
                </div>
            </div>
    </div>
</body>
</html>