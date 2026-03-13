{{-- Formulaire Expert (create/edit) - Modification 4 --}}
@props(['expert' => null])

<div class="space-y-6">
    {{-- Informations personnelles --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
            <input type="text" name="nom_complet" value="{{ old('nom_complet', $expert?->nom_complet) }}" required
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cabinet</label>
            <input type="text" name="cabinet" value="{{ old('cabinet', $expert?->cabinet) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
            <input type="text" name="telephone" value="{{ old('telephone', $expert?->telephone) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Téléphone 2</label>
            <input type="text" name="telephone_2" value="{{ old('telephone_2', $expert?->telephone_2) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div class="md:col-span-2">
            <label class="block text-sm font-medium text-gray-700 mb-1">Adresse</label>
            <input type="text" name="adresse" value="{{ old('adresse', $expert?->adresse) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
            <input type="text" name="ville" value="{{ old('ville', $expert?->ville) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
            <input type="text" name="code_postal" value="{{ old('code_postal', $expert?->code_postal) }}"
                   class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
        </div>
    </div>

    {{-- Emails dynamiques --}}
    <div x-data="emailManager()" class="bg-gray-50 rounded-xl p-5 border border-gray-200">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-700">Adresses Email <span class="text-red-500">*</span></h3>
            <button type="button" @click="addEmail()"
                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-primary-700 bg-primary-50 border border-primary-200 rounded-lg hover:bg-primary-100 transition">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter un email
            </button>
        </div>

        <template x-for="(email, index) in emails" :key="index">
            <div class="flex items-start gap-3 mb-3 p-3 bg-white rounded-lg border border-gray-200">
                <div class="flex-1 grid grid-cols-1 md:grid-cols-3 gap-3">
                    <div class="md:col-span-1">
                        <input type="email" :name="'emails[' + index + '][email]'" x-model="email.email" required
                               placeholder="email@exemple.com"
                               class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                    </div>
                    <div>
                        <select :name="'emails[' + index + '][label]'" x-model="email.label"
                                class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">
                            <option value="">— Label —</option>
                            <option value="principal">Principal</option>
                            <option value="secondaire">Secondaire</option>
                            <option value="cabinet">Cabinet</option>
                            <option value="personnel">Personnel</option>
                        </select>
                    </div>
                    <div class="flex items-center gap-3">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="radio" :name="'primary_email'" :value="index" 
                                   @change="setPrimary(index)"
                                   :checked="email.is_primary"
                                   class="text-primary-600">
                            <span class="text-xs text-gray-600">Principal</span>
                        </label>
                        <input type="hidden" :name="'emails[' + index + '][is_primary]'" :value="email.is_primary ? 1 : 0">
                    </div>
                </div>
                <button type="button" @click="removeEmail(index)" x-show="emails.length > 1"
                        class="mt-1 p-1.5 text-gray-400 hover:text-red-500 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                </button>
            </div>
        </template>
    </div>

    {{-- Notes --}}
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
        <textarea name="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm focus:ring-primary-500 focus:border-primary-500">{{ old('notes', $expert?->notes) }}</textarea>
    </div>

    {{-- Actif --}}
    <div class="flex items-center gap-2">
        <input type="hidden" name="actif" value="0">
        <input type="checkbox" name="actif" value="1" id="actif"
               {{ old('actif', $expert?->actif ?? true) ? 'checked' : '' }}
               class="rounded border-gray-300 text-primary-600 focus:ring-primary-500">
        <label for="actif" class="text-sm text-gray-700">Expert actif</label>
    </div>
</div>

<script>
function emailManager() {
    const existingEmails = @json($expert?->emails ?? []);
    return {
        emails: existingEmails.length > 0
            ? existingEmails.map(e => ({ email: e.email, label: e.label || '', is_primary: e.is_primary }))
            : [{ email: '', label: 'principal', is_primary: true }],
        addEmail() {
            this.emails.push({ email: '', label: '', is_primary: false });
        },
        removeEmail(index) {
            const wasPrimary = this.emails[index].is_primary;
            this.emails.splice(index, 1);
            if (wasPrimary && this.emails.length > 0) {
                this.emails[0].is_primary = true;
            }
        },
        setPrimary(index) {
            this.emails.forEach((e, i) => e.is_primary = (i === index));
        }
    };
}
</script>
