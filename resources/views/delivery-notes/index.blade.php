@extends('layouts.app')

@section('title', 'Bons de Livraison')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Bons de Livraison</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Bons de Livraison</h1>
            <p class="text-sm text-gray-500 mt-0.5">Suivi des livraisons de véhicules</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <a href="{{ route('delivery-notes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouveau bon
        </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="file-text" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Total BL</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['valides'] }}</p>
                <p class="text-xs text-gray-500">Validés</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="truck" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['ce_mois'] }}</p>
                <p class="text-xs text-gray-500">Ce mois</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-circle" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-red-600">{{ $stats['impayes'] }}</p>
                <p class="text-xs text-gray-500">Impayés</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('delivery-notes.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Rechercher N° BL, OR, client, immatriculation...">
                </div>
            </div>
            <select name="statut" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach (App\Models\DeliveryNote::STATUTS as $key => $label)
                    <option value="{{ $key }}" {{ request('statut') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="unpaid" value="1" {{ request('unpaid') ? 'checked' : '' }} class="rounded text-primary-600">
                Impayés
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'statut', 'date_from', 'date_to', 'unpaid']))
                <a href="{{ route('delivery-notes.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">N° BL</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">N° OR</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Total TTC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reste</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($notes as $note)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('delivery-notes.show', $note) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                                {{ $note->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('repair-orders.show', $note->repair_order_id) }}" class="font-mono text-xs text-gray-500 hover:text-primary-600">
                                {{ $note->repairOrder?->numero }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $note->client_name }}</p>
                            @if($note->nom_receptionnaire && $note->nom_receptionnaire !== ($note->client?->nom_complet))
                                <p class="text-xs text-gray-400">Reçu par : {{ $note->nom_receptionnaire }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-700">{{ $note->vehicle?->marque }} {{ $note->vehicle?->modele }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $note->vehicle?->immatriculation }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $note->date_livraison->format('d/m/Y') }}
                            @if($note->heure_livraison)
                                <span class="text-gray-400">{{ $note->heure_livraison }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 text-xs">
                            {{ number_format($note->total_ttc, 2, ',', ' ') }} DH
                        </td>
                        <td class="px-4 py-3 text-right text-xs">
                            @if($note->reste_a_payer > 0)
                                <span class="font-semibold text-red-600">{{ number_format($note->reste_a_payer, 2, ',', ' ') }} DH</span>
                            @else
                                <span class="text-green-600 font-medium">Soldé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{!! $note->statut_badge !!}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('delivery-notes.show', $note) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && $note->statut !== 'annule')
                                <a href="{{ route('delivery-notes.edit', $note) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="truck" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun bon de livraison trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($notes->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $notes->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
