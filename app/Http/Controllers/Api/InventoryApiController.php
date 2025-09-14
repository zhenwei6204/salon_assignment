<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Item;
use App\Models\Service;
use App\Models\Booking;
use App\Models\StockMovement;

class InventoryApiController extends Controller
{
    public function indexItems()
    {
        return Item::select('id','name','sku','unit','stock','reorder_level')
            ->orderBy('name')->get();
    }

    public function showItem($id)
    {
        return Item::select('id','name','sku','unit','stock','reorder_level')->findOrFail($id);
    }

    public function requirements($serviceId)
    {
        $service = Service::findOrFail($serviceId);

        $rows = DB::table('service_item_consumptions as sic')
            ->join('items as i','i.id','=','sic.item_id')
            ->where('sic.service_id', $service->id)
            ->select('i.id as item_id','i.name','i.unit',
                     DB::raw('SUM(sic.qty_per_service) as need_dec'),
                     'i.stock')
            ->groupBy('i.id','i.name','i.unit','i.stock')
            ->get();

        $req = $rows->map(fn($r) => [
            'item_id'   => (int)$r->item_id,
            'item_name' => $r->name,
            'unit'      => $r->unit,
            'need'      => (int)ceil((float)$r->need_dec),
            'stock'     => (int)$r->stock,
        ]);

        return response()->json([
            'service_id'    => $service->id,
            'requirements'  => $req
        ]);
    }

    public function stockCheck($serviceId)
    {
        $req = $this->requirements($serviceId)->getData(true)['requirements'];
        $short = array_values(array_filter($req, fn($x) => $x['stock'] < $x['need']));

        return $short
            ? response()->json(['ok'=>false,'message'=>'Insufficient stock','insufficient'=>$short])
            : response()->json(['ok'=>true,'message'=>'Stock sufficient']);
    }

    public function reserveForBooking(Request $req)
    {
        $data = $req->validate([
            'service_id' => 'required|exists:services,id',
            'booking_id' => 'nullable|exists:bookings,id',
            'user_id'    => 'nullable|exists:users,id',
        ]);

        $service = Service::findOrFail($data['service_id']);

        $needs = DB::table('service_item_consumptions')
            ->select('item_id', DB::raw('SUM(qty_per_service) need_dec'))
            ->where('service_id', $service->id)
            ->groupBy('item_id')->get();

        $short = [];
        foreach ($needs as $n) {
            $item = Item::find($n->item_id);
            $need = (int)ceil((float)$n->need_dec);
            if (!$item || $item->stock < $need) {
                $short[] = ['item_id'=>$n->item_id,'have'=>$item->stock ?? 0,'need'=>$need];
            }
        }
        if ($short) {
            return response()->json([
                'ok'=>false,
                'message'=>'Insufficient stock',
                'insufficient'=>$short
            ], 422);
        }

        foreach ($needs as $n) {
            $need = (int)ceil((float)$n->need_dec);
            $item = Item::find($n->item_id);

            if ($item) {
                $item->decrement('stock', $need);

                StockMovement::create([
                    'item_id'   => $item->id,
                    'item_name' => $item->name,
                    'type'      => 'out',
                    'source'    => 'booking',
                    'booking_id'=> $data['booking_id'] ?? null,
                    'qty'       => $need,
                    'reason'    => "Reserved for booking",
                    'user_id'   => $data['user_id'] ?? null,
                ]);
            }
        }

        return response()->json([
            'ok'=>true,
            'message'=>'Reserved (stock deducted)',
            'service_id'=>$service->id,
            'booking_id'=>$data['booking_id'] ?? null
        ]);
    }
}
