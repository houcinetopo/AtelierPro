<?php

namespace App\Http\Controllers;

use App\Http\Requests\ClientRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Client::withCount('vehicles');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($type = $request->get('type_client')) {
            $query->where('type_client', $type);
        }
        if ($request->get('with_debt')) {
            $query->withDebt();
        }
        if ($request->get('blacklisted')) {
            $query->where('is_blacklisted', true);
        }

        $clients = $query->orderByDesc('created_at')->paginate(15);

        $stats = [
            'total'        => Client::count(),
            'particuliers' => Client::particuliers()->count(),
            'societes'     => Client::societes()->count(),
            'with_debt'    => Client::withDebt()->count(),
            'total_debt'   => Client::sum('solde_credit'),
        ];

        return view('clients.index', compact('clients', 'stats'));
    }

    public function create()
    {
        $sources = Client::SOURCES;
        return view('clients.create', compact('sources'));
    }

    public function store(ClientRequest $request)
    {
        $data = $request->validated();
        $data['is_blacklisted'] = $request->boolean('is_blacklisted');
        $client = Client::create($data);

        ActivityLog::log('create', "Ajout du client {$client->display_name}", $client);

        return redirect()->route('clients.show', $client)
            ->with('success', "Le client {$client->display_name} a été créé avec succès.");
    }

    public function show(Client $client)
    {
        $client->load(['vehicles.photos', 'vehicles' => function ($q) {
            $q->orderByDesc('created_at');
        }]);

        return view('clients.show', compact('client'));
    }

    public function edit(Client $client)
    {
        $sources = Client::SOURCES;
        return view('clients.edit', compact('client', 'sources'));
    }

    public function update(ClientRequest $request, Client $client)
    {
        $data = $request->validated();
        $data['is_blacklisted'] = $request->boolean('is_blacklisted');
        $oldValues = $client->only(['nom_complet', 'raison_sociale', 'telephone', 'solde_credit']);

        $client->update($data);

        ActivityLog::log('update', "Modification du client {$client->display_name}", $client, $oldValues);

        return redirect()->route('clients.show', $client)
            ->with('success', "Le client {$client->display_name} a été modifié.");
    }

    public function destroy(Client $client)
    {
        $name = $client->display_name;
        ActivityLog::log('delete', "Suppression du client {$name}", $client);
        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', "Le client {$name} a été supprimé.");
    }

    /**
     * API : Recherche de clients pour les sélecteurs (AJAX)
     */
    public function apiSearch(Request $request)
    {
        $search = $request->get('q', '');
        $clients = Client::search($search)
            ->select('id', 'type_client', 'nom_complet', 'raison_sociale', 'telephone', 'cin', 'ice')
            ->limit(15)
            ->get()
            ->map(fn ($c) => [
                'id'    => $c->id,
                'text'  => $c->display_name . ($c->telephone ? " — {$c->telephone}" : ''),
                'type'  => $c->type_client,
                'phone' => $c->telephone,
            ]);

        return response()->json($clients);
    }

    /**
     * Export CSV
     */
    public function export()
    {
        $clients = Client::withCount('vehicles')->orderBy('created_at', 'desc')->get();
        $filename = 'clients_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($clients) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            fputcsv($file, ['Type', 'Nom/Raison Sociale', 'CIN/ICE', 'Téléphone', 'Email', 'Ville', 'Véhicules', 'Solde crédit', 'Source'], ';');
            foreach ($clients as $c) {
                fputcsv($file, [
                    $c->type_client, $c->display_name, $c->legal_id,
                    $c->telephone, $c->email, $c->ville,
                    $c->vehicles_count, $c->solde_credit, $c->source_label,
                ], ';');
            }
            fclose($file);
        };

        ActivityLog::log('export', 'Export de la liste des clients en CSV');
        return response()->stream($callback, 200, $headers);
    }
}
