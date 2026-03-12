<?php

namespace App\Http\Requests;

use App\Models\Vehicle;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VehicleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'gestionnaire']);
    }

    public function rules(): array
    {
        $vehicleId = $this->route('vehicle')?->id;

        return [
            'client_id'                  => ['required', 'exists:clients,id'],
            'immatriculation'            => ['required', 'string', 'max:20', Rule::unique('vehicles')->ignore($vehicleId)],
            'marque'                     => ['required', 'string', 'max:100'],
            'modele'                     => ['nullable', 'string', 'max:100'],
            'couleur'                    => ['nullable', 'string', 'max:50'],
            'annee'                      => ['nullable', 'integer', 'min:1950', 'max:' . (date('Y') + 1)],
            'type_carburant'             => ['nullable', Rule::in(array_keys(Vehicle::CARBURANTS))],
            'numero_chassis'             => ['nullable', 'string', 'max:30'],
            'puissance_fiscale'          => ['nullable', 'string', 'max:10'],
            'compagnie_assurance'        => ['nullable', 'string', 'max:255'],
            'numero_police_assurance'    => ['nullable', 'string', 'max:50'],
            'date_expiration_assurance'  => ['nullable', 'date'],
            'date_controle_technique'    => ['nullable', 'date'],
            'date_prochain_controle'     => ['nullable', 'date'],
            'kilometrage'                => ['nullable', 'integer', 'min:0'],
            'notes'                      => ['nullable', 'string', 'max:1000'],
            'photos'                     => ['nullable', 'array', 'max:10'],
            'photos.*'                   => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
        ];
    }

    public function messages(): array
    {
        return [
            'client_id.required'         => 'Le client propriétaire est obligatoire.',
            'client_id.exists'           => 'Le client sélectionné est invalide.',
            'immatriculation.required'   => 'L\'immatriculation est obligatoire.',
            'immatriculation.unique'     => 'Cette immatriculation existe déjà.',
            'marque.required'            => 'La marque est obligatoire.',
            'photos.max'                 => 'Maximum 10 photos à la fois.',
            'photos.*.max'               => 'Chaque photo ne doit pas dépasser 5 Mo.',
        ];
    }
}
