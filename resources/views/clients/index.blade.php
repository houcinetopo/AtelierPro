@extends('layouts.app')

@section('title', 'Gestion des Clients')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Clients</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Clients</h1>
            <p class="text-sm text-gray-500 mt-0.5">Base de données clients et véhicules</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.export') }}" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="download" class="w-4 h-4"></i> Exporter
            </a>
            <a href="{{ route('clients.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Nouveau client
            </a>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 text-center">
            <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
            <p class="text-xs text-gray-500">Total</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 text-center">
            <p class="text-xl font-bold text-blue-600">{{ $stats['particuliers'] }}</p>
            <p class="text-xs text-gray-500">Particuliers</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 text-center">
            <p class="text-xl font-bold text-purple-600">{{ $stats['societes'] }}</p>
            <p class="text-xs text-gray-500">Sociétés</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 text-center">
            <p class="text-xl font-bold text-amber-600">{{ $stats['with_debt'] }}</p>
            <p class="text-xs text-gray-500">Avec crédit</p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-3.5 text-center">
            <p class="text-xl font-bold text-red-600">{{ number_format($stats['total_debt'], 2, ',', ' ') }}</p>
            <p class="text-xs text-gray-500">Total créances (DH)</p>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('clients.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Nom, téléphone, CIN, ICE, email...">
            </div>
            <select name="type_client" class="border border-gray-300 rounded-lg text-sm px-3 py-2">
                <option value="">Tous les types</option>
                <option value="particulier" {{ request('type_client') == 'particulier' ? 'selected' : '' }}>Particulier</option>
                <option value="societe" {{ request('type_client') == 'societe' ? 'selected' : '' }}>Société</option>
            </select>
            <label class="flex items-center gap-2 text-sm text-gray-600 px-3">
                <input type="checkbox" name="with_debt" value="1" {{ request('with_debt') ? 'checked' : '' }} class="rounded text-primary-600">
                Avec crédit
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search','type_client','with_debt','blacklisted']))
                <a href="{{ route('clients.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Type</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contact</th>
                        <th class="text-center px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicules</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Crédit</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($clients as $client)
                    <tr class="hover:bg-gray-50 transition-colors {{ $client->is_blacklisted ? 'bg-red-50/30' : '' }}">
                        <td class="px-5 py-3">
                            <a href="{{ route('clients.show', $client) }}" class="flex items-center gap-3 group">
                                <img src="{{ $client->avatar_url }}" alt="" class="w-9 h-9 rounded-full">
                                <div>
                                    <p class="font-medium text-gray-800 group-hover:text-primary-600">
                                        {{ $client->display_name }}
                                        @if($client->is_blacklisted)
                                            <i data-lucide="ban" class="w-3.5 h-3.5 inline text-red-500 ml-1" title="Blacklisté"></i>
                                        @endif
                                    </p>
                                    @if($client->legal_id)
                                        <p class="text-xs text-gray-400 font-mono">{{ $client->legal_id_label }}: {{ $client->legal_id }}</p>
                                    @endif
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3">{!! $client->type_badge !!}</td>
                        <td class="px-5 py-3 text-xs">
                            <p class="text-gray-700">{{ $client->telephone ?? '—' }}</p>
                            @if($client->email)
                                <p class="text-gray-400">{{ $client->email }}</p>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-center">
                            <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-gray-100 text-gray-600 text-xs font-bold">
                                {{ $client->vehicles_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right">
                            @if($client->solde_credit > 0)
                                <span class="font-semibold {{ $client->isOverCreditLimit() ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ number_format($client->solde_credit, 2, ',', ' ') }} DH
                                </span>
                            @else
                                <span class="text-gray-400 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('clients.show', $client) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('clients.edit', $client) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('clients.destroy', $client) }}"
                                      x-data @submit.prevent="if(confirm('Supprimer ce client ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun client trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($clients->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $clients->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
