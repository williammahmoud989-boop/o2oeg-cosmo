<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Salon;
use App\Models\User;
use App\Http\Resources\SalonResource;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class SalonController extends Controller
{
    public function index()
    {
        $salons = Salon::where('is_active', true)->with('services')->paginate(15);

        $salons = Salon::where('is_active', true)->with('services')->paginate(15);
        return SalonResource::collection($salons);
    }

    public function show($identifier)
    {
        $salon = Salon::where('id', $identifier)
            ->orWhere('slug', $identifier)
            ->orWhere('subdomain', $identifier)
            ->firstOrFail();

        if ($salon->status !== 'active') {
            abort(404);
        }

        return new SalonResource($salon->load(['services', 'offers', 'staff', 'pricingRules']));
    }

    public function search(Request $request)
    {
        $query = Salon::query()->where('status', 'active');

        if ($request->has('city')) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->has('q')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->q . '%');
            });
        }

        return SalonResource::collection($query->with('services')->paginate(15));
    }

    public function register(Request $request)
    {
        $validated = $request->validate([
            'owner_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
            'salon_name' => 'required|string|max:255',
            'city' => 'required|string|max:255',
            'governorate' => 'required|string|max:255',
        ]);

        return DB::transaction(function () use ($validated) {
            $user = User::create([
                'name' => $validated['owner_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'password' => Hash::make($validated['password']),
            ]);

            $salon = Salon::create([
                'user_id' => $user->id,
                'name' => $validated['salon_name'],
                'name_ar' => $validated['salon_name'], 
                'phone' => $validated['phone'],
                'email' => $validated['email'],
                'city' => $validated['city'],
                'governorate' => $validated['governorate'],
                'status' => 'active', 
                'slug' => Str::slug($validated['salon_name']) . '-' . rand(100, 999),
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'user' => $user,
                'salon' => $salon,
                'access_token' => $token,
                'token_type' => 'Bearer',
            ], 201);
        });
    }
}
