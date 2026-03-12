{{-- Formulaire bon de livraison (création + édition) --}}
@php $bl = $deliveryNote ?? null; @endphp

<div class="space-y-6" x-data="deliveryNoteForm()">

    {{-- ═══════ ORDRE DE RÉPARATION ═══════ --}}
    @if(!$bl)
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">1</span>
            Ordre de Réparation source
        </h2>

        @if(isset($repairOrder) && $repairOrder)
            {{-- OR pré-sélectionné --}}
            <input type="hidden" name="repair_order_id" value="{{ $repairOrder->id }}">
            <div class="flex items-center gap-4 p-4 bg-primary-50 rounded-lg border border-primary-200">
                <i data-lucide="clipboard-check" class="w-8 h-8 text-primary-500"></i>
                <div>
                    <p class="font-mono font-semibold text-primary-700">{{ $repairOrder->numero }}</p>
                    <p class="text-sm text-primary-600">
                        {{ $repairOrder->client?->nom_complet ?? $repairOrder->client?->raison_sociale }} —
                        {{ $repairOrder->vehicle?->marque }} {{ $repairOrder->vehicle?->modele }}
                        ({{ $repairOrder->vehicle?->immatriculation }})
                    </p>
                    <p class="text-xs text-primary-500 mt-0.5">
                        Total : {{ number_format($repairOrder->net_a_payer, 2, ',', ' ') }} DH
                    </p>
                </div>
            </div>
        @else
            {{-- Sélection de l'OR --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Sélectionner un ordre terminé <span class="text-red-500">*</span></label>
                <select name="repair_order_id" required x-model="selectedOrderId" @change="onOrderSelect()"
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Choisir un ordre —</option>
                    @foreach ($eligibleOrders as $order)
                        <option value="{{ $order->id }}"
                                data-total="{{ $order->net_a_payer }}"
                                data-client="{{ $order->client?->nom_complet ?? $order->client?->raison_sociale }}"
                                data-vehicle="{{ $order->vehicle?->immatriculation }}"
                                {{ old('repair_order_id') == $order->id ? 'selected' : '' }}>
                            {{ $order->numero }} — {{ $order->client?->nom_complet ?? $order->client?->raison_sociale }}
                            — {{ $order->vehicle?->immatriculation }}
                            ({{ number_format($order->net_a_payer, 2, ',', ' ') }} DH)
                        </option>
                    @endforeach
                </select>
                @error('repair_order_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                @if($eligibleOrders->isEmpty())
                    <p class="text-xs text-amber-600 mt-2">
                        <i data-lucide="info" class="w-3.5 h-3.5 inline"></i>
                        Aucun ordre terminé sans BL. Les ordres doivent être en statut "Terminé" ou "Livré".
                    </p>
                @endif
            </div>
        @endif
    </div>
    @endif

    {{-- ═══════ DATE & VÉHICULE ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $bl ? '1' : '2' }}</span>
            Livraison & État véhicule
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Date de livraison <span class="text-red-500">*</span></label>
                <input type="date" name="date_livraison" required
                       value="{{ old('date_livraison', $bl?->date_livraison?->format('Y-m-d') ?? now()->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Heure</label>
                <input type="time" name="heure_livraison"
                       value="{{ old('heure_livraison', $bl?->heure_livraison ?? now()->format('H:i')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">KM sortie</label>
                <input type="number" name="kilometrage_sortie" min="0"
                       value="{{ old('kilometrage_sortie', $bl?->kilometrage_sortie) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Ex: 85200">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Niveau carburant</label>
                <select name="niveau_carburant" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">—</option>
                    @foreach (App\Models\DeliveryNote::NIVEAUX_CARBURANT as $niv)
                        <option value="{{ $niv }}" {{ old('niveau_carburant', $bl?->niveau_carburant) == $niv ? 'selected' : '' }}>{{ ucfirst($niv) }}</option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    {{-- ═══════ TRAVAUX & OBSERVATIONS ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $bl ? '2' : '3' }}</span>
            Travaux & Observations
        </h2>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Résumé des travaux effectués</label>
                <textarea name="travaux_effectues" rows="4"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Liste des travaux réalisés...">{{ old('travaux_effectues', $bl?->travaux_effectues ?? (isset($repairOrder) ? $repairOrder->items->map(fn($i) => "- {$i->designation}")->implode("\n") : '')) }}</textarea>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Observations à la sortie</label>
                    <textarea name="observations_sortie" rows="2"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                              placeholder="État du véhicule à la remise...">{{ old('observations_sortie', $bl?->observations_sortie) }}</textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Réserves du client
                        <span class="text-xs text-gray-400 ml-1">(points non satisfaisants)</span>
                    </label>
                    <textarea name="reserves_client" rows="2"
                              class="w-full px-4 py-2.5 border border-amber-300 rounded-lg text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 bg-amber-50/50"
                              placeholder="Réserves éventuelles émises par le client...">{{ old('reserves_client', $bl?->reserves_client) }}</textarea>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Recommandations au client</label>
                <textarea name="recommandations" rows="2"
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                          placeholder="Recommandations pour l'entretien futur...">{{ old('recommandations', $bl?->recommandations) }}</textarea>
            </div>
        </div>
    </div>

    {{-- ═══════ RÉCEPTIONNAIRE & SIGNATURES ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $bl ? '3' : '4' }}</span>
            Réceptionnaire & Signatures
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du réceptionnaire</label>
                <input type="text" name="nom_receptionnaire"
                       value="{{ old('nom_receptionnaire', $bl?->nom_receptionnaire) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Qui récupère le véhicule ?">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">CIN du réceptionnaire</label>
                <input type="text" name="cin_receptionnaire"
                       value="{{ old('cin_receptionnaire', $bl?->cin_receptionnaire) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
                       placeholder="AB123456">
            </div>
        </div>
        <div class="grid grid-cols-2 gap-4 mt-4">
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="signe_atelier" value="0">
                <input type="checkbox" name="signe_atelier" value="1"
                       {{ old('signe_atelier', $bl?->signe_atelier) ? 'checked' : '' }}
                       class="rounded text-primary-600 focus:ring-primary-500">
                <div>
                    <p class="text-sm font-medium text-gray-700">Signé par l'atelier</p>
                    <p class="text-xs text-gray-400">Responsable atelier</p>
                </div>
            </label>
            <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-200 cursor-pointer hover:bg-gray-50">
                <input type="hidden" name="signe_client" value="0">
                <input type="checkbox" name="signe_client" value="1"
                       {{ old('signe_client', $bl?->signe_client) ? 'checked' : '' }}
                       class="rounded text-primary-600 focus:ring-primary-500">
                <div>
                    <p class="text-sm font-medium text-gray-700">Signé par le client</p>
                    <p class="text-xs text-gray-400">Réceptionnaire</p>
                </div>
            </label>
        </div>
    </div>

    {{-- ═══════ PAIEMENT ═══════ --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">{{ $bl ? '4' : '5' }}</span>
            Paiement
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Total TTC (DH)</label>
                <p class="px-4 py-2.5 bg-gray-100 rounded-lg text-sm font-semibold text-gray-700 font-mono">
                    <span x-text="totalTtc.toLocaleString('fr-MA', {minimumFractionDigits: 2})">{{ number_format($bl?->total_ttc ?? (isset($repairOrder) ? $repairOrder->net_a_payer : 0), 2, ',', ' ') }}</span> DH
                </p>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Montant payé (DH)</label>
                <input type="number" name="montant_paye" step="0.01" min="0" x-model.number="montantPaye"
                       value="{{ old('montant_paye', $bl?->montant_paye ?? 0) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode de paiement</label>
                <select name="mode_paiement" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">—</option>
                    @foreach (App\Models\DeliveryNote::MODES_PAIEMENT as $key => $label)
                        <option value="{{ $key }}" {{ old('mode_paiement', $bl?->mode_paiement) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        {{-- Reste à payer dynamique --}}
        <div class="mt-3 p-3 rounded-lg" :class="resteAPayer > 0 ? 'bg-red-50' : 'bg-green-50'">
            <div class="flex items-center justify-between">
                <span class="text-sm font-medium" :class="resteAPayer > 0 ? 'text-red-700' : 'text-green-700'">Reste à payer</span>
                <span class="text-lg font-bold" :class="resteAPayer > 0 ? 'text-red-600' : 'text-green-600'"
                      x-text="resteAPayer > 0 ? resteAPayer.toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH' : 'Soldé'"></span>
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="2"
                  class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                  placeholder="Notes additionnelles...">{{ old('notes', $bl?->notes) }}</textarea>
    </div>

    {{-- Boutons --}}
    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('delivery-notes.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit"
                class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i>
            {{ $bl ? 'Enregistrer les modifications' : 'Créer le bon de livraison' }}
        </button>
    </div>
</div>

@push('scripts')
<script>
function deliveryNoteForm() {
    return {
        totalTtc: {{ $bl?->total_ttc ?? (isset($repairOrder) ? $repairOrder->net_a_payer : 0) }},
        montantPaye: {{ old('montant_paye', $bl?->montant_paye ?? 0) }},
        selectedOrderId: '{{ old('repair_order_id', '') }}',

        get resteAPayer() {
            return Math.max(0, this.totalTtc - (this.montantPaye || 0));
        },

        onOrderSelect() {
            const select = document.querySelector('select[name="repair_order_id"]');
            if (!select) return;
            const option = select.options[select.selectedIndex];
            if (option && option.dataset.total) {
                this.totalTtc = parseFloat(option.dataset.total) || 0;
            }
        },
    };
}
</script>
@endpush
