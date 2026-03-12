<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BankAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        return [
            'nom_banque'    => ['required', 'string', 'max:255'],
            'numero_compte' => ['nullable', 'string', 'max:50'],
            'rib'           => ['nullable', 'string', 'max:30'],
            'code_swift'    => ['nullable', 'string', 'max:15'],
            'iban'          => ['nullable', 'string', 'max:40'],
            'agence'        => ['nullable', 'string', 'max:255'],
            'ville_agence'  => ['nullable', 'string', 'max:100'],
            'is_default'    => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_banque.required' => 'Le nom de la banque est obligatoire.',
            'rib.max'             => 'Le RIB ne doit pas dépasser 30 caractères.',
            'code_swift.max'      => 'Le code SWIFT ne doit pas dépasser 15 caractères.',
            'iban.max'            => 'L\'IBAN ne doit pas dépasser 40 caractères.',
        ];
    }
}
