@extends('layouts.app')
@section('title', "BC {$order->numero}")

@section('content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-900">
                    <span class="font-mono text-primary-600">{{ $order->numero }}</span>
                </h1>
                {!! $order->statut_badge !!}
            </div>
            <p class="mt-1 text-sm text-gray-500">
                Fournisseur : <a href="{{ route('suppliers.show', $supplier) }}" class="text-primary-600 hover:underline">{{ $supplier->raison_sociale }}</a>
                @if($order->createdBy) · Créé par {{ $order->createdBy->name }}@endif
            </p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            {{-- Changer statut --}}
            @if(count($transitions) > 0)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                        Changer statut
                    </button>
                    <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 z-20 py-1">
                        @foreach($transitions as $key => $label)
                            <form method="POST" action="{{ route('suppliers.order.update-statut', [$supplier, $order]) }}">
                                @csrf @method('PATCH')
                                <input type="hidden" name="statut" value="{{ $key }}">
                                <button type="submit" class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-50">
                                    → {{ $label }}
                                </button>
                            </form>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Réception --}}
            @if(in_array($order->statut, ['confirmee', 'livree_partiel']))
                <button onclick="document.getElementById('reception-section').scrollIntoView({behavior:'smooth'})" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 8-4-4"/></svg>
                    Réceptionner
                </button>
            @endif

            <a href="{{ route('suppliers.show', $supplier) }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour fournisseur
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Lignes de commande --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100">
                    <h3 class="text-base font-semibold text-gray-900">Détail de la commande</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Désignation</th>
                                <th class="px-4 py-3 text-left font-medium text-gray-500">Réf.</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Qté</th>
                                @if(in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']))
                                    <th class="px-4 py-3 text-right font-medium text-gray-500">Reçue</th>
                                @endif
                                <th class="px-4 py-3 text-right font-medium text-gray-500">P.U.</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Remise</th>
                                <th class="px-4 py-3 text-right font-medium text-gray-500">Montant HT</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($order->items as $item)
                                <tr class="{{ $item->is_fully_received ? 'bg-green-50' : '' }}">
                                    <td class="px-4 py-3">
                                        <div class="font-medium text-gray-900">{{ $item->designation }}</div>
                                        @if($item->product)
                                            <span class="text-xs text-gray-500">Produit : {{ $item->product->reference }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-gray-500 font-mono text-xs">{{ $item->reference ?? '—' }}</td>
                                    <td class="px-4 py-3 text-right text-gray-900">{{ number_format($item->quantite, 2, ',', ' ') }} {{ $item->unite }}</td>
                                    @if(in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']))
                                        <td class="px-4 py-3 text-right">
                                            @if($item->is_fully_received)
                                                <span class="text-green-600 font-medium">{{ number_format($item->quantite_recue, 2, ',', ' ') }}</span>
                                                <svg class="inline w-3.5 h-3.5 text-green-500 ml-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                            @elseif($item->quantite_recue > 0)
                                                <span class="text-amber-600 font-medium">{{ number_format($item->quantite_recue, 2, ',', ' ') }}</span>
                                            @else
                                                <span class="text-gray-400">0</span>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="px-4 py-3 text-right text-gray-900">{{ number_format($item->prix_unitaire, 2, ',', ' ') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-500">{{ $item->remise > 0 ? number_format($item->remise, 0) . '%' : '—' }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($item->montant_ht, 2, ',', ' ') }} DH</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="bg-gray-50">
                            <tr>
                                <td colspan="{{ in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']) ? 5 : 4 }}" class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right text-sm font-medium text-gray-500">Total HT</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900">{{ number_format($order->total_ht, 2, ',', ' ') }} DH</td>
                            </tr>
                            <tr>
                                <td colspan="{{ in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']) ? 5 : 4 }}" class="px-4 py-2"></td>
                                <td class="px-4 py-2 text-right text-sm text-gray-500">TVA {{ number_format($order->taux_tva, 0) }}%</td>
                                <td class="px-4 py-2 text-right text-gray-700">{{ number_format($order->montant_tva, 2, ',', ' ') }} DH</td>
                            </tr>
                            @if($order->remise_globale > 0)
                                <tr>
                                    <td colspan="{{ in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']) ? 5 : 4 }}" class="px-4 py-2"></td>
                                    <td class="px-4 py-2 text-right text-sm text-gray-500">Remise globale</td>
                                    <td class="px-4 py-2 text-right text-red-600">-{{ number_format($order->remise_globale, 2, ',', ' ') }} DH</td>
                                </tr>
                            @endif
                            <tr class="border-t-2 border-gray-300">
                                <td colspan="{{ in_array($order->statut, ['confirmee', 'livree_partiel', 'livree']) ? 5 : 4 }}" class="px-4 py-3"></td>
                                <td class="px-4 py-3 text-right text-sm font-bold text-gray-900">Net à payer</td>
                                <td class="px-4 py-3 text-right text-lg font-bold text-primary-600">{{ number_format($order->net_a_payer, 2, ',', ' ') }} DH</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Section réception --}}
            @if(in_array($order->statut, ['confirmee', 'livree_partiel']))
                <div id="reception-section" class="bg-white rounded-xl shadow-sm border border-green-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-green-100 bg-green-50">
                        <h3 class="text-base font-semibold text-green-800">
                            <svg class="inline w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8 8-4-4"/></svg>
                            Réceptionner les articles
                        </h3>
                        <p class="text-sm text-green-600 mt-1">Saisissez les quantités reçues pour chaque article. Le stock sera mis à jour automatiquement.</p>
                    </div>
                    <form method="POST" action="{{ route('suppliers.order.receive', [$supplier, $order]) }}" class="p-6">
                        @csrf
                        <div class="space-y-3">
                            @foreach($order->items as $item)
                                @if(!$item->is_fully_received)
                                    <div class="flex items-center justify-between gap-4 py-2">
                                        <div class="flex-1">
                                            <div class="font-medium text-gray-900">{{ $item->designation }}</div>
                                            <div class="text-xs text-gray-500">
                                                Commandé : {{ number_format($item->quantite, 2, ',', ' ') }} {{ $item->unite }}
                                                · Déjà reçu : {{ number_format($item->quantite_recue, 2, ',', ' ') }}
                                                · Reste : <span class="text-amber-600 font-medium">{{ number_format($item->reste_a_recevoir, 2, ',', ' ') }}</span>
                                            </div>
                                        </div>
                                        <div class="w-32">
                                            <input type="number" name="quantities[{{ $item->id }}]" value="0" min="0" max="{{ $item->reste_a_recevoir }}" step="0.01"
                                                   class="w-full rounded-lg border-gray-300 text-sm text-right focus:ring-green-500 focus:border-green-500">
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Valider la réception
                            </button>
                        </div>
                    </form>
                </div>
            @endif

            {{-- Notes --}}
            @if($order->notes)
                <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                    <h3 class="text-sm font-medium text-gray-500 mb-2">Notes</h3>
                    <p class="text-gray-700 whitespace-pre-line">{{ $order->notes }}</p>
                </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Infos commande --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informations</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Date commande</dt>
                        <dd class="font-medium text-gray-900">{{ $order->date_commande->format('d/m/Y') }}</dd>
                    </div>
                    @if($order->date_livraison_prevue)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Livraison prévue</dt>
                            <dd class="font-medium {{ $order->date_livraison_prevue->isPast() && !in_array($order->statut, ['livree', 'annulee']) ? 'text-red-600' : 'text-gray-900' }}">
                                {{ $order->date_livraison_prevue->format('d/m/Y') }}
                            </dd>
                        </div>
                    @endif
                    @if($order->date_reception)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Date réception</dt>
                            <dd class="font-medium text-green-600">{{ $order->date_reception->format('d/m/Y') }}</dd>
                        </div>
                    @endif
                    @if($order->reference_fournisseur)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Réf. fournisseur</dt>
                            <dd class="font-mono text-gray-900">{{ $order->reference_fournisseur }}</dd>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Nb articles</dt>
                        <dd class="font-medium text-gray-900">{{ $order->items->count() }}</dd>
                    </div>
                </dl>
            </div>

            {{-- Résumé financier --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Résumé financier</h3>
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <dt class="text-gray-500">Total HT</dt>
                        <dd class="font-medium text-gray-900">{{ number_format($order->total_ht, 2, ',', ' ') }} DH</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-gray-500">TVA {{ number_format($order->taux_tva, 0) }}%</dt>
                        <dd class="text-gray-700">{{ number_format($order->montant_tva, 2, ',', ' ') }} DH</dd>
                    </div>
                    @if($order->remise_globale > 0)
                        <div class="flex justify-between">
                            <dt class="text-gray-500">Remise globale</dt>
                            <dd class="text-red-600">-{{ number_format($order->remise_globale, 2, ',', ' ') }} DH</dd>
                        </div>
                    @endif
                    <div class="pt-3 border-t border-gray-200 flex justify-between">
                        <dt class="font-semibold text-gray-900">Net à payer</dt>
                        <dd class="text-lg font-bold text-primary-600">{{ number_format($order->net_a_payer, 2, ',', ' ') }} DH</dd>
                    </div>
                </dl>
            </div>

            {{-- Fournisseur --}}
            <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Fournisseur</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-full bg-orange-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div>
                        <a href="{{ route('suppliers.show', $supplier) }}" class="font-medium text-primary-600 hover:underline">{{ $supplier->raison_sociale }}</a>
                        <div class="text-xs text-gray-500">{{ $supplier->code }}</div>
                    </div>
                </div>
                @if($supplier->telephone)
                    <p class="text-sm text-gray-600">{{ $supplier->telephone }}</p>
                @endif
                @if($supplier->email)
                    <p class="text-sm text-gray-600">{{ $supplier->email }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
