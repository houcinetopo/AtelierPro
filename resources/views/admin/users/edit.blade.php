@extends('layouts.app')

@section('title', 'Modifier ' . $user->name)
@section('breadcrumb')
    <a href="{{ route('admin.users.index') }}" class="hover:text-primary-600">Utilisateurs</a>
    <i data-lucide="chevron-right" class="w-4 h-4 mx-1"></i>
    <span class="text-gray-700 font-medium">{{ $user->name }}</span>
@endsection

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-800">Modifier l'utilisateur</h1>
        <p class="text-sm text-gray-500 mt-0.5">Modifier les informations de {{ $user->name }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-200 p-6">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" enctype="multipart/form-data" class="space-y-5">
            @csrf
            @method('PUT')

            {{-- Nom --}}
            <div>
                <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nom complet <span class="text-red-500">*</span></label>
                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email <span class="text-red-500">*</span></label>
                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" required
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Téléphone --}}
            <div>
                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Téléphone</label>
                <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone) }}"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500">
                @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Rôle --}}
            <div>
                <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rôle <span class="text-red-500">*</span></label>
                <select id="role" name="role" required
                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                        @if($user->id === auth()->id()) disabled @endif>
                    @foreach ($roles as $key => $role)
                        <option value="{{ $key }}" {{ old('role', $user->role) == $key ? 'selected' : '' }}>
                            {{ $role['label'] }}
                        </option>
                    @endforeach
                </select>
                @if($user->id === auth()->id())
                    <input type="hidden" name="role" value="{{ $user->role }}">
                    <p class="text-xs text-amber-500 mt-1">Vous ne pouvez pas modifier votre propre rôle.</p>
                @endif
                @error('role') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Mot de passe (optionnel) --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Nouveau mot de passe</label>
                    <input type="password" id="password" name="password"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Laisser vide pour ne pas changer">
                    @error('password') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Confirmer</label>
                    <input type="password" id="password_confirmation" name="password_confirmation"
                           class="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-primary-500"
                           placeholder="Confirmer le nouveau mot de passe">
                </div>
            </div>

            {{-- Avatar --}}
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Photo de profil</label>
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-full bg-gray-100 overflow-hidden" id="avatar-preview">
                        <img src="{{ $user->avatar_url }}" class="w-16 h-16 rounded-full object-cover">
                    </div>
                    <div>
                        <input type="file" id="avatar" name="avatar" accept="image/jpeg,image/png,image/webp"
                               class="text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0
                                      file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700
                                      hover:file:bg-primary-100 cursor-pointer"
                               onchange="previewAvatar(this)">
                        <p class="text-xs text-gray-400 mt-1">JPG, PNG ou WebP. Max 2 Mo.</p>
                    </div>
                </div>
                @error('avatar') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Statut actif --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1"
                           {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                           class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                           @if($user->id === auth()->id()) disabled @endif>
                    <span class="text-sm text-gray-700">Compte actif</span>
                </label>
                @if($user->id === auth()->id())
                    <input type="hidden" name="is_active" value="1">
                    <p class="text-xs text-amber-500 mt-1">Vous ne pouvez pas désactiver votre propre compte.</p>
                @endif
            </div>

            {{-- Infos supplémentaires --}}
            <div class="bg-gray-50 rounded-lg p-4 text-xs text-gray-500 space-y-1">
                <p>Créé le : {{ $user->created_at->format('d/m/Y à H:i') }}</p>
                <p>Dernière connexion : {{ $user->last_login_at ? $user->last_login_at->format('d/m/Y à H:i') : 'Jamais' }}</p>
            </div>

            {{-- Actions --}}
            <div class="flex items-center justify-between pt-4 border-t border-gray-200">
                <a href="{{ route('admin.users.index') }}"
                   class="px-4 py-2.5 text-sm text-gray-600 hover:text-gray-800 font-medium">
                    Annuler
                </a>
                <button type="submit"
                        class="px-6 py-2.5 bg-primary-600 hover:bg-primary-700 text-white text-sm font-medium rounded-lg transition-colors flex items-center gap-2">
                    <i data-lucide="save" class="w-4 h-4"></i>
                    Enregistrer
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function previewAvatar(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('avatar-preview').innerHTML =
                `<img src="${e.target.result}" class="w-16 h-16 rounded-full object-cover">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
@endpush
@endsection
