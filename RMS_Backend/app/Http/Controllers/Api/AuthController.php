<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * FR-AUTH-001: Minimal applicant registration.
     * BR-010: Check duplicate email and return clear message.
     */
    public function register(Request $request)
    {
        $existing = User::where('email', $request->email)->first();
        if ($existing) {
            return response()->json([
                'message' => 'An account with this email address already exists. Please log in or reset your password.',
            ], 409);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => 'Applicant',
            'status' => 'active',
            'subsidiary_id' => null,
        ]);

        AuditLog::record('register', 'User', $user->id, null, ['name' => $user->name, 'email' => $user->email, 'role' => 'Applicant'], $user->id);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful.',
            'token' => $token,
            'user' => $user->load('subsidiary'),
        ], 201);
    }

    /**
     * FR-AUTH-002, FR-AUTH-006, NFR-SEC-005:
     * Secure login with brute-force protection (5 failed attempts -> 15 min lock).
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Check if locked
            if ($user->locked_until && Carbon::now()->lessThan($user->locked_until)) {
                $remainingMinutes = Carbon::now()->diffInMinutes($user->locked_until) + 1;
                AuditLog::record('login_blocked_locked', 'User', $user->id, null, ['locked_until' => $user->locked_until], $user->id);

                return response()->json([
                    'message' => "Account is temporarily locked due to repeated failed login attempts. Please try again in {$remainingMinutes} minute(s).",
                ], 423);
            }

            // Check if inactive or suspended (FR-AUTH-005)
            if ($user->status !== 'active') {
                return response()->json([
                    'message' => "Your account is {$user->status}. Please contact the system administrator.",
                ], 403);
            }

            // Verify password
            if (Hash::check($request->password, $user->password)) {
                // Reset failed attempts counter upon successful login
                $user->failed_login_attempts = 0;
                $user->locked_until = null;
                $user->save();

                $token = $user->createToken('auth_token')->plainTextToken;
                AuditLog::record('login', 'User', $user->id, null, ['ip' => $request->ip()], $user->id);

                return response()->json([
                    'message' => 'Login successful.',
                    'token' => $token,
                    'user' => $user->load('subsidiary'),
                ]);
            } else {
                // Increment failed attempts
                $user->failed_login_attempts += 1;
                if ($user->failed_login_attempts >= 5) {
                    $user->locked_until = Carbon::now()->addMinutes(15);
                    $user->failed_login_attempts = 0; // reset counter after locking
                    $user->save();

                    AuditLog::record('account_locked', 'User', $user->id, null, ['locked_until' => $user->locked_until], $user->id);

                    return response()->json([
                        'message' => 'Too many failed login attempts. Your account has been locked for 15 minutes.',
                    ], 423);
                }

                $user->save();
                AuditLog::record('failed_login', 'User', $user->id, null, ['attempts' => $user->failed_login_attempts], $user->id);
            }
        }

        return response()->json([
            'message' => 'Invalid email address or password.',
        ], 401);
    }

    /**
     * Return authenticated user profile.
     */
    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user()->load('subsidiary'),
        ]);
    }

    /**
     * FR-AUTH-002: Secure logout and token revocation.
     */
    public function logout(Request $request)
    {
        $user = $request->user();
        if ($user) {
            AuditLog::record('logout', 'User', $user->id, null, null, $user->id);
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'message' => 'Logged out successfully.',
        ]);
    }
}
