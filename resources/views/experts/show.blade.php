@extends('layouts.app')
@section('title', $expert->nom_complet)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <a href="{{ route('experts.index') }}" class="p-2 text-gray-400 hover:text-gray-600 rounded-lg hover:bg-gray-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">{{ $expert->nom_complet }}</h1>
                @if($expert->cabinet)
                    <p class="text-sm text-gray-500">{{ $expert->cabinet }}</p>
                @endif
            </div>
            @if($expert->actif)
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-700">Actif</span>
            @else
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-500">Inactif</span>
            @endif
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('experts.edit', $expert) }}"
               class="inline-flex items-center gap-2 px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Informations --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Informations</h2>
                <dl class="grid grid-cols-2 gap-4 text-sm">
                    <div><dt class="text-gray-500">Téléphone</dt><dd class="font-medium text-gray-900 mt-1">{{ $expert->telephone ?? '—' }}</dd></div>
                    <div><dt class="text-gray-500">Téléphone 2</dt><dd class="font-medium text-gray-900 mt-1">{{ $expert->telephone_2 ?? '—' }}</dd></div>
                    <div class="col-span-2"><dt class="text-gray-500">Adresse</dt><dd class="font-medium text-gray-900 mt-1">{{ collect([$expert->adresse, $expert->code_postal, $expert->ville])->filter()->implode(', ') ?: '—' }}</dd></div>
                </dl>
            </div>

            {{-- Emails --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Adresses Email</h2>
                <div class="space-y-2">
                    @foreach($expert->emails as $email)
                    <div class="flex items-center justify-between p-3 rounded-lg {{ $email->is_primary ? 'bg-primary-50 border border-primary-200' : 'bg-gray-50 border border-gray-200' }}">
                        <div class="flex items-center gap-3">
                            <span class="text-sm font-medium text-gray-900">{{ $email->email }}</span>
                            @if($email->is_primary)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-primary-100 text-primary-700">Principal</span>
                            @endif
                            @if($email->label)
                                <span class="text-xs text-gray-500">{{ ucfirst($email->label) }}</span>
                            @endif
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- OR associés --}}
            @if($expert->repairOrders->count() > 0)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h2 class="text-sm font-semibold text-gray-700 mb-4">Ordres de Réparation ({{ $expert->repair_orders_count }})</h2>
                <div class="space-y-2">
                    @foreach($expert->repairOrders as $or)
                    <a href="{{ route('repair-orders.show', $or) }}"
                       class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-gray-200 hover:bg-gray-100 transition">
                        <div>
                            <span class="text-sm font-medium text-gray-900">{{ $or->numero }}</span>
                            <span class="text-xs text-gray-500 ml-2">{{ $or->client_name }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            {!! $or->status_badge !!}
                            <span class="text-xs text-gray-500">{{ $or->date_reception?->format('d/m/Y') }}</span>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            <div class="bg-white rounded-xl border border-gray-200 p-6 text-center">
                <div class="w-16 h-16 mx-auto rounded-full bg-primary-100 flex items-center justify-center text-primary-700 text-xl font-bold mb-3">
                    {{ $expert->initials }}
                </div>
                <h3 class="text-lg font-semibold text-gray-900">{{ $expert->nom_complet }}</h3>
                @if($expert->cabinet)
                    <p class="text-sm text-gray-500">{{ $expert->cabinet }}</p>
                @endif
                <div class="mt-4 text-center">
                    <span class="text-2xl font-bold text-primary-600">{{ $expert->repair_orders_count }}</span>
                    <p class="text-xs text-gray-500">ordres de réparation</p>
                </div>
            </div>

            @if($expert->notes)
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-2">Notes</h3>
                <p class="text-sm text-gray-600 whitespace-pre-line">{{ $expert->notes }}</p>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
