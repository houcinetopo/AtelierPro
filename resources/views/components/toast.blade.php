{{-- Toast Notifications --}}
@if (session('success') || session('error') || session('warning') || session('info'))
    <div x-data="{ show: true }"
         x-show="show"
         x-init="setTimeout(() => show = false, 5000)"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-20 right-4 z-50 max-w-sm w-full"
         x-cloak>

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 rounded-lg p-4 flex items-start gap-3 shadow-lg">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-green-800">Succès</p>
                    <p class="text-sm text-green-600 mt-0.5">{{ session('success') }}</p>
                </div>
                <button @click="show = false" class="text-green-400 hover:text-green-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('error'))
            <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3 shadow-lg">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-red-800">Erreur</p>
                    <p class="text-sm text-red-600 mt-0.5">{{ session('error') }}</p>
                </div>
                <button @click="show = false" class="text-red-400 hover:text-red-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('warning'))
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 flex items-start gap-3 shadow-lg">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-yellow-800">Attention</p>
                    <p class="text-sm text-yellow-600 mt-0.5">{{ session('warning') }}</p>
                </div>
                <button @click="show = false" class="text-yellow-400 hover:text-yellow-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif

        @if (session('info'))
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 flex items-start gap-3 shadow-lg">
                <i data-lucide="info" class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5"></i>
                <div class="flex-1">
                    <p class="text-sm font-medium text-blue-800">Information</p>
                    <p class="text-sm text-blue-600 mt-0.5">{{ session('info') }}</p>
                </div>
                <button @click="show = false" class="text-blue-400 hover:text-blue-600">
                    <i data-lucide="x" class="w-4 h-4"></i>
                </button>
            </div>
        @endif
    </div>
@endif

{{-- Validation Errors --}}
@if ($errors->any())
    <div class="mb-4 bg-red-50 border border-red-200 rounded-lg p-4">
        <div class="flex items-center gap-2 mb-2">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            <p class="text-sm font-medium text-red-800">Veuillez corriger les erreurs suivantes :</p>
        </div>
        <ul class="list-disc list-inside text-sm text-red-600 space-y-1 ml-7">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
