@extends('layouts.app')

@section('title', 'Modifier ' . $deliveryNote->numero)
@section('breadcrumb')
    <a href="{{ route('delivery-notes.index') }}" class="hover:text-primary-600">Bons de Livraison</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('delivery-notes.show', $deliveryNote) }}" class="hover:text-primary-600">{{ $deliveryNote->numero }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Modifier le bon de livraison</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $deliveryNote->numero }}</span> — {!! $deliveryNote->statut_badge !!}</p>
    </div>

    <form method="POST" action="{{ route('delivery-notes.update', $deliveryNote) }}">
        @csrf @method('PUT')
        @include('delivery-notes._form')
    </form>
</div>
@endsection
