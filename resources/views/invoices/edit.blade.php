@extends('layouts.app')

@section('title', 'Modifier ' . $invoice->numero)
@section('breadcrumb')
    <a href="{{ route('invoices.index') }}" class="hover:text-primary-600">Factures</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('invoices.show', $invoice) }}" class="hover:text-primary-600">{{ $invoice->numero }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Modifier la facture</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $invoice->numero }}</span> — {!! $invoice->statut_badge !!}</p>
    </div>
    <form method="POST" action="{{ route('invoices.update', $invoice) }}">
        @csrf @method('PUT')
        @include('invoices._form')
    </form>
</div>
@endsection
