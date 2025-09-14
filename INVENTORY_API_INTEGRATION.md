# Inventory API Consumption in Booking Module

## Overview
This document explains how the Booking module consumes the Inventory API to manage stock for services. The integration follows pr### 🧪 **How to Test the Integration:**

1. **Test Stock Check for Service ID 1:**
```bash
curl -X GET "http://localhost:8001/api/test/inventory/stock-check/1" -H "Accept: application/json"
```

2. **Test Service Requirements:**
```bash
curl -X GET "http://localhost:8001/api/v1/services/1/requirements" -H "Accept: application/json"
```

3. **Test Stock Availability:**
```bash
curl -X GET "http://localhost:8001/api/v1/services/1/stock-check" -H "Accept: application/json"
```API consumption patterns between modules as required by your lecturer.

## API Endpoints Available

### 1. Inventory Management API (v1)
```
GET    /api/v1/items                        - Get all inventory items
GET    /api/v1/items/{id}                   - Get specific inventory item
GET    /api/v1/services/{id}/requirements   - Get service inventory requirements
GET    /api/v1/services/{id}/stock-check    - Check stock availability for service
POST   /api/v1/inventory/reserve            - Reserve inventory for booking
```

### 2. Service Data API (for Inventory module consumption)
```
GET    /api/service-data/services           - Get all services
GET    /api/service-data/services/{id}      - Get specific service details
GET    /api/service-data/services/category/{categoryId} - Get services by category
GET    /api/service-data/categories         - Get all categories
GET    /api/service-data/services/active    - Get active services only
POST   /api/service-data/services/{id}/notify-update - Service update webhook
```

### 3. Test API Endpoints
```
GET    /api/test/inventory/stock-check/{serviceId}  - Test stock check
POST   /api/test/inventory/reservation-test         - Test reservation
```

## How Booking Module Consumes Inventory API

### 1. In BookingController (`app/Http/Controllers/BookingController.php`)

The `confirm()` method now calls inventory APIs to:
- Get service requirements
- Check stock availability
- Display inventory status on confirmation page

```php
// Check inventory stock availability using API
try {
    // Get inventory requirements for this service
    $requirementsResponse = $this->callInventoryApi("GET", "/api/v1/services/{$service->id}/requirements");
    if ($requirementsResponse && isset($requirementsResponse['requirements'])) {
        $inventoryRequirements = $requirementsResponse['requirements'];
    }

    // Check stock availability
    $stockResponse = $this->callInventoryApi("GET", "/api/v1/services/{$service->id}/stock-check");
    if ($stockResponse && isset($stockResponse['ok'])) {
        $stockStatus = $stockResponse;
    }
} catch (\Exception $e) {
    Log::warning('Failed to check inventory for service', [
        'service_id' => $service->id,
        'error' => $e->getMessage()
    ]);
    // Continue with booking even if inventory check fails
}
```

### 2. In BookingFacade (`app/Services/BookingFacade.php`)

The facade uses proper API calls instead of direct controller instantiation:

```php
// Reserve inventory for booking using proper API call
try {
    $reservationResult = $this->reserveInventoryForBooking($service->id, $b->id, $actingUser->id);
    
    if (!$reservationResult['success']) {
        throw new \RuntimeException($reservationResult['message']);
    }
} catch (\RuntimeException $e) {
    throw $e; // Re-throw to cancel booking
}
```

### 3. API Helper Methods

Both BookingController and BookingFacade use Laravel's HTTP client for API calls:

```php
private function callInventoryApi(string $method, string $endpoint, array $data = []): ?array
{
    try {
        $baseUrl = config('app.url');
        $fullUrl = $baseUrl . $endpoint;
        
        $request = Http::timeout(10)
            ->withHeaders([
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
            ]);

        $response = match(strtoupper($method)) {
            'GET' => $request->get($fullUrl),
            'POST' => $request->post($fullUrl, $data),
            'PUT' => $request->put($fullUrl, $data),
            'DELETE' => $request->delete($fullUrl),
            default => throw new \InvalidArgumentException("Unsupported HTTP method: {$method}")
        };
        
        if ($response->successful()) {
            return $response->json();
        }
        
        return null;
    } catch (\Exception $e) {
        Log::error('Inventory API call failed', [
            'method' => $method,
            'endpoint' => $endpoint,
            'error' => $e->getMessage()
        ]);
        return null;
    }
}
```

## Frontend Integration

### Booking Confirmation Page (`resources/views/booking/category/confirmation.blade.php`)

The confirmation page now displays:
- Service inventory requirements
- Stock availability status
- Warnings for low stock items

```php
@if(!empty($inventoryRequirements) || isset($stockStatus))
<div class="inventory-status-summary">
    <h3>🔧 Service Requirements</h3>
    
    @if(!empty($inventoryRequirements))
        <div class="inventory-requirements">
            @foreach($inventoryRequirements as $requirement)
                <div class="requirement-item">
                    <span class="item-name">{{ $requirement['item_name'] }}</span>
                    <span class="requirement-details">
                        ({{ $requirement['need'] }} {{ $requirement['unit'] }} needed,
                        {{ $requirement['stock'] }} in stock)
                    </span>
                    @if($requirement['stock'] < $requirement['need'])
                        <span class="stock-warning">⚠️ Low Stock</span>
                    @else
                        <span class="stock-ok">✅ Available</span>
                    @endif
                </div>
            @endforeach
        </div>
    @endif

    @if(isset($stockStatus))
        @if($stockStatus['ok'] ?? true)
            <div class="status-available">✅ All required items are available</div>
        @else
            <div class="status-insufficient">⚠️ {{ $stockStatus['message'] }}</div>
        @endif
    @endif
</div>
@endif
```

## Testing the Integration

### 1. Test Stock Check for a Service
```bash
curl -X GET "http://your-app.local/api/test/inventory/stock-check/1" \
  -H "Accept: application/json"
```

### 2. Test Inventory Reservation
```bash
curl -X POST "http://your-app.local/api/test/inventory/reservation-test" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"service_id": 1, "booking_id": 123, "user_id": 1}'
```

### 3. Check Service Requirements
```bash
curl -X GET "http://your-app.local/api/v1/services/1/requirements" \
  -H "Accept: application/json"
```

### 4. Check Stock Availability
```bash
curl -X GET "http://your-app.local/api/v1/services/1/stock-check" \
  -H "Accept: application/json"
```

## Key Benefits of This Implementation

1. **Proper Module Separation**: Booking module consumes Inventory module through well-defined APIs
2. **Graceful Degradation**: If inventory API fails, booking continues with warnings
3. **Real-time Stock Checking**: Users see current stock status before confirming booking
4. **Automatic Reservation**: Stock is automatically reserved when booking is created
5. **User-Friendly Interface**: Clear inventory status displayed in booking confirmation
6. **Proper Error Handling**: Failed inventory operations don't break the booking flow
7. **Logging**: All API calls and errors are logged for debugging

## Error Scenarios Handled

1. **Insufficient Stock**: Booking is rejected with clear error message
2. **API Timeout**: Graceful fallback with warning message
3. **Service Not Found**: Proper 404 handling
4. **Network Issues**: Timeout handling with fallback behavior

This implementation demonstrates proper inter-module API consumption as required for your assignment.
