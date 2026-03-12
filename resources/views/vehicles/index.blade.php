@extends('layouts.app')
@section('title', 'Véhicules')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Véhicules</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <h1 class="text-2xl font-bold text-gray-800">Véhicules</h1>
        <a href="{{ route('vehicles.create') }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
            <i data-lucide="car" class="w-4 h-4"></i> Nouveau véhicule
        </a>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Immatriculation, marque, modèle, châssis...">
            </div>
            <select name="marque" class="border border-gray-300 rounded-lg text-sm px-3 py-2">
                <option value="">Toutes les marques</option>
                @foreach($marques as $m)
                    <option value="{{ $m }}" {{ request('marque') == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search','marque']))
                <a href="{{ route('vehicles.index') }}" class="px-3 py-2 text-gray-500 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Véhicule</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Propriétaire</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Carburant</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Km</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Assurance</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($vehicles as $v)
                    <tr class="hover:bg-gray-50">
                        <td class="px-5 py-3">
                            <a href="{{ route('vehicles.show', $v) }}" class="group">
                                <p class="font-medium text-gray-800 group-hover:text-primary-600">{{ $v->full_name }}</p>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $v->immatriculation }}</span>
                                    @if($v->couleur) <span class="text-xs text-gray-400">{{ $v->couleur }}</span> @endif
                                    @if($v->annee) <span class="text-xs text-gray-400">{{ $v->annee }}</span> @endif
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3">
                            <a href="{{ route('clients.show', $v->client_id) }}" class="text-sm text-gray-700 hover:text-primary-600">{{ $v->client?->display_name ?? '—' }}</a>
                        </td>
                        <td class="px-5 py-3 text-xs text-gray-600">{{ $v->carburant_label }}</td>
                        <td class="px-5 py-3 text-right text-xs text-gray-600">{{ $v->kilometrage > 0 ? number_format($v->kilometrage, 0, '', ' ') : '—' }}</td>
                        <td class="px-5 py-3">{!! $v->assurance_badge !!}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('vehicles.show', $v) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                <a href="{{ route('vehicles.edit', $v) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                <form method="POST" action="{{ route('vehicles.destroy', $v) }}" x-data @submit.prevent="if(confirm('Supprimer ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-5 py-12 text-center text-gray-400"><i data-lucide="car" class="w-10 h-10 mx-auto mb-2 opacity-40"></i><p>Aucun véhicule trouvé</p></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($vehicles->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $vehicles->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
