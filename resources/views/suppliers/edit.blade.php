@extends('layouts.app')
@section('title', 'Modifier ' . $supplier->code)
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="hover:text-primary-600">Fournisseurs</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('suppliers.show', $supplier) }}" class="hover:text-primary-600">{{ $supplier->code }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection
@section('content')
<div class="max-w-4xl">
    <div class="mb-4"><h1 class="text-2xl font-bold text-gray-800">Modifier le fournisseur</h1><p class="text-sm text-gray-500 mt-0.5"><span class="font-mono font-semibold text-primary-600">{{ $supplier->code }}</span> — {{ $supplier->raison_sociale }}</p></div>
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">@csrf @method('PUT') @include('suppliers._form')</form>
</div>
@endsection
