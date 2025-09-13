<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ServiceApiController extends Controller
{
    /**
     * GET /api/service-data/services
     * Provide ALL services to inventory module
     */
    public function getAllServices(Request $request): JsonResponse
    {
        $query = Service::with('category');

        // Optional filters that inventory module can use
        if ($request->has('search')) {
            $search = $request->get('search');
            $query->where('name', 'like', "%{$search}%");
        }

        if ($request->has('category_id')) {
            $query->where('category_id', $request->get('category_id'));
        }

        $services = $query->select(
            'id',
            'name', 
            'category_id',
            'description',
            'price',
            'duration',
            'is_available',
            'created_at',
            'updated_at'
        )->orderBy('name')->get();

        // Format data for inventory module consumption
        $formattedServices = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category_id' => $service->category_id,
                'category_name' => $service->category?->name ?? 'No Category',
                'description' => $service->description,
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration,
                'is_available' => (bool) $service->is_available,
                'created_at' => $service->created_at->toISOString(),
                'updated_at' => $service->updated_at->toISOString(),
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Services data provided to inventory module',
            'data' => $formattedServices,
            'total_count' => $formattedServices->count()
        ]);
    }

    /**
     * GET /api/service-data/services/{id}
     * Provide specific service details to inventory module
     */
    public function getServiceById(int $id): JsonResponse
    {
        $service = Service::with('category')->find($id);

        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        $serviceData = [
            'id' => $service->id,
            'name' => $service->name,
            'category_id' => $service->category_id,
            'category_name' => $service->category?->name ?? 'No Category',
            'description' => $service->description,
            'benefits' => $service->benefits,
            'price' => (float) $service->price,
            'duration_minutes' => $service->duration,
            'is_available' => (bool) $service->is_available,
            'stylist_qualifications' => $service->stylist_qualifications,
            'created_at' => $service->created_at->toISOString(),
            'updated_at' => $service->updated_at->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Service details provided to inventory module',
            'data' => $serviceData
        ]);
    }

    /**
     * GET /api/service-data/services/category/{categoryId}
     * Provide services by category to inventory module
     */
    public function getServicesByCategory(int $categoryId): JsonResponse
    {
        $category = Category::find($categoryId);
        if (!$category) {
            return response()->json([
                'success' => false,
                'message' => 'Category not found'
            ], 404);
        }

        $services = Service::where('category_id', $categoryId)
            ->select('id', 'name', 'description', 'price', 'duration', 'is_available')
            ->orderBy('name')
            ->get();

        $formattedServices = $services->map(function ($service) use ($category) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category_name' => $category->name,
                'description' => $service->description,
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration,
                'is_available' => (bool) $service->is_available,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => "Services in category '{$category->name}' provided to inventory module",
            'data' => [
                'category' => [
                    'id' => $category->id,
                    'name' => $category->name,
                    'description' => $category->description,
                ],
                'services' => $formattedServices
            ]
        ]);
    }

    /**
     * GET /api/service-data/categories
     * Provide all categories to inventory module
     */
    public function getAllCategories(): JsonResponse
    {
        $categories = Category::where('is_active', true)
            ->select('id', 'name', 'description')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Categories provided to inventory module',
            'data' => $categories
        ]);
    }

    /**
     * GET /api/service-data/services/active
     * Provide only active/available services to inventory module
     */
    public function getActiveServices(): JsonResponse
    {
        $services = Service::with('category')
            ->where('is_available', true)
            ->select('id', 'name', 'category_id', 'description', 'price', 'duration')
            ->orderBy('name')
            ->get();

        $formattedServices = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category_id' => $service->category_id,
                'category_name' => $service->category?->name ?? 'No Category',
                'description' => $service->description,
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Active services provided to inventory module',
            'data' => $formattedServices,
            'total_count' => $formattedServices->count()
        ]);
    }

    /**
     * POST /api/service-data/services/{id}/notify-update
     * Webhook endpoint - notify inventory when service is updated
     */
    public function notifyServiceUpdate(Request $request, int $id): JsonResponse
    {
        $service = Service::find($id);
        if (!$service) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found'
            ], 404);
        }

        // Log that inventory module was notified of service update
        \Log::info("Service updated notification sent to inventory module", [
            'service_id' => $id,
            'service_name' => $service->name,
            'notified_at' => now(),
            'inventory_module_ip' => $request->ip()
        ]);

        // Return updated service data
        $serviceData = [
            'id' => $service->id,
            'name' => $service->name,
            'category_id' => $service->category_id,
            'price' => (float) $service->price,
            'duration_minutes' => $service->duration,
            'is_available' => (bool) $service->is_available,
            'updated_at' => $service->updated_at->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Inventory module notified of service update',
            'data' => $serviceData
        ]);
    }

    /**
     * GET /api/service-data/services/bulk
     * Provide multiple services by IDs (for bulk operations)
     */
    public function getServicesBulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_ids' => 'required|array',
            'service_ids.*' => 'integer|exists:services,id'
        ]);

        $services = Service::with('category')
            ->whereIn('id', $validated['service_ids'])
            ->get();

        $formattedServices = $services->map(function ($service) {
            return [
                'id' => $service->id,
                'name' => $service->name,
                'category_name' => $service->category?->name ?? 'No Category',
                'price' => (float) $service->price,
                'duration_minutes' => $service->duration,
                'is_available' => (bool) $service->is_available,
            ];
        });

        return response()->json([
            'success' => true,
            'message' => 'Bulk services data provided to inventory module',
            'data' => $formattedServices
        ]);
    }
}