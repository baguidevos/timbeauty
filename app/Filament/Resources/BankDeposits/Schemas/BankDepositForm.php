<?php

namespace App\Filament\Resources\BankDeposits\Schemas;

use App\Models\CashRegister;
use App\Models\Setting;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class BankDepositForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('reference')
                    ->label('Référence')
                    ->disabled()
                    ->dehydrated(false)
                    ->placeholder('Généré automatiquement (ex: DEP-202608-0001)'),

                Select::make('cashRegisterId')
                    ->label('Session de Caisse débitée')
                    ->relationship(
                        name: 'cashRegister',
                        titleAttribute: 'id',
                        modifyQueryUsing: fn ($query) => $query->latest('openedAt')
                    )
                    ->getOptionLabelFromRecordUsing(fn (CashRegister $record): string => ($record->isOpen() ? '🟢 ' : '🔴 ')."Caisse #{$record->id} — ".($record->openedAt ? $record->openedAt->format('d/m/Y à H:i') : $record->created_at->format('d/m/Y')).' ('.($record->isOpen() ? 'Ouverte' : 'Fermée').')'
                    )
                    ->searchable()
                    ->preload()
                    ->default(fn () => CashRegister::where('status', 'open')->first()?->id),

                TextInput::make('amount')
                    ->label('Montant déposé (FCFA)')
                    ->numeric()
                    ->required()
                    ->minValue(1),

                TextInput::make('bankName')
                    ->label('Banque réceptrice')
                    ->default(fn () => Setting::get('default_bank_name', 'Ecobank Togo'))
                    ->required()
                    ->maxLength(100),

                TextInput::make('bankAccountNumber')
                    ->label('Numéro de compte bancaire')
                    ->default(fn () => Setting::get('default_bank_account', ''))
                    ->maxLength(100),

                TextInput::make('depositSlipNumber')
                    ->label('N° de bordereau / Reçu de versement')
                    ->placeholder('Ex: BORD-123456')
                    ->maxLength(100),

                Select::make('depositedBy')
                    ->label('Responsable du dépôt (Coursier / Gérant)')
                    ->relationship('courier', 'name')
                    ->searchable()
                    ->preload()
                    ->default(auth()->id()),

                Select::make('status')
                    ->label('Statut du dépôt')
                    ->options([
                        'pending' => 'En cours d\'acheminement',
                        'confirmed' => 'Confirmé / Déposé en banque',
                        'cancelled' => 'Annulé',
                    ])
                    ->default('confirmed')
                    ->required()
                    ->native(false),

                DatePicker::make('depositDate')
                    ->label('Date du dépôt')
                    ->default(now())
                    ->required(),

                FileUpload::make('depositSlipPhoto')
                    ->label('Bordereau scanné / Photo du reçu')
                    ->image()
                    ->directory('bank-deposits')
                    ->maxSize(5120)
                    ->columnSpanFull(),

                Textarea::make('notes')
                    ->label('Observations')
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }
}
