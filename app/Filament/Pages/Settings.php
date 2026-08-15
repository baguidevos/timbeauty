<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class Settings extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Système';

    protected static ?int $navigationSort = 20;

    protected static ?string $navigationLabel = 'Paramètres';

    protected static ?string $title = 'Paramètres';

    protected string $view = 'filament.pages.settings';

    public string $activeTab = 'shop';

    // 1. Informations du salon
    public string $shop_name = 'BarberShop Pro';

    public string $shop_email = '';

    public string $shop_phone = '';

    public string $shop_address = '';

    public string $currency = 'FCFA';

    public string $default_opening_time = '08:00';

    public string $default_closing_time = '18:00';

    // 2. Paramètres de Caisse
    public string $cash_register_mode = 'auto_open';

    public array $enabled_payment_methods = ['cash', 'tmoney', 'flooz', 'card', 'transfer'];

    public bool $auto_print_receipt = true;

    // 3. Programme de Fidélité
    public string $loyalty_visits_threshold = '5';

    public bool $loyalty_auto_notify = false;

    // 4. Tickets & Reçus
    public string $receipt_header = "💈 BarberShop Pro\nMerci de votre visite !\n123 Rue de la République";

    public string $receipt_footer = "À bientôt !\nService client : +228 90 00 00 00\nConservez ce ticket pour vos retours";

    public string $receipt_printer_width = '58';

    // 5. Gestion des Utilisateurs (Modal)
    public bool $isUserModalOpen = false;

    public ?int $editingUserId = null;

    public string $userName = '';

    public string $userEmail = '';

    public string $userPhone = '';

    public bool $userActive = true;

    public string $userPassword = '';

    // 6. Journal d'activité (Filtre)
    public string $activityEntityFilter = 'all';

    public function mount(): void
    {
        // Salon
        $this->shop_name = (string) Setting::get('shop_name', 'BarberShop Pro');
        $this->shop_email = (string) Setting::get('shop_email', 'contact@barbershop.com');
        $this->shop_phone = (string) Setting::get('shop_phone', '+228 90 00 00 00');
        $this->shop_address = (string) Setting::get('shop_address', 'Quartier des Affaires, Lomé, Togo');
        $this->currency = (string) Setting::get('currency', 'FCFA');
        $this->default_opening_time = (string) Setting::get('default_opening_time', '08:00');
        $this->default_closing_time = (string) Setting::get('default_closing_time', '18:00');

        // Caisse
        $this->cash_register_mode = (string) Setting::get('cash_register_mode', 'auto_open');
        $methodsRaw = Setting::get('payment_methods_enabled', 'cash,tmoney,flooz,card,transfer');
        $this->enabled_payment_methods = array_filter(explode(',', (string) $methodsRaw));
        $this->auto_print_receipt = Setting::get('auto_print_receipt', '1') === '1' || Setting::get('auto_print_receipt', '1') === 'true';

        // Fidélité
        $this->loyalty_visits_threshold = (string) Setting::get('loyalty_visits_threshold', '5');
        $this->loyalty_auto_notify = Setting::get('loyalty_auto_notify', '0') === '1' || Setting::get('loyalty_auto_notify', '0') === 'true';

        // Reçus
        $this->receipt_header = (string) Setting::get('receipt_header', "💈 BarberShop Pro\nMerci de votre visite !\n123 Rue de la République");
        $this->receipt_footer = (string) Setting::get('receipt_footer', "À bientôt !\nService client : +228 90 00 00 00\nConservez ce ticket pour vos retours");
        $this->receipt_printer_width = (string) Setting::get('receipt_printer_width', '58');
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function saveShop(): void
    {
        $this->validate([
            'shop_name' => 'required|string|max:100',
            'shop_email' => 'nullable|email|max:100',
            'shop_phone' => 'nullable|string|max:50',
            'shop_address' => 'nullable|string|max:255',
            'currency' => 'required|string|max:20',
            'default_opening_time' => 'required|string',
            'default_closing_time' => 'required|string',
        ]);

        Setting::set('shop_name', $this->shop_name);
        Setting::set('shop_email', $this->shop_email);
        Setting::set('shop_phone', $this->shop_phone);
        Setting::set('shop_address', $this->shop_address);
        Setting::set('currency', $this->currency);
        Setting::set('default_opening_time', $this->default_opening_time);
        Setting::set('default_closing_time', $this->default_closing_time);

        ActivityLog::log(
            action: 'update',
            entity: 'setting',
            details: 'Mise à jour des informations générales du salon',
            userId: auth()->id()
        );

        Notification::make()
            ->title('Informations du salon enregistrées')
            ->success()
            ->send();
    }

    public function togglePaymentMethod(string $method): void
    {
        if (in_array($method, $this->enabled_payment_methods, true)) {
            $this->enabled_payment_methods = array_values(array_filter(
                $this->enabled_payment_methods,
                fn ($m) => $m !== $method
            ));
        } else {
            $this->enabled_payment_methods[] = $method;
        }
    }

    public function saveCash(): void
    {
        $this->validate([
            'cash_register_mode' => 'required|in:auto_open,strict,flexible',
            'enabled_payment_methods' => 'required|array|min:1',
        ]);

        Setting::set('cash_register_mode', $this->cash_register_mode);
        Setting::set('payment_methods_enabled', implode(',', $this->enabled_payment_methods));
        Setting::set('auto_print_receipt', $this->auto_print_receipt ? '1' : '0');

        ActivityLog::log(
            action: 'update',
            entity: 'setting',
            details: 'Mise à jour des paramètres de caisse et moyens de paiement',
            userId: auth()->id()
        );

        Notification::make()
            ->title('Paramètres de caisse enregistrés')
            ->success()
            ->send();
    }

    public function saveLoyalty(): void
    {
        $this->validate([
            'loyalty_visits_threshold' => 'required|numeric|min:1|max:100',
        ]);

        Setting::set('loyalty_visits_threshold', $this->loyalty_visits_threshold);
        Setting::set('loyalty_auto_notify', $this->loyalty_auto_notify ? '1' : '0');

        ActivityLog::log(
            action: 'update',
            entity: 'setting',
            details: 'Mise à jour des paramètres du programme de fidélité',
            userId: auth()->id()
        );

        Notification::make()
            ->title('Programme de fidélité mis à jour')
            ->success()
            ->send();
    }

    public function saveReceipt(): void
    {
        $this->validate([
            'receipt_header' => 'nullable|string|max:500',
            'receipt_footer' => 'nullable|string|max:500',
            'receipt_printer_width' => 'required|in:58,80',
        ]);

        Setting::set('receipt_header', $this->receipt_header);
        Setting::set('receipt_footer', $this->receipt_footer);
        Setting::set('receipt_printer_width', $this->receipt_printer_width);

        ActivityLog::log(
            action: 'update',
            entity: 'setting',
            details: 'Mise à jour des modèles de tickets et largeur thermique ('.$this->receipt_printer_width.'mm)',
            userId: auth()->id()
        );

        Notification::make()
            ->title('Paramètres de ticket enregistrés')
            ->success()
            ->send();
    }

    public function openCreateUserModal(): void
    {
        $this->editingUserId = null;
        $this->userName = '';
        $this->userEmail = '';
        $this->userPhone = '';
        $this->userActive = true;
        $this->userPassword = '';
        $this->isUserModalOpen = true;
    }

    public function openEditUserModal(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userPhone = $user->phone ?? '';
        $this->userActive = (bool) $user->active;
        $this->userPassword = '';
        $this->isUserModalOpen = true;
    }

    public function closeUserModal(): void
    {
        $this->isUserModalOpen = false;
        $this->editingUserId = null;
    }

    public function saveUser(): void
    {
        $rules = [
            'userName' => 'required|string|max:255',
            'userEmail' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingUserId)],
            'userPhone' => 'nullable|string|max:50',
            'userActive' => 'boolean',
        ];

        if (! $this->editingUserId) {
            $rules['userPassword'] = 'required|string|min:6';
        } else {
            $rules['userPassword'] = 'nullable|string|min:6';
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $this->userName;
            $user->email = $this->userEmail;
            $user->phone = $this->userPhone;
            $user->active = $this->userActive;
            if (! empty($this->userPassword)) {
                $user->password = Hash::make($this->userPassword);
            }
            $user->save();

            ActivityLog::log(
                action: 'update',
                entity: 'user',
                entityId: $user->id,
                details: "Modification de l'utilisateur {$user->name} ({$user->email})",
                userId: auth()->id()
            );

            Notification::make()
                ->title('Utilisateur mis à jour')
                ->success()
                ->send();
        } else {
            $user = User::create([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'phone' => $this->userPhone,
                'active' => $this->userActive,
                'password' => Hash::make($this->userPassword),
            ]);

            ActivityLog::log(
                action: 'create',
                entity: 'user',
                entityId: $user->id,
                details: "Création de l'utilisateur {$user->name}",
                userId: auth()->id()
            );

            Notification::make()
                ->title('Utilisateur créé avec succès')
                ->success()
                ->send();
        }

        $this->closeUserModal();
    }

    public function toggleUserActive(int $id): void
    {
        $user = User::findOrFail($id);
        $user->active = ! $user->active;
        $user->save();

        ActivityLog::log(
            action: 'update',
            entity: 'user',
            entityId: $user->id,
            details: $user->active ? "Activation du compte {$user->name}" : "Désactivation du compte {$user->name}",
            userId: auth()->id()
        );

        Notification::make()
            ->title($user->active ? 'Utilisateur activé' : 'Utilisateur désactivé')
            ->color($user->active ? 'success' : 'danger')
            ->send();
    }

    public function getSystemInfoProperty(): array
    {
        $dbDriver = DB::connection()->getDriverName();
        $dbName = match (strtolower($dbDriver)) {
            'sqlite' => 'SQLite',
            'mysql' => 'MySQL',
            'pgsql' => 'PostgreSQL',
            default => ucfirst($dbDriver),
        };

        return [
            'version' => '1.0.0',
            'database' => $dbName,
            'currency' => $this->currency,
            'users_count' => User::count(),
            'active_users_count' => User::where('active', true)->count(),
        ];
    }

    public function getUsersProperty()
    {
        return User::orderBy('name')->get();
    }

    public function getActivityLogsProperty()
    {
        $query = ActivityLog::with('user')->latest();

        if ($this->activityEntityFilter !== 'all') {
            $query->where('entity', $this->activityEntityFilter);
        }

        return $query->limit(50)->get();
    }
}
