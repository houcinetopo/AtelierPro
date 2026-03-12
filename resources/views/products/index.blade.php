@extends('layouts.app')

@section('title', 'Stock & Produits')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Stock</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Stock & Produits</h1>
            <p class="text-sm text-gray-500 mt-0.5">Catalogue et gestion des stocks</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('products.alerts') }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm {{ $stats['en_alerte'] + $stats['en_rupture'] > 0 ? 'text-red-600 bg-red-50 border-red-200' : 'text-gray-600 bg-white border-gray-200' }} border rounded-lg hover:bg-red-100">
                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                Alertes
                @if($stats['en_alerte'] + $stats['en_rupture'] > 0)
                    <span class="px-1.5 py-0.5 bg-red-500 text-white text-xs rounded-full">{{ $stats['en_alerte'] + $stats['en_rupture'] }}</span>
                @endif
            </a>
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <a href="{{ route('products.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouveau produit
            </a>
            @endif
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="package" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Produits actifs</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['valeur_stock'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Valeur stock (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-amber-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-amber-600">{{ $stats['en_alerte'] }}</p>
                <p class="text-xs text-gray-500">En alerte</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="x-circle" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-red-600">{{ $stats['en_rupture'] }}</p>
                <p class="text-xs text-gray-500">En rupture</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('products.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Référence, désignation, marque, code barre...">
                </div>
            </div>
            <select name="type" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les types</option>
                @foreach (\App\Models\Product::TYPES as $key => $label)
                    <option value="{{ $key }}" {{ request('type') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="category" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes catégories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->nom }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="alerte" value="1" {{ request('alerte') ? 'checked' : '' }} class="rounded border-gray-300 text-amber-500 focus:ring-amber-500">
                Alertes seules
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'type', 'category', 'alerte', 'rupture']))
                <a href="{{ route('products.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Référence</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Catégorie</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">P. Achat</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">P. Vente</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">État</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($products as $product)
                    <tr class="hover:bg-gray-50 transition-colors {{ $product->stock_status === 'rupture' ? 'bg-red-50/50' : ($product->stock_status === 'alerte' ? 'bg-amber-50/50' : '') }}">
                        <td class="px-4 py-3">
                            <a href="{{ route('products.show', $product) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                                {{ $product->reference }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $product->designation }}</p>
                            @if($product->marque)
                                <p class="text-xs text-gray-400">{{ $product->marque }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">{!! $product->type_badge !!}</td>
                        <td class="px-4 py-3">
                            @if($product->category)
                                <span class="text-xs text-gray-600">{{ $product->category->nom }}</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right text-xs text-gray-600">{{ number_format($product->prix_achat, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs font-medium text-gray-800">{{ number_format($product->prix_vente, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right">
                            <span class="font-semibold text-sm {{ $product->quantite_stock <= 0 ? 'text-red-600' : ($product->quantite_stock <= $product->seuil_alerte ? 'text-amber-600' : 'text-gray-800') }}">
                                {{ $product->quantite_stock }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $product->unite }}</span>
                        </td>
                        <td class="px-4 py-3">{!! $product->stock_badge !!}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('products.show', $product) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                                <a href="{{ route('products.edit', $product) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="package" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun produit trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($products->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $products->withQueryString()->links() }}</div>
        @endif
    </div>

    {{-- Gestion catégories (admin) --}}
    @if(auth()->user()->isAdmin())
    <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ open: false }">
        <div class="flex items-center justify-between">
            <h2 class="text-base font-semibold text-gray-800">Catégories</h2>
            <button @click="open = !open" class="text-sm text-primary-600 hover:text-primary-800">
                <span x-text="open ? 'Fermer' : 'Gérer'"></span>
            </button>
        </div>
        <div x-show="open" x-cloak x-transition class="mt-4 space-y-3">
            <form method="POST" action="{{ route('products.categories.store') }}" class="flex items-end gap-3">
                @csrf
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Nom</label>
                    <input type="text" name="nom" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-1 focus:ring-primary-500" placeholder="Nom de la catégorie">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Couleur</label>
                    <input type="color" name="couleur" value="#6B7280" class="w-10 h-10 rounded border border-gray-300">
                </div>
                <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm rounded-lg hover:bg-primary-700">Ajouter</button>
            </form>
            <div class="flex flex-wrap gap-2">
                @foreach($categories as $cat)
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg border border-gray-200 text-sm">
                    <span class="w-3 h-3 rounded-full" style="background-color: {{ $cat->couleur }}"></span>
                    {{ $cat->nom }} <span class="text-xs text-gray-400">({{ $cat->products()->count() }})</span>
                    @if($cat->products()->count() === 0)
                    <form method="POST" action="{{ route('products.categories.destroy', $cat) }}" class="inline">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-red-400 hover:text-red-600" onclick="return confirm('Supprimer ?')"><i data-lucide="x" class="w-3 h-3"></i></button>
                    </form>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
