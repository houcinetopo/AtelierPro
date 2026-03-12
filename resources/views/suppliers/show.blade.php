@extends('layouts.app')
@section('title', $supplier->raison_sociale)
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="hover:text-primary-600">Fournisseurs</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $supplier->code }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800">{{ $supplier->raison_sociale }}</h1>
                {!! $supplier->type_badge !!}
                @if(!$supplier->actif)<span class="text-xs text-red-500 font-medium">Inactif</span>@endif
            </div>
            <p class="text-sm text-gray-500 mt-1 font-mono">{{ $supplier->code }}</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <a href="{{ route('suppliers.create-order', $supplier) }}" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg">
                <i data-lucide="shopping-cart" class="w-4 h-4"></i> Nouvelle commande
            </a>
            <a href="{{ route('suppliers.edit', $supplier) }}" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Contact & Adresse --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Contact</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    @if($supplier->nom_contact)<div><span class="text-xs text-gray-500">Contact</span><p class="text-xs font-medium text-gray-800">{{ $supplier->nom_contact }}</p></div>@endif
                    @if($supplier->telephone)<div><span class="text-xs text-gray-500">Téléphone</span><p class="text-xs font-medium text-gray-800">{{ $supplier->telephone }}</p></div>@endif
                    @if($supplier->telephone_2)<div><span class="text-xs text-gray-500">Téléphone 2</span><p class="text-xs font-medium text-gray-800">{{ $supplier->telephone_2 }}</p></div>@endif
                    @if($supplier->email)<div><span class="text-xs text-gray-500">Email</span><p class="text-xs text-primary-600">{{ $supplier->email }}</p></div>@endif
                    @if($supplier->site_web)<div><span class="text-xs text-gray-500">Site web</span><p class="text-xs text-primary-600">{{ $supplier->site_web }}</p></div>@endif
                    @if($supplier->adresse_complete)<div class="col-span-2"><span class="text-xs text-gray-500">Adresse</span><p class="text-xs text-gray-800">{{ $supplier->adresse_complete }}</p></div>@endif
                </div>
            </div>

            {{-- Produits liés --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Produits ({{ $supplier->products->count() }})</h2>
                </div>
                @if($supplier->products->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Réf.</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Désignation</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">P. Achat</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">État</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->products as $product)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5"><a href="{{ route('products.show', $product) }}" class="font-mono text-xs text-primary-600">{{ $product->reference }}</a></td>
                                <td class="px-4 py-2.5 text-xs text-gray-800">{{ $product->designation }}</td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-600">{{ number_format($product->prix_achat, 2, ',', ' ') }} DH</td>
                                <td class="px-4 py-2.5 text-right text-xs font-semibold {{ $product->quantite_stock <= $product->seuil_alerte ? 'text-red-600' : 'text-gray-800' }}">{{ $product->quantite_stock }}</td>
                                <td class="px-4 py-2.5">{!! $product->stock_badge !!}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucun produit lié</div>
                @endif
            </div>

            {{-- Bons de commande --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Bons de commande</h2>
                    <span class="text-xs text-gray-400">{{ $statsCommandes['total_commandes'] }} commande(s)</span>
                </div>
                @if($supplier->purchaseOrders->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50"><tr>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">N°</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                            <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Montant</th>
                            <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                        </tr></thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($supplier->purchaseOrders as $order)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5"><a href="{{ route('suppliers.order', [$supplier, $order]) }}" class="font-mono text-xs text-primary-600 font-semibold">{{ $order->numero }}</a></td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $order->date_commande->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5">{!! $order->statut_badge !!}</td>
                                <td class="px-4 py-2.5 text-right text-xs font-semibold text-gray-800">{{ number_format($order->net_a_payer, 2, ',', ' ') }} DH</td>
                                <td class="px-4 py-2.5 text-right"><a href="{{ route('suppliers.order', [$supplier, $order]) }}" class="text-xs text-primary-600 hover:underline">Voir</a></td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-5 py-8 text-center text-gray-400 text-sm">Aucune commande</div>
                @endif
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-4">
            {{-- Stats commandes --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Achats</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Total commandes</span><span class="text-xs font-medium text-gray-800">{{ $statsCommandes['total_commandes'] }}</span></div>
                    <div class="flex justify-between"><span class="text-xs text-gray-500">En cours</span><span class="text-xs font-medium text-blue-600">{{ $statsCommandes['en_cours'] }}</span></div>
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Total achats</span><span class="text-xs font-bold text-gray-800">{{ number_format($statsCommandes['total_achats'], 2, ',', ' ') }} DH</span></div>
                    @if($supplier->solde_du > 0)
                    <div class="pt-2 border-t flex justify-between"><span class="text-xs text-gray-500">Solde dû</span><span class="text-xs font-bold text-red-600">{{ number_format($supplier->solde_du, 2, ',', ' ') }} DH</span></div>
                    @endif
                </div>
            </div>

            {{-- Conditions --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Conditions</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Paiement</span><span class="text-xs text-gray-700">{{ $supplier->mode_paiement_label }}</span></div>
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Délai paiement</span><span class="text-xs text-gray-700">{{ $supplier->delai_paiement_jours }}j</span></div>
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Remise</span><span class="text-xs text-gray-700">{{ $supplier->remise_globale }}%</span></div>
                    <div class="flex justify-between"><span class="text-xs text-gray-500">Délai livraison</span><span class="text-xs text-gray-700">{{ $supplier->delai_livraison_jours }}j</span></div>
                </div>
            </div>

            {{-- Infos légales --}}
            @if($supplier->ice || $supplier->rc || $supplier->if_fiscal)
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Légal</h2>
                <div class="space-y-2 text-sm">
                    @if($supplier->ice)<div class="flex justify-between"><span class="text-xs text-gray-500">ICE</span><span class="text-xs text-gray-700 font-mono">{{ $supplier->ice }}</span></div>@endif
                    @if($supplier->rc)<div class="flex justify-between"><span class="text-xs text-gray-500">RC</span><span class="text-xs text-gray-700 font-mono">{{ $supplier->rc }}</span></div>@endif
                    @if($supplier->if_fiscal)<div class="flex justify-between"><span class="text-xs text-gray-500">IF</span><span class="text-xs text-gray-700 font-mono">{{ $supplier->if_fiscal }}</span></div>@endif
                    @if($supplier->patente)<div class="flex justify-between"><span class="text-xs text-gray-500">Patente</span><span class="text-xs text-gray-700 font-mono">{{ $supplier->patente }}</span></div>@endif
                    @if($supplier->rib)<div class="flex justify-between"><span class="text-xs text-gray-500">RIB</span><span class="text-xs text-gray-700 font-mono">{{ $supplier->rib }}</span></div>@endif
                </div>
            </div>
            @endif

            @if($supplier->notes)
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes</h2>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $supplier->notes }}</p>
            </div>
            @endif

            {{-- Danger zone --}}
            @if(auth()->user()->isAdmin())
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('suppliers.destroy', $supplier) }}" x-data @submit.prevent="if(confirm('Supprimer ce fournisseur ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 flex items-center justify-center gap-2"><i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer</button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
