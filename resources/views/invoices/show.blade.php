@extends('layouts.app')

@section('title', $invoice->numero)
@section('breadcrumb')
    <a href="{{ route('invoices.index') }}" class="hover:text-primary-600">Factures</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $invoice->numero }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $invoice->numero }}</h1>
                {!! $invoice->statut_badge !!}
                @if($invoice->is_overdue)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> En retard
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">
                {{ $invoice->date_facture->format('d/m/Y') }}
                @if($invoice->createdBy) — par {{ $invoice->createdBy->name }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                @if($invoice->statut === 'brouillon')
                <form method="POST" action="{{ route('invoices.emit', $invoice) }}">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Émettre cette facture ?')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i data-lucide="send" class="w-4 h-4"></i> Émettre
                    </button>
                </form>
                @endif
                @if(!in_array($invoice->statut, ['payee', 'annulee']))
                <a href="{{ route('invoices.edit', $invoice) }}"
                   class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
                </a>
                @endif
                @if(!in_array($invoice->statut, ['annulee']) && $invoice->total_paye == 0)
                <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Annuler cette facture ?')"
                            class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-red-600 bg-white border border-red-200 rounded-lg hover:bg-red-50">
                        <i data-lucide="x-circle" class="w-4 h-4"></i> Annuler
                    </button>
                </form>
                @endif
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════ COLONNE PRINCIPALE (2/3) ═══════ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Références --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Client</h2>
                    <a href="{{ route('clients.show', $invoice->client_id) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600 text-sm">{{ $invoice->client_name }}</p>
                    </a>
                    @if($invoice->client?->telephone)
                        <p class="text-xs text-gray-500 mt-1">{{ $invoice->client->telephone }}</p>
                    @endif
                    @if($invoice->client?->ice)
                        <p class="text-xs text-gray-400 mt-0.5">ICE: {{ $invoice->client->ice }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Véhicule</h2>
                    @if($invoice->vehicle)
                        <a href="{{ route('vehicles.show', $invoice->vehicle_id) }}" class="group">
                            <p class="font-semibold text-gray-800 group-hover:text-primary-600 text-sm">{{ $invoice->vehicle->marque }} {{ $invoice->vehicle->modele }}</p>
                        </a>
                        <p class="text-xs text-gray-500 font-mono mt-1">{{ $invoice->vehicle->immatriculation }}</p>
                    @else
                        <p class="text-sm text-gray-400">—</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Références</h2>
                    @if($invoice->repairOrder)
                        <a href="{{ route('repair-orders.show', $invoice->repair_order_id) }}" class="font-mono text-xs text-primary-600 hover:text-primary-800">OR {{ $invoice->repairOrder->numero }}</a>
                    @endif
                    @if($invoice->deliveryNote)
                        <br><a href="{{ route('delivery-notes.show', $invoice->delivery_note_id) }}" class="font-mono text-xs text-primary-600 hover:text-primary-800">BL {{ $invoice->deliveryNote->numero }}</a>
                    @endif
                    @if(!$invoice->repairOrder && !$invoice->deliveryNote)
                        <p class="text-xs text-gray-400">Facture libre</p>
                    @endif
                </div>
            </div>

            {{-- Objet --}}
            @if($invoice->objet)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Objet</h2>
                <p class="text-sm text-gray-700">{{ $invoice->objet }}</p>
            </div>
            @endif

            {{-- Lignes --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Détail de la facture</h2>
                    <span class="text-xs text-gray-400">{{ $invoice->items->count() }} ligne(s)</span>
                </div>

                @if($invoice->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Qté</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">P.U.</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Remise</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Montant HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($invoice->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    @php $tc = ['main_oeuvre'=>'blue','piece'=>'orange','fourniture'=>'green','sous_traitance'=>'purple'][$item->type] ?? 'gray'; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700">{{ $item->type_label }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-gray-800 text-xs">{{ $item->designation }}</p>
                                    @if($item->reference) <p class="text-xs text-gray-400">Réf: {{ $item->reference }}</p> @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ $item->quantite }} {{ $item->unite }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ number_format($item->prix_unitaire, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ $item->remise > 0 ? $item->remise . '%' : '—' }}</td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-800 text-xs">{{ number_format($item->montant_ht, 2, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr><td colspan="5" class="px-4 py-2 text-right text-xs text-gray-500">Total HT</td><td class="px-4 py-2 text-right font-semibold text-gray-700">{{ number_format($invoice->total_ht, 2, ',', ' ') }}</td></tr>
                            <tr><td colspan="5" class="px-4 py-1.5 text-right text-xs text-gray-500">TVA ({{ $invoice->taux_tva }}%)</td><td class="px-4 py-1.5 text-right text-gray-600">{{ number_format($invoice->montant_tva, 2, ',', ' ') }}</td></tr>
                            @if($invoice->remise_globale > 0)
                            <tr><td colspan="5" class="px-4 py-1.5 text-right text-xs text-red-500">Remise globale</td><td class="px-4 py-1.5 text-right text-red-600">-{{ number_format($invoice->remise_globale, 2, ',', ' ') }}</td></tr>
                            @endif
                            <tr class="border-t border-gray-300">
                                <td colspan="5" class="px-4 py-2.5 text-right font-semibold text-gray-700">Net à payer</td>
                                <td class="px-4 py-2.5 text-right"><span class="text-lg font-bold text-primary-600">{{ number_format($invoice->net_a_payer, 2, ',', ' ') }}</span> <span class="text-xs text-gray-400">DH</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400"><p class="text-sm">Aucune ligne</p></div>
                @endif
            </div>

            {{-- ═══════ HISTORIQUE DES PAIEMENTS ═══════ --}}
            <div class="bg-white rounded-xl border border-gray-200" x-data="{ showPaymentForm: false }">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Paiements</h2>
                    @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($invoice->statut, ['brouillon', 'annulee', 'payee']))
                    <button @click="showPaymentForm = !showPaymentForm"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-green-600 bg-green-50 hover:bg-green-100 rounded-lg transition-colors">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Enregistrer un paiement
                    </button>
                    @endif
                </div>

                {{-- Formulaire paiement --}}
                <div x-show="showPaymentForm" x-cloak x-transition class="px-5 py-4 bg-green-50/50 border-b border-green-100">
                    <form method="POST" action="{{ route('invoices.add-payment', $invoice) }}" class="space-y-3">
                        @csrf
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Date <span class="text-red-500">*</span></label>
                                <input type="date" name="date_paiement" value="{{ now()->format('Y-m-d') }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Montant (DH) <span class="text-red-500">*</span></label>
                                <input type="number" name="montant" step="0.01" min="0.01" max="{{ $invoice->reste_a_payer }}" value="{{ $invoice->reste_a_payer }}" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Mode <span class="text-red-500">*</span></label>
                                <select name="mode" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500">
                                    @foreach (App\Models\Invoice::MODES_PAIEMENT as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Référence</label>
                                <input type="text" name="reference" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500" placeholder="N° chèque...">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Banque</label>
                                <input type="text" name="banque" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500" placeholder="Nom de la banque">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                                <input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-green-500" placeholder="Observations...">
                            </div>
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" @click="showPaymentForm = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                            <button type="submit" class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium">Enregistrer</button>
                        </div>
                    </form>
                </div>

                {{-- Liste paiements --}}
                @if($invoice->payments->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($invoice->payments as $pmt)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-green-100 flex items-center justify-center">
                                <i data-lucide="banknote" class="w-4 h-4 text-green-600"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-green-700">+{{ number_format($pmt->montant, 2, ',', ' ') }} DH</p>
                                <p class="text-xs text-gray-500">
                                    {{ $pmt->date_paiement->format('d/m/Y') }} — {{ $pmt->mode_label }}
                                    @if($pmt->reference) ({{ $pmt->reference }}) @endif
                                    @if($pmt->banque) — {{ $pmt->banque }} @endif
                                </p>
                            </div>
                        </div>
                        @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                        <form method="POST" action="{{ route('invoices.delete-payment', [$invoice, $pmt]) }}" class="shrink-0">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Supprimer ce paiement ?')"
                                    class="p-1.5 rounded hover:bg-red-50 text-gray-400 hover:text-red-500">
                                <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                            </button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-6 text-center text-gray-400 text-sm">Aucun paiement enregistré</div>
                @endif
            </div>
        </div>

        {{-- ═══════ COLONNE DROITE (1/3) ═══════ --}}
        <div class="space-y-4">

            {{-- Progression paiement --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Paiement</h2>
                <div class="mb-3">
                    <div class="flex justify-between text-xs mb-1">
                        <span class="text-gray-500">{{ $invoice->progress_percent }}%</span>
                        <span class="text-gray-500">{{ number_format($invoice->total_paye, 2, ',', ' ') }} / {{ number_format($invoice->net_a_payer, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="h-3 rounded-full transition-all {{ $invoice->progress_percent >= 100 ? 'bg-green-500' : ($invoice->progress_percent > 0 ? 'bg-amber-500' : 'bg-gray-300') }}"
                             style="width: {{ $invoice->progress_percent }}%"></div>
                    </div>
                </div>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Net à payer</span>
                        <span class="text-gray-700 font-semibold text-xs">{{ number_format($invoice->net_a_payer, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Total payé</span>
                        <span class="text-green-600 text-xs">{{ number_format($invoice->total_paye, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="font-semibold text-gray-700 text-xs">Reste à payer</span>
                        <span class="font-bold {{ $invoice->reste_a_payer > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $invoice->reste_a_payer > 0 ? number_format($invoice->reste_a_payer, 2, ',', ' ') . ' DH' : 'Soldé' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Résumé financier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Résumé</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Total HT</span><span class="text-gray-700 text-xs">{{ number_format($invoice->total_ht, 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">TVA ({{ $invoice->taux_tva }}%)</span><span class="text-gray-700 text-xs">{{ number_format($invoice->montant_tva, 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Total TTC</span><span class="text-gray-700 text-xs">{{ number_format($invoice->total_ttc, 2, ',', ' ') }} DH</span></div>
                    @if($invoice->remise_globale > 0)
                    <div class="flex justify-between"><span class="text-red-500 text-xs">Remise</span><span class="text-red-600 text-xs">-{{ number_format($invoice->remise_globale, 2, ',', ' ') }} DH</span></div>
                    @endif
                </div>
            </div>

            {{-- Infos --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-2.5">
                <h2 class="text-base font-semibold text-gray-800">Informations</h2>
                <div class="flex justify-between text-sm"><span class="text-gray-500 text-xs">Date facture</span><span class="text-gray-700 font-medium text-xs">{{ $invoice->date_facture->format('d/m/Y') }}</span></div>
                @if($invoice->date_echeance)
                <div class="flex justify-between text-sm">
                    <span class="text-gray-500 text-xs">Échéance</span>
                    <span class="text-xs {{ $invoice->is_overdue ? 'text-red-600 font-bold' : 'text-gray-700 font-medium' }}">{{ $invoice->date_echeance->format('d/m/Y') }}</span>
                </div>
                @endif
            </div>

            {{-- Conditions --}}
            @if($invoice->conditions_paiement)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Conditions</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->conditions_paiement }}</p>
            </div>
            @endif

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $invoice->notes }}</p>
            </div>
            @endif

            {{-- Danger zone --}}
            @if(auth()->user()->isAdmin() && $invoice->statut !== 'payee')
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('invoices.destroy', $invoice) }}"
                      x-data @submit.prevent="if(confirm('Supprimer la facture {{ $invoice->numero }} ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
