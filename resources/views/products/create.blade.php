@extends('layouts.app')

@section('title', 'Nouveau Produit')
@section('breadcrumb')
    <a href="{{ route('products.index') }}" class="hover:text-primary-600">Stock</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouveau</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Nouveau Produit</h1>
        <p class="text-sm text-gray-500 mt-0.5">Réf. <span class="font-mono font-semibold text-primary-600">{{ $reference }}</span> (modifiable)</p>
    </div>

    <form method="POST" action="{{ route('products.store') }}">
        @csrf
        @include('products._form')
    </form>
</div>
@endsection
