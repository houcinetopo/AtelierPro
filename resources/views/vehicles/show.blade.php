@extends('layouts.app')
@section('title', $vehicle->display_label)
@section('breadcrumb')
    <a href="{{ route('vehicles.index') }}" class="hover:text-primary-600">Véhicules</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $vehicle->immatriculation }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">{{ $vehicle->full_name }}</h1>
            <div class="flex items-center gap-3 mt-1">
                <span class="text-sm font-mono bg-gray-100 text-gray-700 px-2 py-0.5 rounded">{{ $vehicle->immatriculation }}</span>
                @if($vehicle->couleur) <span class="text-sm text-gray-500">{{ $vehicle->couleur }}</span> @endif
                @if($vehicle->annee) <span class="text-sm text-gray-500">{{ $vehicle->annee }}</span> @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('clients.show', $vehicle->client_id) }}" class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="user" class="w-4 h-4"></i> {{ $vehicle->client?->display_name }}
            </a>
            <a href="{{ route('vehicles.edit', $vehicle) }}" class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="lg:col-span-2 space-y-4">
            {{-- Infos techniques --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Détails techniques</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div><p class="text-gray-400 text-xs">Marque</p><p class="text-gray-700 font-medium mt-0.5">{{ $vehicle->marque }}</p></div>
                    <div><p class="text-gray-400 text-xs">Modèle</p><p class="text-gray-700 mt-0.5">{{ $vehicle->modele ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs">Couleur</p><p class="text-gray-700 mt-0.5">{{ $vehicle->couleur ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs">Année</p><p class="text-gray-700 mt-0.5">{{ $vehicle->annee ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs">Carburant</p><p class="text-gray-700 mt-0.5">{{ $vehicle->carburant_label }}</p></div>
                    <div><p class="text-gray-400 text-xs">Puissance fiscale</p><p class="text-gray-700 mt-0.5">{{ $vehicle->puissance_fiscale ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs">N° Châssis (VIN)</p><p class="text-gray-700 font-mono text-xs mt-0.5">{{ $vehicle->numero_chassis ?? '—' }}</p></div>
                    <div><p class="text-gray-400 text-xs">Kilométrage</p><p class="text-gray-700 font-medium mt-0.5">{{ $vehicle->kilometrage > 0 ? number_format($vehicle->kilometrage, 0, '', ' ') . ' km' : '—' }}</p></div>
                </div>
            </div>

            {{-- Assurance + CT --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i data-lucide="shield" class="w-4 h-4 text-blue-500"></i> Assurance {!! $vehicle->assurance_badge !!}
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><p class="text-gray-400 text-xs">Compagnie</p><p class="text-gray-700 mt-0.5">{{ $vehicle->compagnie_assurance ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs">N° Police</p><p class="text-gray-700 font-mono mt-0.5">{{ $vehicle->numero_police_assurance ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs">Expire le</p><p class="text-gray-700 mt-0.5">{{ $vehicle->date_expiration_assurance?->format('d/m/Y') ?? '—' }}</p></div>
                    </div>
                </div>
                <div class="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 class="text-sm font-semibold text-gray-800 mb-3 flex items-center gap-2">
                        <i data-lucide="clipboard-check" class="w-4 h-4 text-green-500"></i> Contrôle technique
                    </h3>
                    <div class="space-y-2 text-sm">
                        <div><p class="text-gray-400 text-xs">Dernier contrôle</p><p class="text-gray-700 mt-0.5">{{ $vehicle->date_controle_technique?->format('d/m/Y') ?? '—' }}</p></div>
                        <div><p class="text-gray-400 text-xs">Prochain contrôle</p><p class="text-gray-700 mt-0.5">{{ $vehicle->date_prochain_controle?->format('d/m/Y') ?? '—' }}</p></div>
                    </div>
                </div>
            </div>

            {{-- Galerie photos --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Photos ({{ $vehicle->photos->count() }})</h2>
                </div>
                @if($vehicle->photos->count() > 0)
                <div class="p-4 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                    @foreach($vehicle->photos as $photo)
                    <div class="relative group rounded-lg overflow-hidden border border-gray-200 aspect-square">
                        <img src="{{ $photo->url }}" alt="{{ $photo->description ?? $photo->type_label }}"
                             class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
                            <a href="{{ $photo->url }}" target="_blank" class="p-2 bg-white/90 rounded-full text-gray-700 hover:bg-white">
                                <i data-lucide="maximize-2" class="w-4 h-4"></i>
                            </a>
                            <form method="POST" action="{{ route('vehicles.photos.delete', [$vehicle, $photo]) }}"
                                  x-data @submit.prevent="if(confirm('Supprimer ?')) $el.submit()">
                                @csrf @method('DELETE')
                                <button class="p-2 bg-white/90 rounded-full text-red-600 hover:bg-white"><i data-lucide="trash-2" class="w-4 h-4"></i></button>
                            </form>
                        </div>
                        <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent px-2 pb-1.5 pt-4">
                            <span class="text-white text-xs">{{ $photo->type_label }}</span>
                        </div>
                    </div>
                    @endforeach
                </div>
                @else
                <div class="p-8 text-center text-gray-400">
                    <i data-lucide="image" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucune photo</p>
                </div>
                @endif
                {{-- Upload form --}}
                <div class="px-5 py-3 border-t border-gray-200 bg-gray-50">
                    <form method="POST" action="{{ route('vehicles.photos.upload', $vehicle) }}" enctype="multipart/form-data" class="flex items-center gap-3">
                        @csrf
                        <select name="type" class="border border-gray-300 rounded-lg text-xs px-2 py-1.5 focus:ring-2 focus:ring-primary-500">
                            @foreach(\App\Models\VehiclePhoto::TYPES as $k => $l)
                                <option value="{{ $k }}">{{ $l }}</option>
                            @endforeach
                        </select>
                        <input type="file" name="photos[]" accept="image/*" multiple required
                               class="flex-1 text-xs text-gray-500 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:text-xs file:bg-primary-50 file:text-primary-700">
                        <button type="submit" class="px-3 py-1.5 bg-primary-600 hover:bg-primary-700 text-white text-xs font-medium rounded-lg">
                            Ajouter
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-3">Propriétaire</h3>
                <a href="{{ route('clients.show', $vehicle->client_id) }}" class="flex items-center gap-3 group">
                    <img src="{{ $vehicle->client?->avatar_url }}" alt="" class="w-10 h-10 rounded-full">
                    <div>
                        <p class="font-medium text-gray-800 group-hover:text-primary-600 text-sm">{{ $vehicle->client?->display_name }}</p>
                        <p class="text-xs text-gray-400">{{ $vehicle->client?->telephone }}</p>
                    </div>
                </a>
            </div>

            @if($vehicle->notes)
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h3 class="text-sm font-semibold text-gray-800 mb-2">Notes</h3>
                <p class="text-sm text-gray-600">{{ $vehicle->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
