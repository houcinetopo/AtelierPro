@extends('layouts.app')

@section('title', $deliveryNote->numero)
@section('breadcrumb')
    <a href="{{ route('delivery-notes.index') }}" class="hover:text-primary-600">Bons de Livraison</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $deliveryNote->numero }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $deliveryNote->numero }}</h1>
                {!! $deliveryNote->statut_badge !!}
                @if(!$deliveryNote->is_paid)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <i data-lucide="alert-circle" class="w-3 h-3"></i> Impayé
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">
                {{ $deliveryNote->date_livraison->format('d/m/Y') }}
                {{ $deliveryNote->heure_livraison ? 'à ' . $deliveryNote->heure_livraison : '' }}
                @if($deliveryNote->createdBy) — par {{ $deliveryNote->createdBy->name }} @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                @if($deliveryNote->statut === 'brouillon')
                <form method="POST" action="{{ route('delivery-notes.validate', $deliveryNote) }}">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Valider ce bon de livraison ?')"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition-colors">
                        <i data-lucide="check" class="w-4 h-4"></i> Valider
                    </button>
                </form>
                @endif
                @if($deliveryNote->statut !== 'annule')
                <a href="{{ route('delivery-notes.edit', $deliveryNote) }}"
                   class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
                </a>
                <form method="POST" action="{{ route('delivery-notes.cancel', $deliveryNote) }}">
                    @csrf @method('PATCH')
                    <button type="submit" onclick="return confirm('Annuler ce bon de livraison ?')"
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

            {{-- Référence OR + Client + Véhicule --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Ordre de Réparation</h2>
                    <a href="{{ route('repair-orders.show', $deliveryNote->repair_order_id) }}" class="font-mono text-sm font-semibold text-primary-600 hover:text-primary-800">
                        {{ $deliveryNote->repairOrder?->numero }}
                    </a>
                    <p class="text-xs text-gray-400 mt-1">{!! $deliveryNote->repairOrder?->status_badge !!}</p>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Client</h2>
                    <a href="{{ route('clients.show', $deliveryNote->client_id) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600 text-sm">{{ $deliveryNote->client_name }}</p>
                    </a>
                    @if($deliveryNote->client?->telephone)
                        <p class="text-xs text-gray-500 mt-1">{{ $deliveryNote->client->telephone }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Véhicule</h2>
                    <a href="{{ route('vehicles.show', $deliveryNote->vehicle_id) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600 text-sm">
                            {{ $deliveryNote->vehicle?->marque }} {{ $deliveryNote->vehicle?->modele }}
                        </p>
                    </a>
                    <p class="text-xs text-gray-500 font-mono mt-1">{{ $deliveryNote->vehicle?->immatriculation }}</p>
                </div>
            </div>

            {{-- Travaux effectués --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Travaux effectués</h2>
                @if($deliveryNote->travaux_effectues)
                    <div class="text-sm text-gray-700 whitespace-pre-line">{{ $deliveryNote->travaux_effectues }}</div>
                @else
                    <p class="text-sm text-gray-400">Non renseigné</p>
                @endif
            </div>

            {{-- Détails OR (lignes) --}}
            @if($deliveryNote->repairOrder && $deliveryNote->repairOrder->items->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h2 class="text-base font-semibold text-gray-800">Détail des travaux (OR)</h2>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Qté</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Montant HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($deliveryNote->repairOrder->items as $item)
                            <tr>
                                <td class="px-4 py-2 text-xs">
                                    @php $tc = ['main_oeuvre'=>'blue','piece'=>'orange','fourniture'=>'green','sous_traitance'=>'purple'][$item->type] ?? 'gray'; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700">{{ $item->type_label }}</span>
                                </td>
                                <td class="px-4 py-2 text-xs text-gray-800">{{ $item->designation }}</td>
                                <td class="px-4 py-2 text-right text-xs text-gray-600">{{ $item->quantite }} {{ $item->unite }}</td>
                                <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">{{ number_format($item->montant_ht, 2, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            @endif

            {{-- Observations / Réserves / Recommandations --}}
            @if($deliveryNote->observations_sortie || $deliveryNote->reserves_client || $deliveryNote->recommandations)
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                @if($deliveryNote->observations_sortie)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-1">Observations à la sortie</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $deliveryNote->observations_sortie }}</p>
                </div>
                @endif
                @if($deliveryNote->reserves_client)
                <div class="bg-amber-50 rounded-lg p-3">
                    <h3 class="text-xs font-semibold text-amber-600 uppercase mb-1">Réserves du client</h3>
                    <p class="text-sm text-amber-800 whitespace-pre-line">{{ $deliveryNote->reserves_client }}</p>
                </div>
                @endif
                @if($deliveryNote->recommandations)
                <div>
                    <h3 class="text-xs font-semibold text-gray-500 uppercase mb-1">Recommandations</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $deliveryNote->recommandations }}</p>
                </div>
                @endif
            </div>
            @endif
        </div>

        {{-- ═══════ COLONNE DROITE (1/3) ═══════ --}}
        <div class="space-y-4">
            {{-- État véhicule --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">État véhicule</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">KM sortie</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $deliveryNote->kilometrage_sortie ? number_format($deliveryNote->kilometrage_sortie, 0, '', ' ') . ' km' : '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Carburant</span>
                        <span class="text-gray-700 text-xs">{{ $deliveryNote->niveau_carburant ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Réceptionnaire --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Réceptionnaire</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Nom</span>
                        <span class="text-gray-700 text-xs">{{ $deliveryNote->nom_receptionnaire ?? '—' }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">CIN</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $deliveryNote->cin_receptionnaire ?? '—' }}</span>
                    </div>
                    <div class="pt-2 border-t border-gray-100 space-y-1.5">
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 text-xs">Signé atelier</span>
                            @if($deliveryNote->signe_atelier)
                                <span class="text-green-600 text-xs font-medium flex items-center gap-1"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Oui</span>
                            @else
                                <span class="text-gray-400 text-xs">Non</span>
                            @endif
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-gray-500 text-xs">Signé client</span>
                            @if($deliveryNote->signe_client)
                                <span class="text-green-600 text-xs font-medium flex items-center gap-1"><i data-lucide="check-circle" class="w-3.5 h-3.5"></i> Oui</span>
                            @else
                                <span class="text-gray-400 text-xs">Non</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Paiement</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Total TTC</span>
                        <span class="text-gray-700 font-semibold text-xs">{{ number_format($deliveryNote->total_ttc, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Montant payé</span>
                        <span class="text-green-600 text-xs">{{ number_format($deliveryNote->montant_paye, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="font-semibold text-gray-700 text-xs">Reste à payer</span>
                        <span class="font-bold {{ $deliveryNote->reste_a_payer > 0 ? 'text-red-600' : 'text-green-600' }}">
                            {{ $deliveryNote->reste_a_payer > 0 ? number_format($deliveryNote->reste_a_payer, 2, ',', ' ') . ' DH' : 'Soldé' }}
                        </span>
                    </div>
                    @if($deliveryNote->mode_paiement)
                    <div class="flex justify-between pt-1">
                        <span class="text-gray-500 text-xs">Mode</span>
                        <span class="text-gray-700 text-xs">{{ $deliveryNote->mode_paiement_label }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Notes --}}
            @if($deliveryNote->notes)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $deliveryNote->notes }}</p>
            </div>
            @endif

            {{-- Danger zone --}}
            @if(auth()->user()->isAdmin() && $deliveryNote->statut !== 'valide')
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('delivery-notes.destroy', $deliveryNote) }}"
                      x-data @submit.prevent="if(confirm('Supprimer définitivement le BL {{ $deliveryNote->numero }} ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer ce bon
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
