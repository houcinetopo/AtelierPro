@php $s = $supplier ?? null; @endphp

<div class="space-y-6">
    {{-- Identification --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">1</span>
            Identification
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code</label>
                <input type="text" name="code" value="{{ old('code', $s?->code ?? $code ?? '') }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500" placeholder="Auto si vide">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale <span class="text-red-500">*</span></label>
                <input type="text" name="raison_sociale" required value="{{ old('raison_sociale', $s?->raison_sociale) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Nom de l'entreprise">
                @error('raison_sociale') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                <select name="type" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    @foreach (\App\Models\Supplier::TYPES as $k => $v)
                        <option value="{{ $k }}" {{ old('type', $s?->type ?? 'general') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Nom du contact</label>
                <input type="text" name="nom_contact" value="{{ old('nom_contact', $s?->nom_contact) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $s?->email) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="text" name="telephone" value="{{ old('telephone', $s?->telephone) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone 2</label>
                <input type="text" name="telephone_2" value="{{ old('telephone_2', $s?->telephone_2) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                <input type="text" name="site_web" value="{{ old('site_web', $s?->site_web) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
    </div>

    {{-- Adresse --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">2</span>
            Adresse
        </h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <input type="text" name="adresse" value="{{ old('adresse', $s?->adresse) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                <input type="text" name="code_postal" value="{{ old('code_postal', $s?->code_postal) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
        <div class="mt-4">
            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
            <input type="text" name="ville" value="{{ old('ville', $s?->ville) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 max-w-sm">
        </div>
    </div>

    {{-- Infos légales --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">3</span>
            Informations légales
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <div><label class="block text-sm font-medium text-gray-700 mb-1">ICE</label><input type="text" name="ice" value="{{ old('ice', $s?->ice) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">RC</label><input type="text" name="rc" value="{{ old('rc', $s?->rc) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">IF</label><input type="text" name="if_fiscal" value="{{ old('if_fiscal', $s?->if_fiscal) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">Patente</label><input type="text" name="patente" value="{{ old('patente', $s?->patente) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"></div>
            <div><label class="block text-sm font-medium text-gray-700 mb-1">RIB</label><input type="text" name="rib" value="{{ old('rib', $s?->rib) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"></div>
        </div>
    </div>

    {{-- Conditions commerciales --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-base font-semibold text-gray-800 mb-4 flex items-center gap-2">
            <span class="w-6 h-6 rounded-full bg-primary-100 text-primary-600 text-xs font-bold flex items-center justify-center">4</span>
            Conditions commerciales
        </h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Mode paiement <span class="text-red-500">*</span></label>
                <select name="mode_paiement_defaut" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    @foreach (\App\Models\Supplier::MODES_PAIEMENT as $k => $v)
                        <option value="{{ $k }}" {{ old('mode_paiement_defaut', $s?->mode_paiement_defaut ?? 'cheque') == $k ? 'selected' : '' }}>{{ $v }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Délai paiement (j)</label>
                <input type="number" name="delai_paiement_jours" min="0" value="{{ old('delai_paiement_jours', $s?->delai_paiement_jours ?? 30) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Remise globale (%)</label>
                <input type="number" name="remise_globale" step="0.01" min="0" max="100" value="{{ old('remise_globale', $s?->remise_globale ?? 0) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Délai livraison (j)</label>
                <input type="number" name="delai_livraison_jours" min="0" value="{{ old('delai_livraison_jours', $s?->delai_livraison_jours ?? 3) }}" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
    </div>

    {{-- Notes --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">{{ old('notes', $s?->notes) }}</textarea>
            </div>
            <div class="flex items-center">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="actif" value="0">
                    <input type="checkbox" name="actif" value="1" {{ old('actif', $s?->actif ?? true) ? 'checked' : '' }} class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
                    <span class="text-sm text-gray-700">Fournisseur actif</span>
                </label>
            </div>
        </div>
    </div>

    <div class="flex items-center justify-between pt-2">
        <a href="{{ route('suppliers.index') }}" class="text-sm text-gray-500 hover:text-gray-700">Annuler</a>
        <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
            <i data-lucide="save" class="w-4 h-4"></i> {{ $s ? 'Enregistrer' : 'Créer le fournisseur' }}
        </button>
    </div>
</div>
