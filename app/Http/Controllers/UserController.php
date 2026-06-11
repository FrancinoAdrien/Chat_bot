<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = trim($request->query('search', ''));
        $usersQuery = User::orderBy('name');

        if ($search !== '') {
            $usersQuery->where(function ($query) use ($search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhere('prenom', 'like', "%{$search}%")
                      ->orWhere('matricule', 'like', "%{$search}%")
                      ->orWhere('poste', 'like', "%{$search}%");
            });
        }

        $users = $usersQuery->get();
        return view('users.index', compact('users', 'search'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('users.form', ['user' => new User()]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'matricule' => 'required|string|unique:users,matricule|max:255',
            'poste' => 'nullable|string|max:255',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'custom_password' => 'nullable|string|min:8', // Only if not auto-generated
        ]);

        // Generate password if not provided
        $password = $validated['custom_password'] ?? $this->generateSecurePassword();

        User::create([
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'matricule' => strtoupper($validated['matricule']),
            'poste' => $validated['poste'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
            'password' => Hash::make($password),
        ]);

        return redirect()->route('users.index')
            ->with('success', "Utilisateur créé avec succès ! Le mot de passe généré est : <strong>{$password}</strong> (à conserver précieusement).");
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('users.form', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'prenom' => 'nullable|string|max:255',
            'matricule' => 'required|string|max:255|unique:users,matricule,' . $user->id,
            'poste' => 'nullable|string|max:255',
            'role' => 'required|in:admin,user',
            'is_active' => 'boolean',
            'reset_password' => 'boolean',
            'custom_password' => 'nullable|string|min:8',
        ]);

        $data = [
            'name' => $validated['name'],
            'prenom' => $validated['prenom'],
            'matricule' => strtoupper($validated['matricule']),
            'poste' => $validated['poste'],
            'role' => $validated['role'],
            'is_active' => $request->has('is_active'),
        ];

        $message = "Utilisateur mis à jour avec succès.";

        if ($request->has('reset_password')) {
            $password = $validated['custom_password'] ?? $this->generateSecurePassword();
            $data['password'] = Hash::make($password);
            $message = "Utilisateur mis à jour. Le NOUVEAU mot de passe est : <strong>{$password}</strong>";
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', $message);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Vous ne pouvez pas vous supprimer vous-même.');
        }

        $user->delete();
        return redirect()->route('users.index')->with('success', 'Utilisateur supprimé.');
    }

    /**
     * Generate a secure password with 1 upper, 1 lower, 1 number, 1 special char.
     */
    private function generateSecurePassword(int $length = 12): string
    {
        $upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $lower = 'abcdefghijklmnopqrstuvwxyz';
        $numbers = '0123456789';
        $symbols = '!@#$%^&*()_+-=[]{}|;:,.<>?';

        $password = [
            $upper[random_int(0, strlen($upper) - 1)],
            $lower[random_int(0, strlen($lower) - 1)],
            $numbers[random_int(0, strlen($numbers) - 1)],
            $symbols[random_int(0, strlen($symbols) - 1)],
        ];

        $allChars = $upper . $lower . $numbers . $symbols;

        for ($i = 4; $i < $length; $i++) {
            $password[] = $allChars[random_int(0, strlen($allChars) - 1)];
        }

        shuffle($password);

        return implode('', $password);
    }
}
