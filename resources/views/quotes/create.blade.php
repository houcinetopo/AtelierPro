@extends('layouts.app')

@section('title', 'Nouveau Devis')
@section('breadcrumb')
    <a href="{{ route('quotes.index') }}" class="hover:text-primary-600">Devis</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Nouveau</span>
@endsection

@section('content')
<div class="max-w-4xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Nouveau Devis</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $numero }}</span></p>
    </div>

    <form method="POST" action="{{ route('quotes.store') }}">
        @csrf
        @include('quotes._form')
    </form>
</div>
@endsection
