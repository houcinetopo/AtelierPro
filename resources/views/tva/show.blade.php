@extends('layouts.app')
@section('title', "TVA — {$tva->periode_label}")

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">TVA — {{ $tva->periode_label }}</h1>
                {!! $tva->statut_badge !!}
                @if($tva->is_overdue)
                    <span class="inline-flex px-2 py-0.5 rounded text-xs font-medium bg-red-100 text-red-700">EN RETARD</span>
                @endif
            </div>
            <p class="mt-1 text-sm text-gray-500">
                {{ \App\Models\TvaDeclaration::REGIMES[$tva->regime] }} · {{ $tva->date_debut->format('d/m/Y') }} au {{ $tva->date_fin->format('d/m/Y') }}
                @if($tva->createdBy) · Par {{ $tva->createdBy->name }}@endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Calculer auto --}}
            @if($tva->is_editable)
                <form method="POST" action="{{ route('tva.calculate', $tva) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700" onclick="return confirm('Recalculer les montants depuis les factures et achats ?')">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Calculer automatiquement
                    </button>
                </form>
            @endif

            {{-- Transitions --}}
            @if(count($transitions) > 0)
                <div x-data="{ open: false, showPayment: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        Changer statut
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                        @foreach($transitions as $key => $label)
                            @if($key === 'payee')
                                <button @click="showPayment = true; open = false" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    → {{ $label }}
                                </button>
                            @else
                                <form method="POST" action="{{ route('tva.update-statut', $tva) }}">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="statut" value="{{ $key }}">
                                    @if($key === 'declaree')
                                        <input type="hidden" name="date_declaration" value="{{ now()->toDateString() }}">
                                    @endif
                                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                        → {{ $label }}
                                    </button>
                                </form>
                            @endif
                        @endforeach
                    </div>

                    {{-- Modal paiement --}}
                    <div x-show="showPayment" x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" @click.self="showPayment = false">
                        <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md mx-4">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">Enregistrer le paiement TVA</h3>
                            <form method="POST" action="{{ route('tva.update-statut', $tva) }}" class="space-y-4">
                                @csrf @method('PATCH')
                                <input type="hidden" name="statut" value="payee">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Date de paiement</label>
                                    <input type="date" name="date_paiement" value="{{ now()->toDateString() }}" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Montant payé (DH)</label>
                                    <input type="number" name="montant_paye" value="{{ $tva->tva_due + $tva->penalites }}" step="0.01" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1">Référence quittance DGI</label>
                                    <input type="text" name="reference_paiement" class="w-full rounded-lg border-gray-300 text-sm" placeholder="N° quittance...">
                                </div>
                                <div class="flex justify-end gap-3 mt-6">
                                    <button type="button" @click="showPayment = false" class="px-4 py-2 text-sm text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Annuler</button>
                                    <button type="submit" class="px-4 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">Confirmer le paiement</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endif

            <a href="{{ route('tva.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Tableau de synthèse TVA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                    <h3 class="text-base font-semibold text-gray-900">Synthèse TVA</h3>
                    @if($tva->is_editable)
                        <button onclick="document.getElementById('edit-section').classList.toggle('hidden')" class="text-sm text-primary-600 hover:underline">Modifier manuellement</button>
                    @endif
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Taux</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Base HT (Ventes)</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">TVA Collectée</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Base HT (Achats)</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">TVA Déductible</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">20%</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->ca_ht_20, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-blue-600">{{ number_format($tva->tva_collectee_20, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->achats_ht_20, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-amber-600">{{ number_format($tva->tva_deductible_20, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">14%</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->ca_ht_14, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-blue-600">{{ number_format($tva->tva_collectee_14, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->achats_ht_14, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-amber-600">{{ number_format($tva->tva_deductible_14, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">10%</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->ca_ht_10, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-blue-600">{{ number_format($tva->tva_collectee_10, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->achats_ht_10, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-amber-600">{{ number_format($tva->tva_deductible_10, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td class="px-4 py-3 font-medium text-gray-900">7%</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->ca_ht_7, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-blue-600">{{ number_format($tva->tva_collectee_7, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->achats_ht_7, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-amber-600">{{ number_format($tva->tva_deductible_7, 2, ',', ' ') }}</td>
                            </tr>
                            @if($tva->ca_ht_exonere > 0)
                                <tr>
                                    <td class="px-4 py-3 font-medium text-gray-900">Exonéré</td>
                                    <td class="px-4 py-3 text-right text-gray-700">{{ number_format($tva->ca_ht_exonere, 2, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-400">—</td>
                                    <td class="px-4 py-3 text-right text-gray-400">—</td>
                                    <td class="px-4 py-3 text-right text-gray-400">—</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-gray-50 font-semibold">
                            <tr class="border-t-2 border-gray-300">
                                <td class="px-4 py-3 text-gray-900">TOTAUX</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($tva->total_ca_ht, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-blue-600">{{ number_format($tva->total_tva_collectee, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-gray-900">{{ number_format($tva->total_achats_ht, 2, ',', ' ') }}</td>
                                <td class="px-4 py-3 text-right text-amber-600">{{ number_format($tva->total_tva_deductible, 2, ',', ' ') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Formulaire édition manuelle --}}
            @if($tva->is_editable)
                <div id="edit-section" class="hidden bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-100 bg-amber-50">
                        <h3 class="text-base font-semibold text-amber-800">Modification manuelle des montants</h3>
                        <p class="text-sm text-amber-600 mt-1">Ajustez les bases HT si nécessaire. La TVA sera recalculée automatiquement.</p>
                    </div>
                    <form method="POST" action="{{ route('tva.update', $tva) }}" class="p-6">
                        @csrf @method('PUT')
                        <div class="space-y-6">
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Chiffre d'affaires (Ventes)</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    @foreach([20, 14, 10, 7] as $taux)
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">CA HT {{ $taux }}%</label>
                                            <input type="number" name="ca_ht_{{ $taux }}" value="{{ old("ca_ht_{$taux}", $tva->{"ca_ht_{$taux}"}) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                        </div>
                                    @endforeach
                                    <div>
                                        <label class="block text-xs text-gray-500 mb-1">CA Exonéré</label>
                                        <input type="number" name="ca_ht_exonere" value="{{ old('ca_ht_exonere', $tva->ca_ht_exonere) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                    </div>
                                </div>
                            </div>
                            <div>
                                <h4 class="text-sm font-semibold text-gray-700 mb-3">Achats</h4>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                                    @foreach([20, 14, 10, 7] as $taux)
                                        <div>
                                            <label class="block text-xs text-gray-500 mb-1">Achats HT {{ $taux }}%</label>
                                            <input type="number" name="achats_ht_{{ $taux }}" value="{{ old("achats_ht_{$taux}", $tva->{"achats_ht_{$taux}"}) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Crédit TVA antérieur</label>
                                    <input type="number" name="credit_tva_anterieur" value="{{ old('credit_tva_anterieur', $tva->credit_tva_anterieur) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-500 mb-1">Pénalités de retard</label>
                                    <input type="number" name="penalites" value="{{ old('penalites', $tva->penalites) }}" step="0.01" min="0" class="w-full rounded-lg border-gray-300 text-sm">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1">Notes</label>
                                <textarea name="notes" rows="2" class="w-full rounded-lg border-gray-300 text-sm">{{ old('notes', $tva->notes) }}</textarea>
                            </div>
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-4 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                                Enregistrer les modifications
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Factures de la période --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Factures émises ({{ $invoices->count() }})</h3>
                </div>
                @if($invoices->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">N° Facture</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Client</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">HT</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">TVA</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">TTC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($invoices as $inv)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2">
                                            <a href="{{ route('invoices.show', $inv) }}" class="text-primary-600 font-mono text-xs hover:underline">{{ $inv->numero }}</a>
                                        </td>
                                        <td class="px-4 py-2 text-gray-700">{{ $inv->client?->nom_complet ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right text-gray-700">{{ number_format($inv->total_ht, 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right text-blue-600">{{ number_format($inv->montant_tva, 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($inv->total_ttc, 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr class="font-semibold">
                                    <td colspan="2" class="px-4 py-2 text-gray-700">Total</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($invoices->sum('total_ht'), 2, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right text-blue-600">{{ number_format($invoices->sum('montant_tva'), 2, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($invoices->sum('total_ttc'), 2, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="p-6 text-center text-gray-500 text-sm">Aucune facture émise pour cette période.</p>
                @endif
            </div>

            {{-- Bons de commande / achats --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Achats / Bons de commande ({{ $purchases->count() }})</h3>
                </div>
                @if($purchases->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">N° BC</th>
                                    <th class="px-4 py-2 text-left font-medium text-gray-500">Fournisseur</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">HT</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">TVA</th>
                                    <th class="px-4 py-2 text-right font-medium text-gray-500">TTC</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach($purchases as $po)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-2 font-mono text-xs text-gray-700">{{ $po->numero }}</td>
                                        <td class="px-4 py-2 text-gray-700">{{ $po->supplier?->raison_sociale ?? '—' }}</td>
                                        <td class="px-4 py-2 text-right text-gray-700">{{ number_format($po->total_ht, 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right text-amber-600">{{ number_format($po->montant_tva, 2, ',', ' ') }}</td>
                                        <td class="px-4 py-2 text-right font-medium text-gray-900">{{ number_format($po->total_ttc, 2, ',', ' ') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr class="font-semibold">
                                    <td colspan="2" class="px-4 py-2 text-gray-700">Total</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($purchases->sum('total_ht'), 2, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right text-amber-600">{{ number_format($purchases->sum('montant_tva'), 2, ',', ' ') }}</td>
                                    <td class="px-4 py-2 text-right">{{ number_format($purchases->sum('total_ttc'), 2, ',', ' ') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                @else
                    <p class="p-6 text-center text-gray-500 text-sm">Aucun achat pour cette période.</p>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Résultat TVA --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Résultat TVA</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">TVA Collectée</dt>
                        <dd class="font-medium text-blue-600">+{{ number_format($tva->total_tva_collectee, 2, ',', ' ') }} DH</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">TVA Déductible</dt>
                        <dd class="font-medium text-amber-600">-{{ number_format($tva->total_tva_deductible, 2, ',', ' ') }} DH</dd>
                    </div>
                    @if($tva->credit_tva_anterieur > 0)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Crédit antérieur</dt>
                            <dd class="font-medium text-green-600">-{{ number_format($tva->credit_tva_anterieur, 2, ',', ' ') }} DH</dd>
                        </div>
                    @endif
                    <div class="pt-3 border-t-2 border-gray-200">
                        @if($tva->tva_due > 0)
                            <div class="flex justify-between">
                                <dt class="font-bold text-gray-900">TVA à payer</dt>
                                <dd class="text-xl font-bold text-red-600">{{ number_format($tva->tva_due, 2, ',', ' ') }} DH</dd>
                            </div>
                        @elseif($tva->credit_tva > 0)
                            <div class="flex justify-between">
                                <dt class="font-bold text-gray-900">Crédit TVA</dt>
                                <dd class="text-xl font-bold text-green-600">{{ number_format($tva->credit_tva, 2, ',', ' ') }} DH</dd>
                            </div>
                        @else
                            <div class="flex justify-between">
                                <dt class="font-bold text-gray-900">TVA due</dt>
                                <dd class="text-xl font-bold text-gray-500">0,00 DH</dd>
                            </div>
                        @endif
                    </div>
                    @if($tva->penalites > 0)
                        <div class="flex justify-between pt-2">
                            <dt class="text-gray-500">Pénalités</dt>
                            <dd class="font-medium text-red-600">+{{ number_format($tva->penalites, 2, ',', ' ') }} DH</dd>
                        </div>
                        <div class="flex justify-between font-bold">
                            <dt class="text-gray-900">Total à payer</dt>
                            <dd class="text-red-700">{{ number_format($tva->tva_due + $tva->penalites, 2, ',', ' ') }} DH</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Informations --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informations</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Régime</dt>
                        <dd class="font-medium text-gray-900">{{ \App\Models\TvaDeclaration::REGIMES[$tva->regime] }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Période</dt>
                        <dd class="font-medium text-gray-900">{{ $tva->date_debut->format('d/m/Y') }} — {{ $tva->date_fin->format('d/m/Y') }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date limite</dt>
                        <dd class="font-medium {{ $tva->is_overdue ? 'text-red-600' : 'text-gray-900' }}">{{ $tva->date_limite->format('d/m/Y') }}</dd>
                    </div>
                    @if($tva->date_declaration)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Déclarée le</dt>
                            <dd class="font-medium text-gray-900">{{ $tva->date_declaration->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($tva->date_paiement)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Payée le</dt>
                            <dd class="font-medium text-green-600">{{ $tva->date_paiement->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($tva->reference_paiement)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Réf. quittance</dt>
                            <dd class="font-mono text-gray-900">{{ $tva->reference_paiement }}</dd>
                        </div>
                    @endif
                    @if($tva->validatedBy)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Validée par</dt>
                            <dd class="text-gray-900">{{ $tva->validatedBy->name }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Notes --}}
            @if($tva->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $tva->notes }}</p>
                </div>
            @endif

            {{-- Danger zone --}}
            @if($tva->is_editable && auth()->user()?->isAdmin())
                <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
                    <h3 class="text-sm font-semibold text-red-600 mb-3">Zone de danger</h3>
                    <form method="POST" action="{{ route('tva.destroy', $tva) }}" onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cette déclaration ?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-red-700 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">
                            Supprimer cette déclaration
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
