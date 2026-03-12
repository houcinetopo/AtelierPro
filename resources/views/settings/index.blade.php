@extends('layouts.app')

@section('title', 'Paramètres Société')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Paramètres Société</span>
@endsection

@section('content')
<div class="space-y-4" x-data="settingsApp()">

    {{-- Header --}}
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Paramètres de la Société</h1>
        <p class="text-sm text-gray-500 mt-0.5">
            Configurez les informations de votre entreprise. Ces données seront utilisées dans tous les documents générés.
        </p>
    </div>

    {{-- Onglets --}}
    <div class="bg-white rounded-xl border border-gray-200">
        {{-- Tab Navigation --}}
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px overflow-x-auto">
                <a href="{{ route('settings.index', ['tab' => 'general']) }}"
                   class="px-6 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                          {{ $tab === 'general' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="building-2" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
                    Informations Générales
                </a>
                <a href="{{ route('settings.index', ['tab' => 'juridique']) }}"
                   class="px-6 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                          {{ $tab === 'juridique' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="scale" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
                    Identifiants Juridiques
                </a>
                <a href="{{ route('settings.index', ['tab' => 'bancaire']) }}"
                   class="px-6 py-3.5 text-sm font-medium border-b-2 whitespace-nowrap transition-colors
                          {{ $tab === 'bancaire' ? 'border-primary-500 text-primary-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="landmark" class="w-4 h-4 inline-block mr-1.5 -mt-0.5"></i>
                    Informations Bancaires
                    @if($bankAccounts->count() > 0)
                        <span class="ml-1.5 inline-flex items-center justify-center w-5 h-5 rounded-full bg-primary-100 text-primary-600 text-xs font-bold">
                            {{ $bankAccounts->count() }}
                        </span>
                    @endif
                </a>
            </nav>
        </div>

        {{-- Tab Content --}}
        <div class="p-6">

            {{-- ═══════════ ONGLET 1 : INFORMATIONS GÉNÉRALES ═══════════ --}}
            @if($tab === 'general')
            <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="general">

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    {{-- Colonne principale (2/3) --}}
                    <div class="lg:col-span-2 space-y-5">
                        {{-- Raison sociale --}}
                        <div>
                            <label for="raison_sociale" class="block text-sm font-medium text-gray-700 mb-1">
                                Raison sociale <span class="text-red-500">*</span>
                            </label>
                            <input type="text" id="raison_sociale" name="raison_sociale"
                                   value="{{ old('raison_sociale', $settings->raison_sociale) }}" required
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="Nom de votre entreprise">
                            @error('raison_sociale') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Adresse --}}
                        <div>
                            <label for="adresse" class="block text-sm font-medium text-gray-700 mb-1">
                                Adresse complète <span class="text-red-500">*</span>
                            </label>
                            <textarea id="adresse" name="adresse" rows="2" required
                                      class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                      placeholder="Rue, N°, Quartier...">{{ old('adresse', $settings->adresse) }}</textarea>
                            @error('adresse') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>

                        {{-- Ville + Code postal --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="ville" class="block text-sm font-medium text-gray-700 mb-1">Ville</label>
                                <input type="text" id="ville" name="ville" value="{{ old('ville', $settings->ville) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="Casablanca">
                            </div>
                            <div>
                                <label for="code_postal" class="block text-sm font-medium text-gray-700 mb-1">Code postal</label>
                                <input type="text" id="code_postal" name="code_postal" value="{{ old('code_postal', $settings->code_postal) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="20000">
                            </div>
                        </div>

                        {{-- Téléphones --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="telephone_portable" class="block text-sm font-medium text-gray-700 mb-1">Téléphone portable</label>
                                <input type="text" id="telephone_portable" name="telephone_portable"
                                       value="{{ old('telephone_portable', $settings->telephone_portable) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="06XXXXXXXX">
                            </div>
                            <div>
                                <label for="telephone_fixe" class="block text-sm font-medium text-gray-700 mb-1">Téléphone fixe</label>
                                <input type="text" id="telephone_fixe" name="telephone_fixe"
                                       value="{{ old('telephone_fixe', $settings->telephone_fixe) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="05XXXXXXXX">
                            </div>
                        </div>

                        {{-- Emails --}}
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label for="email_principal" class="block text-sm font-medium text-gray-700 mb-1">Email principal</label>
                                <input type="email" id="email_principal" name="email_principal"
                                       value="{{ old('email_principal', $settings->email_principal) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="contact@entreprise.ma">
                                @error('email_principal') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label for="email_secondaire" class="block text-sm font-medium text-gray-700 mb-1">Email secondaire</label>
                                <input type="email" id="email_secondaire" name="email_secondaire"
                                       value="{{ old('email_secondaire', $settings->email_secondaire) }}"
                                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                       placeholder="info@entreprise.ma">
                            </div>
                        </div>

                        {{-- Site web --}}
                        <div>
                            <label for="site_web" class="block text-sm font-medium text-gray-700 mb-1">Site web</label>
                            <input type="url" id="site_web" name="site_web" value="{{ old('site_web', $settings->site_web) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                   placeholder="https://www.entreprise.ma">
                            @error('site_web') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                    </div>

                    {{-- Colonne images (1/3) --}}
                    <div class="space-y-5">
                        @foreach(['logo' => 'Logo de la société', 'cachet' => 'Cachet de la société', 'signature' => 'Signature numérique'] as $field => $label)
                        <div class="bg-gray-50 rounded-lg p-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">{{ $label }}</label>
                            <div class="relative">
                                @if($settings->$field)
                                    <div class="mb-3 relative group">
                                        <img src="{{ $settings->{$field . '_url'} }}" alt="{{ $label }}"
                                             class="w-full max-h-32 object-contain rounded-lg border border-gray-200 bg-white p-2">
                                        <button type="button"
                                                onclick="removeImage('{{ $field }}')"
                                                class="absolute -top-2 -right-2 w-6 h-6 bg-red-500 text-white rounded-full flex items-center justify-center
                                                       opacity-0 group-hover:opacity-100 transition-opacity hover:bg-red-600"
                                                title="Supprimer">
                                            <i data-lucide="x" class="w-3 h-3"></i>
                                        </button>
                                    </div>
                                @endif
                                <div class="relative">
                                    <input type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp"
                                           class="w-full text-xs text-gray-500
                                                  file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                                  file:text-xs file:font-medium file:bg-primary-50 file:text-primary-700
                                                  hover:file:bg-primary-100 cursor-pointer"
                                           onchange="previewImage(this, '{{ $field }}')">
                                    {{-- Capture caméra mobile --}}
                                    <input type="file" name="{{ $field }}_camera" accept="image/*" capture="environment"
                                           class="hidden" id="{{ $field }}_camera"
                                           onchange="document.querySelector('input[name={{ $field }}]').files = this.files; previewImage(this, '{{ $field }}')">
                                    <button type="button"
                                            onclick="document.getElementById('{{ $field }}_camera').click()"
                                            class="mt-2 w-full py-1.5 px-3 text-xs text-gray-500 border border-dashed border-gray-300 rounded-lg
                                                   hover:border-primary-400 hover:text-primary-600 transition-colors flex items-center justify-center gap-1.5">
                                        <i data-lucide="camera" class="w-3.5 h-3.5"></i>
                                        Prendre une photo
                                    </button>
                                </div>
                                <div id="{{ $field }}_preview" class="mt-2 hidden">
                                    <img src="" alt="Aperçu" class="w-full max-h-24 object-contain rounded border border-gray-200 bg-white p-1">
                                </div>
                                <p class="text-xs text-gray-400 mt-1.5">JPG, PNG, WebP — Max 2 Mo</p>
                                @error($field) <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
            @endif

            {{-- ═══════════ ONGLET 2 : IDENTIFIANTS JURIDIQUES ═══════════ --}}
            @if($tab === 'juridique')
            <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
                @csrf
                @method('PUT')
                <input type="hidden" name="tab" value="juridique">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- Forme juridique --}}
                    <div>
                        <label for="forme_juridique" class="block text-sm font-medium text-gray-700 mb-1">Forme juridique</label>
                        <select id="forme_juridique" name="forme_juridique"
                                class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            <option value="">— Sélectionner —</option>
                            @foreach(['SARL', 'SARL AU', 'SA', 'SNC', 'SCS', 'SCA', 'Auto-entrepreneur', 'Société civile', 'Coopérative', 'Autre'] as $forme)
                                <option value="{{ $forme }}" {{ old('forme_juridique', $settings->forme_juridique) == $forme ? 'selected' : '' }}>
                                    {{ $forme }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Capital social --}}
                    <div>
                        <label for="capital_social" class="block text-sm font-medium text-gray-700 mb-1">Capital social (DH)</label>
                        <input type="text" id="capital_social" name="capital_social"
                               value="{{ old('capital_social', $settings->capital_social) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="100 000">
                    </div>

                    {{-- RC --}}
                    <div>
                        <label for="registre_commerce" class="block text-sm font-medium text-gray-700 mb-1">Registre de Commerce (RC)</label>
                        <input type="text" id="registre_commerce" name="registre_commerce"
                               value="{{ old('registre_commerce', $settings->registre_commerce) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="123456">
                    </div>

                    {{-- Patente --}}
                    <div>
                        <label for="patente" class="block text-sm font-medium text-gray-700 mb-1">Patente</label>
                        <input type="text" id="patente" name="patente"
                               value="{{ old('patente', $settings->patente) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="12345678">
                    </div>

                    {{-- CNSS --}}
                    <div>
                        <label for="cnss" class="block text-sm font-medium text-gray-700 mb-1">CNSS (N° d'affiliation)</label>
                        <input type="text" id="cnss" name="cnss"
                               value="{{ old('cnss', $settings->cnss) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="1234567">
                    </div>

                    {{-- ICE --}}
                    <div>
                        <label for="ice" class="block text-sm font-medium text-gray-700 mb-1">
                            ICE <span class="text-xs text-gray-400">(Identifiant Commun Entreprise)</span>
                        </label>
                        <input type="text" id="ice" name="ice"
                               value="{{ old('ice', $settings->ice) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
                               placeholder="001234567000012" maxlength="20">
                    </div>

                    {{-- IF --}}
                    <div>
                        <label for="identifiant_fiscal" class="block text-sm font-medium text-gray-700 mb-1">
                            IF <span class="text-xs text-gray-400">(Identifiant Fiscal)</span>
                        </label>
                        <input type="text" id="identifiant_fiscal" name="identifiant_fiscal"
                               value="{{ old('identifiant_fiscal', $settings->identifiant_fiscal) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="12345678">
                    </div>

                    {{-- Nom du responsable --}}
                    <div>
                        <label for="nom_responsable" class="block text-sm font-medium text-gray-700 mb-1">Nom du gérant / responsable</label>
                        <input type="text" id="nom_responsable" name="nom_responsable"
                               value="{{ old('nom_responsable', $settings->nom_responsable) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Nom complet du gérant">
                    </div>

                    {{-- Fonction --}}
                    <div>
                        <label for="fonction_responsable" class="block text-sm font-medium text-gray-700 mb-1">Fonction</label>
                        <input type="text" id="fonction_responsable" name="fonction_responsable"
                               value="{{ old('fonction_responsable', $settings->fonction_responsable) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Gérant">
                    </div>

                    {{-- CIN --}}
                    <div>
                        <label for="cin_responsable" class="block text-sm font-medium text-gray-700 mb-1">CIN du responsable</label>
                        <input type="text" id="cin_responsable" name="cin_responsable"
                               value="{{ old('cin_responsable', $settings->cin_responsable) }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500 font-mono"
                               placeholder="AB123456">
                    </div>
                </div>

                {{-- Objet de la société --}}
                <div>
                    <label for="objet_societe" class="block text-sm font-medium text-gray-700 mb-1">Objet de la société</label>
                    <textarea id="objet_societe" name="objet_societe" rows="3"
                              class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                              placeholder="Description de l'activité principale de l'entreprise...">{{ old('objet_societe', $settings->objet_societe) }}</textarea>
                </div>

                {{-- Aperçu pied de page --}}
                @if($settings->isConfigured())
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-xs font-medium text-gray-500 mb-1.5">Aperçu du pied de page des documents :</p>
                    <p class="text-xs text-gray-600 font-mono leading-relaxed">{{ $settings->getFooterLine() ?: 'Complétez les champs ci-dessus pour générer le pied de page' }}</p>
                </div>
                @endif

                {{-- Actions --}}
                <div class="flex justify-end pt-4 border-t border-gray-200">
                    <button type="submit"
                            class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                        <i data-lucide="save" class="w-4 h-4"></i>
                        Enregistrer les modifications
                    </button>
                </div>
            </form>
            @endif

            {{-- ═══════════ ONGLET 3 : INFORMATIONS BANCAIRES ═══════════ --}}
            @if($tab === 'bancaire')
            <div class="space-y-6">

                {{-- Liste des comptes existants --}}
                @if($bankAccounts->count() > 0)
                <div class="space-y-3">
                    @foreach($bankAccounts as $account)
                    <div class="border border-gray-200 rounded-lg p-4 {{ $account->is_default ? 'ring-2 ring-primary-200 bg-primary-50/30' : 'bg-white' }}"
                         x-data="{ editing: false }">

                        {{-- Mode Affichage --}}
                        <div x-show="!editing">
                            <div class="flex items-start justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-lg {{ $account->is_default ? 'bg-primary-100' : 'bg-gray-100' }} flex items-center justify-center">
                                        <i data-lucide="landmark" class="w-5 h-5 {{ $account->is_default ? 'text-primary-600' : 'text-gray-400' }}"></i>
                                    </div>
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <p class="font-semibold text-gray-800 text-sm">{{ $account->nom_banque }}</p>
                                            @if($account->is_default)
                                                <span class="px-2 py-0.5 bg-primary-100 text-primary-700 text-xs font-medium rounded-full">Par défaut</span>
                                            @endif
                                        </div>
                                        @if($account->agence)
                                            <p class="text-xs text-gray-500 mt-0.5">Agence : {{ $account->agence }} {{ $account->ville_agence ? "— {$account->ville_agence}" : '' }}</p>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-1">
                                    @if(!$account->is_default)
                                    <form method="POST" action="{{ route('settings.bank-accounts.set-default', $account) }}">
                                        @csrf @method('PATCH')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Définir par défaut">
                                            <i data-lucide="star" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                    @endif
                                    <button @click="editing = true" class="p-1.5 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors" title="Modifier">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </button>
                                    <form method="POST" action="{{ route('settings.bank-accounts.destroy', $account) }}"
                                          x-data @submit.prevent="if(confirm('Supprimer ce compte bancaire ?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1.5 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors" title="Supprimer">
                                            <i data-lucide="trash-2" class="w-4 h-4"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            <div class="mt-3 grid grid-cols-2 md:grid-cols-4 gap-3 text-xs">
                                @if($account->numero_compte)
                                <div>
                                    <p class="text-gray-400">N° Compte</p>
                                    <p class="text-gray-700 font-mono mt-0.5">{{ $account->numero_compte }}</p>
                                </div>
                                @endif
                                @if($account->rib)
                                <div>
                                    <p class="text-gray-400">RIB</p>
                                    <p class="text-gray-700 font-mono mt-0.5">{{ $account->rib }}</p>
                                </div>
                                @endif
                                @if($account->code_swift)
                                <div>
                                    <p class="text-gray-400">SWIFT</p>
                                    <p class="text-gray-700 font-mono mt-0.5">{{ $account->code_swift }}</p>
                                </div>
                                @endif
                                @if($account->iban)
                                <div>
                                    <p class="text-gray-400">IBAN</p>
                                    <p class="text-gray-700 font-mono mt-0.5">{{ $account->iban }}</p>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- Mode Édition --}}
                        <div x-show="editing" x-cloak>
                            <form method="POST" action="{{ route('settings.bank-accounts.update', $account) }}" class="space-y-4">
                                @csrf @method('PUT')
                                @include('settings._bank_account_form', ['account' => $account])
                                <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                                    <button type="button" @click="editing = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Annuler</button>
                                    <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                                        Enregistrer
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 mx-auto mb-3 rounded-full bg-gray-100 flex items-center justify-center">
                        <i data-lucide="landmark" class="w-8 h-8 text-gray-300"></i>
                    </div>
                    <p class="text-sm text-gray-500">Aucun compte bancaire enregistré</p>
                    <p class="text-xs text-gray-400 mt-1">Ajoutez votre premier compte bancaire ci-dessous</p>
                </div>
                @endif

                {{-- Formulaire d'ajout --}}
                <div class="border-t border-gray-200 pt-5" x-data="{ showForm: false }">
                    <button @click="showForm = !showForm"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-medium text-primary-600 bg-primary-50 hover:bg-primary-100 rounded-lg transition-colors">
                        <i data-lucide="plus-circle" class="w-4 h-4"></i>
                        Ajouter un compte bancaire
                    </button>

                    <div x-show="showForm" x-cloak x-transition class="mt-4 bg-gray-50 rounded-lg p-5">
                        <h3 class="text-sm font-semibold text-gray-700 mb-4">Nouveau compte bancaire</h3>
                        <form method="POST" action="{{ route('settings.bank-accounts.store') }}" class="space-y-4">
                            @csrf
                            @include('settings._bank_account_form', ['account' => null])
                            <div class="flex justify-end gap-2 pt-3 border-t border-gray-200">
                                <button type="button" @click="showForm = false" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800">Annuler</button>
                                <button type="submit" class="px-4 py-2 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                                    <i data-lucide="plus" class="w-4 h-4"></i>
                                    Ajouter
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function settingsApp() {
    return {};
}

function previewImage(input, field) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const preview = document.getElementById(field + '_preview');
            preview.classList.remove('hidden');
            preview.querySelector('img').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function removeImage(field) {
    if (!confirm('Supprimer cette image ?')) return;

    fetch('{{ route("settings.remove-image") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: JSON.stringify({ field: field }),
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(err => console.error('Erreur:', err));
}
</script>
@endpush
