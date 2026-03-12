@extends('layouts.app')

@section('title', $quote->numero)
@section('breadcrumb')
    <a href="{{ route('quotes.index') }}" class="hover:text-primary-600">Devis</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $quote->numero }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $quote->numero }}</h1>
                {!! $quote->statut_badge !!}
                @if($quote->is_expired)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                        <i data-lucide="clock" class="w-3 h-3"></i> Expiré
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Créé le {{ $quote->created_at->format('d/m/Y à H:i') }}
                @if($quote->createdBy) par {{ $quote->createdBy->name }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Convertir en OR --}}
            @if($quote->is_convertible && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <form method="POST" action="{{ route('quotes.convert', $quote) }}">
                @csrf
                <button type="submit" onclick="return confirm('Convertir ce devis en Ordre de Réparation ?')"
                        class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                    <i data-lucide="arrow-right-circle" class="w-4 h-4"></i> Convertir en OR
                </button>
            </form>
            @endif

            {{-- OR lié --}}
            @if($quote->repairOrder)
            <a href="{{ route('repair-orders.show', $quote->repair_order_id) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-indigo-600 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100">
                <i data-lucide="clipboard-list" class="w-4 h-4"></i> {{ $quote->repairOrder->numero }}
            </a>
            @endif

            {{-- Transitions de statut --}}
            @if($transitions->isNotEmpty() && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <div x-data="{ open: false, showRefus: false }" class="relative">
                <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i data-lucide="git-branch" class="w-4 h-4"></i> Changer statut
                    <i data-lucide="chevron-down" class="w-3 h-3"></i>
                </button>
                <div x-show="open" @click.away="open = false" x-cloak x-transition
                     class="absolute right-0 mt-1 w-52 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                    @foreach($transitions as $statut => $label)
                        @if($statut === 'refuse')
                        <button type="button" @click="open = false; showRefus = true"
                                class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2">
                            @php $c = \App\Models\Quote::STATUT_COLORS[$statut] ?? 'gray'; @endphp
                            <span class="w-2 h-2 rounded-full bg-{{ $c }}-500"></span> {{ $label }}
                        </button>
                        @else
                        <form method="POST" action="{{ route('quotes.update-statut', $quote) }}" class="block">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="{{ $statut }}">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                                    onclick="return confirm('Changer le statut vers {{ $label }} ?')">
                                @php $c = \App\Models\Quote::STATUT_COLORS[$statut] ?? 'gray'; @endphp
                                <span class="w-2 h-2 rounded-full bg-{{ $c }}-500"></span> {{ $label }}
                            </button>
                        </form>
                        @endif
                    @endforeach
                </div>

                {{-- Modal refus --}}
                <div x-show="showRefus" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showRefus = false">
                    <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md">
                        <h3 class="text-lg font-semibold text-gray-800 mb-3">Motif du refus</h3>
                        <form method="POST" action="{{ route('quotes.update-statut', $quote) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="statut" value="refuse">
                            <textarea name="motif_refus" rows="3" class="w-full px-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Pourquoi le client a refusé..."></textarea>
                            <div class="flex justify-end gap-2 mt-4">
                                <button type="button" @click="showRefus = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                                <button type="submit" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg">Confirmer le refus</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($quote->statut, ['converti', 'annule']))
            <a href="{{ route('quotes.edit', $quote) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
            @endif

            {{-- Dupliquer --}}
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <form method="POST" action="{{ route('quotes.duplicate', $quote) }}">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i data-lucide="copy" class="w-4 h-4"></i> Dupliquer
                </button>
            </form>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════ COLONNE PRINCIPALE (2/3) ═══════ --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Client + Véhicule --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Client</h2>
                    <a href="{{ route('clients.show', $quote->client_id) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600">{{ $quote->client_name }}</p>
                    </a>
                    @if($quote->client?->telephone)
                        <p class="text-sm text-gray-500 mt-1">{{ $quote->client->telephone }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Véhicule</h2>
                    @if($quote->vehicle)
                        <a href="{{ route('vehicles.show', $quote->vehicle_id) }}" class="group">
                            <p class="font-semibold text-gray-800 group-hover:text-primary-600">{{ $quote->vehicle->marque }} {{ $quote->vehicle->modele }}</p>
                        </a>
                        <p class="text-sm text-gray-500 font-mono mt-1">{{ $quote->vehicle->immatriculation }}</p>
                    @else
                        <p class="text-sm text-gray-400">Non spécifié</p>
                    @endif
                </div>
            </div>

            {{-- Description --}}
            @if($quote->description_travaux)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Description des travaux</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $quote->description_travaux }}</p>
            </div>
            @endif

            {{-- ═══════ LIGNES ═══════ --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Détail du devis</h2>
                    <span class="text-xs text-gray-400">{{ $quote->items->count() }} ligne(s)</span>
                </div>

                @if($quote->items->count() > 0)
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
                            @foreach($quote->items as $item)
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
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right text-xs text-gray-500">Total HT</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-700 text-sm">{{ number_format($quote->total_ht, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-1.5 text-right text-xs text-gray-500">TVA ({{ $quote->taux_tva }}%)</td>
                                <td class="px-4 py-1.5 text-right text-gray-600 text-sm">{{ number_format($quote->montant_tva, 2, ',', ' ') }}</td>
                            </tr>
                            @if($quote->remise_globale > 0)
                            <tr>
                                <td colspan="5" class="px-4 py-1.5 text-right text-xs text-red-500">Remise globale</td>
                                <td class="px-4 py-1.5 text-right text-red-600 text-sm">-{{ number_format($quote->remise_globale, 2, ',', ' ') }}</td>
                            </tr>
                            @endif
                            <tr class="border-t border-gray-300">
                                <td colspan="5" class="px-4 py-2.5 text-right font-semibold text-gray-700">Net à payer</td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="text-lg font-bold text-primary-600">{{ number_format($quote->net_a_payer, 2, ',', ' ') }}</span>
                                    <span class="text-xs text-gray-400 ml-1">DH</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <p class="text-sm">Aucune ligne ajoutée</p>
                </div>
                @endif
            </div>

            {{-- Motif de refus --}}
            @if($quote->motif_refus)
            <div class="bg-red-50 rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-600 uppercase mb-2 flex items-center gap-1.5">
                    <i data-lucide="x-circle" class="w-3.5 h-3.5"></i> Motif du refus
                </h2>
                <p class="text-sm text-red-800 whitespace-pre-line">{{ $quote->motif_refus }}</p>
            </div>
            @endif
        </div>

        {{-- ═══════ COLONNE DROITE (1/3) ═══════ --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Informations</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Date du devis</span>
                        <span class="text-gray-700 font-medium text-xs">{{ $quote->date_devis->format('d/m/Y') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Validité</span>
                        <span class="text-xs {{ $quote->is_expired ? 'text-red-600 font-bold' : 'text-gray-700 font-medium' }}">
                            {{ $quote->date_validite->format('d/m/Y') }}
                        </span>
                    </div>
                    @if($quote->date_acceptation)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Accepté le</span>
                        <span class="text-green-600 font-medium text-xs">{{ $quote->date_acceptation->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($quote->duree_estimee_jours)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Durée estimée</span>
                        <span class="text-gray-700 text-xs">{{ $quote->duree_estimee_jours }} jour(s)</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Résumé financier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Résumé financier</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Total HT</span><span class="text-gray-700 text-xs">{{ number_format($quote->total_ht, 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">TVA ({{ $quote->taux_tva }}%)</span><span class="text-gray-700 text-xs">{{ number_format($quote->montant_tva, 2, ',', ' ') }} DH</span></div>
                    @if($quote->remise_globale > 0)
                    <div class="flex justify-between"><span class="text-red-500 text-xs">Remise</span><span class="text-red-600 text-xs">-{{ number_format($quote->remise_globale, 2, ',', ' ') }} DH</span></div>
                    @endif
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="font-semibold text-gray-700 text-xs">Net à payer</span>
                        <span class="font-bold text-primary-600">{{ number_format($quote->net_a_payer, 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>

            {{-- Conditions --}}
            @if($quote->conditions)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Conditions</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $quote->conditions }}</p>
            </div>
            @endif

            {{-- Notes --}}
            @if($quote->notes)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $quote->notes }}</p>
            </div>
            @endif

            {{-- Danger zone --}}
            @if(auth()->user()->isAdmin() && $quote->statut !== 'converti')
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('quotes.destroy', $quote) }}"
                      x-data @submit.prevent="if(confirm('Supprimer définitivement le devis {{ $quote->numero }} ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer ce devis
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
