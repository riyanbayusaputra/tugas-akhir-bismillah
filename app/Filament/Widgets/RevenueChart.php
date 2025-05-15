<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class RevenueChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Omset Harian';
    protected static ?int $sort = 1;

    protected function getData(): array
    {
        $data = Order::query()
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total_amount) as total')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Omset',
                    'data' => $data->pluck('total')->toArray(),
                    'fill' => false,
                    'borderColor' => '#4CAF50',
                    'tension' => 0.1,
                    'pointRadius' => 4,
                    'pointHoverRadius' => 6,
                ]
            ],
            'labels' => $data->pluck('date')
                ->map(function($date) {
                    return Carbon::parse($date)->format('d M Y');
                })
                ->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

}