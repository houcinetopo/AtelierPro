<?php

namespace App\Http\Controllers;

use App\Http\Requests\EmployeePaymentRequest;
use App\Http\Requests\EmployeeRequest;
use App\Models\ActivityLog;
use App\Models\Employee;
use App\Models\EmployeePayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    // ══════════════════════════════════════════════
    // CRUD EMPLOYÉS
    // ══════════════════════════════════════════════

    public function index(Request $request)
    {
        $query = Employee::query()->withCount('payments');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($poste = $request->get('poste')) {
            $query->byPoste($poste);
        }
        if ($statut = $request->get('statut')) {
            $query->where('statut', $statut);
        }
        if ($contrat = $request->get('type_contrat')) {
            $query->where('type_contrat', $contrat);
        }

        $employees = $query->orderBy('nom_complet')->paginate(15);
        $postes = Employee::POSTES;

        // Stats rapides
        $stats = [
            'total'  => Employee::count(),
            'actifs' => Employee::active()->count(),
            'masse_salariale' => Employee::active()->sum('salaire_base'),
        ];

        return view('employees.index', compact('employees', 'postes', 'stats'));
    }

    public function create()
    {
        $postes = Employee::POSTES;
        $typesContrat = Employee::TYPES_CONTRAT;
        return view('employees.create', compact('postes', 'typesContrat'));
    }

    public function store(EmployeeRequest $request)
    {
        $data = $request->validated();

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee = Employee::create($data);

        ActivityLog::log('create', "Ajout de l'employé {$employee->nom_complet}", $employee);

        return redirect()->route('employees.show', $employee)
            ->with('success', "L'employé {$employee->nom_complet} a été ajouté avec succès.");
    }

    public function show(Employee $employee)
    {
        $employee->load(['payments' => function ($q) {
            $q->orderByDesc('date_paiement')->limit(24);
        }, 'payments.createdBy']);

        $totalPaid = $employee->payments()->sum('net_paye');
        $currentYearPaid = $employee->payments()
            ->whereYear('date_paiement', now()->year)
            ->sum('net_paye');

        return view('employees.show', compact('employee', 'totalPaid', 'currentYearPaid'));
    }

    public function edit(Employee $employee)
    {
        $postes = Employee::POSTES;
        $typesContrat = Employee::TYPES_CONTRAT;
        return view('employees.edit', compact('employee', 'postes', 'typesContrat'));
    }

    public function update(EmployeeRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $oldValues = $employee->only(['nom_complet', 'poste', 'salaire_base', 'statut']);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employees/photos', 'public');
        }

        $employee->update($data);

        ActivityLog::log('update', "Modification de l'employé {$employee->nom_complet}", $employee, $oldValues);

        return redirect()->route('employees.show', $employee)
            ->with('success', "L'employé {$employee->nom_complet} a été modifié.");
    }

    public function destroy(Employee $employee)
    {
        $name = $employee->nom_complet;
        ActivityLog::log('delete', "Suppression de l'employé {$name}", $employee);
        $employee->delete();

        return redirect()->route('employees.index')
            ->with('success', "L'employé {$name} a été supprimé.");
    }

    // ══════════════════════════════════════════════
    // PAIEMENTS DE SALAIRE
    // ══════════════════════════════════════════════

    public function storePayment(EmployeePaymentRequest $request, Employee $employee)
    {
        $data = $request->validated();
        $data['employee_id'] = $employee->id;
        $data['prime'] = $data['prime'] ?? 0;
        $data['deduction'] = $data['deduction'] ?? 0;
        $data['net_paye'] = $data['montant'] + $data['prime'] - $data['deduction'];
        $data['created_by'] = auth()->id();

        // Vérifier si la période est déjà payée
        if ($employee->isPaidForPeriod($data['periode'])) {
            return back()->with('error', "Le salaire de la période {$data['periode']} a déjà été payé pour cet employé.");
        }

        $payment = EmployeePayment::create($data);

        ActivityLog::log('create', "Paiement de salaire pour {$employee->nom_complet} — période {$payment->periode_label}", $payment);

        return back()->with('success', "Le paiement de {$payment->net_paye} DH a été enregistré pour {$employee->nom_complet}.");
    }

    public function destroyPayment(Employee $employee, EmployeePayment $payment)
    {
        if ($payment->employee_id !== $employee->id) {
            abort(404);
        }

        ActivityLog::log('delete', "Suppression paiement {$payment->periode_label} pour {$employee->nom_complet}", $payment);
        $payment->delete();

        return back()->with('success', 'Le paiement a été supprimé.');
    }

    // ══════════════════════════════════════════════
    // EXPORT
    // ══════════════════════════════════════════════

    public function exportExcel()
    {
        // Placeholder — Sera implémenté avec Maatwebsite/Excel
        // Pour l'instant, export CSV simple
        $employees = Employee::orderBy('nom_complet')->get();
        $filename = 'employes_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($employees) {
            $file = fopen('php://output', 'w');
            // BOM UTF-8
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF));
            // En-têtes
            fputcsv($file, ['Nom', 'CIN', 'Poste', 'Contrat', 'Salaire Base', 'Jours/Mois', 'Salaire Jour', 'Téléphone', 'CNSS', 'Statut', 'Date Embauche'], ';');
            // Données
            foreach ($employees as $e) {
                fputcsv($file, [
                    $e->nom_complet, $e->cin, $e->poste_label, $e->type_contrat,
                    $e->salaire_base, $e->jours_travail_mois, $e->salaire_journalier,
                    $e->telephone, $e->cnss, $e->statut,
                    $e->date_embauche?->format('d/m/Y'),
                ], ';');
            }
            fclose($file);
        };

        ActivityLog::log('export', 'Export de la liste des employés en CSV');

        return response()->stream($callback, 200, $headers);
    }
}
