{{-- Formulaire ordre de réparation (création + édition) --}}
@php $o = $repairOrder ?? null; @endphp

<div x-data="repairOrderForm()" class="space-y-6">

    {{-- ═══════ ÉTAPE 1 : CLIENT + VÉHICULE ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">1</span>
            Client & Véhicule
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                <select name="client_id" required x-model="clientId" @change="loadVehicles()"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Sélectionner un client —</option>
                    @foreach ($clients as $client)
                        <option value="{{ $client->id }}"
                            {{ old('client_id', $o?->client_id ?? $selectedClient?->id) == $client->id ? 'selected' : '' }}>
                            {{ $client->nom_complet ?? $client->raison_sociale }} — {{ $client->telephone }}
                        </option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span class="text-red-500">*</span></label>
                <select name="vehicle_id" required x-model="vehicleId"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Sélectionner un véhicule —</option>
                    <template x-for="v in vehiclesList" :key="v.id">
                        <option :value="v.id" :selected="v.id == vehicleId"
                                x-text="v.immatriculation + ' — ' + v.marque + ' ' + v.modele + (v.couleur ? ' (' + v.couleur + ')' : '')">
                        </option>
                    </template>
                </select>
                @error('vehicle_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>
    </div>

    {{-- ═══════ ÉTAPE 2 : DÉTAILS ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">2</span>
            Détails de l'intervention
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Technicien assigné</label>
                <select name="technicien_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Non assigné —</option>
                    @foreach ($techniciens as $tech)
                        <option value="{{ $tech->id }}" {{ old('technicien_id', $o?->technicien_id) == $tech->id ? 'selected' : '' }}>{{ $tech->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de réception <span class="text-red-500">*</span></label>
                <input type="date" name="date_reception" required
                       value="{{ old('date_reception', $o?->date_reception?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date livraison prévue</label>
                <input type="date" name="date_prevue_livraison"
                       value="{{ old('date_prevue_livraison', $o?->date_prevue_livraison?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description de la panne <span class="text-red-500">*</span></label>
                <textarea name="description_panne" rows="3" required
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Décrire le problème signalé par le client...">{{ old('description_panne', $o?->description_panne) }}</textarea>
                @error('description_panne') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Diagnostic</label>
                <textarea name="diagnostic" rows="3"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Diagnostic technique...">{{ old('diagnostic', $o?->diagnostic) }}</textarea>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">KM entrée</label>
                <input type="number" name="kilometrage_entree" min="0"
                       value="{{ old('kilometrage_entree', $o?->kilometrage_entree) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: 85000">
            </div>
            @if($o)
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">KM sortie</label>
                <input type="number" name="kilometrage_sortie" min="0"
                       value="{{ old('kilometrage_sortie', $o?->kilometrage_sortie) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            @endif
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Niveau carburant</label>
                <select name="niveau_carburant" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">—</option>
                    @foreach (\App\Models\RepairOrder::NIVEAUX_CARBURANT as $niv)
                        <option value="{{ $niv }}" {{ old('niveau_carburant', $o?->niveau_carburant) == $niv ? 'selected' : '' }}>{{ ucfirst($niv) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Source</label>
                <select name="source_ordre" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach (\App\Models\RepairOrder::SOURCES as $key => $label)
                        <option value="{{ $key }}" {{ old('source_ordre', $o?->source_ordre ?? 'direct') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Observations (client)</label>
                <textarea name="observations" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Observations visibles par le client...">{{ old('observations', $o?->observations) }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Notes internes
                    <span class="text-xs text-gray-400 ml-1">(non visibles par le client)</span>
                </label>
                <textarea name="notes_internes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Notes privées...">{{ old('notes_internes', $o?->notes_internes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════ ÉTAPE 3 : LIGNES DE TRAVAUX ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">3</span>
                Travaux & Pièces
            </h2>
            <button type="button" @click="addItem()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Ajouter une ligne
            </button>
        </div>

        <template x-if="items.length === 0">
            <div class="text-center py-6 text-gray-400">
                <i data-lucide="package" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                <p class="text-sm">Aucune ligne ajoutée</p>
                <button type="button" @click="addItem()" class="mt-2 text-primary-600 text-xs hover:underline">Ajouter une première ligne</button>
            </div>
        </template>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="border border-gray-200 rounded-lg p-4 relative bg-gray-50/50">
                    <button type="button" @click="removeItem(index)"
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 text-xs">
                        ×
                    </button>

                    <div class="grid grid-cols-12 gap-3">
                        {{-- Type --}}
                        <div class="col-span-12 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Type</label>
                            <select :name="`items[${index}][type]`" x-model="item.type"
                                    class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                                @foreach (\App\Models\RepairOrderItem::TYPES as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Désignation --}}
                        <div class="col-span-12 sm:col-span-4">
                            <label class="block text-xs text-gray-500 mb-1">Désignation <span class="text-red-500">*</span></label>
                            <input type="text" :name="`items[${index}][designation]`" x-model="item.designation" required
                                   class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500"
                                   placeholder="Description du travail ou pièce">
                        </div>

                        {{-- Référence --}}
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Référence</label>
                            <input type="text" :name="`items[${index}][reference]`" x-model="item.reference"
                                   class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500"
                                   placeholder="Réf.">
                        </div>

                        {{-- Quantité --}}
                        <div class="col-span-3 sm:col-span-1">
                            <label class="block text-xs text-gray-500 mb-1">Qté</label>
                            <input type="number" :name="`items[${index}][quantite]`" x-model.number="item.quantite"
                                   step="0.01" min="0.01"
                                   class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                        </div>

                        {{-- Unité --}}
                        <div class="col-span-3 sm:col-span-1">
                            <label class="block text-xs text-gray-500 mb-1">Unité</label>
                            <select :name="`items[${index}][unite]`" x-model="item.unite"
                                    class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                                @foreach (\App\Models\RepairOrderItem::UNITES as $key => $label)
                                    <option value="{{ $key }}">{{ $key }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Prix unitaire --}}
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">P.U. (DH)</label>
                            <input type="number" :name="`items[${index}][prix_unitaire]`" x-model.number="item.prix_unitaire"
                                   step="0.01" min="0"
                                   class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                        </div>
                    </div>

                    {{-- Montant calculé --}}
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="flex items-center gap-1">
                                <label class="text-xs text-gray-500">Remise %</label>
                                <input type="number" :name="`items[${index}][remise]`" x-model.number="item.remise"
                                       step="0.01" min="0" max="100"
                                       class="w-16 px-2 py-1 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-primary-500">
                            </div>
                            <input type="hidden" :name="`items[${index}][taux_tva]`" value="20">
                        </div>
                        <p class="text-sm font-semibold text-gray-700">
                            HT : <span x-text="itemTotal(item).toLocaleString('fr-MA', {minimumFractionDigits: 2})">0,00</span> DH
                        </p>
                    </div>
                </div>
            </template>
        </div>

        {{-- Totaux --}}
        <template x-if="items.length > 0">
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-col items-end gap-1.5 text-sm">
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500">Total HT</span>
                        <span class="font-semibold text-gray-700 w-28 text-right"
                              x-text="totalHT().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500">TVA (20%)</span>
                        <span class="text-gray-600 w-28 text-right"
                              x-text="totalTVA().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1">
                            <span class="text-gray-500">Remise globale</span>
                            <input type="number" name="remise_globale" x-model.number="remiseGlobale" step="0.01" min="0"
                                   class="w-24 px-2 py-1 border border-gray-300 rounded text-xs text-right focus:ring-1 focus:ring-primary-500">
                        </div>
                        <span class="text-red-500 w-28 text-right"
                              x-text="remiseGlobale > 0 ? '-' + remiseGlobale.toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH' : '—'"></span>
                    </div>
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-300">
                        <span class="font-semibold text-gray-700">Net à payer</span>
                        <span class="text-lg font-bold text-primary-600 w-28 text-right"
                              x-text="netAPayer().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                </div>
                <input type="hidden" name="taux_tva" value="20">
            </div>
        </template>
    </div>

    {{-- ═══════ ÉTAPE 4 : PHOTOS ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">4</span>
            Photos
            <span class="text-xs text-gray-400 font-normal">(optionnel)</span>
        </h2>

        {{-- Photos existantes (édition) --}}
        @if($o && $o->photos->count() > 0)
        <div class="mb-4">
            <p class="text-xs text-gray-500 mb-2">Photos existantes :</p>
            <div class="flex flex-wrap gap-3">
                @foreach($o->photos as $photo)
                <div class="relative group">
                    <img src="{{ $photo->url }}" alt="" class="w-20 h-20 object-cover rounded-lg border border-gray-200">
                    <span class="absolute bottom-0 left-0 right-0 bg-black/50 text-white text-center text-xs py-0.5 rounded-b-lg">{{ $photo->moment }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach(['avant' => 'Avant réparation', 'pendant' => 'Pendant', 'apres' => 'Après réparation'] as $moment => $label)
            <div class="border border-dashed border-gray-300 rounded-lg p-3 text-center">
                <p class="text-xs font-medium text-gray-600 mb-2">{{ $label }}</p>
                <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple
                       class="w-full text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0
                              file:text-xs file:bg-gray-100 file:text-gray-700 cursor-pointer">
                <input type="hidden" name="photo_moments[]" value="{{ $moment }}">
                <input type="hidden" name="photo_captions[]" value="">
            </div>
            @endforeach
        </div>
        <p class="text-xs text-gray-400 mt-2">JPG, PNG, WebP — Max 5 Mo par photo</p>
    </div>

    {{-- ═══════ BOUTONS ═══════ --}}
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('repair-orders.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $o ? 'Enregistrer les modifications' : 'Créer l\'ordre de réparation' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function repairOrderForm() {
    const existingItems = @json(old('items', $o?->items?->map(fn($i) => [
        'type' => $i->type,
        'designation' => $i->designation,
        'reference' => $i->reference,
        'quantite' => (float) $i->quantite,
        'unite' => $i->unite,
        'prix_unitaire' => (float) $i->prix_unitaire,
        'remise' => (float) $i->remise,
    ])->toArray() ?? []));

    const existingVehicles = @json($vehicles ?? []);

    return {
        clientId: '{{ old('client_id', $o?->client_id ?? $selectedClient?->id ?? '') }}',
        vehicleId: '{{ old('vehicle_id', $o?->vehicle_id ?? '') }}',
        vehiclesList: existingVehicles,
        remiseGlobale: {{ old('remise_globale', $o?->remise_globale ?? 0) }},
        items: existingItems.length > 0 ? existingItems : [],

        addItem() {
            this.items.push({
                type: 'main_oeuvre',
                designation: '',
                reference: '',
                quantite: 1,
                unite: 'u',
                prix_unitaire: 0,
                remise: 0,
            });
        },

        removeItem(index) {
            this.items.splice(index, 1);
        },

        itemTotal(item) {
            let ht = (item.quantite || 0) * (item.prix_unitaire || 0);
            if (item.remise > 0) ht -= ht * item.remise / 100;
            return Math.round(ht * 100) / 100;
        },

        totalHT() {
            return this.items.reduce((sum, item) => sum + this.itemTotal(item), 0);
        },

        totalTVA() {
            return Math.round(this.totalHT() * 20 / 100 * 100) / 100;
        },

        netAPayer() {
            return Math.max(0, this.totalHT() + this.totalTVA() - (this.remiseGlobale || 0));
        },

        async loadVehicles() {
            this.vehicleId = '';
            this.vehiclesList = [];
            if (!this.clientId) return;

            try {
                const res = await fetch(`{{ url('/repair-orders/vehicles-by-client') }}?client_id=${this.clientId}`);
                this.vehiclesList = await res.json();
            } catch (e) {
                console.error('Erreur chargement véhicules:', e);
            }
        },
    };
}
</script>
@endpush
