<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return auth()->user()->hasAnyRole(['admin', 'gestionnaire']);
    }

    public function rules(): array
    {
        return [
            'nom_complet'       => ['required', 'string', 'max:255'],
            'cin'               => ['nullable', 'string', 'max:20'],
            'photo'             => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'poste'             => ['required', 'string', Rule::in(array_keys(Employee::POSTES))],
            'date_embauche'     => ['nullable', 'date'],
            'type_contrat'      => ['required', Rule::in(Employee::TYPES_CONTRAT)],
            'salaire_base'      => ['required', 'numeric', 'min:0', 'max:999999.99'],
            'jours_travail_mois'=> ['required', 'integer', 'min:1', 'max:31'],
            'telephone'         => ['nullable', 'string', 'max:20'],
            'adresse'           => ['nullable', 'string', 'max:500'],
            'ville'             => ['nullable', 'string', 'max:100'],
            'cnss'              => ['nullable', 'string', 'max:20'],
            'email'             => ['nullable', 'email', 'max:255'],
            'date_naissance'    => ['nullable', 'date', 'before:today'],
            'contact_urgence'   => ['nullable', 'string', 'max:255'],
            'telephone_urgence' => ['nullable', 'string', 'max:20'],
            'notes'             => ['nullable', 'string', 'max:1000'],
            'statut'            => ['required', Rule::in(['actif', 'inactif'])],
        ];
    }

    public function messages(): array
    {
        return [
            'nom_complet.required'   => 'Le nom complet est obligatoire.',
            'poste.required'         => 'Le poste est obligatoire.',
            'poste.in'               => 'Le poste sélectionné est invalide.',
            'type_contrat.required'  => 'Le type de contrat est obligatoire.',
            'salaire_base.required'  => 'Le salaire de base est obligatoire.',
            'salaire_base.min'       => 'Le salaire ne peut pas être négatif.',
            'photo.image'            => 'Le fichier doit être une image.',
            'photo.max'              => 'La photo ne doit pas dépasser 2 Mo.',
            'date_naissance.before'  => 'La date de naissance doit être antérieure à aujourd\'hui.',
        ];
    }
}
