<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ItemAnalyticsController extends Controller
{
    // GET /api/inventory/low-stock?threshold=5
    public function lowStock(Request $r)
    {
        $min = (int) $r->input('threshold', 5);
        $rows = Item::select('id','name','sku','unit','stock','reorder_level','low_stock_notified_at')
            ->where('stock', '<', $min)
            ->orderBy('stock')->limit(100)->get();

        return response()->json(['threshold'=>$min,'rows'=>$rows]);
    }

    // GET /api/inventory/usage?from=YYYY-MM-DD&to=YYYY-MM-DD
    public function usage(Request $r)
    {
        $data = $r->validate([
            'from' => ['required','date'],
            'to'   => ['required','date','after_or_equal:from'],
        ]);

        $rows = StockMovement::query()
            ->selectRaw('
                item_id,
                COALESCE(MAX(item_name), "") as item_name,
                SUM(CASE WHEN type = ? THEN qty ELSE 0 END) as qty_used
            ', [StockMovement::TYPE_OUT])
            ->whereBetween('created_at', [$data['from'], $data['to']])
            ->groupBy('item_id')
            ->orderByDesc('qty_used')
            ->limit(200)
            ->get();

        return response()->json(['from'=>$data['from'],'to'=>$data['to'],'rows'=>$rows]);
    }
}
