{{-- Formulaire client réutilisable --}}
@php $c = $client ?? null; @endphp

<div x-data="{ type: '{{ old('type_client', $c?->type_client ?? 'particulier') }}' }" class="space-y-5">

    {{-- Toggle type client --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Type de client <span class="text-red-500">*</span></label>
        <div class="flex gap-3">
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="type_client" value="particulier" x-model="type" class="sr-only peer">
                <div class="p-3 rounded-lg border-2 text-center transition-all
                            peer-checked:border-blue-500 peer-checked:bg-blue-50 border-gray-200 hover:border-gray-300">
                    <i data-lucide="user" class="w-5 h-5 mx-auto mb-1 text-blue-500"></i>
                    <p class="text-sm font-medium text-gray-700">Particulier</p>
                </div>
            </label>
            <label class="flex-1 cursor-pointer">
                <input type="radio" name="type_client" value="societe" x-model="type" class="sr-only peer">
                <div class="p-3 rounded-lg border-2 text-center transition-all
                            peer-checked:border-purple-500 peer-checked:bg-purple-50 border-gray-200 hover:border-gray-300">
                    <i data-lucide="building" class="w-5 h-5 mx-auto mb-1 text-purple-500"></i>
                    <p class="text-sm font-medium text-gray-700">Société</p>
                </div>
            </label>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Champs Particulier --}}
        <div x-show="type === 'particulier'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
            <input type="text" name="nom_complet" value="{{ old('nom_complet', $c?->nom_complet) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                   placeholder="Prénom et nom" :required="type === 'particulier'">
            @error('nom_complet') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div x-show="type === 'particulier'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">CIN</label>
            <input type="text" name="cin" value="{{ old('cin', $c?->cin) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500" placeholder="AB123456">
        </div>

        {{-- Champs Société --}}
        <div x-show="type === 'societe'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Raison sociale <span class="text-red-500">*</span></label>
            <input type="text" name="raison_sociale" value="{{ old('raison_sociale', $c?->raison_sociale) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500"
                   placeholder="Nom de l'entreprise" :required="type === 'societe'">
            @error('raison_sociale') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div x-show="type === 'societe'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">ICE</label>
            <input type="text" name="ice" value="{{ old('ice', $c?->ice) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500"
                   placeholder="001234567000012" maxlength="20">
        </div>
        <div x-show="type === 'societe'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Registre de Commerce</label>
            <input type="text" name="registre_commerce" value="{{ old('registre_commerce', $c?->registre_commerce) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="RC">
        </div>
        <div x-show="type === 'societe'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Contact principal</label>
            <input type="text" name="contact_societe" value="{{ old('contact_societe', $c?->contact_societe) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Nom du contact">
        </div>
    </div>

    {{-- Contact --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone principal</label>
            <input type="text" name="telephone" value="{{ old('telephone', $c?->telephone) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="06XXXXXXXX">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone 2</label>
            <input type="text" name="telephone_2" value="{{ old('telephone_2', $c?->telephone_2) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Optionnel">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
            <input type="email" name="email" value="{{ old('email', $c?->email) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="client@email.com">
        </div>
    </div>

    {{-- Adresse --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <input type="text" name="adresse" value="{{ old('adresse', $c?->adresse) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Rue, quartier...">
        </div>
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                <input type="text" name="ville" value="{{ old('ville', $c?->ville) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Casablanca">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">C.P.</label>
                <input type="text" name="code_postal" value="{{ old('code_postal', $c?->code_postal) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="20000">
            </div>
        </div>
    </div>

    {{-- Financier + Source --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Plafond crédit (DH)</label>
            <input type="number" name="plafond_credit" value="{{ old('plafond_credit', $c?->plafond_credit) }}" step="0.01" min="0"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Optionnel">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Source <span class="text-red-500">*</span></label>
            <select name="source" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                @foreach ($sources as $key => $label)
                    <option value="{{ $key }}" {{ old('source', $c?->source ?? 'direct') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="flex items-end pb-1">
            <label class="flex items-center gap-3 cursor-pointer">
                <input type="hidden" name="is_blacklisted" value="0">
                <input type="checkbox" name="is_blacklisted" value="1"
                       {{ old('is_blacklisted', $c?->is_blacklisted) ? 'checked' : '' }}
                       class="w-4 h-4 rounded border-gray-300 text-red-600 focus:ring-red-500">
                <span class="text-sm text-gray-700">Client blacklisté</span>
            </label>
        </div>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Remarques...">{{ old('notes', $c?->notes) }}</textarea>
    </div>
</div>
