{{-- Formulaire employé réutilisable (création + édition) --}}
@php $e = $employee ?? null; @endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Colonne principale (2/3) --}}
    <div class="lg:col-span-2 space-y-5">
        {{-- Nom + CIN --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="nom_complet" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input type="text" id="nom_complet" name="nom_complet" value="{{ old('nom_complet', $e?->nom_complet) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Prénom et nom">
                @error('nom_complet') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="cin" class="block text-sm font-medium text-gray-700 mb-1">CIN</label>
                <input type="text" id="cin" name="cin" value="{{ old('cin', $e?->cin) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
                       placeholder="AB123456">
                @error('cin') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Poste + Contrat --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="poste" class="block text-sm font-medium text-gray-700 mb-1">Poste / Fonction <span class="text-red-500">*</span></label>
                <select id="poste" name="poste" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    <option value="">— Sélectionner —</option>
                    @foreach ($postes as $key => $label)
                        <option value="{{ $key }}" {{ old('poste', $e?->poste) == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('poste') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="type_contrat" class="block text-sm font-medium text-gray-700 mb-1">Type de contrat <span class="text-red-500">*</span></label>
                <select id="type_contrat" name="type_contrat" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    @foreach ($typesContrat as $type)
                        <option value="{{ $type }}" {{ old('type_contrat', $e?->type_contrat) == $type ? 'selected' : '' }}>{{ $type }}</option>
                    @endforeach
                </select>
                @error('type_contrat') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        {{-- Salaire + Jours + Date embauche --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label for="salaire_base" class="block text-sm font-medium text-gray-700 mb-1">Salaire mensuel (DH) <span class="text-red-500">*</span></label>
                <input type="number" id="salaire_base" name="salaire_base" value="{{ old('salaire_base', $e?->salaire_base) }}" required
                       step="0.01" min="0"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="5000.00">
                @error('salaire_base') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="jours_travail_mois" class="block text-sm font-medium text-gray-700 mb-1">Jours travail/mois <span class="text-red-500">*</span></label>
                <input type="number" id="jours_travail_mois" name="jours_travail_mois" value="{{ old('jours_travail_mois', $e?->jours_travail_mois ?? 26) }}" required
                       min="1" max="31"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('jours_travail_mois') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="date_embauche" class="block text-sm font-medium text-gray-700 mb-1">Date d'embauche</label>
                <input type="date" id="date_embauche" name="date_embauche" value="{{ old('date_embauche', $e?->date_embauche?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        {{-- Calcul salaire journalier (affiché en temps réel) --}}
        <div class="bg-blue-50 rounded-lg p-3 flex items-center gap-3" x-data="{
            salaire: {{ old('salaire_base', $e?->salaire_base ?? 0) }},
            jours: {{ old('jours_travail_mois', $e?->jours_travail_mois ?? 26) }},
            get journalier() { return this.jours > 0 ? (this.salaire / this.jours).toFixed(2) : 0; }
        }" x-init="
            $watch('salaire', () => {});
            document.getElementById('salaire_base').addEventListener('input', e => salaire = parseFloat(e.target.value) || 0);
            document.getElementById('jours_travail_mois').addEventListener('input', e => jours = parseInt(e.target.value) || 1);
        ">
            <i data-lucide="calculator" class="w-5 h-5 text-blue-500 flex-shrink-0"></i>
            <p class="text-sm text-blue-700">
                Salaire journalier calculé : <span class="font-bold" x-text="parseFloat(journalier).toLocaleString('fr-MA', {minimumFractionDigits: 2})">0.00</span> DH/jour
            </p>
        </div>

        {{-- Téléphone + Email --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="telephone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="text" id="telephone" name="telephone" value="{{ old('telephone', $e?->telephone) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="06XXXXXXXX">
            </div>
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                <input type="email" id="email" name="email" value="{{ old('email', $e?->email) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="employe@email.com">
            </div>
        </div>

        {{-- Adresse + Ville --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="sm:col-span-2">
                <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
                <input type="text" id="adresse" name="adresse" value="{{ old('adresse', $e?->adresse) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Rue, quartier...">
            </div>
            <div>
                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                <input type="text" id="ville" name="ville" value="{{ old('ville', $e?->ville) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Casablanca">
            </div>
        </div>

        {{-- CNSS + Date naissance --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="cnss" class="block text-sm font-medium text-gray-700 mb-1">N° CNSS (employé)</label>
                <input type="text" id="cnss" name="cnss" value="{{ old('cnss', $e?->cnss) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
                       placeholder="1234567">
            </div>
            <div>
                <label for="date_naissance" class="block text-sm font-medium text-gray-700 mb-1">Date de naissance</label>
                <input type="date" id="date_naissance" name="date_naissance" value="{{ old('date_naissance', $e?->date_naissance?->format('Y-m-d')) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
            </div>
        </div>

        {{-- Contact urgence --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label for="contact_urgence" class="block text-sm font-medium text-gray-700 mb-1">Contact d'urgence</label>
                <input type="text" id="contact_urgence" name="contact_urgence" value="{{ old('contact_urgence', $e?->contact_urgence) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="Nom du contact">
            </div>
            <div>
                <label for="telephone_urgence" class="block text-sm font-medium text-gray-700 mb-1">Téléphone urgence</label>
                <input type="text" id="telephone_urgence" name="telephone_urgence" value="{{ old('telephone_urgence', $e?->telephone_urgence) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                       placeholder="06XXXXXXXX">
            </div>
        </div>

        {{-- Notes --}}
        <div>
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes / Observations</label>
            <textarea id="notes" name="notes" rows="2"
                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                      placeholder="Remarques...">{{ old('notes', $e?->notes) }}</textarea>
        </div>
    </div>

    {{-- Colonne photo + statut (1/3) --}}
    <div class="space-y-5">
        {{-- Photo --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">Photo de l'employé</label>
            <div class="text-center">
                <div class="w-32 h-32 mx-auto rounded-full overflow-hidden bg-white border-2 border-gray-200 mb-3" id="photo-preview-container">
                    <img src="{{ $e?->photo_url ?? 'https://ui-avatars.com/api/?name=Photo&background=e5e7eb&color=9ca3af&size=128' }}"
                         alt="" class="w-full h-full object-cover" id="photo-preview">
                </div>
                <input type="file" name="photo" accept="image/jpeg,image/png,image/webp"
                       class="w-full text-xs text-gray-500 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                              file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100 cursor-pointer"
                       onchange="previewPhoto(this)">
                <input type="file" accept="image/*" capture="environment" class="hidden" id="photo-camera"
                       onchange="document.querySelector('input[name=photo]').files = this.files; previewPhoto(this)">
                <button type="button" onclick="document.getElementById('photo-camera').click()"
                        class="mt-2 w-full py-1.5 px-3 text-xs text-gray-500 border border-dashed border-gray-300 rounded-lg
                               hover:border-primary-400 hover:text-primary-600 transition-colors flex items-center justify-center gap-1.5">
                    <i data-lucide="camera" class="w-3.5 h-3.5"></i> Prendre une photo
                </button>
                <p class="text-xs text-gray-400 mt-2">JPG, PNG, WebP — Max 2 Mo</p>
            </div>
            @error('photo') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
        </div>

        {{-- Statut --}}
        <div class="bg-gray-50 rounded-lg p-4">
            <label class="block text-sm font-medium text-gray-700 mb-3">Statut</label>
            <div class="space-y-2">
                <label class="flex items-center gap-3 p-2.5 rounded-lg border cursor-pointer transition-colors
                    {{ old('statut', $e?->statut ?? 'actif') === 'actif' ? 'border-green-300 bg-green-50' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
                    <input type="radio" name="statut" value="actif"
                           {{ old('statut', $e?->statut ?? 'actif') === 'actif' ? 'checked' : '' }}
                           class="text-green-600 focus:ring-green-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Actif</p>
                        <p class="text-xs text-gray-400">L'employé est en activité</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 p-2.5 rounded-lg border cursor-pointer transition-colors
                    {{ old('statut', $e?->statut) === 'inactif' ? 'border-gray-400 bg-gray-50' : 'border-gray-200 bg-white hover:bg-gray-50' }}">
                    <input type="radio" name="statut" value="inactif"
                           {{ old('statut', $e?->statut) === 'inactif' ? 'checked' : '' }}
                           class="text-gray-600 focus:ring-gray-500">
                    <div>
                        <p class="text-sm font-medium text-gray-700">Inactif</p>
                        <p class="text-xs text-gray-400">L'employé ne travaille plus</p>
                    </div>
                </label>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewPhoto(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('photo-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
