<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class StudentStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    // Ce widget ne doit être visible que pour les étudiants (non-admin)
    public static function canView(): bool
    {
        return !Auth::user()->is_admin;
    }

    protected function getStats(): array
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        return [
            Stat::make('Mes requêtes API', $user->apiRequests()->count())
                ->description('Nombre d\'appels API effectués')
                ->icon('heroicon-o-server')
                ->color('success'),

            Stat::make('Mes notes', $user->notes()->count())
                ->description('Notes créées')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Mes tâches', $user->tasks()->count())
                ->description('Tâches ajoutées')
                ->icon('heroicon-o-check-circle')
                ->color('danger'),

            Stat::make('Dernière activité', $user->apiRequests()->latest()->first() ? $user->apiRequests()->latest()->first()->created_at->diffForHumans() : 'Aucune activité')
                ->description('Dernier appel API')
                ->icon('heroicon-o-clock')
                ->color('primary'),
        ];
    }
}
