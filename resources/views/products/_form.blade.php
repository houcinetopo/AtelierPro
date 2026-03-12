{{-- Formulaire produit (création + édition) --}}
@php $p = $product ?? null; @endphp

<div class="space-y-6" x-data="{ type: '{{ old('type', $p?->type ?? 'piece') }}' }">

    {{-- ═══════ IDENTIFICATION ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">1</span>
            Identification
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence</label>
                <input type="text" name="reference" value="{{ old('reference', $p?->reference ?? $reference ?? '') }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Auto-généré si vide">
                @error('reference') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code barre</label>
                <input type="text" name="code_barre" value="{{ old('code_barre', $p?->code_barre) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="EAN13, Code128...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" required x-model="type"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach (\App\Models\Product::TYPES as $key => $label)
                        <option value="{{ $key }}" {{ old('type', $p?->type) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Désignation <span class="text-red-500">*</span></label>
                <input type="text" name="designation" required value="{{ old('designation', $p?->designation) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Nom du produit">
                @error('designation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Catégorie</label>
                <select name="category_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Aucune —</option>
                    @foreach ($categories as $cat)
                        <option value="{{ $cat->id }}" {{ old('category_id', $p?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marque</label>
                <input type="text" name="marque" value="{{ old('marque', $p?->marque) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: Bosch, Valeo, Mann...">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Modèles compatibles</label>
                <input type="text" name="modele_compatible" value="{{ old('modele_compatible', $p?->modele_compatible) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: Dacia Logan 2016-2024, Renault Clio IV">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
            <textarea name="description" rows="2"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Description détaillée...">{{ old('description', $p?->description) }}</textarea>
        </div>
    </div>

    {{-- ═══════ PRIX ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">2</span>
            Prix & Tarification
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix d'achat HT <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="number" name="prix_achat" step="0.01" min="0" required
                           value="{{ old('prix_achat', $p?->prix_achat ?? 0) }}"
                           class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">DH</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Prix de vente HT <span class="text-red-500">*</span></label>
                <div class="relative">
                    <input type="number" name="prix_vente" step="0.01" min="0" required
                           value="{{ old('prix_vente', $p?->prix_vente ?? 0) }}"
                           class="w-full px-4 py-2.5 pr-12 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <span class="absolute right-3 top-1/2 -translate-y-1/2 text-xs text-gray-400">DH</span>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">TVA (%)</label>
                <input type="number" name="taux_tva" step="0.01" min="0" max="30"
                       value="{{ old('taux_tva', $p?->taux_tva ?? 20) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Marge (%)</label>
                <input type="number" name="marge_percent" step="0.01"
                       value="{{ old('marge_percent', $p?->marge_percent) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Auto si vide">
            </div>
        </div>
    </div>

    {{-- ═══════ STOCK ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">3</span>
            Stock & Seuils
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            @if(!$p)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stock initial</label>
                <input type="number" name="quantite_stock" step="0.01" min="0"
                       value="{{ old('quantite_stock', 0) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Unité <span class="text-red-500">*</span></label>
                <select name="unite" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach (\App\Models\Product::UNITES as $key => $label)
                        <option value="{{ $key }}" {{ old('unite', $p?->unite ?? 'u') == $key ? 'selected' : '' }}>{{ $label }} ({{ $key }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil alerte</label>
                <input type="number" name="seuil_alerte" step="0.01" min="0"
                       value="{{ old('seuil_alerte', $p?->seuil_alerte ?? 5) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Seuil commande</label>
                <input type="number" name="seuil_commande" step="0.01" min="0"
                       value="{{ old('seuil_commande', $p?->seuil_commande ?? 10) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Emplacement</label>
                <input type="text" name="emplacement" value="{{ old('emplacement', $p?->emplacement) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: A3-R2">
            </div>
        </div>
    </div>

    {{-- ═══════ FOURNISSEUR ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">4</span>
            Fournisseur
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du fournisseur</label>
                <input type="text" name="fournisseur_nom" value="{{ old('fournisseur_nom', $p?->fournisseur_nom) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: Auto Parts Maroc">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Référence fournisseur</label>
                <input type="text" name="fournisseur_ref" value="{{ old('fournisseur_ref', $p?->fournisseur_ref) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Réf. catalogue fournisseur">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Délai livraison (jours)</label>
                <input type="number" name="delai_livraison_jours" min="0"
                       value="{{ old('delai_livraison_jours', $p?->delai_livraison_jours) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>
    </div>

    {{-- ═══════ NOTES ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Notes internes...">{{ old('notes', $p?->notes) }}</textarea>
            </div>
            <div class="flex items-center">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" name="actif" value="1"
                           {{ old('actif', $p?->actif ?? true) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Produit actif (visible dans les listes)</span>
                </label>
            </div>
        </div>
    </div>

    {{-- Boutons --}}
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $p ? 'Enregistrer les modifications' : 'Créer le produit' }}
        </button>
    </div>
</div>
