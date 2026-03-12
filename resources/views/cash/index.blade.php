@extends('layouts.app')

@section('title', 'Caisse')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Caisse</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showOpenForm: false }">

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Caisse</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gestion de trésorerie journalière</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            @if($currentSession)
                <a href="{{ route('cash.session', $currentSession) }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="book-open" class="w-4 h-4"></i> Journal du jour
                </a>
            @else
                <button @click="showOpenForm = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i data-lucide="unlock" class="w-4 h-4"></i> Ouvrir la caisse
                </button>
            @endif
        @endif
    </div>

    {{-- Modal ouverture --}}
    <div x-show="showOpenForm" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showOpenForm = false">
        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md" @click.stop>
            <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                <i data-lucide="unlock" class="w-5 h-5 text-primary-500"></i> Ouvrir la caisse
            </h3>
            <form method="POST" action="{{ route('cash.open') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Solde d'ouverture (DH)</label>
                    @php $lastSolde = \App\Models\CashSession::orderByDesc('date_session')->value('solde_theorique') ?? 0; @endphp
                    <input type="number" name="solde_ouverture" step="0.01" min="0" value="{{ $lastSolde }}"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    <p class="text-xs text-gray-400 mt-1">Dernier solde : {{ number_format($lastSolde, 2, ',', ' ') }} DH</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                    <textarea name="notes_ouverture" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Observations à l'ouverture..."></textarea>
                </div>
                <div class="flex justify-end gap-2">
                    <button type="button" @click="showOpenForm = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                    <button type="submit" class="px-4 py-2 text-sm text-white bg-primary-600 hover:bg-primary-700 rounded-lg font-medium">Ouvrir</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="wallet" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['solde_actuel'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Solde actuel (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="arrow-down-left" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-green-600">{{ number_format($stats['entrees_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Entrées mois (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="arrow-up-right" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-red-600">{{ number_format($stats['sorties_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Sorties mois (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="calendar" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['sessions_mois'] }}</p>
                <p class="text-xs text-gray-500">Sessions ce mois</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg {{ $stats['ecart_total_mois'] == 0 ? 'bg-green-50' : 'bg-amber-50' }} flex items-center justify-center">
                <i data-lucide="scale" class="w-5 h-5 {{ $stats['ecart_total_mois'] == 0 ? 'text-green-500' : 'text-amber-500' }}"></i>
            </div>
            <div>
                <p class="text-lg font-bold {{ $stats['ecart_total_mois'] == 0 ? 'text-green-600' : 'text-amber-600' }}">{{ number_format($stats['ecart_total_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Écart mois (DH)</p>
            </div>
        </div>
    </div>

    {{-- Session en cours --}}
    @if($currentSession)
    <div class="bg-green-50 border border-green-200 rounded-xl p-5">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-green-200 flex items-center justify-center">
                    <i data-lucide="book-open" class="w-5 h-5 text-green-700"></i>
                </div>
                <div>
                    <p class="font-semibold text-green-800">Caisse ouverte — {{ now()->translatedFormat('l d F Y') }}</p>
                    <p class="text-sm text-green-600">
                        Ouverture : {{ number_format($currentSession->solde_ouverture, 2, ',', ' ') }} DH
                        — Solde actuel : <span class="font-bold">{{ number_format($currentSession->solde_theorique, 2, ',', ' ') }} DH</span>
                        — {{ $currentSession->movements()->count() }} mouvement(s)
                    </p>
                </div>
            </div>
            <a href="{{ route('cash.session', $currentSession) }}" class="px-4 py-2 text-sm text-green-700 bg-green-200 hover:bg-green-300 rounded-lg font-medium">
                Voir le journal
            </a>
        </div>
    </div>
    @endif

    {{-- Filtre mois --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('cash.index') }}" class="flex items-center gap-3">
            <input type="month" name="month" value="{{ request('month', now()->format('Y-m')) }}"
                   class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request('month'))
                <a href="{{ route('cash.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table sessions --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Ouverture</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Entrées</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Sorties</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Solde</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Écart</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Mvts</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($sessions as $s)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <a href="{{ route('cash.session', $s) }}" class="font-semibold text-primary-600 hover:text-primary-800 text-sm">
                                {{ $s->date_session->translatedFormat('D d/m/Y') }}
                            </a>
                        </td>
                        <td class="px-4 py-3">{!! $s->statut_badge !!}</td>
                        <td class="px-4 py-3 text-right text-xs text-gray-600">{{ number_format($s->solde_ouverture, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs text-green-600 font-medium">+{{ number_format($s->total_entrees, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs text-red-600 font-medium">-{{ number_format($s->total_sorties, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs font-semibold text-gray-800">{{ number_format($s->statut === 'cloturee' && $s->solde_reel !== null ? $s->solde_reel : $s->solde_theorique, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right">{!! $s->statut === 'cloturee' ? $s->ecart_badge : '<span class="text-xs text-gray-400">—</span>' !!}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-600">{{ $s->movements_count }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('cash.session', $s) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="banknote" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucune session de caisse</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sessions->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $sessions->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
