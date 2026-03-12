@extends('layouts.app')

@section('title', 'Modifier ' . $employee->nom_complet)
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="hover:text-primary-600">Employés</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('employees.show', $employee) }}" class="hover:text-primary-600">{{ $employee->nom_complet }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'employé</h1>
        <p class="text-sm text-gray-500 mt-0.5">{{ $employee->nom_complet }} — {{ $employee->poste_label }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('employees._form')

            <div class="flex items-center justify-between pt-5 mt-5 border-t border-gray-200">
                <a href="{{ route('employees.show', $employee) }}" class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium">Annuler</a>
                <button type="submit" class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i> Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
