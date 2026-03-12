{{-- Formulaire véhicule réutilisable --}}
@php $v = $vehicle ?? null; @endphp

<div class="space-y-5">
    {{-- Client propriétaire --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Client propriétaire <span class="text-red-500">*</span></label>
        <select name="client_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            <option value="">— Sélectionner un client —</option>
            @foreach($clients as $client)
                <option value="{{ $client->id }}"
                    {{ old('client_id', $v?->client_id ?? ($preselectedClient ?? '')) == $client->id ? 'selected' : '' }}>
                    {{ $client->display_name }} {{ $client->telephone ? "— {$client->telephone}" : '' }}
                </option>
            @endforeach
        </select>
        @error('client_id') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Immatriculation + Marque + Modèle --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Immatriculation <span class="text-red-500">*</span></label>
            <input type="text" name="immatriculation" value="{{ old('immatriculation', $v?->immatriculation) }}" required
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-primary-500"
                   placeholder="12345-A-6">
            @error('immatriculation') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Marque <span class="text-red-500">*</span></label>
            <select name="marque" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                <option value="">— Sélectionner —</option>
                @foreach($marques as $m)
                    <option value="{{ $m }}" {{ old('marque', $v?->marque) == $m ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
            @error('marque') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Modèle</label>
            <input type="text" name="modele" value="{{ old('modele', $v?->modele) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Clio, Hilux...">
        </div>
    </div>

    {{-- Couleur + Année + Carburant + Puissance --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Couleur</label>
            <input type="text" name="couleur" value="{{ old('couleur', $v?->couleur) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Blanc">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Année</label>
            <input type="number" name="annee" value="{{ old('annee', $v?->annee) }}" min="1950" max="{{ date('Y') + 1 }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="{{ date('Y') }}">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Carburant</label>
            <select name="type_carburant" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                <option value="">—</option>
                @foreach($carburants as $key => $label)
                    <option value="{{ $key }}" {{ old('type_carburant', $v?->type_carburant) == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Puissance fiscale</label>
            <input type="text" name="puissance_fiscale" value="{{ old('puissance_fiscale', $v?->puissance_fiscale) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="7 CV">
        </div>
    </div>

    {{-- Châssis + Kilométrage --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">N° Châssis (VIN)</label>
            <input type="text" name="numero_chassis" value="{{ old('numero_chassis', $v?->numero_chassis) }}"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm font-mono uppercase focus:ring-2 focus:ring-primary-500"
                   placeholder="WVWZZZ3CZWE012345" maxlength="30">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Kilométrage actuel</label>
            <input type="number" name="kilometrage" value="{{ old('kilometrage', $v?->kilometrage ?? 0) }}" min="0"
                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="0">
        </div>
    </div>

    {{-- Assurance --}}
    <div class="border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i data-lucide="shield" class="w-4 h-4 text-blue-500"></i> Assurance
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Compagnie</label>
                <input type="text" name="compagnie_assurance" value="{{ old('compagnie_assurance', $v?->compagnie_assurance) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="SAHAM, WAFA...">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">N° Police</label>
                <input type="text" name="numero_police_assurance" value="{{ old('numero_police_assurance', $v?->numero_police_assurance) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm font-mono focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Date expiration</label>
                <input type="date" name="date_expiration_assurance" value="{{ old('date_expiration_assurance', $v?->date_expiration_assurance?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
    </div>

    {{-- Contrôle technique --}}
    <div class="border border-gray-200 rounded-lg p-4">
        <h3 class="text-sm font-semibold text-gray-700 mb-3 flex items-center gap-2">
            <i data-lucide="clipboard-check" class="w-4 h-4 text-green-500"></i> Contrôle technique
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Dernier contrôle</label>
                <input type="date" name="date_controle_technique" value="{{ old('date_controle_technique', $v?->date_controle_technique?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Prochain contrôle</label>
                <input type="date" name="date_prochain_controle" value="{{ old('date_prochain_controle', $v?->date_prochain_controle?->format('Y-m-d')) }}"
                       class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
    </div>

    {{-- Photos --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Photos du véhicule</label>
        <input type="file" name="photos[]" accept="image/jpeg,image/png,image/webp" multiple
               class="w-full text-sm text-gray-500 file:mr-3 file:py-2 file:px-4 file:rounded-lg file:border-0
                      file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer">
        <input type="file" accept="image/*" capture="environment" multiple class="hidden" id="photos-camera">
        <button type="button" onclick="document.getElementById('photos-camera').click()"
                class="mt-2 py-2 px-4 text-xs text-gray-500 border border-dashed border-gray-300 rounded-lg
                       hover:border-primary-400 hover:text-primary-600 transition-colors inline-flex items-center gap-1.5">
            <i data-lucide="camera" class="w-3.5 h-3.5"></i> Prendre des photos
        </button>
        <p class="text-xs text-gray-400 mt-1">JPG, PNG, WebP — Max 5 Mo/photo — Max 10 photos</p>
        @error('photos') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        @error('photos.*') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="2" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500" placeholder="Remarques...">{{ old('notes', $v?->notes) }}</textarea>
    </div>
</div>
