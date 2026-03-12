<?php

namespace App\Http\Requests;

use App\Models\EmployeePayment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'gestionnaire', 'comptable']);
    }

    public function rules(): array
    {
        return [
            'periode'         => ['required', 'string', 'regex:/^\d{4}-\d{2}$/'],
            'montant'         => ['required', 'numeric', 'min:0'],
            'date_paiement'   => ['required', 'date'],
            'mode_paiement'   => ['required', Rule::in(array_keys(EmployeePayment::MODES_PAIEMENT))],
            'reference'       => ['nullable', 'string', 'max:100'],
            'notes'           => ['nullable', 'string', 'max:500'],
            'prime'           => ['nullable', 'numeric', 'min:0'],
            'deduction'       => ['nullable', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'periode.required'       => 'La période est obligatoire.',
            'periode.regex'          => 'La période doit être au format AAAA-MM.',
            'montant.required'       => 'Le montant est obligatoire.',
            'date_paiement.required' => 'La date de paiement est obligatoire.',
            'mode_paiement.required' => 'Le mode de paiement est obligatoire.',
        ];
    }
}
