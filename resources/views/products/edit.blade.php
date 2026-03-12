@extends('layouts.app')

@section('title', 'Modifier ' . $product->reference)
@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-primary-600">Stock</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('products.show', $product) }}" class="hover:text-primary-600">{{ $product->reference }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Modifier le produit</h1>
        <p class="text-sm text-gray-500 mt-0.5">Réf. <span class="font-mono font-semibold text-primary-600">{{ $product->reference }}</span> — Stock actuel : {{ $product->quantite_stock }} {{ $product->unite }}</p>
    </div>

    <form method="POST" action="{{ route('products.update', $product) }}">
        @csrf @method('PUT')
        @include('products._form')
    </form>
</div>
@endsection
