<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

class RegisteredUserController extends Controller
{
    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        // todo salt and further improve password saving
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->string('password')),
        ]);

        // TODO: Remove this - temporarily auto-verify users
        $user->email_verified_at = now();
        $user->save();
        
        event(new Registered($user));
        Auth::login($user);

        // Create access token with standard abilities
        $accessToken = $user->createToken('access-token', ['*'])->plainTextToken;
        // Create refresh token with limited ability
        $refreshToken = $user->createToken('refresh-token', ['refresh'])->plainTextToken;

        return response()->json([
            'message' => 'Registration successful. Please verify your email address.',
            'user' => new UserResource($user),
            'requires_email_verification' => true,
            'token' => $accessToken,
            'refresh_token' => $refreshToken,
        ], 201);
    }
}
