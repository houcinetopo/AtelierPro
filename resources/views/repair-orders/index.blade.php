@extends('layouts.app')

@section('title', 'Ordres de Réparation')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Ordres de Réparation</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Ordres de Réparation</h1>
            <p class="text-sm text-gray-500 mt-0.5">Suivi des travaux en atelier</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <a href="{{ route('repair-orders.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouvel ordre
        </a>
        @endif
    </div>

    {{-- Stats rapides --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="clipboard-list" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Total</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="wrench" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['en_cours'] }}</p>
                <p class="text-xs text-gray-500">En cours</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-red-600">{{ $stats['en_retard'] }}</p>
                <p class="text-xs text-gray-500">En retard</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="check-circle" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['termines'] }}</p>
                <p class="text-xs text-gray-500">Terminés</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('repair-orders.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Rechercher par N° OR, client, immatriculation...">
                </div>
            </div>
            <select name="status" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach (\App\Models\RepairOrder::STATUSES as $key => $label)
                    <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            @if(!auth()->user()->isTechnicien())
            <select name="technicien_id" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les techniciens</option>
                @foreach ($techniciens as $tech)
                    <option value="{{ $tech->id }}" {{ request('technicien_id') == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                @endforeach
            </select>
            @endif
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500" placeholder="Du">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500" placeholder="Au">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'status', 'technicien_id', 'date_from', 'date_to']))
                <a href="{{ route('repair-orders.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">N° Ordre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Technicien</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Réception</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Montant TTC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($orders as $order)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('repair-orders.show', $order) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                                {{ $order->numero }}
                            </a>
                            @if($order->is_late)
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">En retard</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-800 text-xs">{{ $order->client_name }}</p>
                            @if($order->client?->telephone)
                                <p class="text-xs text-gray-400">{{ $order->client->telephone }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="text-xs text-gray-700">{{ $order->vehicle?->marque }} {{ $order->vehicle?->modele }}</p>
                            <p class="text-xs text-gray-400 font-mono">{{ $order->vehicle?->immatriculation }}</p>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $order->technicien?->name ?? '—' }}
                        </td>
                        <td class="px-4 py-3">{!! $order->status_badge !!}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $order->date_reception->format('d/m/Y') }}
                            @if($order->date_prevue_livraison)
                                <br><span class="text-gray-400">→ {{ $order->date_prevue_livraison->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            @if($order->total_ttc > 0)
                                <span class="font-semibold text-gray-800">{{ number_format($order->total_ttc, 2, ',', ' ') }}</span>
                                <span class="text-xs text-gray-400">DH</span>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('repair-orders.show', $order) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($order->status, ['facture', 'annule']))
                                <a href="{{ route('repair-orders.edit', $order) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="clipboard-list" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun ordre de réparation trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $orders->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
