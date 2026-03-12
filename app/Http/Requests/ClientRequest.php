<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'gestionnaire']);
    }

    public function rules(): array
    {
        $type = $this->input('type_client', 'particulier');

        $rules = [
            'type_client'    => ['required', Rule::in(['particulier', 'societe'])],
            'telephone'      => ['nullable', 'string', 'max:20'],
            'telephone_2'    => ['nullable', 'string', 'max:20'],
            'email'          => ['nullable', 'email', 'max:255'],
            'adresse'        => ['nullable', 'string', 'max:500'],
            'ville'          => ['nullable', 'string', 'max:100'],
            'code_postal'    => ['nullable', 'string', 'max:10'],
            'plafond_credit' => ['nullable', 'numeric', 'min:0'],
            'source'         => ['required', Rule::in(array_keys(\App\Models\Client::SOURCES))],
            'notes'          => ['nullable', 'string', 'max:1000'],
            'is_blacklisted' => ['boolean'],
        ];

        if ($type === 'particulier') {
            $rules['nom_complet'] = ['required', 'string', 'max:255'];
            $rules['cin'] = ['nullable', 'string', 'max:20'];
        } else {
            $rules['raison_sociale'] = ['required', 'string', 'max:255'];
            $rules['ice'] = ['nullable', 'string', 'max:20'];
            $rules['registre_commerce'] = ['nullable', 'string', 'max:50'];
            $rules['contact_societe'] = ['nullable', 'string', 'max:255'];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'nom_complet.required'    => 'Le nom complet est obligatoire pour un particulier.',
            'raison_sociale.required' => 'La raison sociale est obligatoire pour une société.',
            'telephone.max'           => 'Le numéro de téléphone est trop long.',
            'email.email'             => 'L\'adresse email n\'est pas valide.',
        ];
    }
}
