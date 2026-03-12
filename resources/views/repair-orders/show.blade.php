@extends('layouts.app')

@section('title', $repairOrder->numero)
@section('breadcrumb')
    <a href="{{ route('repair-orders.index') }}" class="hover:text-primary-600">Ordres de Réparation</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $repairOrder->numero }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800 font-mono">{{ $repairOrder->numero }}</h1>
                {!! $repairOrder->status_badge !!}
                @if($repairOrder->is_late)
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                        <i data-lucide="alert-triangle" class="w-3 h-3"></i> En retard
                    </span>
                @endif
            </div>
            <p class="text-sm text-gray-500 mt-1">
                Créé le {{ $repairOrder->created_at->format('d/m/Y à H:i') }}
                @if($repairOrder->createdBy)
                    par {{ $repairOrder->createdBy->name }}
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Transitions de statut --}}
            @if($transitions->isNotEmpty() && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <div class="flex items-center gap-1" x-data="{ open: false }">
                <div class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                        <i data-lucide="git-branch" class="w-4 h-4"></i> Changer statut
                        <i data-lucide="chevron-down" class="w-3 h-3"></i>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition
                         class="absolute right-0 mt-1 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                        @foreach($transitions as $status => $label)
                        <form method="POST" action="{{ route('repair-orders.update-status', $repairOrder) }}" class="block">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $status }}">
                            <button type="submit" class="w-full text-left px-4 py-2 text-sm hover:bg-gray-50 flex items-center gap-2"
                                    onclick="return confirm('Changer le statut vers {{ $label }} ?')">
                                @php $c = \App\Models\RepairOrder::STATUS_COLORS[$status] ?? 'gray'; @endphp
                                <span class="w-2 h-2 rounded-full bg-{{ $c }}-500"></span>
                                {{ $label }}
                            </button>
                        </form>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && in_array($repairOrder->status, ['termine', 'livre']) && !$repairOrder->deliveryNote)
            <a href="{{ route('delivery-notes.create', ['repair_order_id' => $repairOrder->id]) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg transition-colors">
                <i data-lucide="truck" class="w-4 h-4"></i> Créer BL
            </a>
            @endif

            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && in_array($repairOrder->status, ['livre', 'termine']) && !$repairOrder->invoice)
            <a href="{{ route('invoices.create', ['repair_order_id' => $repairOrder->id]) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-white bg-indigo-600 hover:bg-indigo-700 rounded-lg transition-colors">
                <i data-lucide="receipt" class="w-4 h-4"></i> Facturer
            </a>
            @endif

            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($repairOrder->status, ['facture', 'annule']))
            <a href="{{ route('repair-orders.edit', $repairOrder) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════════ COLONNE PRINCIPALE (2/3) ═══════════ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Client + Véhicule --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Client</h2>
                    <a href="{{ route('clients.show', $repairOrder->client) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600">{{ $repairOrder->client_name }}</p>
                    </a>
                    @if($repairOrder->client?->telephone)
                        <p class="text-sm text-gray-500 mt-1">{{ $repairOrder->client->telephone }}</p>
                    @endif
                    @if($repairOrder->client?->email)
                        <p class="text-sm text-gray-500">{{ $repairOrder->client->email }}</p>
                    @endif
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h2 class="text-xs font-semibold text-gray-400 uppercase mb-3">Véhicule</h2>
                    <a href="{{ route('vehicles.show', $repairOrder->vehicle) }}" class="group">
                        <p class="font-semibold text-gray-800 group-hover:text-primary-600">
                            {{ $repairOrder->vehicle?->marque }} {{ $repairOrder->vehicle?->modele }}
                        </p>
                    </a>
                    <p class="text-sm text-gray-500 font-mono mt-1">{{ $repairOrder->vehicle?->immatriculation }}</p>
                    @if($repairOrder->vehicle?->couleur)
                        <p class="text-xs text-gray-400 mt-0.5">{{ $repairOrder->vehicle->couleur }}</p>
                    @endif
                </div>
            </div>

            {{-- Description & Diagnostic --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <h2 class="text-base font-semibold text-gray-800">Détails de l'intervention</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Description de la panne</p>
                        <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $repairOrder->description_panne ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Diagnostic</p>
                        <p class="text-gray-700 mt-1 whitespace-pre-line">{{ $repairOrder->diagnostic ?? '—' }}</p>
                    </div>
                    @if($repairOrder->observations)
                    <div class="sm:col-span-2">
                        <p class="text-gray-400 text-xs">Observations</p>
                        <p class="text-gray-600 mt-1 whitespace-pre-line">{{ $repairOrder->observations }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════ LIGNES DE TRAVAUX ═══════════ --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Travaux & Pièces</h2>
                    <span class="text-xs text-gray-400">{{ $repairOrder->items->count() }} ligne(s)</span>
                </div>

                @if($repairOrder->items->count() > 0)
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
                            @foreach($repairOrder->items as $item)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5">
                                    @php
                                        $typeColors = ['main_oeuvre' => 'blue', 'piece' => 'orange', 'fourniture' => 'green', 'sous_traitance' => 'purple'];
                                        $tc = $typeColors[$item->type] ?? 'gray';
                                    @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $tc }}-50 text-{{ $tc }}-700">
                                        {{ $item->type_label }}
                                    </span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <p class="font-medium text-gray-800 text-xs">{{ $item->designation }}</p>
                                    @if($item->reference)
                                        <p class="text-xs text-gray-400">Réf: {{ $item->reference }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ $item->quantite }} {{ $item->unite }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ number_format($item->prix_unitaire, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">
                                    {{ $item->remise > 0 ? $item->remise . '%' : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-semibold text-gray-800 text-xs">
                                    {{ number_format($item->montant_ht, 2, ',', ' ') }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50 border-t border-gray-200">
                            <tr>
                                <td colspan="5" class="px-4 py-2 text-right text-xs text-gray-500">Total HT</td>
                                <td class="px-4 py-2 text-right font-semibold text-gray-700 text-sm">{{ number_format($repairOrder->total_ht, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5" class="px-4 py-1.5 text-right text-xs text-gray-500">TVA ({{ $repairOrder->taux_tva }}%)</td>
                                <td class="px-4 py-1.5 text-right text-gray-600 text-sm">{{ number_format($repairOrder->montant_tva, 2, ',', ' ') }}</td>
                            </tr>
                            @if($repairOrder->remise_globale > 0)
                            <tr>
                                <td colspan="5" class="px-4 py-1.5 text-right text-xs text-red-500">Remise globale</td>
                                <td class="px-4 py-1.5 text-right text-red-600 text-sm">-{{ number_format($repairOrder->remise_globale, 2, ',', ' ') }}</td>
                            </tr>
                            @endif
                            <tr class="border-t border-gray-300">
                                <td colspan="5" class="px-4 py-2.5 text-right font-semibold text-gray-700">Net à payer</td>
                                <td class="px-4 py-2.5 text-right">
                                    <span class="text-lg font-bold text-primary-600">{{ number_format($repairOrder->net_a_payer, 2, ',', ' ') }}</span>
                                    <span class="text-xs text-gray-400 ml-1">DH</span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <i data-lucide="package" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucune ligne de travaux ajoutée</p>
                </div>
                @endif
            </div>

            {{-- ═══════════ PHOTOS ═══════════ --}}
            @if($repairOrder->photos->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Photos</h2>
                @foreach(['avant' => 'Avant réparation', 'pendant' => 'Pendant réparation', 'apres' => 'Après réparation'] as $moment => $label)
                    @php $momentPhotos = $repairOrder->photos->where('moment', $moment); @endphp
                    @if($momentPhotos->count() > 0)
                    <div class="mb-4">
                        <p class="text-xs font-semibold text-gray-500 uppercase mb-2">{{ $label }}</p>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            @foreach($momentPhotos as $photo)
                            <div class="relative group rounded-lg overflow-hidden border border-gray-200">
                                <img src="{{ $photo->url }}" alt="{{ $photo->caption }}" class="w-full h-32 object-cover">
                                @if($photo->caption)
                                    <p class="text-xs text-gray-500 p-1.5 bg-gray-50">{{ $photo->caption }}</p>
                                @endif
                                @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                                <form method="POST" action="{{ route('repair-orders.photos.delete', [$repairOrder, $photo]) }}"
                                      class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                    @csrf @method('DELETE')
                                    <button type="submit" onclick="return confirm('Supprimer cette photo ?')"
                                            class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600">
                                        <i data-lucide="x" class="w-3 h-3"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            </div>
            @endif
        </div>

        {{-- ═══════════ COLONNE DROITE (1/3) ═══════════ --}}
        <div class="space-y-4">
            {{-- Informations clés --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Informations</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Réception</span>
                        <span class="text-gray-700 font-medium text-xs">{{ $repairOrder->date_reception->format('d/m/Y') }}</span>
                    </div>
                    @if($repairOrder->date_prevue_livraison)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Livraison prévue</span>
                        <span class="text-xs {{ $repairOrder->is_late ? 'text-red-600 font-bold' : 'text-gray-700 font-medium' }}">
                            {{ $repairOrder->date_prevue_livraison->format('d/m/Y') }}
                        </span>
                    </div>
                    @endif
                    @if($repairOrder->date_livraison_effective)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Livraison effective</span>
                        <span class="text-gray-700 font-medium text-xs">{{ $repairOrder->date_livraison_effective->format('d/m/Y') }}</span>
                    </div>
                    @endif
                    @if($repairOrder->duree_reelle)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Durée réelle</span>
                        <span class="text-gray-700 font-medium text-xs">{{ $repairOrder->duree_reelle }}</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-gray-100">
                        <div class="flex justify-between">
                            <span class="text-gray-500 text-xs">Technicien</span>
                            <span class="text-gray-700 font-medium text-xs">{{ $repairOrder->technicien?->name ?? 'Non assigné' }}</span>
                        </div>
                    </div>
                    @if($repairOrder->source_ordre)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Source</span>
                        <span class="text-gray-700 text-xs">{{ \App\Models\RepairOrder::SOURCES[$repairOrder->source_ordre] ?? $repairOrder->source_ordre }}</span>
                    </div>
                    @endif
                </div>
            </div>

            {{-- État du véhicule à la réception --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">État véhicule</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">KM entrée</span>
                        <span class="text-gray-700 font-mono text-xs">{{ $repairOrder->kilometrage_entree ? number_format($repairOrder->kilometrage_entree, 0, '', ' ') . ' km' : '—' }}</span>
                    </div>
                    @if($repairOrder->kilometrage_sortie)
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">KM sortie</span>
                        <span class="text-gray-700 font-mono text-xs">{{ number_format($repairOrder->kilometrage_sortie, 0, '', ' ') }} km</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Carburant</span>
                        <span class="text-gray-700 text-xs">{{ $repairOrder->niveau_carburant ?? '—' }}</span>
                    </div>
                </div>
            </div>

            {{-- Résumé financier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Résumé financier</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Total HT</span>
                        <span class="text-gray-700 text-xs">{{ number_format($repairOrder->total_ht, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">TVA ({{ $repairOrder->taux_tva }}%)</span>
                        <span class="text-gray-700 text-xs">{{ number_format($repairOrder->montant_tva, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Total TTC</span>
                        <span class="text-gray-700 text-xs">{{ number_format($repairOrder->total_ttc, 2, ',', ' ') }} DH</span>
                    </div>
                    @if($repairOrder->remise_globale > 0)
                    <div class="flex justify-between">
                        <span class="text-red-500 text-xs">Remise</span>
                        <span class="text-red-600 text-xs">-{{ number_format($repairOrder->remise_globale, 2, ',', ' ') }} DH</span>
                    </div>
                    @endif
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="font-semibold text-gray-700 text-xs">Net à payer</span>
                        <span class="font-bold text-primary-600">{{ number_format($repairOrder->net_a_payer, 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>

            {{-- Notes internes --}}
            @if($repairOrder->notes_internes && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <div class="bg-amber-50 rounded-xl border border-amber-200 p-5">
                <h2 class="text-xs font-semibold text-amber-600 uppercase mb-2 flex items-center gap-1.5">
                    <i data-lucide="lock" class="w-3.5 h-3.5"></i> Notes internes
                </h2>
                <p class="text-sm text-amber-800 whitespace-pre-line">{{ $repairOrder->notes_internes }}</p>
            </div>
            @endif

            {{-- Actions danger --}}
            @if(auth()->user()->isAdmin() && !in_array($repairOrder->status, ['facture']))
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('repair-orders.destroy', $repairOrder) }}"
                      x-data @submit.prevent="if(confirm('Supprimer définitivement l\'ordre {{ $repairOrder->numero }} ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer cet ordre
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
