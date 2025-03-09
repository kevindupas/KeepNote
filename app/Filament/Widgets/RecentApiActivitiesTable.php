<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;

class RecentApiActivitiesTable extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    // Ce widget ne doit être visible que pour les étudiants (non-admin)
    public static function canView(): bool
    {
        return !Auth::user()->is_admin;
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(
                ApiRequest::query()
                    ->where('user_id', Auth::id())
                    ->latest()
                    ->limit(10)
            )
            ->columns([
                Tables\Columns\TextColumn::make('endpoint')
                    ->label('Endpoint')
                    ->searchable(),
                Tables\Columns\TextColumn::make('method')
                    ->label('Méthode')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'GET' => 'success',
                        'POST' => 'info',
                        'PUT', 'PATCH' => 'warning',
                        'DELETE' => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('response_status')
                    ->label('Statut')
                    ->badge()
                    ->color(fn(string $state): string => match (true) {
                        $state >= 200 && $state < 300 => 'success',
                        $state >= 400 && $state < 500 => 'warning',
                        $state >= 500 => 'danger',
                        default => 'gray',
                    }),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Date')
                    ->dateTime('d/m/Y à H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->heading('Mes 10 dernières requêtes API');
    }
}
