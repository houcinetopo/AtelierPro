@extends('layouts.app')

@section('title', $repairOrder->numero)
@section('breadcrumb')
    <a href="{{ route('repair-orders.index') }}" class="hover:text-primary-600">Ordres de Réparation</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $repairOrder->numero }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- ═══════════ HEADER ═══════════ --}}
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
                @if($repairOrder->createdBy) par {{ $repairOrder->createdBy->name }} @endif
                @if($repairOrder->quote)
                    — issu du devis <a href="{{ route('quotes.show', $repairOrder->quote) }}" class="text-primary-600 hover:underline">{{ $repairOrder->quote->numero }}</a>
                @endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
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

            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($repairOrder->status, ['facture', 'annule']))
            <a href="{{ route('repair-orders.edit', $repairOrder) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
            @endif

            @if(!in_array($repairOrder->status, ['facture', 'annule', 'livre']))
            <a href="{{ route('repair-orders.additif-quote', $repairOrder) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-amber-700 bg-amber-50 border border-amber-200 rounded-lg hover:bg-amber-100">
                <i data-lucide="file-plus" class="w-4 h-4"></i> Devis Additif
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
                <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Travaux & Pièces</h2>
                    <span class="text-xs text-gray-400">{{ $repairOrder->items->count() }} ligne(s)</span>
                </div>
                @if($repairOrder->items->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-2 text-left">Type</th>
                                <th class="px-4 py-2 text-left">Désignation</th>
                                <th class="px-4 py-2 text-right">Qté</th>
                                <th class="px-4 py-2 text-right">P.U.</th>
                                <th class="px-4 py-2 text-right">TVA</th>
                                <th class="px-4 py-2 text-right">Remise</th>
                                <th class="px-4 py-2 text-right">Montant HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($repairOrder->items as $item)
                            <tr class="hover:bg-gray-50/50">
                                <td class="px-4 py-2.5">
                                    @php $typeColor = ['main_oeuvre'=>'blue','piece'=>'orange','fourniture'=>'green','sous_traitance'=>'purple'][$item->type] ?? 'gray'; @endphp
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-{{ $typeColor }}-50 text-{{ $typeColor }}-700">{{ $item->type_label }}</span>
                                </td>
                                <td class="px-4 py-2.5">
                                    <span class="text-gray-800">{{ $item->designation }}</span>
                                    @if($item->reference) <span class="text-xs text-gray-400 ml-1">({{ $item->reference }})</span> @endif
                                    @if($item->source === 'stock')
                                        <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-xs bg-green-50 text-green-600">Stock</span>
                                    @elseif($item->source === 'commande')
                                        <span class="inline-flex items-center ml-1 px-1.5 py-0.5 rounded text-xs bg-amber-50 text-amber-600">Commande</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($item->quantite, 2) }} {{ $item->unite }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($item->prix_unitaire, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($item->taux_tva, 0) }}%</td>
                                <td class="px-4 py-2.5 text-right">
                                    @if($item->remise > 0) <span class="text-red-500">-{{ $item->remise }}%</span> @else — @endif
                                </td>
                                <td class="px-4 py-2.5 text-right font-medium text-gray-800">{{ number_format($item->montant_ht, 2, ',', ' ') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50/50 text-sm">
                            <tr class="border-t border-gray-200">
                                <td colspan="5"></td>
                                <td class="px-4 py-1.5 text-right text-xs text-gray-500">Total HT</td>
                                <td class="px-4 py-1.5 text-right font-semibold">{{ number_format($repairOrder->total_ht, 2, ',', ' ') }}</td>
                            </tr>
                            <tr>
                                <td colspan="5"></td>
                                <td class="px-4 py-1.5 text-right text-xs text-gray-500">TVA</td>
                                <td class="px-4 py-1.5 text-right text-gray-600">{{ number_format($repairOrder->montant_tva, 2, ',', ' ') }}</td>
                            </tr>
                            @if($repairOrder->remise_globale > 0)
                            <tr>
                                <td colspan="5"></td>
                                <td class="px-4 py-1.5 text-right text-xs text-red-500">Remise</td>
                                <td class="px-4 py-1.5 text-right text-red-600">-{{ number_format($repairOrder->remise_globale, 2, ',', ' ') }}</td>
                            </tr>
                            @endif
                            <tr class="border-t border-gray-300">
                                <td colspan="5"></td>
                                <td class="px-4 py-2 text-right text-xs font-semibold text-gray-700">Net à payer</td>
                                <td class="px-4 py-2 text-right text-lg font-bold text-primary-600">{{ number_format($repairOrder->net_a_payer, 2, ',', ' ') }} <span class="text-xs text-gray-400">DH</span></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                @else
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="package" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucune ligne de travaux</p>
                </div>
                @endif
            </div>

            {{-- ═══════════ AJOUTER PIÈCE DU STOCK ═══════════ --}}
            @if(!in_array($repairOrder->status, ['facture', 'annule', 'livre']) && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            @include('components.stock-picker', ['repairOrder' => $repairOrder, 'products' => $products])
            @endif

            {{-- ═══════════ PHOTOS DU VÉHICULE & OR ═══════════ --}}
            @php
                $vehiclePhotos = $repairOrder->vehicle?->photos ?? collect();
                $orPhotos = $repairOrder->photos ?? collect();
            @endphp
            @if($vehiclePhotos->count() > 0 || $orPhotos->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="camera" class="w-5 h-5 text-gray-400"></i>
                    Photos du véhicule
                    <span class="text-xs text-gray-400 font-normal">({{ $vehiclePhotos->count() + $orPhotos->count() }})</span>
                </h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($vehiclePhotos as $photo)
                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square">
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->type ?? 'Photo' }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2">
                            <span class="text-xs text-white font-medium">{{ ucfirst($photo->type ?? 'Véhicule') }}</span>
                        </div>
                    </div>
                    @endforeach
                    @foreach($orPhotos as $photo)
                    <div class="relative group rounded-lg overflow-hidden border border-blue-200 aspect-square">
                        <img src="{{ asset('storage/' . $photo->path) }}" alt="{{ $photo->moment ?? 'Photo' }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200">
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-blue-900/60 to-transparent p-2">
                            <span class="text-xs text-white font-medium">{{ ucfirst($photo->moment ?? 'OR') }}</span>
                        </div>
                        @if(!in_array($repairOrder->status, ['facture', 'annule']) && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
                        <form method="POST" action="{{ route('repair-orders.photos.delete', [$repairOrder, $photo]) }}" class="absolute top-1 right-1 opacity-0 group-hover:opacity-100 transition">
                            @csrf @method('DELETE')
                            <button type="submit" onclick="return confirm('Supprimer cette photo ?')"
                                    class="w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center text-xs hover:bg-red-600">x</button>
                        </form>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- ═══════════ COLONNE DROITE (1/3) ═══════════ --}}
        <div class="space-y-4">

            {{-- Informations --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Informations</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Réception</span><span class="text-gray-700 font-medium text-xs">{{ $repairOrder->date_reception->format('d/m/Y') }}</span></div>
                    @if($repairOrder->date_prevue_livraison)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Livraison prévue</span><span class="text-xs {{ $repairOrder->is_late ? 'text-red-600 font-bold' : 'text-gray-700 font-medium' }}">{{ $repairOrder->date_prevue_livraison->format('d/m/Y') }}</span></div>
                    @endif
                    @if($repairOrder->date_livraison_effective)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Livraison effective</span><span class="text-gray-700 font-medium text-xs">{{ $repairOrder->date_livraison_effective->format('d/m/Y') }}</span></div>
                    @endif
                    @if($repairOrder->duree_reelle)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Durée réelle</span><span class="text-gray-700 font-medium text-xs">{{ $repairOrder->duree_reelle }}</span></div>
                    @endif
                    <div class="pt-2 border-t border-gray-100">
                        <div class="flex justify-between"><span class="text-gray-500 text-xs">Technicien</span><span class="text-gray-700 font-medium text-xs">{{ $repairOrder->technicien?->name ?? 'Non assigné' }}</span></div>
                    </div>
                    @if($repairOrder->source_ordre)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Source</span><span class="text-gray-700 text-xs">{{ \App\Models\RepairOrder::SOURCES[$repairOrder->source_ordre] ?? $repairOrder->source_ordre }}</span></div>
                    @endif
                    @if($repairOrder->expert)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Expert</span><span class="text-gray-700 text-xs">{{ $repairOrder->expert->nom_complet }}</span></div>
                    @endif
                </div>
            </div>

            {{-- État véhicule --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">État véhicule</h2>
                <div class="space-y-2.5 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">KM entrée</span><span class="text-gray-700 font-mono text-xs">{{ $repairOrder->kilometrage_entree ? number_format($repairOrder->kilometrage_entree, 0, '', ' ') . ' km' : '—' }}</span></div>
                    @if($repairOrder->kilometrage_sortie)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">KM sortie</span><span class="text-gray-700 font-mono text-xs">{{ number_format($repairOrder->kilometrage_sortie, 0, '', ' ') }} km</span></div>
                    @endif
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Carburant</span><span class="text-gray-700 text-xs">{{ $repairOrder->niveau_carburant ?? '—' }}</span></div>
                </div>
            </div>

            {{-- ═══════════ DOCUMENTS LIÉS ═══════════ --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3 flex items-center gap-2">
                    <i data-lucide="folder-open" class="w-5 h-5 text-gray-400"></i>
                    Documents
                </h2>
                <div class="space-y-2">
                    @php
                        $initialQuote = $repairOrder->quote ?? \App\Models\Quote::where('repair_order_id', $repairOrder->id)->where('type_devis', '!=', 'additif')->first();
                        $additifQuotes = \App\Models\Quote::where('linked_repair_order_id', $repairOrder->id)->get();
                    @endphp
                    @if($initialQuote)
                    <a href="{{ route('quotes.show', $initialQuote) }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 hover:bg-blue-50 hover:border-blue-200 transition group">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-blue-100 text-blue-600 text-xs font-bold">DV</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-blue-700 truncate">{{ $initialQuote->numero }}</p>
                            <p class="text-xs text-gray-400">Devis initial</p>
                        </div>
                    </a>
                    @endif
                    @foreach($additifQuotes as $aq)
                    <a href="{{ route('quotes.show', $aq) }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-amber-200 hover:bg-amber-50 transition group">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-amber-100 text-amber-600 text-xs font-bold">DA</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-amber-700 truncate">{{ $aq->numero }}</p>
                            <p class="text-xs text-amber-500">Devis additif — {!! $aq->statut_badge !!}</p>
                        </div>
                    </a>
                    @endforeach

                    @if($repairOrder->invoice)
                    <a href="{{ route('invoices.show', $repairOrder->invoice) }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 hover:bg-green-50 hover:border-green-200 transition group">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-green-100 text-green-600 text-xs font-bold">FA</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-green-700 truncate">{{ $repairOrder->invoice->numero }}</p>
                            <p class="text-xs text-gray-400">Facture — {!! $repairOrder->invoice->statut_badge !!}</p>
                        </div>
                        <span class="text-xs font-medium text-gray-600">{{ number_format($repairOrder->invoice->net_a_payer, 2, ',', ' ') }} DH</span>
                    </a>
                    @endif

                    @if($repairOrder->deliveryNote)
                    <a href="{{ route('delivery-notes.show', $repairOrder->deliveryNote) }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 hover:bg-indigo-50 hover:border-indigo-200 transition group">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-indigo-100 text-indigo-600 text-xs font-bold">BL</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-indigo-700 truncate">{{ $repairOrder->deliveryNote->numero }}</p>
                            <p class="text-xs text-gray-400">Bon de livraison</p>
                        </div>
                    </a>
                    @endif

                    @foreach($repairOrder->purchaseOrders as $po)
                    <a href="{{ route('suppliers.order', [$po->supplier_id, $po]) }}" class="flex items-center gap-3 p-2.5 rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-200 transition group">
                        <span class="w-8 h-8 flex items-center justify-center rounded-lg bg-orange-100 text-orange-600 text-xs font-bold">BC</span>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-800 group-hover:text-orange-700 truncate">{{ $po->numero }}</p>
                            <p class="text-xs text-gray-400">Bon de commande — {{ $po->supplier?->raison_sociale }}</p>
                        </div>
                    </a>
                    @endforeach

                    @if(!$initialQuote && !$repairOrder->invoice && !$repairOrder->deliveryNote && $repairOrder->purchaseOrders->isEmpty() && $additifQuotes->isEmpty())
                    <p class="text-xs text-gray-400 text-center py-3">Aucun document lié</p>
                    @endif

                    @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !in_array($repairOrder->status, ['annule']))
                    <div class="pt-2 border-t border-gray-100 mt-2 space-y-1.5">
                        @if(!$repairOrder->invoice || $repairOrder->invoice->statut === 'annulee')
                        <form action="{{ route('repair-orders.generate-invoice', $repairOrder) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-green-700 bg-green-50 border border-green-200 rounded-lg hover:bg-green-100 transition">
                                <i data-lucide="receipt" class="w-3.5 h-3.5"></i> Générer la facture
                            </button>
                        </form>
                        @endif
                        @if(!$repairOrder->deliveryNote)
                        <form action="{{ route('repair-orders.generate-delivery-note', $repairOrder) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full flex items-center gap-2 px-3 py-2 text-xs text-indigo-700 bg-indigo-50 border border-indigo-200 rounded-lg hover:bg-indigo-100 transition">
                                <i data-lucide="truck" class="w-3.5 h-3.5"></i> Générer le bon de livraison
                            </button>
                        </form>
                        @endif
                    </div>
                    @endif
                </div>
            </div>

            {{-- ═══════════ RENTABILITÉ ═══════════ --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                    <i data-lucide="bar-chart-3" class="w-5 h-5 text-gray-400"></i>
                    Rentabilité
                </h2>
                <div class="space-y-2 text-sm">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Coûts de revient</p>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Coût pièces</span><span class="text-gray-700 text-xs">{{ number_format($resumeFinancier['cout_pieces'], 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Coût main-d'oeuvre</span><span class="text-gray-700 text-xs">{{ number_format($resumeFinancier['cout_main_oeuvre'], 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between pt-1 border-t border-dashed border-gray-200"><span class="text-gray-600 text-xs font-medium">Total dépenses</span><span class="text-red-600 text-xs font-semibold">{{ number_format($resumeFinancier['cout_total'], 2, ',', ' ') }} DH</span></div>

                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider pt-2">Facturation</p>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Prix facturé HT</span><span class="text-gray-700 text-xs">{{ number_format($resumeFinancier['prix_facture_ht'], 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">TVA</span><span class="text-gray-700 text-xs">{{ number_format($resumeFinancier['tva'], 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-600 text-xs font-medium">Total TTC</span><span class="text-gray-800 text-xs font-semibold">{{ number_format($resumeFinancier['prix_facture_ttc'], 2, ',', ' ') }} DH</span></div>

                    @php $isPositive = $resumeFinancier['benefice'] >= 0; @endphp
                    <div class="pt-2 border-t border-gray-200 space-y-1.5">
                        <div class="flex justify-between"><span class="text-gray-700 text-xs font-semibold">Profit net</span><span class="font-bold text-xs {{ $isPositive ? 'text-green-600' : 'text-red-600' }}">{{ $isPositive ? '+' : '' }}{{ number_format($resumeFinancier['benefice'], 2, ',', ' ') }} DH</span></div>
                        <div class="flex justify-between items-center"><span class="text-gray-700 text-xs font-semibold">Marge</span><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold border {{ $isPositive ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200' }}">{{ $resumeFinancier['marge'] }}%</span></div>
                        @if($resumeFinancier['prix_facture_ht'] > 0)
                        <div class="w-full bg-gray-100 rounded-full h-2 mt-1">
                            <div class="h-2 rounded-full {{ $isPositive ? 'bg-green-500' : 'bg-red-500' }}" style="width: {{ min(100, max(0, abs($resumeFinancier['marge']))) }}%"></div>
                        </div>
                        @endif
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

            {{-- Zone danger --}}
            @if(auth()->user()->isAdmin() && !in_array($repairOrder->status, ['facture']))
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('repair-orders.destroy', $repairOrder) }}"
                      x-data @submit.prevent="if(confirm('Supprimer définitivement cet ordre ?')) $el.submit()">
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
