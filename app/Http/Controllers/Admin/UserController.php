<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserRequest;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::query();

        // Recherche
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtres
        if ($role = $request->get('role')) {
            $query->where('role', $role);
        }

        if ($request->has('status')) {
            $query->where('is_active', $request->boolean('status'));
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        $roles = config('roles.roles');
        return view('admin.users.create', compact('roles'));
    }

    public function store(UserRequest $request)
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        // Upload avatar
        if ($request->hasFile('avatar')) {
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user = User::create($data);

        ActivityLog::log('create', "Création de l'utilisateur {$user->name}", $user);

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été créé avec succès.");
    }

    public function edit(User $user)
    {
        $roles = config('roles.roles');
        return view('admin.users.edit', compact('user', 'roles'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        $oldValues = $user->only(['name', 'email', 'role', 'phone', 'is_active']);

        // Mot de passe : seulement si renseigné
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        // Upload avatar
        if ($request->hasFile('avatar')) {
            // Supprimer l'ancien avatar
            if ($user->avatar) {
                Storage::disk('public')->delete($user->avatar);
            }
            $data['avatar'] = $request->file('avatar')->store('avatars', 'public');
        }

        $user->update($data);

        ActivityLog::log(
            'update',
            "Modification de l'utilisateur {$user->name}",
            $user,
            $oldValues,
            $user->only(['name', 'email', 'role', 'phone', 'is_active'])
        );

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$user->name} a été modifié avec succès.");
    }

    public function destroy(User $user)
    {
        // Empêcher la suppression de son propre compte
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas supprimer votre propre compte.');
        }

        $name = $user->name;
        ActivityLog::log('delete', "Suppression de l'utilisateur {$name}", $user);

        $user->delete(); // Soft delete

        return redirect()->route('admin.users.index')
            ->with('success', "L'utilisateur {$name} a été supprimé.");
    }

    public function toggleStatus(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas désactiver votre propre compte.');
        }

        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activé' : 'désactivé';
        ActivityLog::log('update', "Compte de {$user->name} {$status}", $user);

        return back()->with('success', "Le compte de {$user->name} a été {$status}.");
    }

    public function activityLogs(Request $request)
    {
        $query = ActivityLog::with('user')->orderBy('created_at', 'desc');

        if ($userId = $request->get('user_id')) {
            $query->where('user_id', $userId);
        }

        if ($action = $request->get('action')) {
            $query->where('action', $action);
        }

        if ($date = $request->get('date')) {
            $query->whereDate('created_at', $date);
        }

        $logs = $query->paginate(30);
        $users = User::orderBy('name')->get(['id', 'name']);

        return view('admin.users.activity-logs', compact('logs', 'users'));
    }
}
