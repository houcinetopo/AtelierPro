@extends('layouts.app')
@section('title', 'Nouveau Véhicule')
@section('breadcrumb')
    <a href="{{ route('vehicles.index') }}" class="hover:text-primary-600">Véhicules</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouveau</span>
@endsection
@section('content')
<div class="max-w-3xl mx-auto">
    <div class="mb-6"><h1 class="text-2xl font-bold text-gray-800">Nouveau véhicule</h1></div>
    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('vehicles.store') }}" enctype="multipart/form-data">
            @csrf
            @if($preselectedClient)
                <input type="hidden" name="redirect" value="client">
            @endif
            @include('vehicles._form')
            <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-200">
                <a href="{{ url()->previous() }}" class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium">Annuler</a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
