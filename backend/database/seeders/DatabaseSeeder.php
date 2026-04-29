<?php

namespace Database\Seeders;

use App\Models\Categorie;
use App\Models\Evaluation;
use App\Models\Groupe;
use App\Models\Joueur;
use App\Models\Paiement;
use App\Models\Presence;
use App\Models\Seance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Faker\Factory as Faker;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $faker = Faker::create('fr_FR');

        // 1. Super Admin
        User::firstOrCreate(
            ['email' => 'admin@minifoot.com'],
            [
                'nom'      => 'Admin',
                'prenom'   => 'Super',
                'email'    => 'admin@minifoot.com',
                'password' => Hash::make('password'),
                'role'     => 'superadmin',
            ]
        );

        // 2. Coaches
        $coaches = [];
        $coachEmails = ['coach1@minifoot.com', 'coach2@minifoot.com', 'coach3@minifoot.com'];
        foreach ($coachEmails as $email) {
            $coaches[] = User::firstOrCreate(
                ['email' => $email],
                [
                    'nom'      => $faker->lastName,
                    'prenom'   => $faker->firstName,
                    'email'    => $email,
                    'password' => Hash::make('password'),
                    'role'     => 'coach',
                ]
            );
        }

        // 3. Categories
        $categoriesData = [
            ['nom' => 'U7', 'description' => 'Enfants de 6 et 7 ans'],
            ['nom' => 'U9', 'description' => 'Enfants de 8 et 9 ans'],
            ['nom' => 'U11', 'description' => 'Enfants de 10 et 11 ans'],
            ['nom' => 'U13', 'description' => 'Enfants de 12 et 13 ans'],
        ];

        $categories = [];
        foreach ($categoriesData as $data) {
            $categories[] = Categorie::firstOrCreate(['nom' => $data['nom']], $data);
        }

        // 4. Groups
        $groupes = [];
        foreach ($categories as $cat) {
            // 2 groups per category
            for ($i = 1; $i <= 2; $i++) {
                $groupes[] = Groupe::create([
                    'nom' => $cat->nom . ' - Groupe ' . $i,
                    'categorie_id' => $cat->id,
                    'coach_id' => $coaches[array_rand($coaches)]->id,
                ]);
            }
        }

        // 5. Parents and Players
        $parents = [];
        for ($i = 1; $i <= 10; $i++) {
            $parentEmail = "parent$i@gmail.com";
            $parent = User::firstOrCreate(
                ['email' => $parentEmail],
                [
                    'nom'      => $faker->lastName,
                    'prenom'   => $faker->firstName,
                    'email'    => $parentEmail,
                    'password' => Hash::make('password'),
                    'role'     => 'parent',
                ]
            );
            $parents[] = $parent;

            // 1 or 2 players per parent
            $numPlayers = rand(1, 2);
            for ($j = 0; $j < $numPlayers; $j++) {
                $joueur = Joueur::create([
                    'nom' => $parent->nom,
                    'prenom' => $faker->firstNameMale,
                    'date_naissance' => $faker->date('Y-m-d', '-7 years'),
                    'parent_id' => $parent->id,
                    'categorie_id' => $categories[array_rand($categories)]->id,
                ]);
                
                // Assign player to a random group of their category
                $possibleGroups = array_filter($groupes, function($g) use ($joueur) {
                    return $g->categorie_id == $joueur->categorie_id;
                });
                if (!empty($possibleGroups)) {
                    $randomGroup = $possibleGroups[array_rand($possibleGroups)];
                    $joueur->update(['groupe_id' => $randomGroup->id]);
                }
            }
        }

        // 6. Sessions (Seances)
        foreach ($groupes as $groupe) {
            // 5 past sessions
            for ($i = 1; $i <= 5; $i++) {
                $seance = Seance::create([
                    'titre' => "Entraînement " . $faker->word,
                    'date_seance' => Carbon::now()->subDays($i * 3)->format('Y-m-d'),
                    'heure_debut' => '14:00:00',
                    'heure_fin' => '16:00:00',
                    'groupe_id' => $groupe->id,
                    'coach_id' => $groupe->coach_id,
                ]);

                // 7. Attendances (Presences)
                $playersInGroup = Joueur::where('groupe_id', $groupe->id)->get();
                foreach ($playersInGroup as $joueur) {
                    $isPresent = $faker->boolean(80); // 80% present
                    Presence::create([
                        'seance_id' => $seance->id,
                        'joueur_id' => $joueur->id,
                        'est_present' => $isPresent,
                        'motif_absence' => $isPresent ? null : $faker->sentence,
                    ]);

                    // 8. Evaluations (for some sessions)
                    if ($isPresent && $faker->boolean(30)) {
                        Evaluation::create([
                            'note' => $faker->numberBetween(5, 10),
                            'commentaire' => $faker->sentence,
                            'date_evaluation' => $seance->date_seance,
                            'joueur_id' => $joueur->id,
                            'coach_id' => $groupe->coach_id,
                            'seance_id' => $seance->id,
                        ]);
                    }
                }
            }
            
            // 2 future sessions
            for ($i = 1; $i <= 2; $i++) {
                Seance::create([
                    'titre' => "Entraînement programmé",
                    'date_seance' => Carbon::now()->addDays($i * 3)->format('Y-m-d'),
                    'heure_debut' => '14:00:00',
                    'heure_fin' => '16:00:00',
                    'groupe_id' => $groupe->id,
                    'coach_id' => $groupe->coach_id,
                ]);
            }
        }

        // 9. Payments
        foreach ($parents as $parent) {
            // 3 past payments
            for ($i = 0; $i < 3; $i++) {
                Paiement::create([
                    'montant' => 50.00,
                    'mois_concerne' => Carbon::now()->subMonths($i)->startOfMonth()->format('Y-m-d'),
                    'stripe_transaction_id' => 'ch_' . $faker->regexify('[A-Za-z0-9]{24}'),
                    'statut' => 'paid',
                    'parent_id' => $parent->id,
                ]);
            }
        }
    }
}
