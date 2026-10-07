<?php

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\EditProfile as BaseEditProfile;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EditProfile extends BaseEditProfile
{
    protected static ?string $title = 'Mon profil';

    protected function getPhoneFormComponent(): Component
    {
        return TextInput::make('phone')
            ->label('Téléphone')
            ->tel()
            ->maxLength(20);
    }

    protected function getCurrentPasswordFormComponent(): Component
    {
        return parent::getCurrentPasswordFormComponent()
            ->columnSpanFull();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->inlineLabel(false)
            ->components([
                Section::make('Informations personnelles')
                    ->description('Consultez et mettez à jour les informations de votre compte.')
                    ->icon('heroicon-o-user')
                    ->schema([
                        $this->getNameFormComponent(),
                        $this->getEmailFormComponent(),
                        $this->getPhoneFormComponent(),
                    ])
                    ->columns(2),

                Section::make('Sécurité & Mot de passe')
                    ->description('Modifiez votre mot de passe pour maintenir la sécurité de votre compte.')
                    ->icon('heroicon-o-lock-closed')
                    ->schema([
                        $this->getPasswordFormComponent(),
                        $this->getPasswordConfirmationFormComponent(),
                        $this->getCurrentPasswordFormComponent(),
                    ])
                    ->columns(2),
            ]);
    }
}
