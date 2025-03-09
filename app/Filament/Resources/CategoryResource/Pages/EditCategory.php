<?php

namespace App\Filament\Resources\CategoryResource\Pages;

use App\Filament\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditCategory extends EditRecord
{
    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(
                    fn($record) =>
                    // Seul l'admin peut supprimer des catégories système
                    ($record->is_system && Auth::user()->is_admin) ||
                        // Les utilisateurs ne peuvent supprimer que leurs propres catégories non-système
                        (!$record->is_system && $record->user_id === Auth::id())
                ),
        ];
    }

    // Protection contre la modification des champs critiques
    protected function mutateFormDataBeforeSave(array $data): array
    {
        $record = $this->getRecord();

        // Conserver la valeur du champ is_system
        $data['is_system'] = $record->is_system;

        // Conserver la valeur du champ user_id
        $data['user_id'] = $record->user_id;

        return $data;
    }

    // Vérifier les autorisations d'accès
    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();

        abort_unless(
            // L'admin peut modifier toutes les catégories
            Auth::user()->is_admin ||
                // Les utilisateurs ne peuvent modifier que leurs propres catégories non-système
                (!$record->is_system && $record->user_id === Auth::id()),
            403
        );

        parent::authorizeAccess();
    }
}
