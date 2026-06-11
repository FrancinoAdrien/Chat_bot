<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class CreateAdminUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create-admin';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Crée un utilisateur administrateur par défaut avec ADMIN001 et P455w0rd!';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Vérifier si l'utilisateur admin existe déjà
        $existingAdmin = User::where('matricule', 'ADMIN001')->first();
        
        if ($existingAdmin) {
            $this->error('❌ Un utilisateur avec le matricule ADMIN001 existe déjà!');
            return 1;
        }

        try {
            $admin = User::create([
                'name' => 'Admin',
                'prenom' => 'Système',
                'email' => 'admin@clinokeys.local',
                'matricule' => 'ADMIN001',
                'poste' => 'Administrateur Système',
                'password' => Hash::make('P455w0rd!'),
                'role' => 'admin',
                'is_active' => true,
            ]);

            $this->info('✅ Utilisateur administrateur créé avec succès!');
            $this->line('');
            $this->table(
                ['Champ', 'Valeur'],
                [
                    ['ID', $admin->id],
                    ['Nom', $admin->name . ' ' . $admin->prenom],
                    ['Email', $admin->email],
                    ['Matricule', $admin->matricule],
                    ['Poste', $admin->poste],
                    ['Rôle', $admin->role],
                    ['Actif', $admin->is_active ? 'Oui' : 'Non'],
                ]
            );
            $this->line('');
            $this->info('Identifiants de connexion:');
            $this->line('  Matricule: ADMIN001');
            $this->line('  Mot de passe: P455w0rd!');
            
            return 0;
        } catch (\Exception $e) {
            $this->error('❌ Erreur lors de la création de l\'utilisateur admin: ' . $e->getMessage());
            return 1;
        }
    }
}
