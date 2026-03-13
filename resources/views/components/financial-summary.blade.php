{{-- 
    Composant : Résumé Financier de l'Ordre de Réparation
    Modification 8 : Calcul des coûts et de la rentabilité
    Usage : @include('components.financial-summary', ['resumeFinancier' => $resumeFinancier])
--}}

@props(['resumeFinancier'])

<div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
    <div class="px-5 py-3 bg-gradient-to-r from-emerald-600 to-emerald-700 text-white">
        <h3 class="text-sm font-semibold flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Résumé Financier
        </h3>
    </div>

    <div class="p-5 space-y-3">
        {{-- Coûts --}}
        <div class="space-y-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Coûts</p>
            
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Coût pièces</span>
                <span class="font-medium text-gray-900">{{ number_format($resumeFinancier['cout_pieces'], 2, ',', ' ') }} DH</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Coût main-d'œuvre</span>
                <span class="font-medium text-gray-900">{{ number_format($resumeFinancier['cout_main_oeuvre'], 2, ',', ' ') }} DH</span>
            </div>
            <div class="flex justify-between items-center text-sm pt-1 border-t border-dashed border-gray-200">
                <span class="text-gray-700 font-medium">Coût total réparation</span>
                <span class="font-semibold text-red-600">{{ number_format($resumeFinancier['cout_total'], 2, ',', ' ') }} DH</span>
            </div>
        </div>

        {{-- Facturation --}}
        <div class="space-y-2 pt-3 border-t border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Facturation</p>
            
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">Prix facturé HT</span>
                <span class="font-medium text-gray-900">{{ number_format($resumeFinancier['prix_facture_ht'], 2, ',', ' ') }} DH</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-600">TVA</span>
                <span class="font-medium text-gray-900">{{ number_format($resumeFinancier['tva'], 2, ',', ' ') }} DH</span>
            </div>
            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-700 font-medium">Total TTC</span>
                <span class="font-semibold text-gray-900">{{ number_format($resumeFinancier['prix_facture_ttc'], 2, ',', ' ') }} DH</span>
            </div>
        </div>

        {{-- Rentabilité --}}
        <div class="space-y-2 pt-3 border-t border-gray-200">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Rentabilité</p>
            
            @php
                $isPositive = $resumeFinancier['benefice'] >= 0;
                $benefColor = $isPositive ? 'text-green-600' : 'text-red-600';
                $margeBg = $isPositive ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200';
            @endphp

            <div class="flex justify-between items-center text-sm">
                <span class="text-gray-700 font-medium">Bénéfice brut</span>
                <span class="font-bold {{ $benefColor }}">
                    {{ $isPositive ? '+' : '' }}{{ number_format($resumeFinancier['benefice'], 2, ',', ' ') }} DH
                </span>
            </div>
            
            <div class="flex justify-between items-center">
                <span class="text-sm text-gray-700 font-medium">Marge</span>
                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-sm font-bold border {{ $margeBg }}">
                    {{ $resumeFinancier['marge'] }}%
                </span>
            </div>
        </div>

        {{-- Barre de marge visuelle --}}
        @if($resumeFinancier['prix_facture_ht'] > 0)
        <div class="pt-2">
            <div class="w-full bg-gray-100 rounded-full h-2.5">
                <div class="h-2.5 rounded-full transition-all duration-500 {{ $isPositive ? 'bg-green-500' : 'bg-red-500' }}" 
                     style="width: {{ min(100, max(0, abs($resumeFinancier['marge']))) }}%"></div>
            </div>
        </div>
        @endif
    </div>
</div>
