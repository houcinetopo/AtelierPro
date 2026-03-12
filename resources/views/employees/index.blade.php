@extends('layouts.app')

@section('title', 'Gestion des Employés')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Employés</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Employés</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérer l'équipe de l'atelier</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('employees.export-excel') }}"
               class="inline-flex items-center gap-2 px-3 py-2.5 text-sm text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <i data-lucide="download" class="w-4 h-4"></i> Exporter
            </a>
            <a href="{{ route('employees.create') }}"
               class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
                <i data-lucide="user-plus" class="w-4 h-4"></i> Nouvel employé
            </a>
        </div>
    </div>

    {{-- Stats rapides --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-50 flex items-center justify-center">
                <i data-lucide="users" class="w-5 h-5 text-indigo-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['total'] }}</p>
                <p class="text-xs text-gray-500">Total employés</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-50 flex items-center justify-center">
                <i data-lucide="user-check" class="w-5 h-5 text-green-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ $stats['actifs'] }}</p>
                <p class="text-xs text-gray-500">Actifs</p>
            </div>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-50 flex items-center justify-center">
                <i data-lucide="banknote" class="w-5 h-5 text-amber-500"></i>
            </div>
            <div>
                <p class="text-xl font-bold text-gray-800">{{ number_format($stats['masse_salariale'], 2, ',', ' ') }}</p>
                <p class="text-xs text-gray-500">Masse salariale (DH/mois)</p>
            </div>
        </div>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('employees.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Rechercher par nom, CIN, téléphone...">
                </div>
            </div>
            <select name="poste" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les postes</option>
                @foreach ($postes as $key => $label)
                    <option value="{{ $key }}" {{ request('poste') == $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <select name="statut" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="actif" {{ request('statut') == 'actif' ? 'selected' : '' }}>Actif</option>
                <option value="inactif" {{ request('statut') == 'inactif' ? 'selected' : '' }}>Inactif</option>
            </select>
            <select name="type_contrat" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les contrats</option>
                @foreach (\App\Models\Employee::TYPES_CONTRAT as $type)
                    <option value="{{ $type }}" {{ request('type_contrat') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg">Filtrer</button>
            @if(request()->hasAny(['search', 'poste', 'statut', 'type_contrat']))
                <a href="{{ route('employees.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Employé</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Poste</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Contrat</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Salaire</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Téléphone</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Statut</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($employees as $employee)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-5 py-3">
                            <a href="{{ route('employees.show', $employee) }}" class="flex items-center gap-3 group">
                                <img src="{{ $employee->photo_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                <div>
                                    <p class="font-medium text-gray-800 group-hover:text-primary-600 transition-colors">{{ $employee->nom_complet }}</p>
                                    @if($employee->cin)
                                        <p class="text-xs text-gray-400 font-mono">{{ $employee->cin }}</p>
                                    @endif
                                </div>
                            </a>
                        </td>
                        <td class="px-5 py-3">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-50 text-indigo-700">
                                {{ $employee->poste_label }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs">
                            {{ $employee->type_contrat }}
                            @if($employee->date_embauche)
                                <br><span class="text-gray-400">{{ $employee->anciennete }}</span>
                            @endif
                        </td>
                        <td class="px-5 py-3 text-right">
                            <p class="font-semibold text-gray-800">{{ number_format($employee->salaire_base, 2, ',', ' ') }}</p>
                            <p class="text-xs text-gray-400">{{ number_format($employee->salaire_journalier, 2, ',', ' ') }}/jour</p>
                        </td>
                        <td class="px-5 py-3 text-gray-600 text-xs">{{ $employee->telephone ?? '—' }}</td>
                        <td class="px-5 py-3">{!! $employee->statut_badge !!}</td>
                        <td class="px-5 py-3">
                            <div class="flex items-center justify-end gap-1">
                                <a href="{{ route('employees.show', $employee) }}" class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600" title="Détails">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('employees.edit', $employee) }}" class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600" title="Modifier">
                                    <i data-lucide="edit-3" class="w-4 h-4"></i>
                                </a>
                                <form method="POST" action="{{ route('employees.destroy', $employee) }}"
                                      x-data @submit.prevent="if(confirm('Supprimer {{ $employee->nom_complet }} ?')) $el.submit()">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600" title="Supprimer">
                                        <i data-lucide="trash-2" class="w-4 h-4"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-5 py-12 text-center text-gray-400">
                            <i data-lucide="hard-hat" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                            <p>Aucun employé trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($employees->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">{{ $employees->withQueryString()->links() }}</div>
        @endif
    </div>
</div>
@endsection
