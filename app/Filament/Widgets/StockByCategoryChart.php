<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

class StockByCategoryChart extends ChartWidget
{
    protected static ?string $heading = 'Barang per Kategori';
    
    protected int | string | array $columnSpan = 1;

    protected function getData(): array
    {
        $data = \App\Models\Category::withCount('items')
            ->orderByDesc('items_count')
            ->limit(5)
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total Barang',
                    'data' => $data->pluck('items_count'),
                    'javascript_backgroundColor' => [
                        '#f87171', '#fb923c', '#fbbf24', '#a3e635', '#34d399',
                    ],
                ],
            ],
            'labels' => $data->pluck('name'),
        ];
    }

    protected function getType(): string
    {
        return 'pie';
    }
}
