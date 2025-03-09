<?php

namespace App\Filament\Resources\NoteResource\Pages;

use App\Filament\Resources\NoteResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EditNote extends EditRecord
{
    protected static string $resource = NoteResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    // L'admin ne peut pas changer le propriétaire d'une note
    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si l'utilisateur est un admin, empêcher la modification du user_id
        if (Auth::user()->is_admin) {
            $record = $this->getRecord();
            $data['user_id'] = $record->user_id;
        }

        return $data;
    }

    // Vérification supplémentaire - seul le propriétaire ou l'admin peut éditer
    protected function authorizeAccess(): void
    {
        $record = $this->getRecord();

        abort_unless(
            Auth::user()->is_admin || $record->user_id === Auth::id(),
            403
        );

        parent::authorizeAccess();
    }
}
