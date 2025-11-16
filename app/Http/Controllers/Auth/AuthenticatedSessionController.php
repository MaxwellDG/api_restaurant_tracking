<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $request->authenticate();

        /** @var User $user */
        $user = Auth::user();
        
        // Create access token with standard abilities
        $accessToken = $user->createToken('access-token', ['*'])->plainTextToken;
        
        // Create refresh token with limited ability
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
            'user' => new UserResource($user),
        ]);
    }

    /**
     * Refresh the access token using a refresh token.
     */
    public function refresh(Request $request): JsonResponse
    {
        // Verify the user is authenticated with a refresh token
        if (!$request->user()->tokenCan('refresh')) {
            return response()->json([
                'message' => 'Invalid refresh token',
            ], 403);
        }

        /** @var User $user */
        $user = $request->user();
        
        // Revoke ALL existing tokens for security (token rotation)
        // This includes both the old access token and refresh token
        $user->tokens()->delete();
        
        // Create a new access token
        $accessToken = $user->createToken('access-token', ['*'])->plainTextToken;
        
        // Create a new refresh token
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

        return response()->json([
            'message' => 'Token refreshed successfully',
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ]);
    }

    /**
     * Destroy an authenticated session (revoke current token).
     */
    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ]);
    }
}
