<?php

namespace App\Filament\Pages\Auth;

use Filament\Forms\Components\FileUpload;
use Filament\Forms\Form;

class EditProfile extends \Filament\Pages\Auth\EditProfile
{
    public function form(Form $form): Form
    {
        return $form->schema([
            FileUpload::make('image')->image(),
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            $this->getPasswordFormComponent(),
        ]);
    }
}
