@extends('layouts.app')
@section('title', 'Nouvelle Déclaration TVA')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Nouvelle Déclaration TVA</h1>
            <p class="mt-1 text-sm text-gray-500">Créer une déclaration pour une période fiscale</p>
        </div>
        <a href="{{ route('tva.index') }}" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Retour
        </a>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-200 p-6" x-data="{ regime: '{{ old('regime', 'mensuel') }}' }">
        <form method="POST" action="{{ route('tva.store') }}" class="space-y-6">
            @csrf

            {{-- Régime --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Régime de déclaration</label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="relative flex items-center gap-3 p-4 border rounded-xl cursor-pointer" :class="regime === 'mensuel' ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-500' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="regime" value="mensuel" x-model="regime" class="sr-only">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="regime === 'mensuel' ? 'bg-primary-100' : 'bg-gray-100'">
                            <svg class="w-5 h-5" :class="regime === 'mensuel' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Mensuel</div>
                            <div class="text-xs text-gray-500">CA > 1 000 000 DH</div>
                        </div>
                    </label>
                    <label class="relative flex items-center gap-3 p-4 border rounded-xl cursor-pointer" :class="regime === 'trimestriel' ? 'border-primary-500 bg-primary-50 ring-1 ring-primary-500' : 'border-gray-200 hover:border-gray-300'">
                        <input type="radio" name="regime" value="trimestriel" x-model="regime" class="sr-only">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center" :class="regime === 'trimestriel' ? 'bg-primary-100' : 'bg-gray-100'">
                            <svg class="w-5 h-5" :class="regime === 'trimestriel' ? 'text-primary-600' : 'text-gray-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                        </div>
                        <div>
                            <div class="font-medium text-gray-900">Trimestriel</div>
                            <div class="text-xs text-gray-500">CA ≤ 1 000 000 DH</div>
                        </div>
                    </label>
                </div>
                @error('regime') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Année --}}
            <div>
                <label for="annee" class="block text-sm font-medium text-gray-700 mb-1">Année</label>
                <select name="annee" id="annee" class="w-full rounded-lg border-gray-300 text-sm">
                    @for($y = now()->year + 1; $y >= 2020; $y--)
                        <option value="{{ $y }}" {{ old('annee', now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
                @error('annee') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Mois (mensuel) --}}
            <div x-show="regime === 'mensuel'" x-transition>
                <label for="mois" class="block text-sm font-medium text-gray-700 mb-1">Mois</label>
                <select name="mois" id="mois" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach(\App\Models\TvaDeclaration::MOIS_LABELS as $num => $label)
                        <option value="{{ $num }}" {{ old('mois', now()->month) == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('mois') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Trimestre (trimestriel) --}}
            <div x-show="regime === 'trimestriel'" x-transition>
                <label for="trimestre" class="block text-sm font-medium text-gray-700 mb-1">Trimestre</label>
                <select name="trimestre" id="trimestre" class="w-full rounded-lg border-gray-300 text-sm">
                    @foreach(\App\Models\TvaDeclaration::TRIMESTRE_LABELS as $num => $label)
                        <option value="{{ $num }}" {{ old('trimestre', ceil(now()->month / 3)) == $num ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
                @error('trimestre') <p class="mt-1 text-sm text-red-600">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">Notes <span class="text-gray-400">(optionnel)</span></label>
                <textarea name="notes" id="notes" rows="3" class="w-full rounded-lg border-gray-300 text-sm" placeholder="Remarques pour cette déclaration...">{{ old('notes') }}</textarea>
            </div>

            {{-- Info --}}
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex gap-3">
                    <svg class="w-5 h-5 text-blue-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <div class="text-sm text-blue-700">
                        <p class="font-medium">Comment ça fonctionne</p>
                        <p class="mt-1">Après création, utilisez le bouton « Calculer automatiquement » pour remplir les montants depuis vos factures et bons de commande de la période. Vous pourrez ensuite ajuster manuellement si nécessaire.</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-3">
                <a href="{{ route('tva.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">Annuler</a>
                <button type="submit" class="px-6 py-2 bg-primary-600 text-white text-sm font-medium rounded-lg hover:bg-primary-700">
                    Créer la déclaration
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
