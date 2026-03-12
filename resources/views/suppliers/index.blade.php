@extends('layouts.app')

@section('title', 'Fournisseurs')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Fournisseurs</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Fournisseurs</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion des fournisseurs et bons de commande</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <a href="{{ route('suppliers.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouveau fournisseur
        </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center"><i data-lucide="factory" class="w-5 h-5 text-indigo-500"></i></div>
            <div><p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p><p class="text-xs text-gray-500">Fournisseurs actifs</p></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center"><i data-lucide="credit-card" class="w-5 h-5 text-amber-500"></i></div>
            <div><p class="text-xl font-bold text-amber-600">{{ $stats['avec_solde'] }}</p><p class="text-xs text-gray-500">Avec solde dû</p></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center"><i data-lucide="banknote" class="w-5 h-5 text-red-500"></i></div>
            <div><p class="text-lg font-bold text-red-600">{{ number_format($stats['solde_total'], 0, ',', ' ') }}</p><p class="text-xs text-gray-500">Solde total (DH)</p></div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center"><i data-lucide="shopping-cart" class="w-5 h-5 text-emerald-500"></i></div>
            <div><p class="text-lg font-bold text-gray-800">{{ number_format($stats['commandes_mois'], 0, ',', ' ') }}</p><p class="text-xs text-gray-500">Commandes ce mois</p></div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Nom, code, contact, ville...">
            </div>
            <select name="type" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les types</option>
                @foreach (\App\Models\Supplier::TYPES as $k => $v)
                    <option value="{{ $k }}" {{ request('type') == $k ? 'selected' : '' }}>{{ $v }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'type']))
                <a href="{{ route('suppliers.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Code</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Raison sociale</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Ville</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Téléphone</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Produits</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Solde dû</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($suppliers as $supplier)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('suppliers.show', $supplier) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">{{ $supplier->code }}</a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $supplier->raison_sociale }}</p>
                            @if($supplier->nom_contact)<p class="text-xs text-gray-400">{{ $supplier->nom_contact }}</p>@endif
                        </td>
                        <td class="px-4 py-3">{!! $supplier->type_badge !!}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $supplier->ville ?? '—' }}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $supplier->telephone ?? '—' }}</td>
                        <td class="px-4 py-3 text-right text-xs font-medium text-gray-800">{{ $supplier->products_count }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold {{ $supplier->solde_du > 0 ? 'text-red-600' : 'text-gray-400' }}">
                            {{ $supplier->solde_du > 0 ? number_format($supplier->solde_du, 2, ',', ' ') . ' DH' : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('suppliers.show', $supplier) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                                <a href="{{ route('suppliers.edit', $supplier) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                <a href="{{ route('suppliers.create-order', $supplier) }}" class="p-2 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600" title="Commander"><i data-lucide="shopping-cart" class="w-4 h-4"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-5 py-12 text-center text-gray-400"><i data-lucide="factory" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>Aucun fournisseur</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($suppliers->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $suppliers->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
