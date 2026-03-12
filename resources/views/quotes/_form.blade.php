{{-- Formulaire devis (création + édition) --}}
@php $q = $quote ?? null; @endphp

<div x-data="quoteForm()" class="space-y-6">

    {{-- ═══════ CLIENT + VÉHICULE ═══════ --}}
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
                            {{ old('client_id', $q?->client_id ?? $selectedClient?->id ?? '') == $client->id ? 'selected' : '' }}>
                            {{ $client->nom_complet ?? $client->raison_sociale }} — {{ $client->telephone }}
                        </option>
                    @endforeach
                </select>
                @error('client_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Véhicule <span class="text-xs text-gray-400">(optionnel)</span></label>
                <select name="vehicle_id" x-model="vehicleId"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Aucun véhicule —</option>
                    <template x-for="v in vehiclesList" :key="v.id">
                        <option :value="v.id" :selected="v.id == vehicleId"
                                x-text="v.immatriculation + ' — ' + v.marque + ' ' + (v.modele || '')"></option>
                    </template>
                </select>
            </div>
        </div>
    </div>

    {{-- ═══════ DATES & DÉTAILS ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">2</span>
            Informations du devis
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date du devis <span class="text-red-500">*</span></label>
                <input type="date" name="date_devis" required
                       value="{{ old('date_devis', $q?->date_devis?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Valide jusqu'au <span class="text-red-500">*</span></label>
                <input type="date" name="date_validite" required
                       value="{{ old('date_validite', $q?->date_validite?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Durée estimée (jours)</label>
                <input type="number" name="duree_estimee_jours" min="1"
                       value="{{ old('duree_estimee_jours', $q?->duree_estimee_jours) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: 5">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Description des travaux</label>
            <textarea name="description_travaux" rows="3"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Description générale des travaux proposés...">{{ old('description_travaux', $q?->description_travaux) }}</textarea>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conditions de paiement</label>
                <textarea name="conditions" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Ex: 50% à la commande, 50% à la livraison">{{ old('conditions', $q?->conditions ?? 'Paiement à la livraison du véhicule.') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Notes internes ou pour le client...">{{ old('notes', $q?->notes) }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════ LIGNES ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">3</span>
                Lignes du devis
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
                            class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 text-xs">×</button>
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Type</label>
                            <select :name="`items[${index}][type]`" x-model="item.type" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                                @foreach (\App\Models\QuoteItem::TYPES as $key => $label) <option value="{{ $key }}">{{ $label }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <label class="block text-xs text-gray-500 mb-1">Désignation <span class="text-red-500">*</span></label>
                            <input type="text" :name="`items[${index}][designation]`" x-model="item.designation" required class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500" placeholder="Description">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">Référence</label>
                            <input type="text" :name="`items[${index}][reference]`" x-model="item.reference" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500" placeholder="Réf.">
                        </div>
                        <div class="col-span-3 sm:col-span-1">
                            <label class="block text-xs text-gray-500 mb-1">Qté</label>
                            <input type="number" :name="`items[${index}][quantite]`" x-model.number="item.quantite" step="0.01" min="0.01" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                        </div>
                        <div class="col-span-3 sm:col-span-1">
                            <label class="block text-xs text-gray-500 mb-1">Unité</label>
                            <select :name="`items[${index}][unite]`" x-model="item.unite" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                                @foreach (\App\Models\QuoteItem::UNITES as $key => $label) <option value="{{ $key }}">{{ $key }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <label class="block text-xs text-gray-500 mb-1">P.U. (DH)</label>
                            <input type="number" :name="`items[${index}][prix_unitaire]`" x-model.number="item.prix_unitaire" step="0.01" min="0" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs focus:ring-1 focus:ring-primary-500">
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <label class="text-xs text-gray-500">Remise %</label>
                            <input type="number" :name="`items[${index}][remise]`" x-model.number="item.remise" step="0.01" min="0" max="100" class="w-16 px-2 py-1 border border-gray-300 rounded text-xs focus:ring-1 focus:ring-primary-500">
                        </div>
                        <input type="hidden" :name="`items[${index}][taux_tva]`" value="20">
                        <p class="text-sm font-semibold text-gray-700">HT : <span x-text="itemTotal(item).toLocaleString('fr-MA', {minimumFractionDigits: 2})">0,00</span> DH</p>
                    </div>
                </div>
            </template>
        </div>

        <template x-if="items.length > 0">
            <div class="mt-4 pt-4 border-t border-gray-200">
                <div class="flex flex-col items-end gap-1.5 text-sm">
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500">Total HT</span>
                        <span class="font-semibold text-gray-700 w-28 text-right" x-text="totalHT().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <span class="text-gray-500">TVA (20%)</span>
                        <span class="text-gray-600 w-28 text-right" x-text="totalTVA().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1">
                            <span class="text-gray-500">Remise globale</span>
                            <input type="number" name="remise_globale" x-model.number="remiseGlobale" step="0.01" min="0" class="w-24 px-2 py-1 border border-gray-300 rounded text-xs text-right focus:ring-1 focus:ring-primary-500">
                        </div>
                        <span class="text-red-500 w-28 text-right" x-text="remiseGlobale > 0 ? '-' + remiseGlobale.toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH' : '—'"></span>
                    </div>
                    <div class="flex items-center gap-4 pt-2 border-t border-gray-300">
                        <span class="font-semibold text-gray-700">Net à payer</span>
                        <span class="text-lg font-bold text-primary-600 w-28 text-right" x-text="netAPayer().toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></span>
                    </div>
                </div>
                <input type="hidden" name="taux_tva" value="20">
            </div>
        </template>
    </div>

    {{-- Boutons --}}
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('quotes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $q ? 'Enregistrer les modifications' : 'Créer le devis' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function quoteForm() {
    const existingItems = @json(old('items', $q?->items?->map(fn($i) => [
        'type' => $i->type, 'designation' => $i->designation, 'reference' => $i->reference,
        'quantite' => (float) $i->quantite, 'unite' => $i->unite,
        'prix_unitaire' => (float) $i->prix_unitaire, 'remise' => (float) $i->remise,
    ])->toArray() ?? []));
    const existingVehicles = @json($vehicles ?? []);

    return {
        clientId: '{{ old('client_id', $q?->client_id ?? $selectedClient?->id ?? '') }}',
        vehicleId: '{{ old('vehicle_id', $q?->vehicle_id ?? '') }}',
        vehiclesList: existingVehicles,
        remiseGlobale: {{ old('remise_globale', $q?->remise_globale ?? 0) }},
        items: existingItems.length > 0 ? existingItems : [],

        addItem() {
            this.items.push({ type: 'main_oeuvre', designation: '', reference: '', quantite: 1, unite: 'u', prix_unitaire: 0, remise: 0 });
        },
        removeItem(index) { this.items.splice(index, 1); },
        itemTotal(item) {
            let ht = (item.quantite || 0) * (item.prix_unitaire || 0);
            if (item.remise > 0) ht -= ht * item.remise / 100;
            return Math.round(ht * 100) / 100;
        },
        totalHT() { return this.items.reduce((sum, item) => sum + this.itemTotal(item), 0); },
        totalTVA() { return Math.round(this.totalHT() * 20 / 100 * 100) / 100; },
        netAPayer() { return Math.max(0, this.totalHT() + this.totalTVA() - (this.remiseGlobale || 0)); },
        async loadVehicles() {
            this.vehicleId = '';
            this.vehiclesList = [];
            if (!this.clientId) return;
            try {
                const res = await fetch(`{{ url('/quotes/vehicles-by-client') }}?client_id=${this.clientId}`);
                this.vehiclesList = await res.json();
            } catch (e) { console.error('Erreur:', e); }
        },
    };
}
</script>
@endpush
