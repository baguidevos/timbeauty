<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ─── Suppliers ───────────────────────────────────────────────
        $suppliersData = [
            [
                'name' => 'Lomé Beauty Supplies',
                'contactName' => 'Komi Mensah',
                'phone' => '+228 90 00 11 22',
                'email' => 'contact@lomebeauty.tg',
                'address' => 'Grand Marché, Lomé, Togo',
                'notes' => 'Fournisseur principal de gels, cires et soins capillaires',
                'status' => 'active',
            ],
            [
                'name' => 'Barber Pro Afrique',
                'contactName' => 'Kwesi Arthur',
                'phone' => '+228 91 11 22 33',
                'email' => 'sales@barberproafrique.com',
                'address' => 'Boulevard du 13 Janvier, Lomé, Togo',
                'notes' => 'Distributeur officiel tondeuses Wahl, Andis et ciseaux professionnels',
                'status' => 'active',
            ],
            [
                'name' => 'Cosmétiques & Soins du Golfe',
                'contactName' => 'Estelle Lawson',
                'phone' => '+228 92 22 33 44',
                'email' => 'estelle@soinsdugolfe.tg',
                'address' => 'Quartier Administratif, Lomé, Togo',
                'notes' => 'Spécialiste huiles de barbe, crèmes et soins visage',
                'status' => 'active',
            ],
        ];

        $suppliers = [];
        foreach ($suppliersData as $sup) {
            $suppliers[$sup['name']] = Supplier::updateOrCreate(
                ['name' => $sup['name']],
                $sup
            );
        }

        // ─── Product Categories & Products ────────────────────────────
        $categoriesData = [
            [
                'name' => 'Coiffants & Fixateurs',
                'description' => 'Cires, gels de fixation forte, pomades et sprays coiffants',
                'products' => [
                    [
                        'name' => 'Gel Coiffant Extra Fixation (500ml)',
                        'reference' => 'GEL-001',
                        'brand' => 'Elegance Plus',
                        'purchasePrice' => 1200,
                        'sellingPrice' => 2500,
                        'stockQuantity' => 35,
                        'minStockLevel' => 8,
                        'supplier_name' => 'Lomé Beauty Supplies',
                        'description' => 'Gel de fixation ultra forte sans résidus blancs, parfum frais',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Cire Pomade Fini Mat (150ml)',
                        'reference' => 'CIR-001',
                        'brand' => 'Uppercut Deluxe',
                        'purchasePrice' => 2500,
                        'sellingPrice' => 5000,
                        'stockQuantity' => 20,
                        'minStockLevel' => 5,
                        'supplier_name' => 'Lomé Beauty Supplies',
                        'description' => 'Cire mate texturisante pour un rendu naturel et malléable',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Poudre Texturisante & Volumatrice (20g)',
                        'reference' => 'POU-001',
                        'brand' => 'Slick Gorilla',
                        'purchasePrice' => 3000,
                        'sellingPrice' => 6000,
                        'stockQuantity' => 15,
                        'minStockLevel' => 4,
                        'supplier_name' => 'Lomé Beauty Supplies',
                        'description' => 'Donne du volume instantané et une texture mate aux cheveux courts',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Soins Barbe & Rasage',
                'description' => 'Huiles, baumes, lotions après-rasage et soins de barbe',
                'products' => [
                    [
                        'name' => 'Huile Nourrissante pour Barbe (50ml)',
                        'reference' => 'HUI-001',
                        'brand' => 'BarberClub',
                        'purchasePrice' => 2000,
                        'sellingPrice' => 4000,
                        'stockQuantity' => 25,
                        'minStockLevel' => 6,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Huile aux extraits de cèdre et d\'amande douce, adoucit et fait briller la barbe',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Baume Hydratant & Sculptant Barbe',
                        'reference' => 'BAU-001',
                        'brand' => 'Proraso',
                        'purchasePrice' => 2500,
                        'sellingPrice' => 4500,
                        'stockQuantity' => 18,
                        'minStockLevel' => 5,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Hydrate la peau sous la barbe et discipline les poils rebelles',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Lotion Après-Rasage Rafraîchissante',
                        'reference' => 'LOT-001',
                        'brand' => 'Clubman Pinaud',
                        'purchasePrice' => 1800,
                        'sellingPrice' => 3500,
                        'stockQuantity' => 22,
                        'minStockLevel' => 5,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Apaise le feu du rasoir et désinfecte les micro-coupures',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Soins Cheveux & Shampoings',
                'description' => 'Shampoings professionnels, masques hydratants et sérums capillaires',
                'products' => [
                    [
                        'name' => 'Shampoing Professionnel Kératine (1000ml)',
                        'reference' => 'SHP-001',
                        'brand' => 'L\'Oréal Professionnel',
                        'purchasePrice' => 4500,
                        'sellingPrice' => 8000,
                        'stockQuantity' => 12,
                        'minStockLevel' => 4,
                        'supplier_name' => 'Lomé Beauty Supplies',
                        'description' => 'Nettoie en douceur et renforce la fibre capillaire fragilisée',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Masque Capillaire Réparateur Intense (500ml)',
                        'reference' => 'MSK-001',
                        'brand' => 'Keratin Complex',
                        'purchasePrice' => 3500,
                        'sellingPrice' => 6500,
                        'stockQuantity' => 14,
                        'minStockLevel' => 3,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Soin réparateur profond pour cheveux secs et abîmés',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Sérum Fortifiant Anti-Casse (100ml)',
                        'reference' => 'SER-001',
                        'brand' => 'Kérastase',
                        'purchasePrice' => 6000,
                        'sellingPrice' => 12000,
                        'stockQuantity' => 8,
                        'minStockLevel' => 2,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Stimule la pousse et protège contre les agressions extérieures',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Outils & Matériel Pro',
                'description' => 'Tondeuses, ciseaux de coupe, rasoirs et accessoires de coiffure',
                'products' => [
                    [
                        'name' => 'Tondeuse de Coupe Sans Fil Pro',
                        'reference' => 'TND-001',
                        'brand' => 'Wahl Magic Clip Cordless',
                        'purchasePrice' => 45000,
                        'sellingPrice' => 65000,
                        'stockQuantity' => 4,
                        'minStockLevel' => 1,
                        'supplier_name' => 'Barber Pro Afrique',
                        'description' => 'Tondeuse professionnelle à batterie lithium haute autonomie pour dégradés parfaits',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Tondeuse de Finition & Tracé (Detailer)',
                        'reference' => 'TND-002',
                        'brand' => 'Wahl Detailer Li',
                        'purchasePrice' => 35000,
                        'sellingPrice' => 50000,
                        'stockQuantity' => 5,
                        'minStockLevel' => 1,
                        'supplier_name' => 'Barber Pro Afrique',
                        'description' => 'Lame T-Wide ultra précise pour les contours de barbe et motifs',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Ciseaux Professionnels Acier Japonais 6.0"',
                        'reference' => 'CIS-001',
                        'brand' => 'Kasho Silver Series',
                        'purchasePrice' => 15000,
                        'sellingPrice' => 25000,
                        'stockQuantity' => 6,
                        'minStockLevel' => 2,
                        'supplier_name' => 'Barber Pro Afrique',
                        'description' => 'Ciseaux ergonomiques de haute précision pour coupe franche et effilage',
                        'status' => 'active',
                    ],
                ],
            ],
            [
                'name' => 'Hygiène & Entretien',
                'description' => 'Désinfectants pour lames, talc de barbier, serviettes et consommables',
                'products' => [
                    [
                        'name' => 'Boîte de 100 Lames de Rasoir Derby',
                        'reference' => 'LAM-001',
                        'brand' => 'Derby Professional Single Edge',
                        'purchasePrice' => 2500,
                        'sellingPrice' => 4000,
                        'stockQuantity' => 50,
                        'minStockLevel' => 15,
                        'supplier_name' => 'Barber Pro Afrique',
                        'description' => 'Lames demi-lunes en acier suédois pour rasoirs coupe-chou et shavettes',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Spray Désinfectant 5-en-1 pour Tondeuses (400ml)',
                        'reference' => 'SPY-001',
                        'brand' => 'Andis Cool Care Plus',
                        'purchasePrice' => 3500,
                        'sellingPrice' => 6000,
                        'stockQuantity' => 20,
                        'minStockLevel' => 5,
                        'supplier_name' => 'Barber Pro Afrique',
                        'description' => 'Refroidit, lubrifie, nettoie, désinfecte et prévient la rouille',
                        'status' => 'active',
                    ],
                    [
                        'name' => 'Poudre Talc Barbier Parfumé (250g)',
                        'reference' => 'TLC-001',
                        'brand' => 'Clubman Talc',
                        'purchasePrice' => 1500,
                        'sellingPrice' => 3000,
                        'stockQuantity' => 25,
                        'minStockLevel' => 6,
                        'supplier_name' => 'Cosmétiques & Soins du Golfe',
                        'description' => 'Absorbe l\'humidité et soulage les peaux sensibles après la coupe',
                        'status' => 'active',
                    ],
                ],
            ],
        ];

        foreach ($categoriesData as $catData) {
            $products = $catData['products'] ?? [];
            unset($catData['products']);

            $category = ProductCategory::updateOrCreate(
                ['name' => $catData['name']],
                $catData
            );

            foreach ($products as $prd) {
                $supplierName = $prd['supplier_name'] ?? null;
                unset($prd['supplier_name']);

                $prd['categoryId'] = $category->id;
                if ($supplierName && isset($suppliers[$supplierName])) {
                    $prd['supplierId'] = $suppliers[$supplierName]->id;
                    $prd['supplier'] = $supplierName;
                }

                Product::updateOrCreate(
                    ['reference' => $prd['reference']],
                    $prd
                );
            }
        }
    }
}
