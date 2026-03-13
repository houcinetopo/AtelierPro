@extends('layouts.app')
@section('title', 'Modifier Expert')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('experts.show', $expert) }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-gray-900">Modifier : {{ $expert->nom_complet }}</h1>
    </div>

    <form action="{{ route('experts.update', $expert) }}" method="POST" class="bg-white rounded-xl border border-gray-200 p-6">
        @csrf
        @method('PUT')
        @include('experts._form', ['expert' => $expert])

        <div class="flex items-center justify-end gap-3 mt-6 pt-6 border-t border-gray-200">
            <a href="{{ route('experts.show', $expert) }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border rounded-lg hover:bg-gray-50">Annuler</a>
            <button type="submit" class="px-6 py-2 text-sm font-medium text-white bg-primary-600 rounded-lg hover:bg-primary-700">Mettre à jour</button>
        </div>
    </form>
</div>
@endsection
