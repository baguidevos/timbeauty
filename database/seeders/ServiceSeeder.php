<?php

namespace Database\Seeders;

use App\Models\Service;
use App\Models\ServiceCategory;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categoriesData = [
            [
                'name' => 'Coupe Homme',
                'description' => 'Coupes de cheveux hommes et enfants, finitions précises',
                'icon' => 'heroicon-o-scissors',
                'order' => 1,
                'services' => [
                    [
                        'name' => 'Coupe Homme Classique',
                        'description' => 'Coupe aux ciseaux ou tondeuse avec contour soigné',
                        'price' => 3000,
                        'duration' => 30,
                        'commissionRate' => 0.10,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Dégradé Américain (Fade)',
                        'description' => 'Dégradé à blanc haut, moyen ou bas avec finition rasoir',
                        'price' => 4000,
                        'duration' => 35,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Coupe Ciseaux & Styling',
                        'description' => 'Coupe personnalisée aux ciseaux avec coiffage et produit de finition',
                        'price' => 5000,
                        'duration' => 40,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Coupe Enfant (-12 ans)',
                        'description' => 'Coupe adaptée aux jeunes enfants avec douceur et patience',
                        'price' => 2000,
                        'duration' => 20,
                        'commissionRate' => 0.10,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Coupe Étudiant',
                        'description' => 'Tarif préférentiel sur présentation de la carte étudiant',
                        'price' => 2500,
                        'duration' => 25,
                        'commissionRate' => 0.10,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Motif & Hair Tattoo (Freestyle)',
                        'description' => 'Dessins artistiques personnalisés réalisés au rasoir de précision',
                        'price' => 2500,
                        'duration' => 20,
                        'commissionRate' => 0.20,
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Barbe & Rasage',
                'description' => 'Entretien, taille et soins complets pour la barbe',
                'icon' => 'heroicon-o-face-smile',
                'order' => 2,
                'services' => [
                    [
                        'name' => 'Taille & Contours Barbe',
                        'description' => 'Tracé net des contours et égalisation de la longueur',
                        'price' => 1500,
                        'duration' => 15,
                        'commissionRate' => 0.10,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Rasage Traditionnel à l\'ancienne',
                        'description' => 'Rasage coupe-chou avec serviette chaude, savon à barbe et soin après-rasage',
                        'price' => 3000,
                        'duration' => 25,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Soin & Teinture Barbe',
                        'description' => 'Masquage des poils blancs ou uniformisation de la teinte avec soin nourrissant',
                        'price' => 3500,
                        'duration' => 25,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Combo Coupe + Barbe VIP',
                        'description' => 'Formule complète : coupe dégradé, taille barbe au millimètre et soin serviette chaude',
                        'price' => 6000,
                        'duration' => 45,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Coiffure & Tresses',
                'description' => 'Tresses africaines, vanilles, locks et défrisage',
                'icon' => 'heroicon-o-sparkles',
                'order' => 3,
                'services' => [
                    [
                        'name' => 'Tresses Homme (Cornrows)',
                        'description' => 'Nattes plaquées simples ou avec motifs géométriques',
                        'price' => 4000,
                        'duration' => 45,
                        'commissionRate' => 0.20,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Tresses Femme / Nattes',
                        'description' => 'Tresses africaines créatives ou nattes avec ou sans mèches',
                        'price' => 6000,
                        'duration' => 60,
                        'commissionRate' => 0.25,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Twists / Vanilles',
                        'description' => 'Vanilles soignées pour cheveux naturels ou avec rajouts',
                        'price' => 7000,
                        'duration' => 75,
                        'commissionRate' => 0.25,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Entretien Locks (Retwist & Soin)',
                        'description' => 'Lavage en profondeur, repiquage et tournage des racines avec gel naturel',
                        'price' => 10000,
                        'duration' => 90,
                        'commissionRate' => 0.30,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Défrisage & Brushing',
                        'description' => 'Lissage soigné avec soin protecteur et brushing',
                        'price' => 5000,
                        'duration' => 45,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Soins Capillaires & Visage',
                'description' => 'Traitements capillaires profonds et soins esthétiques du visage',
                'icon' => 'heroicon-o-heart',
                'order' => 4,
                'services' => [
                    [
                        'name' => 'Shampooing & Massage Crânien',
                        'description' => 'Lavage revitalisant avec massage crânien relaxant',
                        'price' => 1500,
                        'duration' => 15,
                        'commissionRate' => 0.10,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Soin Profond Masque Kératine',
                        'description' => 'Traitement fortifiant et hydratant sous casque à vapeur',
                        'price' => 4000,
                        'duration' => 30,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Gommage Visage & Masque Noir Purifiant',
                        'description' => 'Élimination des impuretés et des points noirs, resserrement des pores',
                        'price' => 5000,
                        'duration' => 30,
                        'commissionRate' => 0.20,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Soin Visage VIP Complet',
                        'description' => 'Bain de vapeur, extraction douce, masque hydratant, serviette chaude et massage facial',
                        'price' => 8000,
                        'duration' => 45,
                        'commissionRate' => 0.25,
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Coloration & Traitements',
                'description' => 'Coloration, mèches et traitements spécifiques du cuir chevelu',
                'icon' => 'heroicon-o-paint-brush',
                'order' => 5,
                'services' => [
                    [
                        'name' => 'Coloration Cheveux Complète',
                        'description' => 'Teinte uniforme (noir intense, châtain, blond, couleur fantaisie)',
                        'price' => 6000,
                        'duration' => 45,
                        'commissionRate' => 0.20,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Décoloration / Mèches / Flash',
                        'description' => 'Effet lumière ou décoloration ciblée des pointes',
                        'price' => 7500,
                        'duration' => 50,
                        'commissionRate' => 0.20,
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Traitement Anti-Pelliculaire & Cuir Chevelu',
                        'description' => 'Traitement assainissant aux huiles essentielles pour cuirs chevelus irrités',
                        'price' => 4500,
                        'duration' => 30,
                        'commissionRate' => 0.15,
                        'status' => 'active',
                    ],
                ],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $services = $catData['services'] ?? [];
            unset($catData['services']);

            $category = ServiceCategory::updateOrCreate(
                ['name' => $catData['name']],
                $catData
            );

            foreach ($services as $srv) {
                $srv['categoryId'] = $category->id;
                Service::updateOrCreate(
                    ['name' => $srv['name']],
                    $srv
                );
            }
        }
    }
}
