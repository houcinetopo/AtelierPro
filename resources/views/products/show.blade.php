@extends('layouts.app')

@section('title', $product->reference)
@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-primary-600">Stock</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $product->reference }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800">{{ $product->designation }}</h1>
                {!! $product->type_badge !!}
                {!! $product->stock_badge !!}
            </div>
            <p class="text-sm text-gray-500 mt-1 font-mono">{{ $product->reference }}
                @if($product->code_barre) · <span class="text-xs">{{ $product->code_barre }}</span> @endif
            </p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <a href="{{ route('products.edit', $product) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════ COLONNE PRINCIPALE (2/3) ═══════ --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Infos produit --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Informations</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-xs text-gray-500">Marque</span>
                        <p class="font-medium text-gray-800 text-xs">{{ $product->marque ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Catégorie</span>
                        <p class="font-medium text-gray-800 text-xs">{{ $product->category?->nom ?? '—' }}</p>
                    </div>
                    <div>
                        <span class="text-xs text-gray-500">Unité</span>
                        <p class="font-medium text-gray-800 text-xs">{{ $product->unite_label }}</p>
                    </div>
                    @if($product->modele_compatible)
                    <div class="col-span-2 sm:col-span-3">
                        <span class="text-xs text-gray-500">Modèles compatibles</span>
                        <p class="font-medium text-gray-800 text-xs">{{ $product->modele_compatible }}</p>
                    </div>
                    @endif
                    @if($product->description)
                    <div class="col-span-2 sm:col-span-3">
                        <span class="text-xs text-gray-500">Description</span>
                        <p class="text-gray-700 text-xs whitespace-pre-line">{{ $product->description }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Formulaire mouvement --}}
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
            <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="{ type: 'entree' }">
                <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="arrow-left-right" class="w-4 h-4 text-primary-500"></i>
                    Nouveau mouvement de stock
                </h2>
                <form method="POST" action="{{ route('products.add-movement', $product) }}">
                    @csrf
                    <div class="grid grid-cols-12 gap-3">
                        {{-- Type --}}
                        <div class="col-span-12 sm:col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">Type</label>
                            <div class="flex rounded-lg overflow-hidden border border-gray-300">
                                <label class="flex-1 text-center py-2 text-xs cursor-pointer transition-colors"
                                       :class="type === 'entree' ? 'bg-green-500 text-white' : 'bg-white text-gray-600 hover:bg-green-50'">
                                    <input type="radio" name="type" value="entree" x-model="type" class="sr-only"> + Entrée
                                </label>
                                <label class="flex-1 text-center py-2 text-xs cursor-pointer transition-colors"
                                       :class="type === 'sortie' ? 'bg-red-500 text-white' : 'bg-white text-gray-600 hover:bg-red-50'">
                                    <input type="radio" name="type" value="sortie" x-model="type" class="sr-only"> - Sortie
                                </label>
                                <label class="flex-1 text-center py-2 text-xs cursor-pointer transition-colors"
                                       :class="type === 'ajustement' ? 'bg-amber-500 text-white' : 'bg-white text-gray-600 hover:bg-amber-50'">
                                    <input type="radio" name="type" value="ajustement" x-model="type" class="sr-only"> Ajust.
                                </label>
                            </div>
                        </div>

                        {{-- Motif --}}
                        <div class="col-span-12 sm:col-span-3">
                            <label class="block text-xs text-gray-500 mb-1">Motif</label>
                            <select name="motif" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                                <template x-if="type === 'entree'">
                                    <optgroup label="Entrées">
                                        @foreach (\App\Models\StockMovement::MOTIFS_ENTREE as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                                <template x-if="type === 'sortie'">
                                    <optgroup label="Sorties">
                                        @foreach (\App\Models\StockMovement::MOTIFS_SORTIE as $k => $v)
                                            <option value="{{ $k }}">{{ $v }}</option>
                                        @endforeach
                                    </optgroup>
                                </template>
                                <template x-if="type === 'ajustement'">
                                    <optgroup label="Ajustement">
                                        <option value="inventaire_plus">Correction inventaire (+)</option>
                                        <option value="inventaire_moins">Correction inventaire (-)</option>
                                    </optgroup>
                                </template>
                            </select>
                        </div>

                        {{-- Quantité --}}
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">
                                <span x-show="type !== 'ajustement'">Quantité</span>
                                <span x-show="type === 'ajustement'">Nouveau stock</span>
                            </label>
                            <input type="number" name="quantite" step="0.01" min="0.01" required
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500"
                                   placeholder="{{ $product->quantite_stock }}">
                        </div>

                        {{-- Prix unitaire --}}
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">P.U. (DH)</label>
                            <input type="number" name="prix_unitaire" step="0.01" min="0"
                                   value="{{ $product->prix_achat }}"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                        </div>

                        {{-- Référence doc --}}
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Réf. document</label>
                            <input type="text" name="reference_document" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500" placeholder="N° BL, facture...">
                        </div>

                        {{-- Notes --}}
                        <div class="col-span-12 sm:col-span-8">
                            <label class="block text-xs text-gray-500 mb-1">Notes</label>
                            <input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500" placeholder="Commentaire optionnel...">
                        </div>

                        <div class="col-span-12 sm:col-span-4 flex items-end">
                            <button type="submit" class="w-full py-2 text-sm font-medium rounded-lg transition-colors"
                                    :class="type === 'entree' ? 'bg-green-600 hover:bg-green-700 text-white' : (type === 'sortie' ? 'bg-red-600 hover:bg-red-700 text-white' : 'bg-amber-600 hover:bg-amber-700 text-white')">
                                Enregistrer
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            @endif

            {{-- Historique mouvements --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Historique des mouvements</h2>
                    <span class="text-xs text-gray-400">{{ $mouvements->total() }} mouvement(s)</span>
                </div>
                @if($mouvements->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Type</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Motif</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Qté</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock avant</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Stock après</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Réf.</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Par</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($mouvements as $mv)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 text-xs text-gray-600">{{ $mv->created_at->format('d/m/Y H:i') }}</td>
                                <td class="px-4 py-2.5">{!! $mv->type_badge !!}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-600">
                                    {{ $mv->motif_label }}
                                    @if($mv->repair_order_id)
                                        <a href="{{ route('repair-orders.show', $mv->repair_order_id) }}" class="text-primary-600 hover:underline ml-1">OR</a>
                                    @endif
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs font-semibold {{ $mv->is_entree ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $mv->is_entree ? '+' : '-' }}{{ $mv->quantite }}
                                </td>
                                <td class="px-4 py-2.5 text-right text-xs text-gray-500">{{ $mv->stock_avant }}</td>
                                <td class="px-4 py-2.5 text-right text-xs font-medium text-gray-800">{{ $mv->stock_apres }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $mv->reference_document ?? '—' }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $mv->recordedBy?->name ?? '—' }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if ($mouvements->hasPages())
                    <div class="px-5 py-3 border-t border-gray-200">{{ $mouvements->links() }}</div>
                @endif
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <p class="text-sm">Aucun mouvement enregistré</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════ COLONNE DROITE (1/3) ═══════ --}}
        <div class="space-y-4">
            {{-- Stock actuel --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 text-center">
                <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Stock actuel</p>
                <p class="text-3xl font-bold {{ $product->stock_status === 'rupture' ? 'text-red-600' : ($product->stock_status === 'alerte' ? 'text-amber-600' : 'text-gray-800') }}">
                    {{ $product->quantite_stock }}
                </p>
                <p class="text-sm text-gray-400">{{ $product->unite_label }}</p>
                <div class="mt-3">{!! $product->stock_badge !!}</div>
            </div>

            {{-- Prix & Marge --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Prix & Marge</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Prix d'achat</span><span class="text-gray-700 text-xs">{{ number_format($product->prix_achat, 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Prix de vente HT</span><span class="font-semibold text-gray-800 text-xs">{{ number_format($product->prix_vente, 2, ',', ' ') }} DH</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">TVA</span><span class="text-gray-700 text-xs">{{ $product->taux_tva }}%</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Prix TTC</span><span class="font-bold text-primary-600 text-xs">{{ number_format($product->prix_vente_ttc, 2, ',', ' ') }} DH</span></div>
                    <div class="pt-2 border-t border-gray-200 flex justify-between">
                        <span class="text-gray-500 text-xs">Marge</span>
                        <span class="font-semibold text-xs {{ $product->marge_calculee > 30 ? 'text-green-600' : ($product->marge_calculee > 10 ? 'text-amber-600' : 'text-red-600') }}">
                            {{ $product->marge_calculee !== null ? $product->marge_calculee . '%' : '—' }}
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 text-xs">Valeur du stock</span>
                        <span class="font-medium text-gray-700 text-xs">{{ number_format($product->valeur_stock, 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>

            {{-- Seuils --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Seuils</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Seuil d'alerte</span><span class="text-amber-600 font-medium text-xs">{{ $product->seuil_alerte }} {{ $product->unite }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Seuil de commande</span><span class="text-blue-600 font-medium text-xs">{{ $product->seuil_commande }} {{ $product->unite }}</span></div>
                    @if($product->quantite_max)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Quantité max</span><span class="text-gray-700 text-xs">{{ $product->quantite_max }} {{ $product->unite }}</span></div>
                    @endif
                    @if($product->emplacement)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Emplacement</span><span class="text-gray-700 text-xs font-mono">{{ $product->emplacement }}</span></div>
                    @endif
                </div>
            </div>

            {{-- Fournisseur --}}
            @if($product->fournisseur_nom)
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-base font-semibold text-gray-800">Fournisseur</h2>
                <div class="space-y-2 text-sm">
                    <p class="font-medium text-gray-800 text-xs">{{ $product->fournisseur_nom }}</p>
                    @if($product->fournisseur_ref)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Réf. fournisseur</span><span class="text-gray-700 font-mono text-xs">{{ $product->fournisseur_ref }}</span></div>
                    @endif
                    @if($product->delai_livraison_jours)
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Délai livraison</span><span class="text-gray-700 text-xs">{{ $product->delai_livraison_jours }} jour(s)</span></div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Stats 30 jours --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-3">
                <h2 class="text-xs font-semibold text-gray-500 uppercase">Activité (30 jours)</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Entrées</span><span class="text-green-600 font-medium text-xs">+{{ $statsStock['entrees_30j'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Sorties</span><span class="text-red-600 font-medium text-xs">-{{ $statsStock['sorties_30j'] }}</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 text-xs">Mouvements</span><span class="text-gray-700 text-xs">{{ $statsStock['nb_mvts_30j'] }}</span></div>
                </div>
            </div>

            {{-- Danger zone --}}
            @if(auth()->user()->isAdmin())
            <div class="bg-white rounded-xl border border-red-200 p-5">
                <h2 class="text-xs font-semibold text-red-500 uppercase mb-3">Zone de danger</h2>
                <form method="POST" action="{{ route('products.destroy', $product) }}"
                      x-data @submit.prevent="if(confirm('Supprimer {{ $product->reference }} ?')) $el.submit()">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50 flex items-center justify-center gap-2"
                            {{ $product->quantite_stock > 0 ? 'disabled' : '' }}>
                        <i data-lucide="trash-2" class="w-4 h-4"></i> Supprimer
                    </button>
                    @if($product->quantite_stock > 0)
                        <p class="text-xs text-gray-400 mt-2 text-center">Ajustez le stock à 0 avant de supprimer</p>
                    @endif
                </form>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
