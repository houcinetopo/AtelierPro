@extends('layouts.app')

@section('title', 'Journal d\'activité')
@section('breadcrumb')
    <span class="text-gray-700 font-medium">Journal d'activité</span>
@endsection

@section('content')
<div class="space-y-4">
    <div>
        <h1 class="text-2xl font-bold text-gray-800">Journal d'activité</h1>
        <p class="text-sm text-gray-500 mt-0.5">Historique de toutes les actions effectuées dans le système</p>
    </div>

    {{-- Filtres --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <form method="GET" action="{{ route('admin.activity-logs') }}" class="flex flex-col sm:flex-row gap-3">
            <select name="user_id" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les utilisateurs</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                @endforeach
            </select>
            <select name="action" class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes les actions</option>
                <option value="create" {{ request('action') == 'create' ? 'selected' : '' }}>Création</option>
                <option value="update" {{ request('action') == 'update' ? 'selected' : '' }}>Modification</option>
                <option value="delete" {{ request('action') == 'delete' ? 'selected' : '' }}>Suppression</option>
                <option value="login" {{ request('action') == 'login' ? 'selected' : '' }}>Connexion</option>
                <option value="logout" {{ request('action') == 'logout' ? 'selected' : '' }}>Déconnexion</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}"
                   class="border border-gray-300 rounded-lg text-sm px-3 py-2 focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm rounded-lg transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['user_id', 'action', 'date']))
                <a href="{{ route('admin.activity-logs') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700 text-sm">
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
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Utilisateur</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Action</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                        <th class="text-left px-5 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse ($logs as $log)
                        <tr class="hover:bg-gray-50">
                            <td class="px-5 py-3 text-xs text-gray-500 whitespace-nowrap">
                                {{ $log->created_at->format('d/m/Y H:i:s') }}
                            </td>
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-2">
                                    @if($log->user)
                                        <img src="{{ $log->user->avatar_url }}" alt="" class="w-6 h-6 rounded-full">
                                        <span class="text-gray-700 font-medium">{{ $log->user->name }}</span>
                                    @else
                                        <span class="text-gray-400 italic">Système</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-5 py-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                    {{ match($log->action_color) {
                                        'green' => 'bg-green-100 text-green-700',
                                        'blue' => 'bg-blue-100 text-blue-700',
                                        'red' => 'bg-red-100 text-red-700',
                                        'indigo' => 'bg-indigo-100 text-indigo-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    } }}">
                                    {{ $log->action_label }}
                                </span>
                            </td>
                            <td class="px-5 py-3 text-gray-600 max-w-md truncate">{{ $log->description }}</td>
                            <td class="px-5 py-3 text-xs text-gray-400 font-mono">{{ $log->ip_address }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-12 text-center text-gray-400">
                                <i data-lucide="scroll-text" class="w-10 h-10 mx-auto mb-2 opacity-40"></i>
                                <p>Aucune activité enregistrée</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="px-5 py-3 border-t border-gray-200">
                {{ $logs->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
