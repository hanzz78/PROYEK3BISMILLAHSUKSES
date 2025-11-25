<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ActivityLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    // /**
    //  * Register user (Fungsi Tambahan untuk fix error sebelumnya)
    //  */
    // public function register(Request $request)
    // {
    //     // 1. Validasi input
    //     $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //         'role' => 'nullable|string' // Opsional
    //     ]);

    //     // 2. Buat User (Pakai create agar langsung tersimpan di DB & dapat ID)
    //     $user = User::create([
    //         'name' => $request->name,
    //         'email' => $request->email,
    //         'password' => Hash::make($request->password),
    //         'role' => $request->role ?? 'user', // Default role jika tidak diisi
    //     ]);

    //     // 3. Buat token (Sekarang aman karena user sudah punya ID)
    //     $token = $user->createToken('auth_token')->plainTextToken;

    //     // 4. Log activity (Sesuai pattern kamu)
    //     $this->activityLogService->log(
    //         'register',
    //         'New user registered',
    //         $user->id,
    //         $request
    //     );

    //     // 5. Return response
    //     return response()->json([
    //         'message' => 'User registered successfully',
    //         'user' => [
    //             'id' => $user->id,
    //             'name' => $user->name,
    //             'email' => $user->email,
    //             'role' => $user->role,
    //         ],
    //         'token' => $token,
    //     ], 201);
    // }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.'],
            ]);
        }
        $token = $user->createToken('auth_token')->plainTextToken;

        $this->activityLogService->log(
            'login',
            'User logged in',
            $user->id,
            $request
        );

        return response()->json([
            'message' => 'Login successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'token' => $token,
        ], 200);
    }

    public function logout(Request $request)
    {
        $this->activityLogService->log(
            'logout',
            'User logged out',
            $request->user()->id,
            $request
        );

        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout successful',
        ], 200);
    }
}