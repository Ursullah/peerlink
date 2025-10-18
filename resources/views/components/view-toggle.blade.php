@props(['currentView' => 'cards', 'views' => ['cards', 'list', 'grid']])

<div x-data="viewToggle()" class="flex items-center space-x-1 bg-gray-100 dark:bg-gray-700 rounded-lg p-1">
    @foreach ($views as $view)
        <button @click="setView('{{ $view }}')"
            :class="[
                'flex items-center px-3 py-2 text-sm font-medium rounded-md transition-colors duration-200',
                currentView === '{{ $view }}' ?
                'bg-white dark:bg-gray-600 text-indigo-600 dark:text-indigo-400 shadow-sm' :
                'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
            ]"
            title="Switch to {{ ucfirst($view) }} view">
            @if ($view === 'cards')
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                    </path>
                </svg>
                <span class="hidden sm:inline">Cards</span>
            @elseif($view === 'list')
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6h16M4 10h16M4 14h16M4 18h16"></path>
                </svg>
                <span class="hidden sm:inline">List</span>
            @elseif($view === 'grid')
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z">
                    </path>
                </svg>
                <span class="hidden sm:inline">Grid</span>
            @endif
        </button>
    @endforeach
</div>

<script>
    function viewToggle() {
        return {
            currentView: '{{ $currentView }}',

            setView(view) {
                this.currentView = view;
                // Store preference in localStorage
                localStorage.setItem('preferredView', view);
                // Dispatch event for parent components to listen
                this.$dispatch('view-changed', {
                    view: view
                });
            },

            init() {
                // Load saved preference
                const savedView = localStorage.getItem('preferredView');
                if (savedView && ['cards', 'list', 'grid'].includes(savedView)) {
                    this.currentView = savedView;
                }
            }
        }
    }
</script>
