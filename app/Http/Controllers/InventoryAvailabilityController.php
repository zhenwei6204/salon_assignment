<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InventoryAvailabilityController extends Controller
{
   
    public function requirements(Request $r)
    {
        $data = $r->validate(['service_id' => ['required','integer','exists:services,id']]);

        $rows = DB::table('service_item_consumptions as sic')
            ->join('items', 'items.id', '=', 'sic.item_id')
            ->select([
                'items.id as item_id',
                'items.name as item_name',
                'items.unit',
                'items.stock',
                'sic.qty_per_service',
            ])
            ->where('sic.service_id', $data['service_id'])
            ->orderBy('items.name')
            ->get();

        return response()->json([
            'service_id' => (int) $data['service_id'],
            'requirements' => $rows,
        ]);
    }

   
    public function availability(Request $r)
    {
        $data = $r->validate([
            'service_id' => ['required','integer','exists:services,id'],
            'units'      => ['nullable','integer','min:1'],
        ]);

        $units = max((int) ($data['units'] ?? 1), 1);

        $reqs = DB::table('service_item_consumptions as sic')
            ->join('items', 'items.id', '=', 'sic.item_id')
            ->select([
                'items.id as item_id',
                'items.name as item_name',
                'items.stock',
                'sic.qty_per_service',
            ])
            ->where('sic.service_id', $data['service_id'])
            ->get();

       
        if ($reqs->isEmpty()) {
            return response()->json([
                'service_id' => (int) $data['service_id'],
                'units'      => $units,
                'available'  => true,
                'available_units' => PHP_INT_MAX, 
                'items' => [],
                'reason' => 'Service does not consume inventory items.',
            ]);
        }

        $items = [];
        $availableUnits = PHP_INT_MAX;

        foreach ($reqs as $row) {
            $requiredPerBooking = (int) $row->qty_per_service;
            $stock              = max((int) $row->stock, 0);

           
            $supportable = $requiredPerBooking > 0 ? intdiv($stock, $requiredPerBooking) : PHP_INT_MAX;

            $availableUnits = min($availableUnits, $supportable);

            $items[] = [
                'item_id'            => (int) $row->item_id,
                'item_name'          => $row->item_name,
                'stock'              => $stock,
                'required_per_unit'  => $requiredPerBooking,
                'supportable_units'  => $supportable,
            ];
        }

        $ok = $availableUnits >= $units;

        return response()->json([
            'service_id'       => (int) $data['service_id'],
            'units'            => $units,
            'available'        => $ok,
            'available_units'  => $availableUnits,   // how many bookings you could accept now
            'items'            => $items,
            'reason'           => $ok ? null : 'Insufficient stock for one or more required items.',
        ]);
    }

   
}
