<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->isAdmin();
    }

    public function rules(): array
    {
        $tab = $this->input('tab', 'general');

        $rules = match($tab) {
            'general' => [
                'raison_sociale'      => ['required', 'string', 'max:255'],
                'adresse'             => ['required', 'string', 'max:500'],
                'ville'               => ['nullable', 'string', 'max:100'],
                'code_postal'         => ['nullable', 'string', 'max:10'],
                'pays'                => ['nullable', 'string', 'max:100'],
                'telephone_portable'  => ['nullable', 'string', 'max:20'],
                'telephone_fixe'      => ['nullable', 'string', 'max:20'],
                'email_principal'     => ['nullable', 'email', 'max:255'],
                'email_secondaire'    => ['nullable', 'email', 'max:255'],
                'site_web'            => ['nullable', 'url', 'max:255'],
                'logo'                => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'cachet'              => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
                'signature'           => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            ],
            'juridique' => [
                'forme_juridique'      => ['nullable', 'string', 'max:100'],
                'capital_social'       => ['nullable', 'string', 'max:50'],
                'registre_commerce'    => ['nullable', 'string', 'max:50'],
                'patente'              => ['nullable', 'string', 'max:50'],
                'cnss'                 => ['nullable', 'string', 'max:50'],
                'ice'                  => ['nullable', 'string', 'max:20'],
                'identifiant_fiscal'   => ['nullable', 'string', 'max:50'],
                'objet_societe'        => ['nullable', 'string', 'max:1000'],
                'nom_responsable'      => ['nullable', 'string', 'max:255'],
                'fonction_responsable' => ['nullable', 'string', 'max:100'],
                'cin_responsable'      => ['nullable', 'string', 'max:20'],
            ],
            default => [],
        };

        // Toujours valider le champ tab
        $rules['tab'] = ['required', 'in:general,juridique'];

        return $rules;
    }

    public function messages(): array
    {
        return [
            'raison_sociale.required' => 'La raison sociale est obligatoire.',
            'adresse.required'        => 'L\'adresse est obligatoire.',
            'email_principal.email'   => 'L\'email principal n\'est pas valide.',
            'email_secondaire.email'  => 'L\'email secondaire n\'est pas valide.',
            'site_web.url'            => 'L\'URL du site web n\'est pas valide.',
            'logo.image'              => 'Le logo doit être une image.',
            'logo.max'                => 'Le logo ne doit pas dépasser 2 Mo.',
            'cachet.image'            => 'Le cachet doit être une image.',
            'cachet.max'              => 'Le cachet ne doit pas dépasser 2 Mo.',
            'signature.image'         => 'La signature doit être une image.',
            'signature.max'           => 'La signature ne doit pas dépasser 2 Mo.',
            'ice.max'                 => 'L\'ICE ne doit pas dépasser 20 caractères.',
        ];
    }
}
