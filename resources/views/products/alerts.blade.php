@extends('layouts.app')

@section('title', 'Alertes Stock')
@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-primary-600">Stock</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Alertes</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Alertes Stock</h1>
            <p class="text-sm text-gray-500 mt-0.5">Produits nécessitant une attention</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-sm text-gray-500 hover:text-gray-700">&larr; Retour au stock</a>
    </div>

    {{-- En rupture --}}
    @if($enRupture->count() > 0)
    <div class="bg-white rounded-xl border border-red-200">
        <div class="px-5 py-4 border-b border-red-200 bg-red-50 rounded-t-xl flex items-center gap-2">
            <i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>
            <h2 class="font-semibold text-red-800">En rupture de stock ({{ $enRupture->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Seuil cmd.</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($enRupture as $product)
                    <tr class="bg-red-50/50">
                        <td class="px-4 py-2.5"><a href="{{ route('products.show', $product) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">{{ $product->reference }}</a></td>
                        <td class="px-4 py-2.5"><span class="text-xs font-medium text-gray-800">{{ $product->designation }}</span></td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $product->category?->nom ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-red-600 font-bold">{{ $product->quantite_stock }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-blue-600">{{ $product->seuil_commande }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $product->fournisseur_nom ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('products.show', $product) }}" class="text-xs text-primary-600 hover:underline">Approvisionner</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- En alerte --}}
    @if($enAlerte->count() > 0)
    <div class="bg-white rounded-xl border border-amber-200">
        <div class="px-5 py-4 border-b border-amber-200 bg-amber-50 rounded-t-xl flex items-center gap-2">
            <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500"></i>
            <h2 class="font-semibold text-amber-800">Sous le seuil d'alerte ({{ $enAlerte->count() }})</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Seuil alerte</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Seuil cmd.</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($enAlerte as $product)
                    <tr class="bg-amber-50/50">
                        <td class="px-4 py-2.5"><a href="{{ route('products.show', $product) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">{{ $product->reference }}</a></td>
                        <td class="px-4 py-2.5"><span class="text-xs font-medium text-gray-800">{{ $product->designation }}</span></td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $product->category?->nom ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-amber-600 font-bold">{{ $product->quantite_stock }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-amber-600">{{ $product->seuil_alerte }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-blue-600">{{ $product->seuil_commande }}</td>
                        <td class="px-4 py-2.5 text-xs text-gray-600">{{ $product->fournisseur_nom ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right">
                            <a href="{{ route('products.show', $product) }}" class="text-xs text-primary-600 hover:underline">Approvisionner</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- À commander --}}
    @if($aCommander->count() > 0 && $aCommander->diff($enAlerte)->diff($enRupture)->count() > 0)
    <div class="bg-white rounded-xl border border-blue-200">
        <div class="px-5 py-4 border-b border-blue-200 bg-blue-50 rounded-t-xl flex items-center gap-2">
            <i data-lucide="shopping-cart" class="w-5 h-5 text-blue-500"></i>
            <h2 class="font-semibold text-blue-800">Sous le seuil de commande</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Seuil cmd.</th>
                        <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Fournisseur</th>
                        <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Délai</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @foreach($aCommander->diff($enAlerte)->diff($enRupture) as $product)
                    <tr>
                        <td class="px-4 py-2.5"><a href="{{ route('products.show', $product) }}" class="font-mono text-sm font-semibold text-primary-600">{{ $product->reference }}</a></td>
                        <td class="px-4 py-2.5 text-xs">{{ $product->designation }}</td>
                        <td class="px-4 py-2.5 text-right text-xs font-medium">{{ $product->quantite_stock }}</td>
                        <td class="px-4 py-2.5 text-right text-xs text-blue-600">{{ $product->seuil_commande }}</td>
                        <td class="px-4 py-2.5 text-xs">{{ $product->fournisseur_nom ?? '—' }}</td>
                        <td class="px-4 py-2.5 text-right text-xs">{{ $product->delai_livraison_jours ? $product->delai_livraison_jours . 'j' : '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Tout va bien --}}
    @if($enRupture->count() === 0 && $enAlerte->count() === 0)
    <div class="bg-green-50 rounded-xl border border-green-200 p-8 text-center">
        <i data-lucide="check-circle" class="w-12 h-12 text-green-400 mx-auto mb-3"></i>
        <h2 class="text-lg font-semibold text-green-700">Tous les stocks sont en ordre</h2>
        <p class="text-sm text-green-600 mt-1">Aucun produit en alerte ou en rupture.</p>
    </div>
    @endif
</div>
@endsection
