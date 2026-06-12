<?php

namespace App\Console\Commands;

use App\Models\Utilisateur;
use Illuminate\Console\Command;

class AnonymizeData extends Command
{
    protected $signature = 'db:anonymize {--backup : Crée un fichier CSV de mapping avant anonymisation}';
    protected $description = 'Remplace les données réelles (nom, email, matricule) par des données fictives';

    public function handle(): int
    {
        $this->info('=== Anonymisation des utilisateurs ===');

        $total = Utilisateur::count();
        if ($total === 0) {
            $this->warn('Aucun utilisateur trouvé.');
            return Command::SUCCESS;
        }

        $this->newLine();
        $bar = $this->output->createProgressBar($total);
        $bar->start();

        $backup = [];
        $counters = ['etudiant' => 1, 'enseignant' => 1, 'admin' => 1];

        Utilisateur::chunk(100, function ($users) use (&$bar, &$backup, &$counters) {
            foreach ($users as $user) {
                $prefix = match ($user->role?->value) {
                    'etudiant' => 'ETU',
                    'enseignant' => 'ENS',
                    'admin' => 'ADM',
                    default => 'USR',
                };
                $num = str_pad($counters[$user->role?->value ?? 'etudiant']++, 4, '0', STR_PAD_LEFT);
                $fakeName = "{$prefix} {$num}";
                $fakeEmail = strtolower("{$prefix}{$num}@e-exam.fake");
                $fakeMatricule = "{$prefix}-{$num}";

                $backup[] = [
                    $user->id_utilisateur,
                    $user->nom,
                    $user->email,
                    $user->matricule,
                    $fakeName,
                    $fakeEmail,
                    $fakeMatricule,
                ];

                $user->update([
                    'nom' => $fakeName,
                    'email' => $fakeEmail,
                    'matricule' => $fakeMatricule,
                ]);

                $bar->advance();
            }
        });

        $bar->finish();
        $this->newLine(2);

        if ($this->option('backup')) {
            $path = storage_path('app/anonymize_backup_' . now()->format('Ymd_His') . '.csv');
            $handle = fopen($path, 'w');
            fputcsv($handle, ['id', 'ancien_nom', 'ancien_email', 'ancien_matricule', 'nouveau_nom', 'nouveau_email', 'nouveau_matricule']);
            foreach ($backup as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
            $this->info("Fichier de sauvegarde créé : {$path}");
        }

        $this->info("{$total} utilisateur(s) anonymisé(s) avec succès !");
        $this->warn('Les mots de passe sont conservés inchangés.');

        return Command::SUCCESS;
    }
}
