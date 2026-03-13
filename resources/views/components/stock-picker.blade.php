{{-- 
    Composant : Ajouter une pièce du stock à l'OR
    Modification 7 : Liaison OR-Stock-Fournisseurs
    Usage : @include('components.stock-picker', ['repairOrder' => $repairOrder, 'products' => $products])
--}}

@props(['repairOrder', 'products'])

<div x-data="{ 
    showPicker: false, 
    search: '',
    selectedProduct: null,
    quantite: 1,
    get filteredProducts() {
        if (!this.search) return [];
        const s = this.search.toLowerCase();
        return {{ Js::from($products) }}.filter(p => 
            p.designation.toLowerCase().includes(s) || 
            (p.reference && p.reference.toLowerCase().includes(s))
        ).slice(0, 10);
    }
}" class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    
    <div class="px-5 py-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white flex items-center justify-between">
        <h3 class="text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
            </svg>
            Ajouter une pièce du stock
        </h3>
        <button @click="showPicker = !showPicker" class="text-white/80 hover:text-white text-xs">
            <span x-text="showPicker ? 'Fermer' : 'Ouvrir'"></span>
        </button>
    </div>

    <div x-show="showPicker" x-transition class="p-5">
        <form action="{{ route('repair-orders.add-product', $repairOrder) }}" method="POST" class="space-y-4">
            @csrf

            {{-- Recherche de pièce --}}
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">Rechercher une pièce</label>
                <input type="text" x-model="search" placeholder="Tapez le nom ou la référence..."
                       class="w-full rounded-lg border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">
                
                {{-- Résultats --}}
                <div x-show="search.length > 1 && filteredProducts.length > 0" 
                     class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="product in filteredProducts" :key="product.id">
                        <button type="button" 
                                @click="selectedProduct = product; search = product.designation; $refs.productId.value = product.id"
                                class="w-full px-4 py-2.5 text-left hover:bg-orange-50 border-b border-gray-100 last:border-0 transition">
                            <div class="flex justify-between items-center">
                                <div>
                                    <span class="text-sm font-medium text-gray-900" x-text="product.designation"></span>
                                    <span class="text-xs text-gray-500 ml-2" x-text="product.reference || ''"></span>
                                </div>
                                <div class="text-right">
                                    <span class="text-sm font-semibold text-orange-600" x-text="parseFloat(product.prix_vente).toFixed(2) + ' DH'"></span>
                                    <span class="block text-xs" 
                                          :class="product.quantite_stock > 0 ? 'text-green-600' : 'text-red-600'"
                                          x-text="'Stock: ' + parseFloat(product.quantite_stock)"></span>
                                </div>
                            </div>
                        </button>
                    </template>
                </div>

                <div x-show="search.length > 1 && filteredProducts.length === 0"
                     class="absolute z-20 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-4 text-center text-sm text-gray-500">
                    Aucune pièce trouvée
                </div>
            </div>

            <input type="hidden" name="product_id" x-ref="productId">

            {{-- Pièce sélectionnée --}}
            <div x-show="selectedProduct" x-transition class="bg-orange-50 rounded-lg p-3 border border-orange-200">
                <div class="flex justify-between items-center">
                    <div>
                        <span class="text-sm font-medium text-gray-900" x-text="selectedProduct?.designation"></span>
                        <span class="block text-xs text-gray-500" x-text="'Réf: ' + (selectedProduct?.reference || '—')"></span>
                    </div>
                    <button type="button" @click="selectedProduct = null; search = ''; $refs.productId.value = ''" 
                            class="text-gray-400 hover:text-red-500">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                <div class="flex gap-4 mt-2 text-xs text-gray-600">
                    <span>Prix vente: <strong class="text-gray-900" x-text="parseFloat(selectedProduct?.prix_vente || 0).toFixed(2) + ' DH'"></strong></span>
                    <span>Prix achat: <strong class="text-gray-900" x-text="parseFloat(selectedProduct?.prix_achat || 0).toFixed(2) + ' DH'"></strong></span>
                    <span>Stock: <strong :class="(selectedProduct?.quantite_stock || 0) > 0 ? 'text-green-600' : 'text-red-600'" 
                                         x-text="parseFloat(selectedProduct?.quantite_stock || 0) + ' ' + (selectedProduct?.unite || 'u')"></strong></span>
                </div>
            </div>

            {{-- Quantité --}}
            <div x-show="selectedProduct">
                <label class="block text-sm font-medium text-gray-700 mb-1">Quantité</label>
                <input type="number" name="quantite" x-model="quantite" step="0.01" min="0.01"
                       class="w-32 rounded-lg border-gray-300 text-sm focus:ring-orange-500 focus:border-orange-500">
                <span class="text-xs text-gray-500 ml-2" x-text="selectedProduct?.unite || 'unité(s)'"></span>
            </div>

            {{-- Bouton --}}
            <div x-show="selectedProduct">
                <button type="submit" 
                        class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Ajouter à l'OR
                </button>
            </div>
        </form>
    </div>
</div>
