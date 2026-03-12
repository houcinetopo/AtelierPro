@extends('layouts.app')

@section('title', 'Factures')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Factures</span>
@endsection

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Factures</h1>
            <p class="text-sm text-gray-500 mt-0.5">Suivi facturation et encaissements</p>
        </div>
        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <a href="{{ route('invoices.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="plus-circle" class="w-4 h-4"></i> Nouvelle facture
        </a>
        @endif
    </div>

    {{-- Stats financières --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="receipt" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Total factures</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-blue-50 flex items-center justify-center">
                <i data-lucide="trending-up" class="w-5 h-5 text-blue-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-gray-800">{{ number_format($stats['ca_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">CA ce mois (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-green-600">{{ number_format($stats['encaisse_mois'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Encaissé (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="clock" class="w-5 h-5 text-amber-500"></i>
            </div>
            <div>
                <p class="text-lg font-bold text-amber-600">{{ number_format($stats['impayes'], 0, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Impayés (DH)</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-3">
            <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-red-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-red-600">{{ $stats['en_retard_count'] }}</p>
                <p class="text-xs text-gray-500">En retard</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('invoices.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
                <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                <input type="text" name="search" value="{{ request('search') }}"
                       class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="N° facture, OR, client, immatriculation...">
            </div>
            <select name="statut" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach (App\Models\Invoice::STATUTS as $key => $label)
                    <option value="{{ $key }}" {{ request('statut') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border border-gray-300 rounded-lg text-sm px-3 py-2">
            <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                <input type="checkbox" name="unpaid" value="1" {{ request('unpaid') ? 'checked' : '' }} class="rounded text-primary-600">
                Impayées
            </label>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'statut', 'date_from', 'date_to', 'unpaid']))
                <a href="{{ route('invoices.index') }}" class="px-3 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">N° Facture</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Client</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">OR</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Date</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Net TTC</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Payé</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Reste</th>
                        <th class="text-right px-4 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($invoices as $inv)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3">
                            <a href="{{ route('invoices.show', $inv) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                                {{ $inv->numero }}
                            </a>
                            @if($inv->is_overdue)
                                <span class="ml-1 inline-flex items-center px-1.5 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">Retard</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-xs font-medium text-gray-800">{{ $inv->client_name }}</td>
                        <td class="px-4 py-3">
                            @if($inv->repairOrder)
                                <a href="{{ route('repair-orders.show', $inv->repair_order_id) }}" class="font-mono text-xs text-gray-500 hover:text-primary-600">{{ $inv->repairOrder->numero }}</a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">{!! $inv->statut_badge !!}</td>
                        <td class="px-4 py-3 text-xs text-gray-600">
                            {{ $inv->date_facture->format('d/m/Y') }}
                            @if($inv->date_echeance)
                                <br><span class="text-gray-400">Éch. {{ $inv->date_echeance->format('d/m/Y') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-800 text-xs">{{ number_format($inv->net_a_payer, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs text-green-600">{{ number_format($inv->total_paye, 2, ',', ' ') }}</td>
                        <td class="px-4 py-3 text-right text-xs">
                            @if($inv->reste_a_payer > 0)
                                <span class="font-semibold text-red-600">{{ number_format($inv->reste_a_payer, 2, ',', ' ') }}</span>
                            @else
                                <span class="text-green-600 font-medium">Soldé</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('invoices.show', $inv) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600"><i data-lucide="eye" class="w-4 h-4"></i></a>
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($inv->statut, ['payee', 'annulee']))
                                <a href="{{ route('invoices.edit', $inv) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600"><i data-lucide="edit-3" class="w-4 h-4"></i></a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="receipt" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucune facture trouvée</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($invoices->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $invoices->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
