<?php

namespace App\Http\Controllers;

use App\Models\StockMovement;
use Illuminate\Http\Request;

class StockMovementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $r) {
        $data = $r->validate([
            'item_id' => 'required|exists:items,id',
            'type' => 'required|in:in,out,adjust',
            'qty' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
        ]);

        $data['user_id'] = auth()->id(); // optional, if using login

        return StockMovement::create($data);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
