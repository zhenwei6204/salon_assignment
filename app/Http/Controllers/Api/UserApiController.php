<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;

use Illuminate\Http\JsonResponse;

use Illuminate\Http\Request;

class UserApiController extends Controller
{
    /**
     * Get user details - API endpoint provided by your teammate
     * GET /api/users/{id}
     */
    public function show($id): JsonResponse
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found',
                    'data' => null
                ], 404);
            }
            

          
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone ,
                'roles' => $user->role ,   
                
             
                'source' => 'User web service',
                'fetched_at' => now()->toISOString()
            ];

            return response()->json([
                'status' => 'success',
                'message' => 'User details retrieved successfully',
                'data' => $userData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user details: ' . $e->getMessage(),
                'data' => null
            ], 500);
        }
    }

    /**
     * Get user profile - Extended user information
     * GET /api/users/{id}/profile
     */
    public function profile($id): JsonResponse
    {
        try {
            $user = User::find($id);
            
            if (!$user) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'User not found'
                ], 404);
            }

            $profileData = [
                'basic_info' => [
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                  
                ],
         
            ];

            return response()->json([
                'status' => 'success',
                'data' => $profileData
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to retrieve user profile'
            ], 500);
        }
    }
}