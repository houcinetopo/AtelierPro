@extends('layouts.app')
@section('title', 'Experts')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Experts</h1>
            <p class="text-sm text-gray-500 mt-1">Gestion des experts automobiles</p>
        </div>
        <a href="{{ route('experts.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700 transition shadow-sm">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
            Nouvel expert
        </a>
    </div>

    {{-- Recherche --}}
    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un expert..."
               class="flex-1 rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">Rechercher</button>
    </form>

    {{-- Liste --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-4 py-3 text-left">Expert</th>
                    <th class="px-4 py-3 text-left">Cabinet</th>
                    <th class="px-4 py-3 text-left">Email principal</th>
                    <th class="px-4 py-3 text-left">Téléphone</th>
                    <th class="px-4 py-3 text-center">OR</th>
                    <th class="px-4 py-3 text-center">Statut</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($experts as $expert)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3">
                        <a href="{{ route('experts.show', $expert) }}" class="font-medium text-gray-900 hover:text-primary-600">
                            {{ $expert->nom_complet }}
                        </a>
                    </td>
                    <td class="px-4 py-3 text-gray-600">{{ $expert->cabinet ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $expert->primary_email ?? '—' }}</td>
                    <td class="px-4 py-3 text-gray-600">{{ $expert->telephone ?? '—' }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">
                            {{ $expert->repair_orders_count }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($expert->actif)
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Actif</span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('experts.show', $expert) }}" class="p-1.5 text-gray-400 hover:text-primary-600 rounded-lg hover:bg-gray-100" title="Voir">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <a href="{{ route('experts.edit', $expert) }}" class="p-1.5 text-gray-400 hover:text-amber-600 rounded-lg hover:bg-gray-100" title="Modifier">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                        Aucun expert enregistré.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{ $experts->withQueryString()->links() }}
</div>
@endsection
