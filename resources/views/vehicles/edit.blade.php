@extends('layouts.app')
@section('title', 'Modifier ' . $vehicle->display_label)
@section('breadcrumb')
    <a href="{{ route('vehicles.index') }}" class="hover:text-primary-600">Véhicules</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('vehicles.show', $vehicle) }}" class="hover:text-primary-600">{{ $vehicle->immatriculation }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection
@php $preselectedClient = null; @endphp
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Modifier le véhicule</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $vehicle->display_label }}</p>
    </div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('vehicles.update', $vehicle) }}" enctype="multipart/form-data">
            @csrf @method('PUT')
            @include('vehicles._form')
            <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-200">
                <a href="{{ route('vehicles.show', $vehicle) }}" class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium">Annuler</a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
