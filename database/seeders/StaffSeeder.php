<?php

namespace Database\Seeders;

use App\Models\Barber;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class StaffSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $staffMembers = [
            [
                'user' => [
                    'name' => 'Marc Kodjo',
                    'email' => 'manager@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 90 11 22 33',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Marc',
                    'lastName' => 'Kodjo',
                    'phone' => '+228 90 11 22 33',
                    'address' => 'Lomé, Tokoin Casablanca',
                    'hireDate' => now()->subYears(4)->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'manager',
                    'canPerformServices' => true,
                    'specialties' => 'Gestion salon, Coupe VIP, Conseil style, Taille de barbe',
                    'remunerationType' => 'fixed_plus_commission',
                    'fixedSalary' => 150000,
                    'commissionRate' => 0.10,
                    'perServiceRate' => 0,
                ],
            ],
            [
                'user' => [
                    'name' => 'Kofi Mensah',
                    'email' => 'coiffeur@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 92 34 56 78',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Kofi',
                    'lastName' => 'Mensah',
                    'phone' => '+228 92 34 56 78',
                    'address' => 'Lomé, Tokoin',
                    'hireDate' => now()->subYears(3)->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'barber',
                    'canPerformServices' => true,
                    'specialties' => 'Dégradé américain, Taille de barbe à l\'ancienne, Coupe ciseaux',
                    'remunerationType' => 'fixed',
                    'fixedSalary' => 90000,
                    'commissionRate' => 0,
                    'perServiceRate' => 0,
                ],
            ],
            [
                'user' => [
                    'name' => 'Abla Tchagba',
                    'email' => 'abla.tchagba@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 93 45 67 89',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Abla',
                    'lastName' => 'Tchagba',
                    'phone' => '+228 93 45 67 89',
                    'address' => 'Lomé, Bè',
                    'hireDate' => now()->subYears(2)->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'barber',
                    'canPerformServices' => true,
                    'specialties' => 'Tresses africaines, Vanilles, Locks, Soin kératine, Défrisage',
                    'remunerationType' => 'commission',
                    'fixedSalary' => 0,
                    'commissionRate' => 0.35,
                    'perServiceRate' => 0,
                ],
            ],
            [
                'user' => [
                    'name' => 'Yao Amégan',
                    'email' => 'yao.amegan@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 94 56 78 90',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Yao',
                    'lastName' => 'Amégan',
                    'phone' => '+228 94 56 78 90',
                    'address' => 'Lomé, Kodjoviakopé',
                    'hireDate' => now()->subYear()->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'barber',
                    'canPerformServices' => true,
                    'specialties' => 'Coupes modernes, Freestyle hair tattoo, Coloration, Soin visage',
                    'remunerationType' => 'fixed_plus_commission',
                    'fixedSalary' => 55000,
                    'commissionRate' => 0.15,
                    'perServiceRate' => 0,
                ],
            ],
            [
                'user' => [
                    'name' => 'Afi Sossou',
                    'email' => 'caissier@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 91 23 45 67',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Afi',
                    'lastName' => 'Sossou',
                    'phone' => '+228 91 23 45 67',
                    'address' => 'Lomé, Hedzranawoé',
                    'hireDate' => now()->subMonths(18)->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'cashier',
                    'canPerformServices' => false,
                    'specialties' => null,
                    'remunerationType' => 'fixed',
                    'fixedSalary' => 75000,
                    'commissionRate' => 0,
                    'perServiceRate' => 0,
                ],
            ],
            [
                'user' => [
                    'name' => 'Paul Kodjo',
                    'email' => 'paul.kodjo@barbershop.com',
                    'password' => Hash::make('password'),
                    'phone' => '+228 97 65 43 21',
                    'active' => true,
                ],
                'barber' => [
                    'firstName' => 'Paul',
                    'lastName' => 'Kodjo',
                    'phone' => '+228 97 65 43 21',
                    'address' => 'Lomé, Adidogomé',
                    'hireDate' => now()->subMonths(10)->toDateString(),
                    'status' => 'active',
                    'jobTitle' => 'cleaner',
                    'canPerformServices' => false,
                    'specialties' => null,
                    'remunerationType' => 'fixed',
                    'fixedSalary' => 50000,
                    'commissionRate' => 0,
                    'perServiceRate' => 0,
                ],
            ],
        ];

        foreach ($staffMembers as $member) {
            $user = User::updateOrCreate(
                ['email' => $member['user']['email']],
                $member['user']
            );

            $barberData = $member['barber'];
            $barberData['userId'] = $user->id;

            Barber::updateOrCreate(
                ['userId' => $user->id],
                $barberData
            );
        }
    }
}
