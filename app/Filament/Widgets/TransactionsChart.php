<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\StockTransaction;

class TransactionsChart extends ChartWidget
{
    protected static ?string $heading = 'Grafik Transaksi Masuk/Keluar (7 Hari Terakhir)';
    
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = \App\Models\StockTransaction::selectRaw('DATE(created_at) as date, type, SUM(quantity) as count')
            ->where('created_at', '>=', now()->subDays(7))
            ->groupBy('date', 'type')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Barang Masuk',
                    'data' => $data->where('type', 'in')->pluck('count', 'date'),
                    'borderColor' => '#22c55e',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                ],
                [
                    'label' => 'Barang Keluar',
                    'data' => $data->where('type', 'out')->pluck('count', 'date'),
                    'borderColor' => '#ef4444',
                    'backgroundColor' => 'rgba(239, 68, 68, 0.1)',
                ],
            ],
            'labels' => $data->pluck('date')->unique()->values(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
