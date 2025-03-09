<?php

namespace App\Filament\Widgets;

use App\Models\ApiRequest;
use Carbon\Carbon;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\Auth;

class ApiRequestsChart extends ChartWidget
{
    protected static ?string $heading = 'Activité API (7 derniers jours)';

    protected static ?int $sort = 3;

    // Ce widget ne doit être visible que pour les administrateurs
    public static function canView(): bool
    {
        return Auth::user()->is_admin;
    }

    protected function getData(): array
    {
        $data = [];
        $labels = [];

        // Récupérer les données des 7 derniers jours
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $count = ApiRequest::whereDate('created_at', $date)->count();

            $labels[] = Carbon::now()->subDays($i)->format('d/m');
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Requêtes API',
                    'data' => $data,
                    'fill' => false,
                    'borderColor' => 'rgb(75, 192, 192)',
                    'tension' => 0.1,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
