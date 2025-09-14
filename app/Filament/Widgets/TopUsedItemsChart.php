<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class TopUsedItemsChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Top 5 Most Used Items (30days)';
    }

    protected function getData(): array
    {
        $rows = DB::table('stock_movements as sm')
            ->join('items as i', 'i.id', '=', 'sm.item_id')
            ->selectRaw('i.name as item, SUM(CASE WHEN sm.type = "OUT" THEN sm.qty ELSE 0 END) as used_qty')
            ->where('sm.created_at', '>=', now()->subDays(30))
            ->groupBy('i.name')
            ->orderByDesc('used_qty')
            ->limit(5)
            ->get();

        return [
            'labels'   => $rows->pluck('item')->all(),
            'datasets' => [[
                'label' => 'Qty (OUT)',
                'data'  => $rows->pluck('used_qty')->map(fn ($v) => (int) $v)->all(),
            ]],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
