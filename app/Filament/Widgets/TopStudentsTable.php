<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class TopStudentsTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    // Ce widget ne doit être visible que pour les administrateurs
    public static function canView(): bool
    {
        return Auth::user()->is_admin;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::query()
                    ->where('is_admin', false) // Montrer les étudiants (non-admins)
                    ->withCount(['apiRequests', 'notes', 'tasks'])
                    ->orderByDesc('api_requests_count')
            )
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Étudiant')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('api_requests_count')
                    ->label('Requêtes API')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('notes_count')
                    ->label('Notes')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('tasks_count')
                    ->label('Tâches')
                    ->sortable()
                    ->alignRight(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Inscrit le')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
            ])
            ->defaultSort('api_requests_count', 'desc');
    }
}
