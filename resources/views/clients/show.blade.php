@extends('layouts.app')
@section('title', $client->display_name)
@section('breadcrumb')
    <a href="{{ route('clients.index') }}" class="hover:text-primary-600">Clients</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $client->display_name }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-4">
            <img src="{{ $client->avatar_url }}" alt="" class="w-14 h-14 rounded-full border-2 border-white shadow">
            <div>
                <div class="flex items-center gap-2">
                    <h1 class="text-2xl font-bold text-gray-800">{{ $client->display_name }}</h1>
                    {!! $client->type_badge !!}
                    @if($client->is_blacklisted)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Blacklisté</span>
                    @endif
                </div>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ $client->legal_id ? $client->legal_id_label . ': ' . $client->legal_id : '' }}
                    {{ $client->telephone ? '— ' . $client->telephone : '' }}
                </p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('vehicles.create', ['client_id' => $client->id]) }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <i data-lucide="car" class="w-4 h-4"></i> Ajouter véhicule
            </a>
            <a href="{{ route('clients.edit', $client) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm bg-primary-600 hover:bg-primary-700 text-white font-medium rounded-lg">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Colonne principale --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Infos --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Informations</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    @if($client->type_client === 'societe')
                    <div>
                        <p class="text-gray-400 text-xs">Raison sociale</p>
                        <p class="text-gray-700 font-medium mt-0.5">{{ $client->raison_sociale }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Contact</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->contact_societe ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">RC</p>
                        <p class="text-gray-700 font-mono mt-0.5">{{ $client->registre_commerce ?? '—' }}</p>
                    </div>
                    @else
                    <div>
                        <p class="text-gray-400 text-xs">Nom complet</p>
                        <p class="text-gray-700 font-medium mt-0.5">{{ $client->nom_complet }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">CIN</p>
                        <p class="text-gray-700 font-mono mt-0.5">{{ $client->cin ?? '—' }}</p>
                    </div>
                    @endif
                    <div>
                        <p class="text-gray-400 text-xs">Téléphone</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->telephone ?? '—' }} {{ $client->telephone_2 ? "/ {$client->telephone_2}" : '' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Email</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Adresse</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->adresse ?? '—' }} {{ $client->ville ? "— {$client->ville}" : '' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Source</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->source_label }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Client depuis</p>
                        <p class="text-gray-700 mt-0.5">{{ $client->created_at->format('d/m/Y') }}</p>
                    </div>
                </div>
                @if($client->notes)
                <div class="mt-4 pt-4 border-t border-gray-100">
                    <p class="text-gray-400 text-xs">Notes</p>
                    <p class="text-gray-600 text-sm mt-1">{{ $client->notes }}</p>
                </div>
                @endif
            </div>

            {{-- Véhicules --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">
                        Véhicules <span class="text-gray-400 text-sm font-normal">({{ $client->vehicles->count() }})</span>
                    </h2>
                    <a href="{{ route('vehicles.create', ['client_id' => $client->id]) }}"
                       class="text-xs text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                        <i data-lucide="plus" class="w-3.5 h-3.5"></i> Ajouter
                    </a>
                </div>

                @if($client->vehicles->count() > 0)
                <div class="divide-y divide-gray-100">
                    @foreach($client->vehicles as $vehicle)
                    <a href="{{ route('vehicles.show', $vehicle) }}" class="flex items-center gap-4 px-5 py-3.5 hover:bg-gray-50 transition-colors group">
                        <div class="w-12 h-12 rounded-lg bg-gray-100 flex items-center justify-center flex-shrink-0">
                            <i data-lucide="car" class="w-6 h-6 text-gray-400 group-hover:text-primary-500"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="font-medium text-gray-800 group-hover:text-primary-600 text-sm">{{ $vehicle->full_name }}</p>
                            <div class="flex items-center gap-3 mt-0.5">
                                <span class="text-xs font-mono text-gray-500 bg-gray-100 px-1.5 py-0.5 rounded">{{ $vehicle->immatriculation }}</span>
                                @if($vehicle->couleur)
                                    <span class="text-xs text-gray-400">{{ $vehicle->couleur }}</span>
                                @endif
                                @if($vehicle->annee)
                                    <span class="text-xs text-gray-400">{{ $vehicle->annee }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            {!! $vehicle->assurance_badge !!}
                            @if($vehicle->kilometrage > 0)
                                <p class="text-xs text-gray-400 mt-1">{{ number_format($vehicle->kilometrage, 0, '', ' ') }} km</p>
                            @endif
                        </div>
                        <i data-lucide="chevron-right" class="w-4 h-4 text-gray-300 group-hover:text-gray-400"></i>
                    </a>
                    @endforeach
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <i data-lucide="car" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucun véhicule enregistré</p>
                    <a href="{{ route('vehicles.create', ['client_id' => $client->id]) }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-1 inline-block">
                        + Ajouter un véhicule
                    </a>
                </div>
                @endif
            </div>
        </div>

        {{-- Colonne droite --}}
        <div class="space-y-4">
            {{-- Résumé financier --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Situation financière</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Solde crédit</span>
                        <span class="text-sm font-bold {{ $client->solde_credit > 0 ? ($client->isOverCreditLimit() ? 'text-red-600' : 'text-amber-600') : 'text-green-600' }}">
                            {{ number_format($client->solde_credit, 2, ',', ' ') }} DH
                        </span>
                    </div>
                    @if($client->plafond_credit)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Plafond crédit</span>
                        <span class="text-sm text-gray-700">{{ number_format($client->plafond_credit, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="w-full bg-gray-200 rounded-full h-1.5">
                        @php
                            $pct = $client->plafond_credit > 0 ? min(100, ($client->solde_credit / $client->plafond_credit) * 100) : 0;
                            $color = $pct > 80 ? 'bg-red-500' : ($pct > 50 ? 'bg-amber-500' : 'bg-green-500');
                        @endphp
                        <div class="{{ $color }} rounded-full h-1.5" style="width: {{ $pct }}%"></div>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Véhicules count --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-3">Résumé</h2>
                <div class="space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Véhicules</span>
                        <span class="text-sm font-bold text-gray-800">{{ $client->vehicles->count() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Client depuis</span>
                        <span class="text-xs text-gray-600">{{ $client->created_at->diffForHumans() }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
