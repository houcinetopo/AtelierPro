@extends('layouts.app')
@section('title', 'Nouveau Fournisseur')
@section('breadcrumb')
    <a href="{{ route('suppliers.index') }}" class="hover:text-primary-600">Fournisseurs</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouveau</span>
@endsection
@section('content')
<div class="max-w-4xl">
    <div class="mb-4"><h1 class="text-2xl font-bold text-gray-800">Nouveau Fournisseur</h1><p class="text-sm text-gray-500 mt-0.5">Code <span class="font-mono font-semibold text-primary-600">{{ $code }}</span></p></div>
    <form method="POST" action="{{ route('suppliers.store') }}">@csrf @include('suppliers._form')</form>
</div>
@endsection
