<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStats extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Barang', \App\Models\Item::count()),
            Stat::make('Stok Menipis', \App\Models\Item::whereColumn('current_stock', '<=', 'minimum_stock')->count())
                ->description('Barang perlu restock')
                ->descriptionIcon('heroicon-m-arrow-trending-down')
                ->color('danger'),
            Stat::make('Transaksi Hari Ini', \App\Models\StockTransaction::whereDate('created_at', today())->count())
                ->description('Total aktivitas stok hari ini'),
        ];
    }
}
