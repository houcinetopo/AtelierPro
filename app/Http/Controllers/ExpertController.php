<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Expert;
use App\Models\ExpertEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ExpertController extends Controller
{
    public function index(Request $request)
    {
        $query = Expert::with('emails')->withCount('repairOrders');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($request->get('actifs_only')) {
            $query->actifs();
        }

        $experts = $query->orderBy('nom_complet')->paginate(15);

        return view('experts.index', compact('experts'));
    }

    public function create()
    {
        return view('experts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nom_complet'  => ['required', 'string', 'max:255'],
            'cabinet'      => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'string', 'max:20'],
            'telephone_2'  => ['nullable', 'string', 'max:20'],
            'adresse'      => ['nullable', 'string', 'max:500'],
            'ville'        => ['nullable', 'string', 'max:100'],
            'code_postal'  => ['nullable', 'string', 'max:10'],
            'actif'        => ['nullable', 'boolean'],
            'notes'        => ['nullable', 'string', 'max:1000'],

            'emails'             => ['required', 'array', 'min:1'],
            'emails.*.email'     => ['required', 'email', 'max:255'],
            'emails.*.label'     => ['nullable', 'string', 'max:50'],
            'emails.*.is_primary'=> ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $expert = Expert::create([
                'nom_complet'  => $data['nom_complet'],
                'cabinet'      => $data['cabinet'] ?? null,
                'telephone'    => $data['telephone'] ?? null,
                'telephone_2'  => $data['telephone_2'] ?? null,
                'adresse'      => $data['adresse'] ?? null,
                'ville'        => $data['ville'] ?? null,
                'code_postal'  => $data['code_postal'] ?? null,
                'actif'        => $data['actif'] ?? true,
                'notes'        => $data['notes'] ?? null,
            ]);

            $this->syncEmails($expert, $data['emails']);

            DB::commit();

            ActivityLog::log('create', "Expert {$expert->nom_complet} créé", $expert);

            return redirect()->route('experts.show', $expert)
                ->with('success', "L'expert {$expert->nom_complet} a été créé.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function show(Expert $expert)
    {
        $expert->load(['emails', 'repairOrders' => fn($q) => $q->latest()->limit(10)]);
        $expert->loadCount('repairOrders');

        return view('experts.show', compact('expert'));
    }

    public function edit(Expert $expert)
    {
        $expert->load('emails');
        return view('experts.edit', compact('expert'));
    }

    public function update(Request $request, Expert $expert)
    {
        $data = $request->validate([
            'nom_complet'  => ['required', 'string', 'max:255'],
            'cabinet'      => ['nullable', 'string', 'max:255'],
            'telephone'    => ['nullable', 'string', 'max:20'],
            'telephone_2'  => ['nullable', 'string', 'max:20'],
            'adresse'      => ['nullable', 'string', 'max:500'],
            'ville'        => ['nullable', 'string', 'max:100'],
            'code_postal'  => ['nullable', 'string', 'max:10'],
            'actif'        => ['nullable', 'boolean'],
            'notes'        => ['nullable', 'string', 'max:1000'],

            'emails'             => ['required', 'array', 'min:1'],
            'emails.*.email'     => ['required', 'email', 'max:255'],
            'emails.*.label'     => ['nullable', 'string', 'max:50'],
            'emails.*.is_primary'=> ['nullable', 'boolean'],
        ]);

        DB::beginTransaction();
        try {
            $expert->update([
                'nom_complet'  => $data['nom_complet'],
                'cabinet'      => $data['cabinet'] ?? null,
                'telephone'    => $data['telephone'] ?? null,
                'telephone_2'  => $data['telephone_2'] ?? null,
                'adresse'      => $data['adresse'] ?? null,
                'ville'        => $data['ville'] ?? null,
                'code_postal'  => $data['code_postal'] ?? null,
                'actif'        => $data['actif'] ?? true,
                'notes'        => $data['notes'] ?? null,
            ]);

            $this->syncEmails($expert, $data['emails']);

            DB::commit();

            ActivityLog::log('update', "Expert {$expert->nom_complet} modifié", $expert);

            return redirect()->route('experts.show', $expert)
                ->with('success', "L'expert {$expert->nom_complet} a été mis à jour.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Erreur : ' . $e->getMessage());
        }
    }

    public function destroy(Expert $expert)
    {
        $name = $expert->nom_complet;

        if ($expert->repairOrders()->exists()) {
            return back()->with('error', "Impossible de supprimer l'expert {$name} car il est lié à des ordres de réparation.");
        }

        ActivityLog::log('delete', "Expert {$name} supprimé", $expert);
        $expert->delete();

        return redirect()->route('experts.index')
            ->with('success', "L'expert {$name} a été supprimé.");
    }

    // ══════════════════════════════════════════════
    // PRIVATE HELPERS
    // ══════════════════════════════════════════════

    private function syncEmails(Expert $expert, array $emails): void
    {
        $expert->emails()->delete();

        $hasPrimary = collect($emails)->contains(fn($e) => !empty($e['is_primary']));

        foreach ($emails as $i => $emailData) {
            $expert->emails()->create([
                'email'      => $emailData['email'],
                'label'      => $emailData['label'] ?? null,
                'is_primary' => !empty($emailData['is_primary']) || (!$hasPrimary && $i === 0),
            ]);
        }
    }
}
