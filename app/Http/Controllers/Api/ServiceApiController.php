<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Service;
use App\Models\Category;
use App\Http\Resources\ServiceResource;
use App\Http\Resources\ServiceCollection;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class ServiceApiController extends Controller
{
    /**
     * Get all services with optional filtering
     * GET /api/services
     * 
     * Query Parameters:
     * - available: boolean (true/false)
     * - category_id: integer
     * - min_price: decimal
     * - max_price: decimal
     * - min_duration: integer (minutes)
     * - max_duration: integer (minutes)
     * - search: string (search in name/description)
     * - per_page: integer (pagination, default 15)
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Service::with(['category']);

            // Filter by availability
            if ($request->has('available')) {
                $available = filter_var($request->available, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($available !== null) {
                    $query->where('is_available', $available);
                }
            }

            // Filter by category
            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            // Filter by price range
            if ($request->has('min_price')) {
                $query->where('price', '>=', $request->min_price);
            }
            if ($request->has('max_price')) {
                $query->where('price', '<=', $request->max_price);
            }

            // Filter by duration range
            if ($request->has('min_duration')) {
                $query->where('duration', '>=', $request->min_duration);
            }
            if ($request->has('max_duration')) {
                $query->where('duration', '<=', $request->max_duration);
            }

            // Search functionality
            if ($request->has('search') && !empty($request->search)) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('benefits', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $services = $query->orderBy('name')->paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Services retrieved successfully',
                'data' => ServiceResource::collection($services),
                'meta' => [
                    'current_page' => $services->currentPage(),
                    'last_page' => $services->lastPage(),
                    'per_page' => $services->perPage(),
                    'total' => $services->total(),
                    'from' => $services->firstItem(),
                    'to' => $services->lastItem()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving services: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific service by ID
     * GET /api/services/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $service = Service::with(['category', 'stylists'])->find($id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Service retrieved successfully',
                'data' => new ServiceResource($service)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new service
     * POST /api/services
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'nullable|exists:categories,id',
                'name' => 'required|string|max:150|unique:services,name',
                'description' => 'nullable|string',
                'benefits' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
                'is_available' => 'boolean',
                'stylist_qualifications' => 'nullable|string',
                'image_url' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $service = Service::create($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => new ServiceResource($service->load('category'))
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creating service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update an existing service
     * PUT /api/services/{id}
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'category_id' => 'nullable|exists:categories,id',
                'name' => 'required|string|max:150|unique:services,name,' . $id,
                'description' => 'nullable|string',
                'benefits' => 'nullable|string',
                'price' => 'required|numeric|min:0',
                'duration' => 'required|integer|min:1',
                'is_available' => 'boolean',
                'stylist_qualifications' => 'nullable|string',
                'image_url' => 'nullable|url'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $service->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'data' => new ServiceResource($service->load('category'))
            ]);

        } catch (\Exception $e) {
            Log::error('Error updating service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a service
     * DELETE /api/services/{id}
     */
    public function destroy($id): JsonResponse
    {
        try {
            $service = Service::find($id);

            if (!$service) {
                return response()->json([
                    'success' => false,
                    'message' => 'Service not found'
                ], 404);
            }

            // Check if service has active bookings
            if ($service->bookings()->whereIn('status', ['booked', 'confirmed'])->exists()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete service with active bookings'
                ], 409);
            }

            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error deleting service: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete service',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available services by duration
     * GET /api/services/by-duration/{duration}
     */
    public function getByDuration($duration): JsonResponse
    {
        try {
            $services = Service::with(['category'])
                ->where('is_available', true)
                ->where('duration', '<=', $duration)
                ->orderBy('duration')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Services retrieved successfully',
                'data' => ServiceResource::collection($services)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving services by duration: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get services by price range
     * GET /api/services/by-price?min=50&max=100
     */
    public function getByPriceRange(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'min' => 'required|numeric|min:0',
                'max' => 'required|numeric|min:0|gte:min'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $services = Service::with(['category'])
                ->where('is_available', true)
                ->whereBetween('price', [$request->min, $request->max])
                ->orderBy('price')
                ->get();

            return response()->json([
                'success' => true,
                'message' => 'Services retrieved successfully',
                'data' => ServiceResource::collection($services)
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving services by price range: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get service categories
     * GET /api/services/categories
     */
    public function getCategories(): JsonResponse
    {
        try {
            $categories = Category::withCount('services')->get();

            return response()->json([
                'success' => true,
                'message' => 'Categories retrieved successfully',
                'data' => $categories
            ]);

        } catch (\Exception $e) {
            Log::error('Error retrieving categories: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve categories',
                'error' => $e->getMessage()
            ], 500);
        }
    }

      public function getByPrice(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'min' => 'required|numeric|min:0',
            'max' => 'required|numeric|gte:min',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid price range provided.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $services = Service::where('is_available', true)
                ->whereBetween('price', [$request->min, $request->max])
                ->orderBy('price')
                ->get();
            return response()->json([
                'success' => true,
                'message' => 'Services retrieved successfully by price range.',
                'data' => ServiceResource::collection($services)
            ]);
        } catch (\Exception $e) {
            Log::error('Error retrieving services by price range: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve services by price range.'
            ], 500);
        }
    }
       
}

