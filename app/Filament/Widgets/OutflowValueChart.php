<?php 

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Illuminate\Support\Facades\DB;

class OutflowValueChart extends ChartWidget
{
    public function getHeading(): ?string
    {
        return 'Item Outflow Value (30days)';
    }

    protected function getData(): array
    {
        $rows = DB::table('stock_movements as sm')
            ->join('items as i', 'i.id', '=', 'sm.item_id')
            ->selectRaw('DATE(sm.created_at) as d, SUM(CASE WHEN sm.type = "OUT" THEN sm.qty * i.price ELSE 0 END) as v')
            ->where('sm.created_at', '>=', now()->subDays(30))
            ->groupBy('d')
            ->orderBy('d')
            ->get();

        return [
            'labels' => $rows->pluck('d')->all(),
            'datasets' => [[ 'label' => 'RM', 'data' => $rows->pluck('v')->all() ]],
        ];
    }

    protected function getType(): string { return 'line'; }
}
