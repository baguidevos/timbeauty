<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Core roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $cashier = Role::firstOrCreate(['name' => 'cashier', 'guard_name' => 'web']);
        $barber = Role::firstOrCreate(['name' => 'barber', 'guard_name' => 'web']);

        // 2. Admin receives all current permissions
        $allPermissions = Permission::all();
        if ($allPermissions->isNotEmpty()) {
            $admin->syncPermissions($allPermissions);
            $superAdmin->syncPermissions($allPermissions);
        }

        // 3. Cashier permissions: Sales, CashRegisters, Clients, Appointments, Products/Services viewing, POS
        $cashierPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', '%:Sale')
                ->orWhere('name', 'like', '%:CashRegister')
                ->orWhere('name', 'like', '%:Client')
                ->orWhere('name', 'like', '%:Appointment')
                ->orWhere('name', 'like', '%:Promotion')
                ->orWhere('name', 'like', 'View%:Product%')
                ->orWhere('name', 'like', 'View%:Service%')
                ->orWhere('name', 'like', 'View%:Notification')
                ->orWhere('name', 'like', 'View%:Pos');
        })->where('name', 'not like', '%Delete%')->get();

        if ($cashierPermissions->isNotEmpty()) {
            $cashier->syncPermissions($cashierPermissions);
        }

        // 4. Barber permissions: Appointments, Clients (view), Services (view), AppointmentPhotos
        $barberPermissions = Permission::where(function ($query) {
            $query->where('name', 'like', 'View%:Appointment')
                ->orWhere('name', 'like', 'Update:Appointment')
                ->orWhere('name', 'like', 'Create:Appointment')
                ->orWhere('name', 'like', 'View%:Client')
                ->orWhere('name', 'like', 'View%:Service%')
                ->orWhere('name', 'like', '%:AppointmentPhoto')
                ->orWhere('name', 'like', 'View%:StaffSchedule')
                ->orWhere('name', 'like', 'View%:StaffAttendance');
        })->where('name', 'not like', '%Delete%')->get();

        if ($barberPermissions->isNotEmpty()) {
            $barber->syncPermissions($barberPermissions);
        }
    }
}
