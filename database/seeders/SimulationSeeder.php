<?php

namespace Database\Seeders;

use App\Models\Group;
use App\Models\Utilisateur;
use App\Models\Test;
use App\Models\Question;
use App\Models\OptionQcm;
use App\Enums\UserRole;
use App\Enums\TestStatus;
use App\Enums\QuestionType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SimulationSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Créer le groupe
        $group = Group::create([
            'id_groupe' => 13,
            'nom_groupe' => 'Classe Test',
            'description' => 'Groupe de test pour simulation',
        ]);

        // 2. Créer l'enseignant ens0001
        $teacher = Utilisateur::create([
            'id_utilisateur' => 10,
            'id_groupe' => $group->id_groupe,
            'nom' => 'Enseignant Un',
            'email' => 'ens0001@univ.fr',
            'matricule' => 'ens0001',
            'password' => Hash::make('password'),
            'role' => UserRole::Enseignant,
            'est_valider' => true,
        ]);

        // 3. Créer l'étudiant etu001
        $student = Utilisateur::create([
            'id_utilisateur' => 11,
            'id_groupe' => $group->id_groupe,
            'nom' => 'Etudiant Un',
            'email' => 'etu001@univ.fr',
            'matricule' => 'etu001',
            'password' => Hash::make('password'),
            'role' => UserRole::Etudiant,
            'est_valider' => true,
        ]);

        // 4. Créer le test
        $test = Test::create([
            'id_test' => 1005,
            'id_utilisateur' => $teacher->id_utilisateur,
            'id_groupe' => $group->id_groupe,
            'titre' => 'Test simulation ens0001',
            'description' => null,
            'duree_minutes' => 60,
            'max_questions' => 15,
            'note_max' => 20,
            'date_declechement' => null,
            'status' => TestStatus::EnAttente,
        ]);

        // 5. Créer les questions QCM avec leurs options
        $questionsData = [
            [
                'id_question' => 200,
                'texte_question' => "Qu'est-ce qu'une clé primaire dans une base de données relationnelle ?",
                'reponse_correcte' => 'Un identifiant unique pour chaque enregistrement',
                'options' => [
                    ['texte_option' => 'Un identifiant unique pour chaque enregistrement', 'est_correcte' => true],
                    ['texte_option' => 'Une clé utilisée pour chiffrer les données', 'est_correcte' => false],
                    ['texte_option' => 'Un index sur plusieurs colonnes', 'est_correcte' => false],
                    ['texte_option' => 'Une contrainte de vérification', 'est_correcte' => false],
                ],
            ],
            [
                'id_question' => 201,
                'texte_question' => 'En Java, quel mot-clé permet d\'empêcher une classe d\'être héritée ?',
                'reponse_correcte' => 'final',
                'options' => [
                    ['texte_option' => 'static', 'est_correcte' => false],
                    ['texte_option' => 'final', 'est_correcte' => true],
                    ['texte_option' => 'abstract', 'est_correcte' => false],
                    ['texte_option' => 'private', 'est_correcte' => false],
                ],
            ],
            [
                'id_question' => 202,
                'texte_question' => 'Quelle est la complexité temporelle de la recherche binaire dans un tableau trié ?',
                'reponse_correcte' => 'O(log n)',
                'options' => [
                    ['texte_option' => 'O(n)', 'est_correcte' => false],
                    ['texte_option' => 'O(log n)', 'est_correcte' => true],
                    ['texte_option' => 'O(n²)', 'est_correcte' => false],
                    ['texte_option' => 'O(n log n)', 'est_correcte' => false],
                ],
            ],
            [
                'id_question' => 203,
                'texte_question' => 'Quel protocole est utilisé pour envoyer un e-mail ?',
                'reponse_correcte' => 'SMTP',
                'options' => [
                    ['texte_option' => 'FTP', 'est_correcte' => false],
                    ['texte_option' => 'SMTP', 'est_correcte' => true],
                    ['texte_option' => 'HTTP', 'est_correcte' => false],
                    ['texte_option' => 'POP3', 'est_correcte' => false],
                ],
            ],
            [
                'id_question' => 204,
                'texte_question' => "Quel est le rôle du noyau (kernel) d'un système d'exploitation ?",
                'reponse_correcte' => 'Gérer les ressources matérielles et logicielles',
                'options' => [
                    ['texte_option' => 'Gérer les ressources matérielles et logicielles', 'est_correcte' => true],
                    ['texte_option' => 'Afficher l\'interface utilisateur', 'est_correcte' => false],
                    ['texte_option' => 'Compiler les programmes', 'est_correcte' => false],
                    ['texte_option' => 'Gérer les mots de passe', 'est_correcte' => false],
                ],
            ],
        ];

        foreach ($questionsData as $qData) {
            $question = Question::create([
                'id_question' => $qData['id_question'],
                'id_test' => $test->id_test,
                'texte_question' => $qData['texte_question'],
                'type_question' => QuestionType::Qcm,
                'points' => 1,
                'reponse_correcte' => $qData['reponse_correcte'],
            ]);

            foreach ($qData['options'] as $optData) {
                OptionQcm::create([
                    'id_question' => $question->id_question,
                    'texte_option' => $optData['texte_option'],
                    'est_correcte' => $optData['est_correcte'],
                ]);
            }
        }

        $this->command->info('Données de simulation insérées avec succès !');
        $this->command->info("  Groupe [{$group->id_groupe}] : {$group->nom_groupe}");
        $this->command->info("  Enseignant [{$teacher->id_utilisateur}] : {$teacher->email} / password");
        $this->command->info("  Étudiant   [{$student->id_utilisateur}] : {$student->email} / password");
        $this->command->info("  Test [{$test->id_test}] : {$test->titre}");
        $this->command->info("  5 questions QCM (200-204) avec 20 options ajoutées.");
    }
}
