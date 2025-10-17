@php use Illuminate\Support\Facades\Storage; @endphp {{-- Keep this --}}

{{-- Revert x-data for theme to the layout file (app.blade.php), just handle mobile menu 'open' state here --}}
<nav x-data="{ open: false }"
    class="bg-white dark:bg-gray-800 border-b border-gray-100 dark:border-gray-700 transition duration-300 ease-in-out">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                {{-- Logo Section --}}
                <div class="shrink-0 flex items-center">
                    @php
                        $homeRoute = match (Auth::user()->role) {
                            'admin' => route('admin.dashboard'),
                            'lender' => route('lender.loans.index'),
                            default => route('dashboard'),
                        };
                    @endphp
                    <a href="{{ $homeRoute }}">
                        <x-application-logo class="block h-9 w-auto fill-current text-gray-800 dark:text-gray-200" />
                    </a>
                </div>

                {{-- Main Navigation Links --}}
                <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                    @if (Auth::user()->role == 'admin')
                        <x-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                            {{ __('Dashboard') }}
                        </x-nav-link>
                        <x-nav-link :href="route('admin.loans.index')" :active="request()->routeIs('admin.loans.index')">
                            {{ __('Manage Loans') }}
                        </x-nav-link>
                    @elseif(Auth::user()->role == 'lender')
                        <x-nav-link :href="route('lender.loans.index')" :active="request()->routeIs('lender.loans.index')">
                            {{ __('Browse Loans') }}
                        </x-nav-link>
                        <x-nav-link :href="route('lender.loans.investments')" :active="request()->routeIs('lender.loans.investments')">
                            {{ __('My Investments') }}
                        </x-nav-link>
                    @else
                        {{-- Borrower --}}
                        <x-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                            {{ __('My Dashboard') }}
                        </x-nav-link>
                    @endif
                </div>
            </div>

            <div class="flex items-center sm:ms-6"> {{-- Removed space-x-3 for better control --}}

                {{-- Theme Toggle Button --}}
                {{-- It should call the toggle() method from the 'theme' component defined in app.blade.php / app.js --}}
                <button @click="toggle()" {{-- Use the method from external theme.js --}}
                    class="relative flex items-center justify-center w-10 h-10 rounded-full overflow-hidden
                           bg-gradient-to-tr from-yellow-100 via-gray-200 to-yellow-200
                           dark:from-indigo-800 dark:via-gray-700 dark:to-indigo-900
                           shadow-md hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800
                           transition-all duration-700 ease-in-out transform hover:scale-105 mr-3"
                    {{-- Added margin-right --}} title="Toggle Theme">

                    {{-- Background Motion Path --}}
                    <span
                        class="absolute inset-0 bg-gradient-to-br from-yellow-300 to-orange-300 dark:from-indigo-500 dark:to-blue-700
                               opacity-30 blur-lg transition-all duration-700 scale-150"></span>

                    {{-- Sun Icon --}}
                    {{-- Uses the 'dark' variable from the 'theme' component --}}
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

                    {{-- Moon Icon --}}
                    {{-- Uses the 'dark' variable from the 'theme' component --}}
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

                <div class="hidden sm:flex sm:items-center">
                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button
                                class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                                <div class="flex items-center">
                                    <img src="{{ Auth::user()->avatar ? Storage::disk('s3')->url(Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                                        alt="User Avatar" class="h-8 w-8 rounded-full object-cover mr-2">
                                    <div>{{ Auth::user()->name }}</div>
                                </div>
                                <div class="ms-1">
                                    <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-dropdown-link :href="route('logout')"
                                    onclick="event.preventDefault(); this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <div class="-me-2 flex items-center sm:hidden"> {{-- Ensure margin only on mobile --}}
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

    <div :class="{ 'block': open, 'hidden': !open }"
        class="hidden sm:hidden bg-white dark:bg-gray-800 border-t border-gray-100 dark:border-gray-700">
        {{-- Mobile Nav Links --}}
        <div class="pt-2 pb-3 space-y-1">
            @if (Auth::user()->role == 'admin')
                <x-responsive-nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    {{ __('Dashboard') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('admin.loans.index')" :active="request()->routeIs('admin.loans.index')">
                    {{ __('Manage Loans') }}
                </x-responsive-nav-link>
            @elseif(Auth::user()->role == 'lender')
                <x-responsive-nav-link :href="route('lender.loans.index')" :active="request()->routeIs('lender.loans.index')">
                    {{ __('Browse Loans') }}
                </x-responsive-nav-link>
                <x-responsive-nav-link :href="route('lender.loans.investments')" :active="request()->routeIs('lender.loans.investments')">
                    {{ __('My Investments') }}
                </x-responsive-nav-link>
            @else
                {{-- Borrower --}}
                <x-responsive-nav-link :href="route('dashboard')" :active="request()->routeIs('dashboard')">
                    {{ __('My Dashboard') }}
                </x-responsive-nav-link>
            @endif
        </div>

        {{-- Mobile Profile + Logout --}}
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">
            <div class="px-4">
                <div class="flex items-center"> {{-- Added flex container for avatar --}}
                    <img src="{{ Auth::user()->avatar ? Storage::disk('s3')->url(Auth::user()->avatar) : asset('images/default-avatar.png') }}"
                        alt="User Avatar" class="h-10 w-10 rounded-full object-cover mr-3">
                    <div>
                        <div class="font-medium text-base text-gray-800 dark:text-gray-200">{{ Auth::user()->name }}
                        </div>
                        <div class="font-medium text-sm text-gray-500">{{ Auth::user()->email ?? 'No Email' }}</div>
                    </div>
                </div>
            </div>

            <div class="mt-3 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <x-responsive-nav-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
