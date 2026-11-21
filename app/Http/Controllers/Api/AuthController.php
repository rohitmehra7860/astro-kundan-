<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\OtpVerificationMail;
use App\Models\EmailVerificationOtp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    // ─── Registration ────────────────────────────────────────────────────────

    /**
     * Register a new user and send an OTP to their email.
     * No token is returned until the email is verified.
     */
    public function register(Request $request)
    {
        try {
            $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|string|email|unique:users,email',
                'password' => 'required|string|min:8|confirmed',
            ]);

            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                // email_verified_at intentionally left null
            ]);

            if (\Spatie\Permission\Models\Role::where('name', 'user')->exists()) {
                $user->assignRole('user');
            }

            $user->sendEmailVerificationOtp();

            return response()->json([
                'message'          => 'Registration successful. Please check your email for the OTP to verify your account.',
                'email'            => $user->email,
                'requires_verification' => true,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@register: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred during registration.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    // ─── OTP Verification ────────────────────────────────────────────────────

    /**
     * Verify the OTP and activate the user's account.
     * Returns an auth token on success.
     */
    public function verifyEmail(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
                'otp'   => 'required|string|size:6',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return response()->json(['message' => 'Email is already verified.'], 200);
            }

            $otpRecord = EmailVerificationOtp::where('user_id', $user->id)
                ->latest()
                ->first();

            if (!$otpRecord) {
                return response()->json(['message' => 'No OTP found. Please request a new one.'], 404);
            }

            if ($otpRecord->isExpired()) {
                $otpRecord->delete();
                return response()->json(['message' => 'OTP has expired. Please request a new one.'], 422);
            }

            if (!Hash::check($request->otp, $otpRecord->otp)) {
                return response()->json(['message' => 'Invalid OTP. Please try again.'], 422);
            }

            // Mark email as verified and clean up OTP
            $user->update(['email_verified_at' => now()]);
            $otpRecord->delete();

            // Issue auth token
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Email verified successfully! You are now logged in.',
                'token'   => $token,
                'user'    => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@verifyEmail: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred during verification.'], 500);
        }
    }

    /**
     * Resend a fresh OTP to the user's email.
     * Rate-limited to 3 requests per minute.
     */
    public function resendOtp(Request $request)
    {
        try {
            $request->validate([
                'email' => 'required|email|exists:users,email',
            ]);

            $user = User::where('email', $request->email)->first();

            if ($user->email_verified_at) {
                return response()->json(['message' => 'Email is already verified.'], 200);
            }

            $user->sendEmailVerificationOtp();

            return response()->json([
                'message' => 'A new OTP has been sent to your email.',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@resendOtp: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Could not resend OTP.'], 500);
        }
    }

    // ─── Login ────────────────────────────────────────────────────────────────

    /**
     * Login — rejects unverified users with a clear message.
     */
    public function login(Request $request)
    {
        try {
            $request->validate([
                'email'    => 'required|email',
                'password' => 'required',
            ]);

            if (!Auth::attempt($request->only('email', 'password'))) {
                return response()->json(['message' => 'Invalid credentials.'], 401);
            }

            $user = Auth::user();

            // Block unverified users from logging in
            if (!$user->email_verified_at) {
                Auth::logout();
                // Send a fresh OTP automatically
                $user->sendEmailVerificationOtp();
                return response()->json([
                    'message'               => 'Please verify your email first. A new OTP has been sent to your email.',
                    'email'                 => $user->email,
                    'requires_verification' => true,
                ], 403);
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'token'   => $token,
                'user'    => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json(['message' => 'The given data was invalid.', 'errors' => $e->errors()], 422);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@login: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json([
                'message' => 'An error occurred during login.',
                'error'   => config('app.debug') ? $e->getMessage() : 'Internal Server Error',
            ], 500);
        }
    }

    // ─── Logout & Me ─────────────────────────────────────────────────────────

    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();
            return response()->json(['message' => 'Logged out successfully']);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@logout: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred during logout.'], 500);
        }
    }

    public function me(Request $request)
    {
        try {
            return response()->json([
                'user'        => $request->user(),
                'roles'       => $request->user()->getRoleNames(),
                'permissions' => $request->user()->getAllPermissions()->pluck('name'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error in AuthController@me: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'An error occurred while fetching user profile.'], 500);
        }
    }
}
