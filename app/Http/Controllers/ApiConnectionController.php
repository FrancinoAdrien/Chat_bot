<?php

namespace App\Http\Controllers;

use App\Models\ApiConnection;
use App\Services\DynamicApiService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ApiConnectionController extends Controller
{
    public function __construct(
        private readonly DynamicApiService $apiService
    ) {}

    public function index(Request $request): View
    {
        $search = trim($request->query('search', ''));
        $connectionsQuery = ApiConnection::latest();

        if ($search !== '') {
            $connectionsQuery->where('name', 'like', "%{$search}%");
        }

        $connections = $connectionsQuery->get();
        return view('connections.index', compact('connections', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'base_url'    => ['required', 'url', 'max:255'],
            'login_url'   => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'active'      => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active', true);

        ApiConnection::create($data);

        return back()->with('success', "Connexion « {$data['name']} » ajoutée.");
    }

    public function update(Request $request, ApiConnection $connection): RedirectResponse
    {
        $data = $request->validate([
            'name'        => ['required', 'string', 'max:100'],
            'base_url'    => ['required', 'url', 'max:255'],
            'login_url'   => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'active'      => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->boolean('active');
        $connection->update($data);

        return back()->with('success', "Connexion « {$connection->name} » mise à jour.");
    }

    public function destroy(ApiConnection $connection): RedirectResponse
    {
        $name = $connection->name;
        $connection->delete();
        return back()->with('success', "Connexion « {$name} » supprimée.");
    }

    /**
     * Authenticate via login URL (email + password flow).
     */
    public function authenticate(Request $request, ApiConnection $connection): JsonResponse
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        try {
            $this->apiService->authenticate(
                $connection,
                $request->input('email'),
                $request->input('password')
            );

            return response()->json([
                'success' => true,
                'message' => "✅ Authentifié avec succès sur « {$connection->name} ».",
                'masked_token' => $connection->fresh()->masked_token,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Store a raw Bearer token directly.
     */
    public function storeToken(Request $request, ApiConnection $connection): JsonResponse
    {
        $request->validate(['token' => ['required', 'string', 'min:8']]);

        $this->apiService->authenticateWithToken($connection, $request->input('token'));

        return response()->json([
            'success'      => true,
            'message'      => "✅ Token enregistré pour « {$connection->name} ».",
            'masked_token' => $connection->fresh()->masked_token,
        ]);
    }

    /**
     * Remove token / disconnect.
     */
    public function disconnect(ApiConnection $connection): JsonResponse
    {
        $this->apiService->disconnect($connection);

        return response()->json([
            'success' => true,
            'message' => "🔌 Déconnecté de « {$connection->name} ».",
        ]);
    }

    /**
     * Ping the API to test connectivity.
     */
    public function ping(ApiConnection $connection): JsonResponse
    {
        $online = $this->apiService->ping($connection);

        return response()->json([
            'online'  => $online,
            'message' => $online
                ? "✅ « {$connection->name} » est joignable."
                : "❌ « {$connection->name} » est hors ligne.",
        ]);
    }
}
