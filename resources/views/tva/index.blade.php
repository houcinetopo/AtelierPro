@extends('layouts.app')
@section('title', 'Déclarations TVA')

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Déclarations TVA</h1>
            <p class="mt-1 text-sm text-gray-500">Suivi des déclarations et paiements TVA — Année {{ $annee }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if($enRetard > 0)
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-red-50 text-red-700 border border-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    {{ $enRetard }} en retard
                </span>
            @endif
            <a href="{{ route('tva.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Nouvelle déclaration
            </a>
        </div>
    </div>

    {{-- Stats annuelles --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">TVA Collectée</p>
                    <p class="text-lg font-bold text-blue-600">{{ number_format($stats['total_tva_collectee'], 2, ',', ' ') }} <span class="text-xs font-normal">DH</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">TVA Déductible</p>
                    <p class="text-lg font-bold text-amber-600">{{ number_format($stats['total_tva_deductible'], 2, ',', ' ') }} <span class="text-xs font-normal">DH</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">TVA Due</p>
                    <p class="text-lg font-bold text-red-600">{{ number_format($stats['total_tva_due'], 2, ',', ' ') }} <span class="text-xs font-normal">DH</span></p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-5">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                </div>
                <div>
                    <p class="text-xs text-gray-500">TVA Payée</p>
                    <p class="text-lg font-bold text-green-600">{{ number_format($stats['total_paye'], 2, ',', ' ') }} <span class="text-xs font-normal">DH</span></p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Année</label>
                <select name="annee" class="rounded-lg border-gray-300 text-sm">
                    @foreach($annees as $a)
                        <option value="{{ $a }}" {{ $annee == $a ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Statut</label>
                <select name="statut" class="rounded-lg border-gray-300 text-sm">
                    <option value="">Tous</option>
                    @foreach(\App\Models\TvaDeclaration::STATUTS as $key => $label)
                        <option value="{{ $key }}" {{ $statut === $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200">Filtrer</button>
            <a href="{{ route('tva.index') }}" class="px-4 py-2 text-sm text-gray-500 hover:text-gray-700">Réinitialiser</a>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Période</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Régime</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Statut</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">TVA Collectée</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">TVA Déductible</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">TVA Due</th>
                        <th class="px-4 py-3 text-right font-medium text-gray-500">Date limite</th>
                        <th class="px-4 py-3 text-center font-medium text-gray-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($declarations as $decl)
                        <tr class="hover:bg-gray-50 {{ $decl->is_overdue ? 'bg-red-50' : '' }}">
                            <td class="px-4 py-3">
                                <a href="{{ route('tva.show', $decl) }}" class="font-medium text-primary-600 hover:underline">
                                    {{ $decl->periode_label }}
                                </a>
                                @if($decl->is_overdue)
                                    <span class="ml-1 inline-flex px-1.5 py-0.5 rounded text-[10px] font-medium bg-red-100 text-red-700">EN RETARD</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ \App\Models\TvaDeclaration::REGIMES[$decl->regime] ?? $decl->regime }}</td>
                            <td class="px-4 py-3">{!! $decl->statut_badge !!}</td>
                            <td class="px-4 py-3 text-right font-medium text-blue-600">{{ number_format($decl->total_tva_collectee, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right text-amber-600">{{ number_format($decl->total_tva_deductible, 2, ',', ' ') }}</td>
                            <td class="px-4 py-3 text-right font-semibold {{ $decl->tva_due > 0 ? 'text-red-600' : ($decl->credit_tva > 0 ? 'text-green-600' : 'text-gray-500') }}">
                                @if($decl->credit_tva > 0)
                                    <span class="text-xs">Crédit</span> {{ number_format($decl->credit_tva, 2, ',', ' ') }}
                                @else
                                    {{ number_format($decl->tva_due, 2, ',', ' ') }}
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right text-xs {{ $decl->is_overdue ? 'text-red-600 font-semibold' : 'text-gray-500' }}">
                                {{ $decl->date_limite->format('d/m/Y') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('tva.show', $decl) }}" class="text-gray-400 hover:text-primary-600">
                                    <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center">
                                <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                <p class="text-gray-500">Aucune déclaration pour {{ $annee }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($declarations->hasPages())
            <div class="px-4 py-3 border-t border-gray-200">
                {{ $declarations->appends(request()->query())->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
