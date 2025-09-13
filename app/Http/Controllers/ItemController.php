<?php

namespace App\Http\Controllers;

use App\Models\Item;
use Illuminate\Http\Request;


class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $r)
    {
        return Item::query()
            ->when($r->search, fn($q) =>
                $q->where('name', 'like', "%{$r->search}%")
                    ->orWhere('sku', 'like', "%{$r->search}%"))
            ->orderBy('name')
            ->paginate(10);
            
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r)
    {
        $data = $r->validate([
            'sku' => 'required|string|max:64|unique:items,sku',
            'name' => 'required|string|max:255',
            'unit' => 'nullable|string|max:16',
            'reorder_level' => 'required|integer|min:0',
            'tracking_type'  => 'required|in:unit,measure', 
        ]);
        Item::create($data);

        return back()->with('success', 'Item added');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        return [
            'id' => $item->id,
            'sku' => $item->sku,
            'name' => $item->name,
            'unit' => $item->unit,
            'reorder_level' => $item->reorder_level,
            'on_hand' => $item->onHand(),
        ];
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
