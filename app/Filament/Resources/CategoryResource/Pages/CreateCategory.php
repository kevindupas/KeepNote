<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Si l'utilisateur n'est pas admin, forcer à false pour is_system
        if (!Auth::user()->is_admin) {
            $data['is_system'] = false;
        }

        // Définir l'utilisateur si non spécifié
        if (!isset($data['user_id']) || !Auth::user()->is_admin) {
            $data['user_id'] = Auth::id();
        }

        return $data;
    }
}
