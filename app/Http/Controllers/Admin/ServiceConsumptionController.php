<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServiceConsumptionController extends Controller
{
    public function edit(Service $service)
    {
        $items = Item::orderBy('name')->get();

        $existing = DB::table('service_item_consumptions')
            ->where('service_id', $service->id)
            ->pluck('qty_per_service', 'item_id')
            ->toArray();

        return view('admin.services.consumption', compact('service', 'items', 'existing'));
    }

    public function update(Request $r, Service $service)
    {
        $data = $r->validate([
            'items' => ['array'],
            'items.*' => ['integer|min:0'],
        ]);

        $ids   = array_keys($data['items'] ?? []);
        $items = Item::whereIn('id', $ids)->get()->keyBy('id');

        DB::transaction(function () use ($service, $data, $items) {
            DB::table('service_item_consumptions')->where('service_id', $service->id)->delete();



            foreach (($data['items'] ?? []) as $itemId => $qty) {

                if (!isset($items[$itemId])) {
                continue;
                }

                $item = $items[$itemId];
                $type = $item->tracking_type ?? 'unit';

                $qtyToSave = ($type === 'unit')
                ? (int) ceil((float) $qty)      // whole units
                : round((float) $qty, 2); //decimals

                if ($qty > 0) {
                    DB::table('service_item_consumptions')->insert([
                        'service_id' => $service->id,
                        'item_id' => $itemId,
                        'qty_per_service' => $qtyToSave,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        });

        return back()->with('success', 'Consumption saved.');
    }
}