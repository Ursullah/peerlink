@props(['defaultView' => 'cards'])

<div x-data="viewContainer()" x-init="init()" @view-changed.window="handleViewChange($event.detail.view)">
    <!-- View Toggle Controls -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <span x-text="getViewTitle()"></span>
            </h3>
            <p class="text-sm text-gray-500 dark:text-gray-400">
                <span x-text="getViewDescription()"></span>
            </p>
        </div>
        <x-view-toggle :current-view="$defaultView" />
    </div>

    <!-- Content Container -->
    <div class="space-y-4">
        <!-- Cards View -->
        <div x-show="currentView === 'cards'" x-transition class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{ $cards ?? '' }}
        </div>

        <!-- List View -->
        <div x-show="currentView === 'list'" x-transition class="space-y-4">
            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                {{ $listHeaders ?? '' }}
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                            {{ $listRows ?? '' }}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Grid View -->
        <div x-show="currentView === 'grid'" x-transition
            class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {{ $grid ?? '' }}
        </div>

        <!-- Empty State -->
        <div x-show="isEmpty" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                </path>
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">No items found</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Get started by creating your first item.</p>
        </div>
    </div>
</div>

<script>
    function viewContainer() {
        return {
            currentView: '{{ $defaultView }}',
            isEmpty: false,

            init() {
                // Load saved view preference
                const savedView = localStorage.getItem('preferredView');
                if (savedView && ['cards', 'list', 'grid'].includes(savedView)) {
                    this.currentView = savedView;
                }
            },

            handleViewChange(view) {
                this.currentView = view;
            },

            getViewTitle() {
                const titles = {
                    'cards': 'Card View',
                    'list': 'List View',
                    'grid': 'Grid View'
                };
                return titles[this.currentView] || 'Card View';
            },

            getViewDescription() {
                const descriptions = {
                    'cards': 'Display items as detailed cards with full information',
                    'list': 'Display items in a compact table format',
                    'grid': 'Display items in a compact grid layout'
                };
                return descriptions[this.currentView] || 'Display items as detailed cards';
            }
        }
    }
</script>
