<?php

namespace App\Filament\Pages;

use Filament\Forms\Form;
use Filament\Pages\Auth\EditProfile as BaseEditProfile;


class Profile extends BaseEditProfile
{
    public function form(Form $form): Form
    {
        return $form
            ->schema([]);
    }
}
