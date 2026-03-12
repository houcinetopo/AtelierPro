@extends('layouts.app')

@section('title', 'Nouvel Ordre de Réparation')
@section('breadcrumb')
    <a href="{{ route('repair-orders.index') }}" class="hover:text-primary-600">Ordres de Réparation</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouveau</span>
@endsection

@section('content')
<div class="max-w-5xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Nouvel Ordre de Réparation</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $numero }}</span></p>
    </div>

    <form method="POST" action="{{ route('repair-orders.store') }}" enctype="multipart/form-data">
        @csrf
        @include('repair-orders._form')
    </form>
</div>
@endsection
