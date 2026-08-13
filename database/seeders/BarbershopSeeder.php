<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Barber;
use App\Models\CashRegister;
use App\Models\Client;
use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\LoyaltyTier;
use App\Models\Notification;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Promotion;
use App\Models\PromotionService;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\ServiceCategory;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class BarbershopSeeder extends Seeder
{
    public function run(): void
    {
        // Disable FK checks for MySQL; SQLite uses PRAGMA
        $driver = DB::getDriverName();
        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = OFF;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        }

        // Truncate all tables
        $tables = [
            'promotion_usages', 'promotion_services', 'loyalty_point_transactions',
            'loyalty_rules', 'sale_items', 'sales', 'appointments', 'appointment_photos',
            'cash_transactions', 'expenses', 'notifications', 'salary_payments',
            'payrolls', 'staff_schedules', 'staff_absences', 'staff_attendances',
            'stock_movements', 'activity_logs', 'revenue_targets',
            'purchase_order_items', 'purchase_orders', 'payments',
            'products', 'product_categories', 'services', 'service_categories',
            'promotions', 'expense_categories', 'cash_registers',
            'barbers', 'clients', 'loyalty_tiers', 'settings', 'suppliers',
            'users',
        ];
        foreach ($tables as $table) {
            DB::table($table)->truncate();
        }

        if ($driver === 'sqlite') {
            DB::statement('PRAGMA foreign_keys = ON;');
        } else {
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        }

        $now = now();
        $today = $now->toDateString();

        // ─── Users ─────────────────────────────────────────────────────
        $admin = User::create([
            'name' => 'Admin Barbershop',
            'email' => 'admin@barbershop.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'phone' => '+228 90 12 34 56',
            'active' => true,
        ]);

        $cashier = User::create([
            'name' => 'Afi Sossou',
            'email' => 'caissier@barbershop.com',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'phone' => '+228 91 23 45 67',
            'active' => true,
        ]);

        $barberUser = User::create([
            'name' => 'Kofi Mensah',
            'email' => 'coiffeur@barbershop.com',
            'password' => Hash::make('password'),
            'role' => 'barber',
            'phone' => '+228 92 34 56 78',
            'active' => true,
        ]);

        // ─── Barbers ──────────────────────────────────────────────────
        $barber1 = Barber::create([
            'firstName' => 'Kofi',
            'lastName' => 'Mensah',
            'phone' => '+228 92 34 56 78',
            'address' => 'Lomé, Tokoin',
            'hireDate' => $now->copy()->subYears(3)->toDateString(),
            'status' => 'active',
            'specialties' => 'Coupe, Rasage',
            'remunerationType' => 'fixed',
            'fixedSalary' => 80000,
            'commissionRate' => 0,
            'perServiceRate' => 0,
            'userId' => $barberUser->id,
        ]);

        $barber2 = Barber::create([
            'firstName' => 'Abla',
            'lastName' => 'Tchagba',
            'phone' => '+228 93 45 67 89',
            'address' => 'Lomé, Bè',
            'hireDate' => $now->copy()->subYears(2)->toDateString(),
            'status' => 'active',
            'specialties' => 'Tresse, Coiffure femme',
            'remunerationType' => 'commission',
            'fixedSalary' => 0,
            'commissionRate' => 0.30,
            'perServiceRate' => 0,
            'userId' => null,
        ]);

        $barber3 = Barber::create([
            'firstName' => 'Yao',
            'lastName' => 'Amégan',
            'phone' => '+228 94 56 78 90',
            'address' => 'Lomé, Kodjoviakopé',
            'hireDate' => $now->copy()->subYear()->toDateString(),
            'status' => 'active',
            'specialties' => 'Coupe, Coloration, Soin',
            'remunerationType' => 'fixed_plus_commission',
            'fixedSalary' => 50000,
            'commissionRate' => 0.15,
            'perServiceRate' => 0,
            'userId' => null,
        ]);

        // ─── Service Categories ───────────────────────────────────────
        $catCoupe = ServiceCategory::create([
            'name' => 'Coupe',
            'description' => 'Coupes et tailles de cheveux',
            'icon' => 'heroicon-o-scissors',
            'order' => 1,
        ]);

        $catCoiffure = ServiceCategory::create([
            'name' => 'Coiffure',
            'description' => 'Tresses et défrisage',
            'icon' => 'heroicon-o-sparkles',
            'order' => 2,
        ]);

        $catSoin = ServiceCategory::create([
            'name' => 'Soin',
            'description' => 'Soins et traitements capillaires',
            'icon' => 'heroicon-o-heart',
            'order' => 3,
        ]);

        $catBarbier = ServiceCategory::create([
            'name' => 'Barbier',
            'description' => 'Rasage et taille de barbe',
            'icon' => 'heroicon-o-face-smile',
            'order' => 4,
        ]);

        // ─── Services ────────────────────────────────────────────────
        $services = [];

        $services['coupe_homme'] = Service::create([
            'name' => 'Coupe homme',
            'description' => 'Coupe classique pour homme',
            'price' => 3000,
            'duration' => 30,
            'categoryId' => $catCoupe->id,
            'status' => 'active',
        ]);

        $services['coupe_enfant'] = Service::create([
            'name' => 'Coupe enfant',
            'description' => 'Coupe pour enfants de moins de 12 ans',
            'price' => 1500,
            'duration' => 20,
            'categoryId' => $catCoupe->id,
            'status' => 'active',
        ]);

        $services['coupe_barbe'] = Service::create([
            'name' => 'Coupe barbe',
            'description' => 'Taille et mise en forme de la barbe',
            'price' => 2000,
            'duration' => 15,
            'categoryId' => $catCoupe->id,
            'status' => 'active',
        ]);

        $services['tresse_femme'] = Service::create([
            'name' => 'Tresse femme',
            'description' => 'Tresses africaines pour femme',
            'price' => 5000,
            'duration' => 60,
            'categoryId' => $catCoiffure->id,
            'status' => 'active',
        ]);

        $services['tresse_homme'] = Service::create([
            'name' => 'Tresse homme',
            'description' => 'Tresses pour homme',
            'price' => 3000,
            'duration' => 45,
            'categoryId' => $catCoiffure->id,
            'status' => 'active',
        ]);

        $services['defrisage'] = Service::create([
            'name' => 'Défrisage',
            'description' => 'Défrisage cheveux',
            'price' => 4000,
            'duration' => 40,
            'categoryId' => $catCoiffure->id,
            'status' => 'active',
        ]);

        $services['shampooing'] = Service::create([
            'name' => 'Shampooing',
            'description' => 'Lavage et shampooing',
            'price' => 1000,
            'duration' => 10,
            'categoryId' => $catSoin->id,
            'status' => 'active',
        ]);

        $services['soin_cheveux'] = Service::create([
            'name' => 'Soin cheveux',
            'description' => 'Soin profond capillaire',
            'price' => 2500,
            'duration' => 20,
            'categoryId' => $catSoin->id,
            'status' => 'active',
        ]);

        $services['coloration'] = Service::create([
            'name' => 'Coloration',
            'description' => 'Coloration cheveux',
            'price' => 5000,
            'duration' => 45,
            'categoryId' => $catSoin->id,
            'status' => 'active',
        ]);

        $services['rasage_complet'] = Service::create([
            'name' => 'Rasage complet',
            'description' => 'Rasage complet du visage',
            'price' => 1500,
            'duration' => 15,
            'categoryId' => $catBarbier->id,
            'status' => 'active',
        ]);

        $services['taille_barbe'] = Service::create([
            'name' => 'Taille barbe',
            'description' => 'Taille et contour de barbe',
            'price' => 1000,
            'duration' => 10,
            'categoryId' => $catBarbier->id,
            'status' => 'active',
        ]);

        // ─── Clients ─────────────────────────────────────────────────
        $clients = [];

        $clients[] = Client::create([
            'firstName' => 'Esso',
            'lastName' => 'Lété',
            'phone' => '+228 90 11 22 33',
            'gender' => 'male',
            'birthDate' => $now->copy()->subYears(28)->toDateString(),
            'totalVisits' => 12,
            'totalSpent' => 36000,
            'isLoyal' => true,
            'loyaltyPoints' => 150,
            'firstVisitDate' => $now->copy()->subMonths(6)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(2)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Adjo',
            'lastName' => 'Kpohoué',
            'phone' => '+228 91 22 33 44',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(24)->month($now->month)->toDateString(),
            'totalVisits' => 8,
            'totalSpent' => 40000,
            'isLoyal' => true,
            'loyaltyPoints' => 80,
            'firstVisitDate' => $now->copy()->subMonths(4)->toDateString(),
            'lastVisitDate' => $now->copy()->subDay()->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Komlan',
            'lastName' => 'Dégbé',
            'phone' => '+228 92 33 44 55',
            'gender' => 'male',
            'totalVisits' => 3,
            'totalSpent' => 9000,
            'isLoyal' => false,
            'loyaltyPoints' => 10,
            'firstVisitDate' => $now->copy()->subMonths(2)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(5)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Afi',
            'lastName' => 'Mawuli',
            'phone' => '+228 93 44 55 66',
            'whatsapp' => '+228 93 44 55 66',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(30)->toDateString(),
            'totalVisits' => 15,
            'totalSpent' => 75000,
            'isLoyal' => true,
            'loyaltyPoints' => 520,
            'firstVisitDate' => $now->copy()->subYears(1)->toDateString(),
            'lastVisitDate' => $today,
        ]);

        $clients[] = Client::create([
            'firstName' => 'Kodjo',
            'lastName' => 'Agbéko',
            'phone' => '+228 94 55 66 77',
            'gender' => 'male',
            'totalVisits' => 6,
            'totalSpent' => 18000,
            'isLoyal' => false,
            'loyaltyPoints' => 30,
            'firstVisitDate' => $now->copy()->subMonths(3)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(3)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Dzifa',
            'lastName' => 'Ahanhanzo',
            'phone' => '+228 95 66 77 88',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(22)->toDateString(),
            'totalVisits' => 2,
            'totalSpent' => 10000,
            'isLoyal' => false,
            'loyaltyPoints' => 5,
            'firstVisitDate' => $now->copy()->subWeeks(2)->toDateString(),
            'lastVisitDate' => $now->copy()->subWeek()->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Sénam',
            'lastName' => 'Klouvi',
            'phone' => '+228 96 77 88 99',
            'gender' => 'male',
            'totalVisits' => 20,
            'totalSpent' => 60000,
            'isLoyal' => true,
            'loyaltyPoints' => 1200,
            'firstVisitDate' => $now->copy()->subYears(2)->toDateString(),
            'lastVisitDate' => $today,
        ]);

        $clients[] = Client::create([
            'firstName' => 'Akossiwa',
            'lastName' => 'Boko',
            'phone' => '+228 97 88 99 00',
            'whatsapp' => '+228 97 88 99 00',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(26)->toDateString(),
            'totalVisits' => 5,
            'totalSpent' => 25000,
            'isLoyal' => false,
            'loyaltyPoints' => 25,
            'firstVisitDate' => $now->copy()->subMonths(5)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(4)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Folly',
            'lastName' => 'Sèdoh',
            'phone' => '+228 98 99 00 11',
            'gender' => 'male',
            'totalVisits' => 10,
            'totalSpent' => 30000,
            'isLoyal' => true,
            'loyaltyPoints' => 200,
            'firstVisitDate' => $now->copy()->subMonths(8)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(1)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Yawa',
            'lastName' => 'Gbeho',
            'phone' => '+228 99 00 11 22',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(35)->toDateString(),
            'totalVisits' => 1,
            'totalSpent' => 5000,
            'isLoyal' => false,
            'loyaltyPoints' => 0,
            'firstVisitDate' => $today,
            'lastVisitDate' => $today,
        ]);

        $clients[] = Client::create([
            'firstName' => 'Atsu',
            'lastName' => 'Gomez',
            'phone' => '+228 90 12 21 34',
            'gender' => 'male',
            'totalVisits' => 7,
            'totalSpent' => 21000,
            'isLoyal' => false,
            'loyaltyPoints' => 40,
            'firstVisitDate' => $now->copy()->subMonths(4)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(6)->toDateString(),
        ]);

        $clients[] = Client::create([
            'firstName' => 'Ama',
            'lastName' => 'Dakpa',
            'phone' => '+228 91 23 32 45',
            'whatsapp' => '+228 91 23 32 45',
            'gender' => 'female',
            'birthDate' => $now->copy()->subYears(29)->toDateString(),
            'totalVisits' => 9,
            'totalSpent' => 45000,
            'isLoyal' => true,
            'loyaltyPoints' => 300,
            'firstVisitDate' => $now->copy()->subMonths(7)->toDateString(),
            'lastVisitDate' => $now->copy()->subDays(2)->toDateString(),
        ]);

        // ─── Appointments ────────────────────────────────────────────
        $barbers = [$barber1, $barber2, $barber3];

        // Today's appointments
        Appointment::create([
            'clientId' => $clients[0]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['coupe_homme']->id,
            'date' => $today,
            'startTime' => '09:00',
            'endTime' => '09:30',
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'clientId' => $clients[1]->id,
            'barberId' => $barber2->id,
            'serviceId' => $services['tresse_femme']->id,
            'date' => $today,
            'startTime' => '09:30',
            'endTime' => '10:30',
            'status' => 'in_progress',
        ]);

        Appointment::create([
            'clientId' => $clients[3]->id,
            'barberId' => $barber3->id,
            'serviceId' => $services['coloration']->id,
            'date' => $today,
            'startTime' => '10:00',
            'endTime' => '10:45',
            'status' => 'pending',
        ]);

        Appointment::create([
            'clientId' => $clients[4]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['coupe_homme']->id,
            'date' => $today,
            'startTime' => '11:00',
            'endTime' => '11:30',
            'status' => 'pending',
        ]);

        Appointment::create([
            'clientId' => $clients[6]->id,
            'barberId' => $barber3->id,
            'serviceId' => $services['soin_cheveux']->id,
            'date' => $today,
            'startTime' => '14:00',
            'endTime' => '14:20',
            'status' => 'confirmed',
        ]);

        // Tomorrow's appointments
        $tomorrow = $now->copy()->addDay()->toDateString();
        Appointment::create([
            'clientId' => $clients[2]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['coupe_barbe']->id,
            'date' => $tomorrow,
            'startTime' => '09:00',
            'endTime' => '09:15',
            'status' => 'confirmed',
        ]);

        Appointment::create([
            'clientId' => $clients[5]->id,
            'barberId' => $barber2->id,
            'serviceId' => $services['defrisage']->id,
            'date' => $tomorrow,
            'startTime' => '10:00',
            'endTime' => '10:40',
            'status' => 'pending',
        ]);

        Appointment::create([
            'clientId' => $clients[7]->id,
            'barberId' => $barber3->id,
            'serviceId' => $services['shampooing']->id,
            'date' => $tomorrow,
            'startTime' => '11:00',
            'endTime' => '11:10',
            'status' => 'pending',
        ]);

        // Past completed appointments
        Appointment::create([
            'clientId' => $clients[0]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['coupe_homme']->id,
            'date' => $now->copy()->subDays(2)->toDateString(),
            'startTime' => '09:00',
            'endTime' => '09:30',
            'status' => 'completed',
        ]);

        Appointment::create([
            'clientId' => $clients[1]->id,
            'barberId' => $barber2->id,
            'serviceId' => $services['tresse_femme']->id,
            'date' => $now->copy()->subDays(2)->toDateString(),
            'startTime' => '10:00',
            'endTime' => '11:00',
            'status' => 'completed',
        ]);

        Appointment::create([
            'clientId' => $clients[8]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['rasage_complet']->id,
            'date' => $now->copy()->subDay()->toDateString(),
            'startTime' => '14:00',
            'endTime' => '14:15',
            'status' => 'completed',
        ]);

        Appointment::create([
            'clientId' => $clients[3]->id,
            'barberId' => $barber2->id,
            'serviceId' => $services['tresse_femme']->id,
            'date' => $now->copy()->subDay()->toDateString(),
            'startTime' => '15:00',
            'endTime' => '16:00',
            'status' => 'completed',
        ]);

        // Cancelled
        Appointment::create([
            'clientId' => $clients[9]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['coupe_enfant']->id,
            'date' => $now->copy()->subDay()->toDateString(),
            'startTime' => '08:00',
            'endTime' => '08:20',
            'status' => 'cancelled',
            'notes' => 'Client absent',
        ]);

        // No show
        Appointment::create([
            'clientId' => $clients[10]->id,
            'barberId' => $barber3->id,
            'serviceId' => $services['coloration']->id,
            'date' => $now->copy()->subDays(3)->toDateString(),
            'startTime' => '16:00',
            'endTime' => '16:45',
            'status' => 'no_show',
        ]);

        // More past appointments
        Appointment::create([
            'clientId' => $clients[11]->id,
            'barberId' => $barber2->id,
            'serviceId' => $services['defrisage']->id,
            'date' => $now->copy()->subDays(4)->toDateString(),
            'startTime' => '09:00',
            'endTime' => '09:40',
            'status' => 'completed',
        ]);

        Appointment::create([
            'clientId' => $clients[6]->id,
            'barberId' => $barber1->id,
            'serviceId' => $services['taille_barbe']->id,
            'date' => $now->copy()->subDays(4)->toDateString(),
            'startTime' => '10:00',
            'endTime' => '10:10',
            'status' => 'completed',
        ]);

        // ─── Product Categories ───────────────────────────────────────
        $catCapillaire = ProductCategory::create([
            'name' => 'Produits capillaires',
            'description' => 'Gels, huiles et produits pour cheveux',
        ]);

        $catOutils = ProductCategory::create([
            'name' => 'Outils',
            'description' => 'Outils et équipements professionnels',
        ]);

        $catEntretien = ProductCategory::create([
            'name' => 'Entretien',
            'description' => 'Produits d\'entretien et de nettoyage',
        ]);

        // ─── Products ────────────────────────────────────────────────
        Product::create([
            'name' => 'Gel coiffant',
            'reference' => 'GEL-001',
            'categoryId' => $catCapillaire->id,
            'purchasePrice' => 800,
            'sellingPrice' => 1500,
            'stockQuantity' => 25,
            'minStockLevel' => 5,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Huile cheveux',
            'reference' => 'HUI-001',
            'categoryId' => $catCapillaire->id,
            'purchasePrice' => 1200,
            'sellingPrice' => 2000,
            'stockQuantity' => 15,
            'minStockLevel' => 3,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Ciseaux professionnel',
            'reference' => 'CIS-001',
            'categoryId' => $catOutils->id,
            'purchasePrice' => 5000,
            'sellingPrice' => 8000,
            'stockQuantity' => 5,
            'minStockLevel' => 2,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Tondeuse',
            'reference' => 'TON-001',
            'categoryId' => $catOutils->id,
            'purchasePrice' => 18000,
            'sellingPrice' => 25000,
            'stockQuantity' => 3,
            'minStockLevel' => 1,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Shampooing professionnel',
            'reference' => 'SHP-001',
            'categoryId' => $catEntretien->id,
            'purchasePrice' => 1500,
            'sellingPrice' => 3000,
            'stockQuantity' => 30,
            'minStockLevel' => 8,
            'status' => 'active',
        ]);

        Product::create([
            'name' => 'Après-shampooing',
            'reference' => 'APS-001',
            'categoryId' => $catEntretien->id,
            'purchasePrice' => 1200,
            'sellingPrice' => 2500,
            'stockQuantity' => 20,
            'minStockLevel' => 5,
            'status' => 'active',
        ]);

        // ─── Cash Register ───────────────────────────────────────────
        $cashRegister = CashRegister::create([
            'openingAmount' => 50000,
            'status' => 'open',
            'openedAt' => $now->copy()->startOfDay()->addHours(8),
            'openedBy' => $cashier->id,
        ]);

        // ─── Sales ──────────────────────────────────────────────────
        $sale1 = Sale::create([
            'clientId' => $clients[0]->id,
            'barberId' => $barber1->id,
            'subtotal' => 3000,
            'discountAmount' => 0,
            'total' => 3000,
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(2)->addHours(9),
        ]);

        SaleItem::create([
            'saleId' => $sale1->id,
            'type' => 'service',
            'itemId' => $services['coupe_homme']->id,
            'name' => 'Coupe homme',
            'quantity' => 1,
            'unitPrice' => 3000,
            'discount' => 0,
            'total' => 3000,
        ]);

        $sale2 = Sale::create([
            'clientId' => $clients[1]->id,
            'barberId' => $barber2->id,
            'subtotal' => 5000,
            'discountAmount' => 0,
            'total' => 5000,
            'paymentMethod' => 'tmoney',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(2)->addHours(10),
        ]);

        SaleItem::create([
            'saleId' => $sale2->id,
            'type' => 'service',
            'itemId' => $services['tresse_femme']->id,
            'name' => 'Tresse femme',
            'quantity' => 1,
            'unitPrice' => 5000,
            'discount' => 0,
            'total' => 5000,
        ]);

        $sale3 = Sale::create([
            'clientId' => $clients[8]->id,
            'barberId' => $barber1->id,
            'subtotal' => 1500,
            'discountAmount' => 0,
            'total' => 1500,
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDay()->addHours(14),
        ]);

        SaleItem::create([
            'saleId' => $sale3->id,
            'type' => 'service',
            'itemId' => $services['rasage_complet']->id,
            'name' => 'Rasage complet',
            'quantity' => 1,
            'unitPrice' => 1500,
            'discount' => 0,
            'total' => 1500,
        ]);

        $sale4 = Sale::create([
            'clientId' => $clients[3]->id,
            'barberId' => $barber2->id,
            'subtotal' => 7500,
            'discountAmount' => 0,
            'total' => 7500,
            'paymentMethod' => 'flooz',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDay()->addHours(15),
        ]);

        SaleItem::create([
            'saleId' => $sale4->id,
            'type' => 'service',
            'itemId' => $services['tresse_femme']->id,
            'name' => 'Tresse femme',
            'quantity' => 1,
            'unitPrice' => 5000,
            'discount' => 0,
            'total' => 5000,
        ]);

        SaleItem::create([
            'saleId' => $sale4->id,
            'type' => 'product',
            'itemId' => 1, // Gel coiffant
            'name' => 'Gel coiffant',
            'quantity' => 1,
            'unitPrice' => 1500,
            'discount' => 0,
            'total' => 1500,
        ]);

        SaleItem::create([
            'saleId' => $sale4->id,
            'type' => 'service',
            'itemId' => $services['shampooing']->id,
            'name' => 'Shampooing',
            'quantity' => 1,
            'unitPrice' => 1000,
            'discount' => 0,
            'total' => 1000,
        ]);

        $sale5 = Sale::create([
            'clientId' => $clients[11]->id,
            'barberId' => $barber2->id,
            'subtotal' => 4000,
            'discountAmount' => 0,
            'total' => 4000,
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(4)->addHours(9),
        ]);

        SaleItem::create([
            'saleId' => $sale5->id,
            'type' => 'service',
            'itemId' => $services['defrisage']->id,
            'name' => 'Défrisage',
            'quantity' => 1,
            'unitPrice' => 4000,
            'discount' => 0,
            'total' => 4000,
        ]);

        $sale6 = Sale::create([
            'clientId' => $clients[6]->id,
            'barberId' => $barber1->id,
            'subtotal' => 1000,
            'discountAmount' => 0,
            'total' => 1000,
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(4)->addHours(10),
        ]);

        SaleItem::create([
            'saleId' => $sale6->id,
            'type' => 'service',
            'itemId' => $services['taille_barbe']->id,
            'name' => 'Taille barbe',
            'quantity' => 1,
            'unitPrice' => 1000,
            'discount' => 0,
            'total' => 1000,
        ]);

        $sale7 = Sale::create([
            'clientId' => $clients[2]->id,
            'barberId' => $barber1->id,
            'subtotal' => 3000,
            'discountAmount' => 0,
            'total' => 3000,
            'paymentMethod' => 'card',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(3)->addHours(11),
        ]);

        SaleItem::create([
            'saleId' => $sale7->id,
            'type' => 'service',
            'itemId' => $services['coupe_homme']->id,
            'name' => 'Coupe homme',
            'quantity' => 1,
            'unitPrice' => 3000,
            'discount' => 0,
            'total' => 3000,
        ]);

        $sale8 = Sale::create([
            'clientId' => $clients[5]->id,
            'barberId' => $barber2->id,
            'subtotal' => 5000,
            'discountAmount' => 0,
            'total' => 5000,
            'paymentMethod' => 'tmoney',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(5)->addHours(13),
        ]);

        SaleItem::create([
            'saleId' => $sale8->id,
            'type' => 'service',
            'itemId' => $services['tresse_femme']->id,
            'name' => 'Tresse femme',
            'quantity' => 1,
            'unitPrice' => 5000,
            'discount' => 0,
            'total' => 5000,
        ]);

        $sale9 = Sale::create([
            'clientId' => $clients[4]->id,
            'barberId' => $barber3->id,
            'subtotal' => 5000,
            'discountAmount' => 500,
            'total' => 4500,
            'paymentMethod' => 'cash',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(6)->addHours(16),
        ]);

        SaleItem::create([
            'saleId' => $sale9->id,
            'type' => 'service',
            'itemId' => $services['coloration']->id,
            'name' => 'Coloration',
            'quantity' => 1,
            'unitPrice' => 5000,
            'discount' => 500,
            'total' => 4500,
        ]);

        $sale10 = Sale::create([
            'clientId' => $clients[7]->id,
            'barberId' => $barber3->id,
            'subtotal' => 7500,
            'discountAmount' => 0,
            'total' => 7500,
            'paymentMethod' => 'flooz',
            'status' => 'completed',
            'cashRegisterId' => $cashRegister->id,
            'createdBy' => $cashier->id,
            'created_at' => $now->copy()->subDays(1)->addHours(11),
        ]);

        SaleItem::create([
            'saleId' => $sale10->id,
            'type' => 'service',
            'itemId' => $services['defrisage']->id,
            'name' => 'Défrisage',
            'quantity' => 1,
            'unitPrice' => 4000,
            'discount' => 0,
            'total' => 4000,
        ]);

        SaleItem::create([
            'saleId' => $sale10->id,
            'type' => 'service',
            'itemId' => $services['soin_cheveux']->id,
            'name' => 'Soin cheveux',
            'quantity' => 1,
            'unitPrice' => 2500,
            'discount' => 0,
            'total' => 2500,
        ]);

        SaleItem::create([
            'saleId' => $sale10->id,
            'type' => 'product',
            'itemId' => 1,
            'name' => 'Gel coiffant',
            'quantity' => 1,
            'unitPrice' => 1500,
            'discount' => 0,
            'total' => 1500,
        ]);

        // ─── Expense Categories ──────────────────────────────────────
        $expCats = [];
        $expCatData = [
            ['name' => 'Loyer', 'icon' => 'heroicon-o-home'],
            ['name' => 'Charges', 'icon' => 'heroicon-o-bolt'],
            ['name' => 'Fournitures', 'icon' => 'heroicon-o-shopping-cart'],
            ['name' => 'Marketing', 'icon' => 'heroicon-o-megaphone'],
            ['name' => 'Transport', 'icon' => 'heroicon-o-truck'],
            ['name' => 'Salaires', 'icon' => 'heroicon-o-banknotes'],
        ];
        foreach ($expCatData as $cat) {
            $expCats[] = ExpenseCategory::create($cat);
        }

        // ─── Expenses ──────────────────────────────────────────────
        Expense::create([
            'categoryId' => $expCats[0]->id,
            'amount' => 50000,
            'date' => $now->copy()->startOfMonth()->toDateString(),
            'description' => 'Loyer mensuel atelier',
            'paymentMethod' => 'cash',
            'createdBy' => $admin->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        Expense::create([
            'categoryId' => $expCats[1]->id,
            'amount' => 15000,
            'date' => $now->copy()->subWeek()->toDateString(),
            'description' => 'Facture électricité',
            'paymentMethod' => 'tmoney',
            'createdBy' => $admin->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        Expense::create([
            'categoryId' => $expCats[2]->id,
            'amount' => 8000,
            'date' => $now->copy()->subDays(3)->toDateString(),
            'description' => 'Produits capillaires divers',
            'paymentMethod' => 'cash',
            'createdBy' => $cashier->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        Expense::create([
            'categoryId' => $expCats[4]->id,
            'amount' => 3000,
            'date' => $today,
            'description' => 'Transport fournitures',
            'paymentMethod' => 'cash',
            'createdBy' => $cashier->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        Expense::create([
            'categoryId' => $expCats[3]->id,
            'amount' => 10000,
            'date' => $now->copy()->subDays(5)->toDateString(),
            'description' => 'Publicité réseaux sociaux',
            'paymentMethod' => 'tmoney',
            'createdBy' => $admin->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        Expense::create([
            'categoryId' => $expCats[1]->id,
            'amount' => 5000,
            'date' => $now->copy()->subDays(10)->toDateString(),
            'description' => 'Facture eau',
            'paymentMethod' => 'cash',
            'createdBy' => $admin->id,
            'cashRegisterId' => $cashRegister->id,
        ]);

        // ─── Loyalty Tiers ──────────────────────────────────────────
        $tierBronze = LoyaltyTier::create([
            'name' => 'Bronze',
            'minPoints' => 0,
            'pointsPerFCFA' => 0.0010,
            'discountPercentage' => 0,
            'color' => '#CD7F32',
            'icon' => '🥉',
            'perks' => 'Accès programme fidélité',
            'status' => 'active',
        ]);

        $tierArgent = LoyaltyTier::create([
            'name' => 'Argent',
            'minPoints' => 100,
            'pointsPerFCFA' => 0.0015,
            'discountPercentage' => 5,
            'color' => '#C0C0C0',
            'icon' => '🥈',
            'perks' => '5% de réduction, priorité RDV',
            'status' => 'active',
        ]);

        $tierOr = LoyaltyTier::create([
            'name' => 'Or',
            'minPoints' => 500,
            'pointsPerFCFA' => 0.0020,
            'discountPercentage' => 10,
            'color' => '#FFD700',
            'icon' => '🥇',
            'perks' => '10% de réduction, priorité RDV, offre anniversaire',
            'status' => 'active',
        ]);

        $tierPlatine = LoyaltyTier::create([
            'name' => 'Platine',
            'minPoints' => 1000,
            'pointsPerFCFA' => 0.0025,
            'discountPercentage' => 15,
            'color' => '#E5E4E2',
            'icon' => '💎',
            'perks' => '15% de réduction, priorité RDV, offre anniversaire, service VIP',
            'status' => 'active',
        ]);

        // Assign loyalty tiers to clients
        $clients[0]->update(['loyaltyTierId' => $tierArgent->id]);
        $clients[1]->update(['loyaltyTierId' => $tierArgent->id]);
        $clients[3]->update(['loyaltyTierId' => $tierOr->id]);
        $clients[6]->update(['loyaltyTierId' => $tierPlatine->id]);
        $clients[8]->update(['loyaltyTierId' => $tierArgent->id]);
        $clients[11]->update(['loyaltyTierId' => $tierArgent->id]);

        // ─── Promotions ─────────────────────────────────────────────
        $promo1 = Promotion::create([
            'name' => 'Première visite -20%',
            'description' => '20% de réduction pour les nouveaux clients',
            'type' => 'percentage',
            'value' => 20,
            'startDate' => $now->copy()->startOfMonth()->toDateString(),
            'endDate' => $now->copy()->addMonths(3)->toDateString(),
            'minVisits' => 0,
            'forLoyalOnly' => false,
            'maxUsages' => 50,
            'currentUsages' => 5,
            'status' => 'active',
        ]);

        $promo2 = Promotion::create([
            'name' => 'Fidélité 5ème visite',
            'description' => '15% de réduction après 5 visites',
            'type' => 'percentage',
            'value' => 15,
            'startDate' => $now->copy()->startOfMonth()->toDateString(),
            'endDate' => $now->copy()->addMonths(6)->toDateString(),
            'minVisits' => 5,
            'forLoyalOnly' => true,
            'maxUsages' => null,
            'currentUsages' => 3,
            'status' => 'active',
        ]);

        $promo3 = Promotion::create([
            'name' => 'Spécial coupe',
            'description' => '500 FCFA de réduction sur les coupes',
            'type' => 'fixed',
            'value' => 500,
            'startDate' => $now->copy()->startOfMonth()->toDateString(),
            'endDate' => $now->copy()->endOfMonth()->toDateString(),
            'minVisits' => 0,
            'forLoyalOnly' => false,
            'maxUsages' => 30,
            'currentUsages' => 8,
            'status' => 'active',
        ]);

        // Link promotion 3 to coupe services
        PromotionService::create([
            'promotionId' => $promo3->id,
            'serviceId' => $services['coupe_homme']->id,
        ]);

        PromotionService::create([
            'promotionId' => $promo3->id,
            'serviceId' => $services['coupe_enfant']->id,
        ]);

        // ─── Settings ──────────────────────────────────────────────
        $settings = [
            ['key' => 'shop_name', 'value' => 'BarberShop Pro'],
            ['key' => 'shop_address', 'value' => 'Rue des Alizés, Lomé, Togo'],
            ['key' => 'shop_phone', 'value' => '+228 90 12 34 56'],
            ['key' => 'shop_email', 'value' => 'contact@barbershop.com'],
            ['key' => 'business_hours_start', 'value' => '08:00'],
            ['key' => 'business_hours_end', 'value' => '19:00'],
            ['key' => 'currency', 'value' => 'FCFA'],
            ['key' => 'receipt_width', 'value' => '58mm'],
            ['key' => 'receipt_footer', 'value' => 'Merci pour votre visite !'],
            ['key' => 'tax_rate', 'value' => '0'],
            ['key' => 'default_payment_method', 'value' => 'cash'],
            ['key' => 'appointment_slot_duration', 'value' => '30'],
            ['key' => 'max_appointments_per_slot', 'value' => '2'],
            ['key' => 'auto_confirm_appointments', 'value' => 'false'],
            ['key' => 'send_sms_reminders', 'value' => 'true'],
            ['key' => 'reminder_hours_before', 'value' => '2'],
            ['key' => 'enable_loyalty', 'value' => 'true'],
            ['key' => 'points_per_fcfa', 'value' => '0.001'],
            ['key' => 'min_redeem_points', 'value' => '100'],
        ];

        foreach ($settings as $setting) {
            Setting::create($setting);
        }

        // ─── Notifications ──────────────────────────────────────────
        Notification::create([
            'clientId' => $clients[0]->id,
            'type' => 'appointment_reminder',
            'channel' => 'sms',
            'message' => 'Rappel : vous avez un rendez-vous demain à 09h00 pour une coupe homme.',
            'read' => true,
            'sent' => true,
            'sentAt' => $now->copy()->subDay(),
        ]);

        Notification::create([
            'clientId' => $clients[1]->id,
            'type' => 'birthday',
            'channel' => 'sms',
            'message' => 'Joyeux anniversaire Adjo ! Profitez de -20% sur votre prochaine visite.',
            'read' => false,
            'sent' => false,
        ]);

        Notification::create([
            'clientId' => $clients[3]->id,
            'type' => 'promotion',
            'channel' => 'whatsapp',
            'message' => 'Spécial cette semaine : 500 FCFA de réduction sur les coupes !',
            'read' => false,
            'sent' => true,
            'sentAt' => $now->copy()->subHours(2),
        ]);

        Notification::create([
            'clientId' => $clients[6]->id,
            'type' => 'appointment_reminder',
            'channel' => 'sms',
            'message' => 'Rappel : rendez-vous aujourd\'hui à 14h00 pour un soin cheveux.',
            'read' => false,
            'sent' => true,
            'sentAt' => $now->copy()->subHours(1),
        ]);

        Notification::create([
            'clientId' => $clients[9]->id,
            'type' => 'promotion',
            'channel' => 'sms',
            'message' => 'Bienvenue ! Profitez de -20% pour votre première visite.',
            'read' => false,
            'sent' => false,
        ]);

        Notification::create([
            'clientId' => $clients[11]->id,
            'type' => 'appointment_reminder',
            'channel' => 'whatsapp',
            'message' => 'Votre rendez-vous de demain à 10h est confirmé.',
            'read' => true,
            'sent' => true,
            'sentAt' => $now->copy()->subHours(3),
        ]);
    }
}
