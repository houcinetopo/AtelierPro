@extends('layouts.app')
@section('title', 'Nouvelle Commande')
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="hover:text-primary-600">Fournisseurs</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-primary-600">{{ $supplier->code }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouvelle commande</span>
@endsection

@section('content')
<div class="max-w-5xl" x-data="orderForm()">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Bon de commande</h1>
        <p class="text-sm text-gray-500 mt-0.5">Fournisseur : <span class="font-semibold text-gray-700">{{ $supplier->raison_sociale }}</span> · N° <span class="font-mono font-semibold text-primary-600">{{ $numero }}</span></p>
    </div>

    <form method="POST" action="{{ route('suppliers.store-order', $supplier) }}" class="space-y-4">
        @csrf

        {{-- Infos commande --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date commande <span class="text-red-500">*</span></label>
                    <input type="date" name="date_commande" required value="{{ old('date_commande', now()->format('Y-m-d')) }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Livraison prévue</label>
                    <input type="date" name="date_livraison_prevue" value="{{ old('date_livraison_prevue', now()->addDays($supplier->delai_livraison_jours)->format('Y-m-d')) }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Réf. fournisseur</label>
                    <input type="text" name="reference_fournisseur" value="{{ old('reference_fournisseur') }}" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="N° du fournisseur">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Remise globale (DH)</label>
                    <input type="number" name="remise_globale" step="0.01" min="0" x-model="remise" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div class="mt-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-3 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">{{ old('notes') }}</textarea>
            </div>
        </div>

        {{-- Lignes --}}
        <div class="bg-white rounded-xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-semibold text-gray-800">Articles</h2>
                <button type="button" @click="addItem()" class="text-sm text-primary-600 hover:text-primary-800 flex items-center gap-1"><i data-lucide="plus" class="w-4 h-4"></i> Ajouter</button>
            </div>

            @if($allProducts->count() > 0)
            <div class="mb-4 p-3 bg-blue-50 rounded-lg">
                <p class="text-xs text-blue-700 font-medium mb-2">Produits du catalogue :</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($allProducts->take(15) as $p)
                    <button type="button" @click="addFromProduct({{ json_encode(['id'=>$p->id,'ref'=>$p->reference,'designation'=>$p->designation,'prix'=>$p->prix_achat,'unite'=>$p->unite]) }})"
                            class="text-xs px-2 py-1 bg-white border border-blue-200 rounded hover:bg-blue-100 text-blue-700">
                        {{ $p->reference }} — {{ $p->designation }}
                    </button>
                    @endforeach
                </div>
            </div>
            @endif

            <template x-for="(item, index) in items" :key="index">
                <div class="grid grid-cols-12 gap-2 mb-2 items-end">
                    <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                    <div class="col-span-12 sm:col-span-4">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">Désignation *</label>
                        <input type="text" :name="'items['+index+'][designation]'" x-model="item.designation" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-4 sm:col-span-1">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">Réf.</label>
                        <input type="text" :name="'items['+index+'][reference]'" x-model="item.reference" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-3 sm:col-span-1">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">Qté *</label>
                        <input type="number" :name="'items['+index+'][quantite]'" x-model="item.quantite" step="0.01" min="0.01" required class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-3 sm:col-span-1">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">Unité</label>
                        <select :name="'items['+index+'][unite]'" x-model="item.unite" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                            <option value="u">u</option><option value="kg">kg</option><option value="l">l</option><option value="m">m</option><option value="boite">boîte</option><option value="jeu">jeu</option><option value="kit">kit</option>
                        </select>
                    </div>
                    <div class="col-span-3 sm:col-span-2">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">P.U. *</label>
                        <input type="number" :name="'items['+index+'][prix_unitaire]'" x-model="item.prix_unitaire" step="0.01" min="0" required class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-3 sm:col-span-1">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">Rem.%</label>
                        <input type="number" :name="'items['+index+'][remise]'" x-model="item.remise" step="0.01" min="0" max="100" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                    </div>
                    <div class="col-span-9 sm:col-span-1 text-right">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">HT</label>
                        <p class="py-2 text-xs font-semibold text-gray-800" x-text="itemTotal(item).toFixed(2) + ' DH'"></p>
                    </div>
                    <div class="col-span-3 sm:col-span-1 text-right">
                        <label x-show="index===0" class="block text-xs text-gray-500 mb-1">&nbsp;</label>
                        <button type="button" @click="items.splice(index, 1)" class="p-2 text-red-400 hover:text-red-600" x-show="items.length > 1"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                    </div>
                </div>
            </template>

            <div class="mt-4 pt-4 border-t border-gray-200 text-right space-y-1">
                <p class="text-sm text-gray-500">Total HT : <span class="font-semibold text-gray-800" x-text="totalHT().toFixed(2) + ' DH'"></span></p>
                <p class="text-sm text-gray-500">TVA 20% : <span class="font-semibold text-gray-800" x-text="totalTVA().toFixed(2) + ' DH'"></span></p>
                <p class="text-sm text-gray-500" x-show="parseFloat(remise) > 0">Remise : <span class="text-red-600 font-semibold" x-text="'-' + parseFloat(remise).toFixed(2) + ' DH'"></span></p>
                <p class="text-lg font-bold text-primary-600">Net : <span x-text="netAPayer().toFixed(2) + ' DH'"></span></p>
            </div>
        </div>

        <div class="flex items-center justify-between pt-2">
            <a href="{{ route('suppliers.show', $supplier) }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
            <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg flex items-center gap-2"><i data-lucide="save" class="w-4 h-4"></i> Créer le bon de commande</button>
        </div>
    </form>
</div>

<script>
function orderForm() {
    return {
        remise: 0,
        items: [{ product_id: '', designation: '', reference: '', quantite: 1, unite: 'u', prix_unitaire: 0, remise: 0 }],
        addItem() { this.items.push({ product_id: '', designation: '', reference: '', quantite: 1, unite: 'u', prix_unitaire: 0, remise: 0 }); },
        addFromProduct(p) { this.items.push({ product_id: p.id, designation: p.designation, reference: p.ref, quantite: 1, unite: p.unite, prix_unitaire: p.prix, remise: 0 }); },
        itemTotal(item) { return (item.quantite * item.prix_unitaire) * (1 - item.remise / 100); },
        totalHT() { return this.items.reduce((s, i) => s + this.itemTotal(i), 0); },
        totalTVA() { return this.totalHT() * 0.20; },
        netAPayer() { return this.totalHT() + this.totalTVA() - parseFloat(this.remise || 0); },
    }
}
</script>
@endsection
