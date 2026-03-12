{{-- Formulaire de compte bancaire réutilisable (création + édition) --}}
@php $a = $account ?? null; @endphp

<div class="grid grid-cols-1 md:grid-cols-2 gap-4">
    {{-- Nom de la banque --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Nom de la banque <span class="text-red-500">*</span></label>
        <select name="nom_banque" required
                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            <option value="">— Sélectionner —</option>
            @foreach([
                'Attijariwafa Bank', 'BMCE Bank (Bank of Africa)', 'Banque Populaire',
                'BMCI', 'Société Générale Maroc', 'Crédit du Maroc', 'CIH Bank',
                'CDG Capital', 'Al Barid Bank', 'CFG Bank', 'Bank Assafa',
                'Umnia Bank', 'BTI Bank', 'Autre'
            ] as $bank)
                <option value="{{ $bank }}" {{ old('nom_banque', $a?->nom_banque) == $bank ? 'selected' : '' }}>{{ $bank }}</option>
            @endforeach
        </select>
        @error('nom_banque') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- N° de compte --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">N° de compte</label>
        <input type="text" name="numero_compte" value="{{ old('numero_compte', $a?->numero_compte) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
               placeholder="0011223344556677">
    </div>

    {{-- RIB --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">RIB complet</label>
        <input type="text" name="rib" value="{{ old('rib', $a?->rib) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
               placeholder="001 810 0000012345678901 23" maxlength="30">
        @error('rib') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- SWIFT --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Code SWIFT</label>
        <input type="text" name="code_swift" value="{{ old('code_swift', $a?->code_swift) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono uppercase"
               placeholder="BCMAMAMC" maxlength="15">
    </div>

    {{-- IBAN --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">IBAN</label>
        <input type="text" name="iban" value="{{ old('iban', $a?->iban) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
               placeholder="MA64 0011 1111 0001 2345 6789 0123" maxlength="40">
    </div>

    {{-- Agence --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Agence</label>
        <input type="text" name="agence" value="{{ old('agence', $a?->agence) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Nom de l'agence">
    </div>

    {{-- Ville agence --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Ville de l'agence</label>
        <input type="text" name="ville_agence" value="{{ old('ville_agence', $a?->ville_agence) }}"
               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
               placeholder="Casablanca">
    </div>

    {{-- Compte par défaut --}}
    <div class="flex items-end">
        <label class="flex items-center gap-3 cursor-pointer pb-2.5">
            <input type="hidden" name="is_default" value="0">
            <input type="checkbox" name="is_default" value="1"
                   {{ old('is_default', $a?->is_default) ? 'checked' : '' }}
                   class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500">
            <span class="text-sm text-gray-700">Compte par défaut</span>
        </label>
    </div>
</div>
