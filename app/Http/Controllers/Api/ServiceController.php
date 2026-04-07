<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Service;

class ServiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Service::with('salon:id,name,name_ar,city,rating,cover_image')
            ->whereHas('salon', fn($q) => $q->where('is_active', true));

        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        if ($request->has('q')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->q . '%')
                  ->orWhere('name_ar', 'like', '%' . $request->q . '%');
            });
        }

        $services = $query->orderBy('created_at', 'desc')->paginate(20);

        return response()->json([
            'data' => $services->map(function ($service) {
                return [
                    'id'                => $service->id,
                    'name'              => $service->name,
                    'name_ar'           => $service->name_ar,
                    'price'             => $service->price,
                    'duration_minutes'  => $service->duration_minutes,
                    'category'          => $service->category ?? 'عام',
                    'salon'             => $service->salon ? [
                        'id'           => $service->salon->id,
                        'name'         => $service->salon->name,
                        'name_ar'      => $service->salon->name_ar,
                        'city'         => $service->salon->city,
                        'rating'       => $service->salon->rating,
                        'cover_image'  => $service->salon->cover_image,
                    ] : null,
                ];
            }),
            'meta' => [
                'total'        => $services->total(),
                'current_page' => $services->currentPage(),
                'last_page'    => $services->lastPage(),
            ],
        ]);
    }
}
