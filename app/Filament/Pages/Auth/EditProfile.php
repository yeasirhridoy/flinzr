<?php

namespace App\Filament\Pages\Auth;

use App\Enums\UserType;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ToggleButtons;
use Filament\Forms\Form;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('image')->image(),
            TextInput::make('username')->required(),
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            ToggleButtons::make('type')->options(UserType::class)->inline(),
            Select::make('country_id')
                ->preload()
                ->relationship('country', 'name')->required(),
            TextInput::make('coin'),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }
}
