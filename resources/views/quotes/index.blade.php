@extends('layouts.app')

@section('title', 'Devis')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Devis</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Devis</h1>
            <p class="text-sm text-gray-500 mt-0.5">Estimations et propositions commerciales</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <a href="{{ route('quotes.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouveau devis
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
                <p class="text-xs text-gray-500">Total devis</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="send" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['en_attente'] }}</p>
                <p class="text-xs text-gray-500">En attente</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['acceptes'] }}</p>
                <p class="text-xs text-gray-500">Acceptés</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-emerald-50 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5 text-emerald-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['montant_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">DH ce mois</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('quotes.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Rechercher N° devis, client, immatriculation...">
                </div>
            </div>
            <select name="statut" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\Quote::STATUTS as $key => $label)
                    <option value="{{ $key }}" {{ request('statut') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'statut', 'date_from', 'date_to']))
                <a href="{{ route('quotes.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">N° Devis</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Validité</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Montant TTC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($quotes as $quote)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('quotes.show', $quote) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                                {{ $quote->numero }}
                            </a>
                            @if($quote->is_expired)
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-700">Expiré</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $quote->client_name }}</p>
                        </td>
                        <td class="px-4 py-3">
                            @if($quote->vehicle)
                                <p class="text-xs text-gray-700">{{ $quote->vehicle->marque }} {{ $quote->vehicle->modele }}</p>
                                <p class="text-xs text-gray-400 font-mono">{{ $quote->vehicle->immatriculation }}</p>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{!! $quote->statut_badge !!}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">{{ $quote->date_devis->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-xs {{ $quote->is_expired ? 'text-red-600 font-semibold' : 'text-gray-600' }}">
                            {{ $quote->date_validite->format('d/m/Y') }}
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($quote->net_a_payer > 0)
                                <span class="font-semibold text-gray-800">{{ number_format($quote->net_a_payer, 2, ',', ' ') }}</span>
                                <span class="text-xs text-gray-400">DH</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('quotes.show', $quote) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($quote->statut, ['converti', 'annule']))
                                <a href="{{ route('quotes.edit', $quote) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                @endif
                                @if($quote->is_convertible)
                                <form method="POST" action="{{ route('quotes.convert', $quote) }}" class="inline">
                                    @csrf
                                    <button type="submit" onclick="return confirm('Convertir ce devis en Ordre de Réparation ?')"
                                            class="p-2 rounded-lg hover:bg-green-50 text-gray-400 hover:text-green-600" title="Convertir en OR">
                                        <i data-lucide="arrow-right-circle" class="w-4 h-4"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="file-text" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun devis trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($quotes->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $quotes->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
