@extends('layouts.app')

@section('title', $employee->nom_complet)
@section('breadcrumb')
    <a href="{{ route('employees.index') }}" class="hover:text-primary-600">Employés</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $employee->nom_complet }}</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="flex items-center gap-4">
            <img src="{{ $employee->photo_url }}" alt="" class="w-16 h-16 rounded-full object-cover border-2 border-white shadow">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">{{ $employee->nom_complet }}</h1>
                <div class="flex items-center gap-2 mt-1">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">{{ $employee->poste_label }}</span>
                    {!! $employee->statut_badge !!}
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.edit', $employee) }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="edit-3" class="w-4 h-4"></i> Modifier
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- ═══════════ INFOS PERSONNELLES ═══════════ --}}
        <div class="lg:col-span-2 space-y-4">
            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Informations personnelles</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">CIN</p>
                        <p class="text-gray-700 font-mono mt-0.5">{{ $employee->cin ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Téléphone</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->telephone ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Email</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->email ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Date de naissance</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->date_naissance?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Adresse</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->adresse ?? '—' }} {{ $employee->ville ? "— {$employee->ville}" : '' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Contact urgence</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->contact_urgence ?? '—' }} {{ $employee->telephone_urgence ? "({$employee->telephone_urgence})" : '' }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-5">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Informations professionnelles</h2>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-gray-400 text-xs">Poste</p>
                        <p class="text-gray-700 font-medium mt-0.5">{{ $employee->poste_label }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Type de contrat</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->type_contrat }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Date d'embauche</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->date_embauche?->format('d/m/Y') ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Ancienneté</p>
                        <p class="text-gray-700 font-medium mt-0.5">{{ $employee->anciennete }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">N° CNSS</p>
                        <p class="text-gray-700 font-mono mt-0.5">{{ $employee->cnss ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Salaire mensuel</p>
                        <p class="text-gray-800 font-bold mt-0.5">{{ number_format($employee->salaire_base, 2, ',', ' ') }} DH</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Jours travail/mois</p>
                        <p class="text-gray-700 mt-0.5">{{ $employee->jours_travail_mois }} jours</p>
                    </div>
                    <div>
                        <p class="text-gray-400 text-xs">Salaire journalier</p>
                        <p class="text-gray-700 font-medium mt-0.5">{{ number_format($employee->salaire_journalier, 2, ',', ' ') }} DH/jour</p>
                    </div>
                </div>
                @if($employee->notes)
                    <div class="mt-4 pt-4 border-t border-gray-100">
                        <p class="text-gray-400 text-xs">Notes</p>
                        <p class="text-gray-600 text-sm mt-1">{{ $employee->notes }}</p>
                    </div>
                @endif
            </div>

            {{-- ═══════════ HISTORIQUE PAIEMENTS ═══════════ --}}
            <div class="bg-white rounded-xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200 flex items-center justify-between">
                    <h2 class="text-base font-semibold text-gray-800">Historique des paiements</h2>
                    <p class="text-xs text-gray-400">{{ $employee->payments->count() }} paiement(s)</p>
                </div>

                @if($employee->payments->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Période</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Date</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Base</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Prime</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Déduction</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Net payé</th>
                                <th class="text-left px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase">Mode</th>
                                <th class="text-right px-4 py-2.5 text-xs font-semibold text-gray-500 uppercase"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach($employee->payments as $payment)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2.5 font-medium text-gray-700 text-xs">{{ $payment->periode_label }}</td>
                                <td class="px-4 py-2.5 text-gray-500 text-xs">{{ $payment->date_paiement->format('d/m/Y') }}</td>
                                <td class="px-4 py-2.5 text-right text-gray-600">{{ number_format($payment->montant, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-right {{ $payment->prime > 0 ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $payment->prime > 0 ? '+' . number_format($payment->prime, 2, ',', ' ') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right {{ $payment->deduction > 0 ? 'text-red-600' : 'text-gray-400' }}">
                                    {{ $payment->deduction > 0 ? '-' . number_format($payment->deduction, 2, ',', ' ') : '—' }}
                                </td>
                                <td class="px-4 py-2.5 text-right font-bold text-gray-800">{{ number_format($payment->net_paye, 2, ',', ' ') }}</td>
                                <td class="px-4 py-2.5 text-xs text-gray-500">{{ $payment->mode_paiement_label }}</td>
                                <td class="px-4 py-2.5 text-right">
                                    <form method="POST" action="{{ route('employees.payments.destroy', [$employee, $payment]) }}"
                                          x-data @submit.prevent="if(confirm('Supprimer ce paiement ?')) $el.submit()">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="p-1 rounded hover:bg-red-50 text-gray-400 hover:text-red-600">
                                            <i data-lucide="trash-2" class="w-3.5 h-3.5"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="px-5 py-10 text-center text-gray-400">
                    <i data-lucide="banknote" class="w-8 h-8 mx-auto mb-2 opacity-40"></i>
                    <p class="text-sm">Aucun paiement enregistré</p>
                </div>
                @endif
            </div>
        </div>

        {{-- ═══════════ COLONNE DROITE ═══════════ --}}
        <div class="space-y-4">
            {{-- Stats paiements --}}
            <div class="bg-white rounded-xl border border-gray-200 p-5 space-y-4">
                <h2 class="text-base font-semibold text-gray-800">Résumé financier</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Total payé (tout)</span>
                        <span class="text-sm font-bold text-gray-800">{{ number_format($totalPaid, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Payé cette année</span>
                        <span class="text-sm font-bold text-primary-600">{{ number_format($currentYearPaid, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                        <span class="text-xs text-gray-500">Salaire mensuel</span>
                        <span class="text-sm font-medium text-gray-700">{{ number_format($employee->salaire_base, 2, ',', ' ') }} DH</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Salaire journalier</span>
                        <span class="text-sm font-medium text-gray-700">{{ number_format($employee->salaire_journalier, 2, ',', ' ') }} DH</span>
                    </div>
                </div>
            </div>

            {{-- Formulaire paiement --}}
            @if(auth()->user()->hasAnyRole(['admin', 'gestionnaire', 'comptable']))
            <div class="bg-white rounded-xl border border-gray-200 p-5" x-data="paymentForm()">
                <h2 class="text-base font-semibold text-gray-800 mb-4">Enregistrer un paiement</h2>

                <form method="POST" action="{{ route('employees.payments.store', $employee) }}" class="space-y-3">
                    @csrf

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Période <span class="text-red-500">*</span></label>
                        <input type="month" name="periode" required value="{{ old('periode', now()->format('Y-m')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        @error('periode') <p class="text-xs text-red-500 mt-0.5">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Montant base (DH) <span class="text-red-500">*</span></label>
                        <input type="number" name="montant" required step="0.01" min="0"
                               value="{{ old('montant', $employee->salaire_base) }}" x-model="montant"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Prime</label>
                            <input type="number" name="prime" step="0.01" min="0" value="{{ old('prime', 0) }}" x-model="prime"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Déduction</label>
                            <input type="number" name="deduction" step="0.01" min="0" value="{{ old('deduction', 0) }}" x-model="deduction"
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Net calculé --}}
                    <div class="bg-green-50 rounded-lg p-2.5 text-center">
                        <p class="text-xs text-green-600">Net à payer</p>
                        <p class="text-lg font-bold text-green-700" x-text="netPaye.toLocaleString('fr-MA', {minimumFractionDigits: 2}) + ' DH'"></p>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Date de paiement <span class="text-red-500">*</span></label>
                        <input type="date" name="date_paiement" required value="{{ old('date_paiement', now()->format('Y-m-d')) }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Mode de paiement <span class="text-red-500">*</span></label>
                        <select name="mode_paiement" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                            @foreach(\App\Models\EmployeePayment::MODES_PAIEMENT as $key => $label)
                                <option value="{{ $key }}" {{ old('mode_paiement') == $key ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Référence / N° chèque</label>
                        <input type="text" name="reference" value="{{ old('reference') }}"
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                               placeholder="Optionnel">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                        <textarea name="notes" rows="2"
                                  class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                                  placeholder="Optionnel">{{ old('notes') }}</textarea>
                    </div>

                    <button type="submit"
                            class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center justify-center gap-2">
                        <i data-lucide="banknote" class="w-4 h-4"></i>
                        Enregistrer le paiement
                    </button>
                </form>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function paymentForm() {
    return {
        montant: {{ $employee->salaire_base }},
        prime: 0,
        deduction: 0,
        get netPaye() {
            return (parseFloat(this.montant) || 0) + (parseFloat(this.prime) || 0) - (parseFloat(this.deduction) || 0);
        }
    };
}
</script>
@endpush
@endsection
