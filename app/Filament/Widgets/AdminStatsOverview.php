<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use App\Models\Note;
use App\Models\Task;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class AdminStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    public static function canView(): bool
    {
        return Auth::user()->is_admin;
    }

    protected function getStats(): array
    {
        return [
            Stat::make('Nombre d\'étudiants', User::where('is_admin', false)->count())
                ->description('Étudiants inscrits')
                ->icon('heroicon-o-users')
                ->color('primary'),

            Stat::make('Requêtes API totales', ApiRequest::count())
                ->description('Total des appels d\'API')
                ->icon('heroicon-o-server')
                ->color('success'),

            Stat::make('Notes créées', Note::count())
                ->description('Notes créées par les étudiants')
                ->icon('heroicon-o-document-text')
                ->color('warning'),

            Stat::make('Tâches créées', Task::count())
                ->description('Tâches ajoutées par les étudiants')
                ->icon('heroicon-o-check-circle')
                ->color('danger'),
        ];
    }
}
