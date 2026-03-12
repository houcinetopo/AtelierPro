@extends('layouts.app')

@section('title', 'Caisse — ' . $cashSession->date_session->format('d/m/Y'))
@section('breadcrumb')
    <a href="{{ route('cash.index') }}" class="hover:text-primary-600">Caisse</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $cashSession->date_session->translatedFormat('D d/m/Y') }}</span>
@endsection

@section('content')
<div class="space-y-4" x-data="{ showEntree: false, showSortie: false, showClose: false }">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-gray-800">Journal de caisse</h1>
                {!! $cashSession->statut_badge !!}
            </div>
            <p class="text-sm text-gray-500 mt-1">
                {{ $cashSession->date_session->translatedFormat('l d F Y') }}
                @if($cashSession->openedBy) — Ouvert par {{ $cashSession->openedBy->name }}
                    @if($cashSession->heure_ouverture) à {{ $cashSession->heure_ouverture->format('H:i') }}@endif
                @endif
            </p>
        </div>
        @if($cashSession->is_open && auth()->user()->hasAnyRole(['admin', 'gestionnaire']))
        <div class="flex items-center gap-2">
            <button @click="showEntree = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                <i data-lucide="arrow-down-left" class="w-4 h-4"></i> Entrée
            </button>
            <button @click="showSortie = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg">
                <i data-lucide="arrow-up-right" class="w-4 h-4"></i> Sortie
            </button>
            <button @click="showClose = true"
                    class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="lock" class="w-4 h-4"></i> Clôturer
            </button>
        </div>
        @endif
    </div>

    {{-- ═══════ MODAL ENTRÉE ═══════ --}}
    <template x-teleport="body">
        <div x-show="showEntree" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showEntree = false">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg" @click.stop>
                <h3 class="text-lg font-semibold text-green-700 mb-4 flex items-center gap-2">
                    <i data-lucide="arrow-down-left" class="w-5 h-5"></i> Nouvelle entrée
                </h3>
                <form method="POST" action="{{ route('cash.add-movement', $cashSession) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="entree">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie *</label>
                            <select name="categorie" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\CashMovement::CATEGORIES_ENTREE as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Montant (DH) *</label>
                            <input type="number" name="montant" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Libellé *</label>
                        <input type="text" name="libelle" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Description du mouvement">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mode de paiement</label>
                            <select name="mode_paiement" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\CashMovement::MODES_PAIEMENT as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Référence</label>
                            <input type="text" name="reference" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="N° chèque...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Payeur / Client</label>
                        <input type="text" name="beneficiaire" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Nom du payeur">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showEntree = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-green-600 hover:bg-green-700 rounded-lg font-medium">Enregistrer l'entrée</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ═══════ MODAL SORTIE ═══════ --}}
    <template x-teleport="body">
        <div x-show="showSortie" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showSortie = false">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-lg" @click.stop>
                <h3 class="text-lg font-semibold text-red-700 mb-4 flex items-center gap-2">
                    <i data-lucide="arrow-up-right" class="w-5 h-5"></i> Nouvelle sortie
                </h3>
                <form method="POST" action="{{ route('cash.add-movement', $cashSession) }}" class="space-y-4">
                    @csrf
                    <input type="hidden" name="type" value="sortie">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Catégorie *</label>
                            <select name="categorie" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\CashMovement::CATEGORIES_SORTIE as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Montant (DH) *</label>
                            <input type="number" name="montant" step="0.01" min="0.01" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Libellé *</label>
                        <input type="text" name="libelle" required class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Description de la dépense">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Mode de paiement</label>
                            <select name="mode_paiement" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                @foreach (\App\Models\CashMovement::MODES_PAIEMENT as $k => $v)
                                    <option value="{{ $k }}">{{ $v }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-700 mb-1">Référence</label>
                            <input type="text" name="reference" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="N° pièce...">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Bénéficiaire</label>
                        <input type="text" name="beneficiaire" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" placeholder="Nom du fournisseur / employé">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-700 mb-1">Notes</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm">
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showSortie = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" class="px-4 py-2 text-sm text-white bg-red-600 hover:bg-red-700 rounded-lg font-medium">Enregistrer la sortie</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- ═══════ MODAL CLÔTURE ═══════ --}}
    <template x-teleport="body">
        <div x-show="showClose" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/30" @click.self="showClose = false">
            <div class="bg-white rounded-xl shadow-xl p-6 w-full max-w-md" @click.stop>
                <h3 class="text-lg font-semibold text-gray-800 mb-4 flex items-center gap-2">
                    <i data-lucide="lock" class="w-5 h-5 text-gray-500"></i> Clôturer la caisse
                </h3>
                <form method="POST" action="{{ route('cash.close', $cashSession) }}" class="space-y-4">
                    @csrf @method('PATCH')
                    <div class="bg-gray-50 rounded-lg p-4 space-y-1.5 text-sm">
                        <div class="flex justify-between"><span class="text-gray-500">Solde d'ouverture</span><span class="text-gray-700">{{ number_format($cashSession->solde_ouverture, 2, ',', ' ') }} DH</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Total entrées</span><span class="text-green-600">+{{ number_format($cashSession->total_entrees, 2, ',', ' ') }} DH</span></div>
                        <div class="flex justify-between"><span class="text-gray-500">Total sorties</span><span class="text-red-600">-{{ number_format($cashSession->total_sorties, 2, ',', ' ') }} DH</span></div>
                        <div class="flex justify-between border-t border-gray-300 pt-1.5"><span class="font-semibold text-gray-700">Solde théorique</span><span class="font-bold text-primary-600">{{ number_format($cashSession->solde_theorique, 2, ',', ' ') }} DH</span></div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Solde réel compté en caisse (DH) <span class="text-red-500">*</span></label>
                        <input type="number" name="solde_reel" step="0.01" min="0" required value="{{ $cashSession->solde_theorique }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes de clôture</label>
                        <textarea name="notes_cloture" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Observations..."></textarea>
                    </div>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="showClose = false" class="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Annuler</button>
                        <button type="submit" onclick="return confirm('Clôturer la caisse ? Cette action est irréversible.')"
                                class="px-4 py-2 text-sm text-white bg-gray-700 hover:bg-gray-800 rounded-lg font-medium">Clôturer</button>
                    </div>
                </form>
            </div>
        </div>
    </template>

    {{-- Résumé --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-white rounded-xl border border-gray-200 p-4 text-center">
            <p class="text-xs text-gray-500 uppercase font-semibold mb-1">Ouverture</p>
            <p class="text-lg font-bold text-gray-700">{{ number_format($cashSession->solde_ouverture, 2, ',', ' ') }} <span class="text-xs text-gray-400">DH</span></p>
        </div>
        <div class="bg-green-50 rounded-xl border border-green-200 p-4 text-center">
            <p class="text-xs text-green-600 uppercase font-semibold mb-1">Entrées</p>
            <p class="text-lg font-bold text-green-700">+{{ number_format($cashSession->total_entrees, 2, ',', ' ') }} <span class="text-xs text-green-400">DH</span></p>
        </div>
        <div class="bg-red-50 rounded-xl border border-red-200 p-4 text-center">
            <p class="text-xs text-red-600 uppercase font-semibold mb-1">Sorties</p>
            <p class="text-lg font-bold text-red-700">-{{ number_format($cashSession->total_sorties, 2, ',', ' ') }} <span class="text-xs text-red-400">DH</span></p>
        </div>
        <div class="bg-primary-50 rounded-xl border border-primary-200 p-4 text-center">
            <p class="text-xs text-primary-600 uppercase font-semibold mb-1">Solde</p>
            <p class="text-lg font-bold text-primary-700">{{ number_format($cashSession->solde_theorique, 2, ',', ' ') }} <span class="text-xs text-primary-400">DH</span></p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════ COLONNE PRINCIPALE : MOUVEMENTS ═══════ --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Mouvements</h2>
                    <span class="text-xs text-gray-400">{{ $cashSession->movements->count() }} opération(s)</span>
                </div>

                @if($cashSession->movements->isNotEmpty())
                <div class="divide-y divide-gray-100">
                    @foreach($cashSession->movements as $mv)
                    <div class="px-5 py-3 flex items-center justify-between hover:bg-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full {{ $mv->is_entree ? 'bg-green-100' : 'bg-red-100' }} flex items-center justify-center">
                                <i data-lucide="{{ $mv->is_entree ? 'arrow-down-left' : 'arrow-up-right' }}" class="w-4 h-4 {{ $mv->is_entree ? 'text-green-600' : 'text-red-600' }}"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-800">{{ $mv->libelle }}</p>
                                <p class="text-xs text-gray-500">
                                    {{ $mv->categorie_label }} — {{ $mv->mode_label }}
                                    @if($mv->beneficiaire) — {{ $mv->beneficiaire }} @endif
                                    @if($mv->reference) <span class="text-gray-400">({{ $mv->reference }})</span> @endif
                                </p>
                                <p class="text-xs text-gray-400">{{ $mv->created_at->format('H:i') }} @if($mv->recordedBy) par {{ $mv->recordedBy->name }} @endif</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-bold {{ $mv->is_entree ? 'text-green-600' : 'text-red-600' }}">
                                {{ $mv->is_entree ? '+' : '-' }}{{ number_format($mv->montant, 2, ',', ' ') }} DH
                            </span>
                            @if($cashSession->is_open && auth()->user()->hasAnyRole(['admin', 'gestionnaire']) && !$mv->invoice_payment_id)
                            <form method="POST" action="{{ route('cash.delete-movement', [$cashSession, $mv]) }}">
                                @csrf @method('DELETE')
                                <button type="submit" onclick="return confirm('Supprimer ce mouvement ?')" class="p-1.5 rounded hover:bg-red-50 text-gray-400 hover:text-red-500">
                                    <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <i data-lucide="inbox" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucun mouvement enregistré</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════ COLONNE DROITE : RÉSUMÉ PAR CATÉGORIE ═══════ --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Par catégorie</h2>
                @if($parCategorie->isNotEmpty())
                <div class="space-y-2">
                    @foreach($parCategorie as $cat)
                    <div class="flex items-center justify-between py-1.5">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full {{ $cat['type'] === 'entree' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span class="text-xs text-gray-700">{{ $cat['label'] }}</span>
                            <span class="text-xs text-gray-400">({{ $cat['count'] }})</span>
                        </div>
                        <span class="text-xs font-semibold {{ $cat['type'] === 'entree' ? 'text-green-600' : 'text-red-600' }}">
                            {{ number_format($cat['total'], 2, ',', ' ') }} DH
                        </span>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-sm text-gray-400">Aucun mouvement</p>
                @endif
            </div>

            {{-- Clôture infos --}}
            @if($cashSession->statut === 'cloturee')
            <div class="bg-gray-50 rounded-xl border border-gray-200 p-5 space-y-2">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-3">Clôture</h2>
                <div class="flex justify-between text-sm"><span class="text-gray-500 text-xs">Solde réel</span><span class="text-gray-700 font-semibold text-xs">{{ number_format($cashSession->solde_reel, 2, ',', ' ') }} DH</span></div>
                <div class="flex justify-between text-sm"><span class="text-gray-500 text-xs">Solde théorique</span><span class="text-gray-700 text-xs">{{ number_format($cashSession->solde_theorique, 2, ',', ' ') }} DH</span></div>
                <div class="flex justify-between text-sm pt-1 border-t border-gray-200"><span class="text-gray-500 text-xs">Écart</span>{!! $cashSession->ecart_badge !!}</div>
                @if($cashSession->closedBy)
                <p class="text-xs text-gray-400 pt-2">Clôturée par {{ $cashSession->closedBy->name }} à {{ $cashSession->heure_cloture?->format('H:i') }}</p>
                @endif
                @if($cashSession->notes_cloture)
                <p class="text-xs text-gray-600 mt-2 italic">{{ $cashSession->notes_cloture }}</p>
                @endif
            </div>
            @endif

            {{-- Notes ouverture --}}
            @if($cashSession->notes_ouverture)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-xs font-semibold text-gray-500 uppercase mb-2">Notes d'ouverture</h2>
                <p class="text-sm text-gray-700">{{ $cashSession->notes_ouverture }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
