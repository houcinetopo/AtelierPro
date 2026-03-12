@extends('layouts.app')

@section('title', 'Nouvelle Facture')
@section('breadcrumb')
    <a href="{{ route('invoices.index') }}" class="hover:text-primary-600">Factures</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouvelle</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Nouvelle Facture</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $numero }}</span></p>
    </div>
    <form method="POST" action="{{ route('invoices.store') }}">
        @csrf
        @include('invoices._form')
    </form>
</div>
@endsection
