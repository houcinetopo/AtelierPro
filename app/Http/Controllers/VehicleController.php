<?php

namespace App\Http\Controllers;

use App\Http\Requests\VehicleRequest;
use App\Models\ActivityLog;
use App\Models\Client;
use App\Models\Vehicle;
use App\Models\VehiclePhoto;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VehicleController extends Controller
{
    public function index(Request $request)
    {
        $query = Vehicle::with('client');

        if ($search = $request->get('search')) {
            $query->search($search);
        }
        if ($marque = $request->get('marque')) {
            $query->byMarque($marque);
        }
        if ($clientId = $request->get('client_id')) {
            $query->where('client_id', $clientId);
        }

        $vehicles = $query->orderByDesc('created_at')->paginate(15);
        $marques = Vehicle::MARQUES;

        return view('vehicles.index', compact('vehicles', 'marques'));
    }

    public function create(Request $request)
    {
        $clients = Client::orderBy('nom_complet')->orderBy('raison_sociale')->get();
        $marques = Vehicle::MARQUES;
        $carburants = Vehicle::CARBURANTS;
        $preselectedClient = $request->get('client_id');

        return view('vehicles.create', compact('clients', 'marques', 'carburants', 'preselectedClient'));
    }

    public function store(VehicleRequest $request)
    {
        $data = $request->validated();
        unset($data['photos']);
        $vehicle = Vehicle::create($data);

        // Upload photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store("vehicles/{$vehicle->id}", 'public');
                VehiclePhoto::create([
                    'vehicle_id'  => $vehicle->id,
                    'path'        => $path,
                    'type'        => 'general',
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        ActivityLog::log('create', "Ajout du véhicule {$vehicle->display_label}", $vehicle);

        // Rediriger vers la fiche client si on vient de là
        if ($request->get('redirect') === 'client') {
            return redirect()->route('clients.show', $vehicle->client_id)
                ->with('success', "Le véhicule {$vehicle->display_label} a été ajouté.");
        }

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Le véhicule {$vehicle->display_label} a été ajouté.");
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['client', 'photos']);
        return view('vehicles.show', compact('vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $clients = Client::orderBy('nom_complet')->orderBy('raison_sociale')->get();
        $marques = Vehicle::MARQUES;
        $carburants = Vehicle::CARBURANTS;

        return view('vehicles.edit', compact('vehicle', 'clients', 'marques', 'carburants'));
    }

    public function update(VehicleRequest $request, Vehicle $vehicle)
    {
        $data = $request->validated();
        unset($data['photos']);
        $oldValues = $vehicle->only(['immatriculation', 'marque', 'modele', 'kilometrage']);

        $vehicle->update($data);

        // Upload nouvelles photos
        if ($request->hasFile('photos')) {
            foreach ($request->file('photos') as $photo) {
                $path = $photo->store("vehicles/{$vehicle->id}", 'public');
                VehiclePhoto::create([
                    'vehicle_id'  => $vehicle->id,
                    'path'        => $path,
                    'type'        => 'general',
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        ActivityLog::log('update', "Modification du véhicule {$vehicle->display_label}", $vehicle, $oldValues);

        return redirect()->route('vehicles.show', $vehicle)
            ->with('success', "Le véhicule a été modifié.");
    }

    public function destroy(Vehicle $vehicle)
    {
        $label = $vehicle->display_label;
        // Supprimer les photos physiques
        foreach ($vehicle->photos as $photo) {
            Storage::disk('public')->delete($photo->path);
        }
        ActivityLog::log('delete', "Suppression du véhicule {$label}", $vehicle);
        $vehicle->delete();

        return redirect()->route('vehicles.index')
            ->with('success', "Le véhicule {$label} a été supprimé.");
    }

    /**
     * Upload de photos additionnelles (AJAX)
     */
    public function uploadPhotos(Request $request, Vehicle $vehicle)
    {
        $request->validate([
            'photos'   => ['required', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'type'     => ['nullable', 'in:avant_reparation,apres_reparation,dommage,general'],
        ]);

        $uploaded = [];
        foreach ($request->file('photos') as $photo) {
            $path = $photo->store("vehicles/{$vehicle->id}", 'public');
            $vp = VehiclePhoto::create([
                'vehicle_id'  => $vehicle->id,
                'path'        => $path,
                'type'        => $request->input('type', 'general'),
                'uploaded_by' => auth()->id(),
            ]);
            $uploaded[] = $vp;
        }

        ActivityLog::log('create', count($uploaded) . " photo(s) ajoutée(s) au véhicule {$vehicle->display_label}", $vehicle);

        return back()->with('success', count($uploaded) . ' photo(s) ajoutée(s) avec succès.');
    }

    /**
     * Suppression d'une photo
     */
    public function deletePhoto(Vehicle $vehicle, VehiclePhoto $photo)
    {
        if ($photo->vehicle_id !== $vehicle->id) abort(404);

        Storage::disk('public')->delete($photo->path);
        $photo->delete();

        return back()->with('success', 'Photo supprimée.');
    }

    /**
     * API: recherche véhicules par client (AJAX)
     */
    public function apiByClient(Request $request)
    {
        $clientId = $request->get('client_id');
        if (!$clientId) return response()->json([]);

        $vehicles = Vehicle::where('client_id', $clientId)
            ->select('id', 'immatriculation', 'marque', 'modele', 'couleur')
            ->orderBy('immatriculation')
            ->get()
            ->map(fn ($v) => [
                'id'   => $v->id,
                'text' => "{$v->marque} {$v->modele} — {$v->immatriculation}",
            ]);

        return response()->json($vehicles);
    }
}
