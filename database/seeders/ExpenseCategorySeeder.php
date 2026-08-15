<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Loyer & Local',
                'icon' => 'heroicon-o-home',
            ],
            [
                'name' => 'Électricité & Eau',
                'icon' => 'heroicon-o-bolt',
            ],
            [
                'name' => 'Salaires & Rémunérations',
                'icon' => 'heroicon-o-banknotes',
            ],
            [
                'name' => 'Achat Produits & Consommables',
                'icon' => 'heroicon-o-shopping-bag',
            ],
            [
                'name' => 'Entretien & Réparation Matériel',
                'icon' => 'heroicon-o-wrench-screwdriver',
            ],
            [
                'name' => 'Marketing & Communication',
                'icon' => 'heroicon-o-megaphone',
            ],
            [
                'name' => 'Transport & Logistique',
                'icon' => 'heroicon-o-truck',
            ],
            [
                'name' => 'Internet & Téléphonie',
                'icon' => 'heroicon-o-signal',
            ],
            [
                'name' => 'Taxes & Frais Administratifs',
                'icon' => 'heroicon-o-document-text',
            ],
            [
                'name' => 'Hygiène & Produits de Nettoyage',
                'icon' => 'heroicon-o-sparkles',
            ],
            [
                'name' => 'Divers & Imprévus',
                'icon' => 'heroicon-o-ellipsis-horizontal-circle',
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
