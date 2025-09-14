<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\BookingFacade;
use Illuminate\Http\Request;

/**
 * Test controller to demonstrate inventory API consumption
 */
class InventoryTestController extends Controller
{
    protected BookingFacade $bookingFacade;

    public function __construct(BookingFacade $bookingFacade)
    {
        $this->bookingFacade = $bookingFacade;
    }

    /**
     * Test inventory stock check for a service
     */
    public function testStockCheck(Request $request, int $serviceId)
    {
        $stockStatus = $this->bookingFacade->checkInventoryStock($serviceId);
        $requirements = $this->bookingFacade->getInventoryRequirements($serviceId);

        return response()->json([
            'service_id' => $serviceId,
            'stock_status' => $stockStatus,
            'requirements' => $requirements,
            'message' => 'Inventory API consumption test completed'
        ]);
    }

    /**
     * Test inventory reservation simulation
     */
    public function testReservation(Request $request)
    {
        $validated = $request->validate([
            'service_id' => 'required|integer|exists:services,id',
            'booking_id' => 'required|integer', // For testing, may not exist
            'user_id' => 'required|integer',
        ]);

        // Use reflection to access private method for testing
        $reflection = new \ReflectionClass($this->bookingFacade);
        $method = $reflection->getMethod('reserveInventoryForBooking');
        $method->setAccessible(true);

        try {
            $result = $method->invoke(
                $this->bookingFacade,
                $validated['service_id'],
                $validated['booking_id'],
                $validated['user_id']
            );

            return response()->json([
                'test_type' => 'inventory_reservation',
                'parameters' => $validated,
                'result' => $result,
                'message' => 'Inventory reservation test completed'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'test_type' => 'inventory_reservation',
                'parameters' => $validated,
                'error' => $e->getMessage(),
                'message' => 'Inventory reservation test failed'
            ], 422);
        }
    }
}
