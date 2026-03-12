{{-- Formulaire facture (création + édition) --}}
@php $inv = $invoice ?? null; @endphp

<div x-data="invoiceForm()" class="space-y-6">

    {{-- ═══════ ORDRE SOURCE (seulement en création) ═══════ --}}
    @if(!$inv)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">1</span>
            Source
        </h2>

        @if(isset($repairOrder) && $repairOrder)
            <input type="hidden" name="repair_order_id" value="{{ $repairOrder->id }}">
            <input type="hidden" name="client_id" value="{{ $repairOrder->client_id }}">
            <input type="hidden" name="vehicle_id" value="{{ $repairOrder->vehicle_id }}">
            @if($repairOrder->deliveryNote)
                <input type="hidden" name="delivery_note_id" value="{{ $repairOrder->deliveryNote->id }}">
            @endif
            <div class="flex items-center gap-4 p-4 bg-primary-50 rounded-lg border border-primary-200">
                <i data-lucide="clipboard-check" class="w-8 h-8 text-primary-500"></i>
                <div>
                    <p class="font-mono font-semibold text-primary-700">{{ $repairOrder->numero }}</p>
                    <p class="text-sm text-primary-600">
                        {{ $repairOrder->client?->nom_complet ?? $repairOrder->client?->raison_sociale }} —
                        {{ $repairOrder->vehicle?->marque }} {{ $repairOrder->vehicle?->modele }}
                        ({{ $repairOrder->vehicle?->immatriculation }})
                    </p>
                    <p class="text-xs text-primary-500 mt-0.5">Total : {{ number_format($repairOrder->net_a_payer, 2, ',', ' ') }} DH</p>
                </div>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Depuis un Ordre de Réparation</label>
                    <select name="repair_order_id" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">— Facture libre (sans OR) —</option>
                        @foreach ($eligibleOrders as $order)
                            <option value="{{ $order->id }}" {{ old('repair_order_id') == $order->id ? 'selected' : '' }}>
                                {{ $order->numero }} — {{ $order->client?->nom_complet ?? $order->client?->raison_sociale }}
                                ({{ number_format($order->net_a_payer, 2, ',', ' ') }} DH)
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Client <span class="text-red-500">*</span></label>
                    <select name="client_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                        <option value="">— Sélectionner —</option>
                        @foreach ($clients as $client)
                            <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>
                                {{ $client->nom_complet ?? $client->raison_sociale }} — {{ $client->telephone }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        @endif
    </div>
    @endif

    {{-- ═══════ DATES & INFOS ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $inv ? '1' : '2' }}</span>
            Informations
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date facture <span class="text-red-500">*</span></label>
                <input type="date" name="date_facture" required value="{{ old('date_facture', $inv?->date_facture?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Échéance</label>
                <input type="date" name="date_echeance" value="{{ old('date_echeance', $inv?->date_echeance?->format('Y-m-d') ?? now()->addDays(30)->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Objet</label>
                <input type="text" name="objet" value="{{ old('objet', $inv?->objet ?? (isset($repairOrder) ? 'Réparation véhicule — OR '.$repairOrder->numero : '')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Objet de la facture">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Conditions de paiement</label>
                <textarea name="conditions_paiement" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">{{ old('conditions_paiement', $inv?->conditions_paiement ?? 'Paiement à 30 jours.') }}</textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">{{ old('notes', $inv?->notes) }}</textarea>
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Mentions légales</label>
            <textarea name="mentions_legales" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm text-gray-600 focus:ring-2 focus:ring-primary-500">{{ old('mentions_legales', $inv?->mentions_legales ?? App\Models\Invoice::MENTIONS_LEGALES_DEFAULT) }}</textarea>
        </div>
    </div>

    {{-- ═══════ LIGNES ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-base font-semibold text-gray-800 flex items-center gap-2">
                <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $inv ? '2' : '3' }}</span>
                Lignes de la facture
            </h2>
            <button type="button" @click="addItem()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg">
                <i data-lucide="plus" class="w-3.5 h-3.5"></i> Ajouter
            </button>
        </div>

        <template x-if="items.length === 0">
            <div class="text-center py-6 text-gray-400">
                <p class="text-sm">Aucune ligne</p>
                <button type="button" @click="addItem()" class="mt-2 text-primary-600 text-xs hover:underline">Ajouter une ligne</button>
            </div>
        </template>

        <div class="space-y-3">
            <template x-for="(item, index) in items" :key="index">
                <div class="border border-gray-200 rounded-lg p-4 relative bg-gray-50/50">
                    <button type="button" @click="removeItem(index)" class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center hover:bg-red-600 text-xs">×</button>
                    <div class="grid grid-cols-12 gap-3">
                        <div class="col-span-12 sm:col-span-2">
                            <select :name="`items[${index}][type]`" x-model="item.type" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs">
                                @foreach (\App\Models\InvoiceItem::TYPES as $key => $label) <option value="{{ $key }}">{{ $label }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-span-12 sm:col-span-4">
                            <input type="text" :name="`items[${index}][designation]`" x-model="item.designation" required class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs" placeholder="Désignation *">
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <input type="text" :name="`items[${index}][reference]`" x-model="item.reference" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs" placeholder="Réf.">
                        </div>
                        <div class="col-span-3 sm:col-span-1">
                            <input type="number" :name="`items[${index}][quantite]`" x-model.number="item.quantite" step="0.01" min="0.01" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs">
                        </div>
                        <div class="col-span-3 sm:col-span-1">
                            <select :name="`items[${index}][unite]`" x-model="item.unite" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs">
                                @foreach (\App\Models\InvoiceItem::UNITES as $key => $label) <option value="{{ $key }}">{{ $key }}</option> @endforeach
                            </select>
                        </div>
                        <div class="col-span-6 sm:col-span-2">
                            <input type="number" :name="`items[${index}][prix_unitaire]`" x-model.number="item.prix_unitaire" step="0.01" min="0" class="w-full px-2 py-2 border border-gray-300 rounded-lg text-xs" placeholder="P.U.">
                        </div>
                    </div>
                    <div class="mt-2 flex items-center justify-between">
                        <div class="flex items-center gap-1">
                            <label class="text-xs text-gray-500">Remise %</label>
                            <input type="number" :name="`items[${index}][remise]`" x-model.number="item.remise" step="0.01" min="0" max="100" class="w-16 px-2 py-1 border border-gray-300 rounded text-xs">
                        </div>
                        <input type="hidden" :name="`items[${index}][taux_tva]`" value="20">
                        <p class="text-sm font-semibold text-gray-700">HT : <span x-text="itemTotal(item).toLocaleString('fr-MA', {minimumFractionDigits: 2})">0</span> DH</p>
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
                        <span class="text-gray-500">Remise globale</span>
                        <input type="number" name="remise_globale" x-model.number="remiseGlobale" step="0.01" min="0" class="w-24 px-2 py-1 border border-gray-300 rounded text-xs text-right">
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

    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('invoices.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $inv ? 'Enregistrer' : 'Créer la facture' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function invoiceForm() {
    const existingItems = @json(old('items', $inv?->items?->map(fn($i) => [
        'type' => $i->type, 'designation' => $i->designation, 'reference' => $i->reference,
        'quantite' => (float) $i->quantite, 'unite' => $i->unite,
        'prix_unitaire' => (float) $i->prix_unitaire, 'remise' => (float) $i->remise,
    ])->toArray() ?? (isset($repairOrder) ? $repairOrder->items->map(fn($i) => [
        'type' => $i->type, 'designation' => $i->designation, 'reference' => $i->reference,
        'quantite' => (float) $i->quantite, 'unite' => $i->unite,
        'prix_unitaire' => (float) $i->prix_unitaire, 'remise' => (float) $i->remise,
    ])->toArray() : [])));

    return {
        remiseGlobale: {{ old('remise_globale', $inv?->remise_globale ?? (isset($repairOrder) ? $repairOrder->remise_globale : 0)) }},
        items: existingItems,

        addItem() { this.items.push({ type: 'main_oeuvre', designation: '', reference: '', quantite: 1, unite: 'u', prix_unitaire: 0, remise: 0 }); },
        removeItem(index) { this.items.splice(index, 1); },
        itemTotal(item) {
            let ht = (item.quantite || 0) * (item.prix_unitaire || 0);
            if (item.remise > 0) ht -= ht * item.remise / 100;
            return Math.round(ht * 100) / 100;
        },
        totalHT() { return this.items.reduce((s, i) => s + this.itemTotal(i), 0); },
        totalTVA() { return Math.round(this.totalHT() * 20 / 100 * 100) / 100; },
        netAPayer() { return Math.max(0, this.totalHT() + this.totalTVA() - (this.remiseGlobale || 0)); },
    };
}
</script>
@endpush
