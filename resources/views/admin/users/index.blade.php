@extends('layouts.app')

@section('title', 'Gestion des Utilisateurs')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Utilisateurs</span>
@endsection

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-gray-800">Utilisateurs</h1>
            <p class="text-sm text-gray-500 mt-0.5">Gérer les comptes et les droits d'accès</p>
        </div>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors">
            <i data-lucide="user-plus" class="w-4 h-4"></i>
            Nouvel utilisateur
        </a>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1">
                <div class="relative">
                    <i data-lucide="search" class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400"></i>
                    <input type="text" name="search" value="{{ request('search') }}"
                           class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Rechercher par nom, email, téléphone...">
                </div>
            </div>
            <select name="role" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les rôles</option>
                @foreach (config('roles.roles') as $key => $role)
                    <option value="{{ $key }}" {{ request('role') == $key ? 'selected' : '' }}>{{ $role['label'] }}</option>
                @endforeach
            </select>
            <select name="status" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Actif</option>
                <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactif</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['search', 'role', 'status']))
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm rounded-lg transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Email</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Rôle</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Statut</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Dernière connexion</th>
                        <th class="text-right px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($users as $user)
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $user->avatar_url }}" alt="" class="w-9 h-9 rounded-full object-cover">
                                    <div>
                                        <p class="font-medium text-gray-800">{{ $user->name }}</p>
                                        @if($user->phone)
                                            <p class="text-xs text-gray-400">{{ $user->phone }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3 text-gray-600">{{ $user->email }}</td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    {{ match($user->role) {
                                        'admin' => 'bg-red-100 text-red-700',
                                        'gestionnaire' => 'bg-blue-100 text-blue-700',
                                        'comptable' => 'bg-green-100 text-green-700',
                                        'technicien' => 'bg-amber-100 text-amber-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    } }}">
                                    {{ $user->role_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3">
                                @if ($user->is_active)
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-green-600">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500"></span> Actif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-medium text-gray-400">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-300"></span> Inactif
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3 text-gray-500 text-xs">
                                {{ $user->last_login_at ? $user->last_login_at->diffForHumans() : 'Jamais' }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center justify-end gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="p-2 rounded-lg hover:bg-blue-50 text-gray-400 hover:text-blue-600 transition-colors"
                                       title="Modifier">
                                        <i data-lucide="edit-3" class="w-4 h-4"></i>
                                    </a>

                                    @if ($user->id !== auth()->id())
                                        {{-- Toggle status --}}
                                        <form method="POST" action="{{ route('admin.users.toggle-status', $user) }}">
                                            @csrf @method('PATCH')
                                            <button type="submit"
                                                    class="p-2 rounded-lg hover:bg-amber-50 text-gray-400 hover:text-amber-600 transition-colors"
                                                    title="{{ $user->is_active ? 'Désactiver' : 'Activer' }}">
                                                <i data-lucide="{{ $user->is_active ? 'user-x' : 'user-check' }}" class="w-4 h-4"></i>
                                            </button>
                                        </form>

                                        {{-- Delete --}}
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              x-data
                                              @submit.prevent="if(confirm('Êtes-vous sûr de vouloir supprimer {{ $user->name }} ?')) $el.submit()">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                    class="p-2 rounded-lg hover:bg-red-50 text-gray-400 hover:text-red-600 transition-colors"
                                                    title="Supprimer">
                                                <i data-lucide="trash-2" class="w-4 h-4"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-5 py-12 text-center text-gray-400">
                                <i data-lucide="users" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                <p>Aucun utilisateur trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if ($users->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">
                {{ $users->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
