{{-- 
    Composant : Générer des documents depuis l'OR
    Modification 6 : Actions possibles depuis l'Ordre de Réparation
    Usage : @include('components.or-document-generator', ['repairOrder' => $repairOrder])
--}}

@props(['repairOrder'])

<div x-data="{ open: false, showBcModal: false }" class="relative">
    {{-- Bouton principal --}}
    <button @click="open = !open" 
            class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-indigo-600 rounded-lg hover:bg-indigo-700 transition shadow-sm">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        Générer un document
        <svg class="w-4 h-4 transition-transform" :class="open ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
        </svg>
    </button>

    {{-- Dropdown --}}
    <div x-show="open" @click.outside="open = false" x-transition
         class="absolute right-0 mt-2 w-64 bg-white rounded-xl shadow-lg border border-gray-200 z-30 overflow-hidden">
        
        {{-- Facture --}}
        @if(!$repairOrder->invoice || $repairOrder->invoice->statut === 'annulee')
        <form action="{{ route('repair-orders.generate-invoice', $repairOrder) }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-100 text-green-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </span>
                <div class="text-left">
                    <span class="font-medium">Facture</span>
                    <span class="block text-xs text-gray-500">Générer la facture client</span>
                </div>
            </button>
        </form>
        @else
        <a href="{{ route('invoices.show', $repairOrder->invoice) }}" 
           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-100 text-green-600">✓</span>
            <div class="text-left">
                <span class="font-medium">Facture</span>
                <span class="block text-xs text-green-600">{{ $repairOrder->invoice->numero }} — Voir</span>
            </div>
        </a>
        @endif

        {{-- Bon de livraison --}}
        @if(!$repairOrder->deliveryNote)
        <form action="{{ route('repair-orders.generate-delivery-note', $repairOrder) }}" method="POST">
            @csrf
            <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
                <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                    </svg>
                </span>
                <div class="text-left">
                    <span class="font-medium">Bon de livraison</span>
                    <span class="block text-xs text-gray-500">Générer le BL</span>
                </div>
            </button>
        </form>
        @else
        <a href="{{ route('delivery-notes.show', $repairOrder->deliveryNote) }}"
           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600">✓</span>
            <div class="text-left">
                <span class="font-medium">Bon de livraison</span>
                <span class="block text-xs text-blue-600">{{ $repairOrder->deliveryNote->numero }} — Voir</span>
            </div>
        </a>
        @endif

        {{-- Attestation de démobilisation --}}
        <a href="{{ route('documents.download', ['attestation', $repairOrder->id]) }}"
           class="flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition border-b border-gray-100">
            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-purple-100 text-purple-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                </svg>
            </span>
            <div class="text-left">
                <span class="font-medium">Attestation de démobilisation</span>
                <span class="block text-xs text-gray-500">Télécharger le PDF</span>
            </div>
        </a>

        {{-- Bon de commande --}}
        <button @click="showBcModal = true; open = false"
                class="w-full flex items-center gap-3 px-4 py-3 text-sm text-gray-700 hover:bg-gray-50 transition">
            <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-100 text-amber-600">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
            </span>
            <div class="text-left">
                <span class="font-medium">Bon de commande</span>
                <span class="block text-xs text-gray-500">Commander des pièces</span>
            </div>
        </button>
    </div>

    {{-- Modal choix fournisseur pour BC --}}
    <div x-show="showBcModal" x-cloak
         class="fixed inset-0 z-50 flex items-center justify-center bg-black/50"
         @keydown.escape.window="showBcModal = false">
        <div @click.outside="showBcModal = false" class="bg-white rounded-xl shadow-2xl w-full max-w-sm mx-4">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white rounded-t-xl">
                <h3 class="text-lg font-semibold">Bon de commande</h3>
                <p class="text-amber-100 text-sm">Choisir le fournisseur</p>
            </div>
            <form action="{{ route('repair-orders.generate-purchase-order', $repairOrder) }}" method="POST" class="p-6">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Fournisseur</label>
                    <select name="supplier_id" required
                            class="w-full rounded-lg border-gray-300 text-sm focus:ring-amber-500 focus:border-amber-500">
                        <option value="">Sélectionner...</option>
                        @foreach(\App\Models\Supplier::actifs()->orderBy('raison_sociale')->get() as $supplier)
                            <option value="{{ $supplier->id }}">{{ $supplier->raison_sociale }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" @click="showBcModal = false"
                            class="px-4 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="submit"
                            class="px-4 py-2 text-sm text-white bg-amber-600 rounded-lg hover:bg-amber-700">Créer le BC</button>
                </div>
            </form>
        </div>
    </div>
</div>
