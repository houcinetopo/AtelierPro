@extends('layouts.app')

@section('title', 'Modifier ' . $repairOrder->numero)
@section('breadcrumb')
    <a href="{{ route('repair-orders.index') }}" class="hover:text-primary-600">Ordres de Réparation</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <a href="{{ route('repair-orders.show', $repairOrder) }}" class="hover:text-primary-600">{{ $repairOrder->numero }}</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">Modifier</span>
@endsection

@section('content')
<div class="max-w-5xl">
    <div class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'ordre</h1>
        <p class="text-sm text-gray-500 mt-0.5">N° <span class="font-mono font-semibold text-primary-600">{{ $repairOrder->numero }}</span> — {!! $repairOrder->status_badge !!}</p>
    </div>

    <form method="POST" action="{{ route('repair-orders.update', $repairOrder) }}" enctype="multipart/form-data">
        @csrf @method('PUT')
        @include('repair-orders._form')
    </form>
</div>
@endsection
