<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SocialAuthController extends Controller
{
    /**
     * Redirect the user to the provider authentication page.
     */
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->stateless()->redirect();
    }

    /**
     * Obtain the user information from the provider.
     */
    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->stateless()->user();
            
            $user = User::where($provider . '_id', $socialUser->getId())
                ->orWhere('email', $socialUser->getEmail())
                ->first();

            if (!$user) {
                // Create new user for social login
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => Hash::make(Str::random(24)), // Random password
                    'avatar' => $socialUser->getAvatar(),
                    $provider . '_id' => $socialUser->getId(),
                ]);
            } else {
                // Link social account if not already linked
                if (!$user->{$provider . '_id'}) {
                    $user->update([
                        $provider . '_id' => $socialUser->getId(),
                        'avatar' => $socialUser->getAvatar(),
                    ]);
                }
            }

            $token = $user->createToken('auth_token')->plainTextToken;

            // In a real production app with Next.js, we would redirect back to the frontend with the token
            // For now, we'll return a JSON response (standard for API testing)
            return \response()->json([
                'access_token' => $token,
                'token_type' => 'Bearer',
                'user' => $user
            ]);

        } catch (\Exception $e) {
            return \response()->json(['message' => 'حدث خطأ أثناء تسجيل الدخول عبر ' . $provider], 500);
        }
    }
}
