<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\AI\GeminiService;
use App\Models\Salon;
use Illuminate\Support\Facades\Storage;

class AIController extends Controller
{
    protected $gemini;

    public function __construct(GeminiService $gemini)
    {
        $this->gemini = $gemini;
    }

    /**
     * Analyze beauty image (Hair/Skin) and suggest salon services.
     */
    public function analyzeConsultation(Request $request)
    {
        $request->validate([
            'image' => 'required|image|max:5120', // Max 5MB
            'salon_id' => 'nullable|string', // Can be 'demo' or actual ID
        ]);

        $services = [];
        if (!$request->salon_id || $request->salon_id === 'demo') {
            // General high-end services for O2O EG Demo
            $services = [
                ['name_ar' => 'قص شعر احترافي', 'name' => 'Professional Hair Cut'],
                ['name_ar' => 'تنظيف بشرة هيدرافيشل', 'name' => 'HydraFacial Skin Treatment'],
                ['name_ar' => 'صبغة شعر أومبري', 'name' => 'Ombre Hair Coloring'],
                ['name_ar' => 'ترميم الشعر بالبروتين', 'name' => 'Protein Hair Restoration'],
                ['name_ar' => 'باديكير ومانيكير سبا', 'name' => 'Spa Pedicure & Manicure'],
                ['name_ar' => 'جلسة نضارة كولاجين', 'name' => 'Collagen Boosting Session'],
                ['name_ar' => 'مساج وجه مانيوال', 'name' => 'Manual Face Massage']
            ];
        } else {
            $salon = Salon::with('services')->findOrFail($request->salon_id);
            $services = $salon->services->toArray();
        }

        $image = $request->file('image');

        // Store temporarily for analysis
        $tempPath = $image->store('temp/ai-consultation', 'public');
        $absolutePath = storage_path('app/public/' . $tempPath);

        // Analyze via Gemini
        $analysis = $this->gemini->analyzeImage($absolutePath, $services);

        // PRIVACY POLICY: Delete image immediately after analysis
        if (Storage::disk('public')->exists($tempPath)) {
            Storage::disk('public')->delete($tempPath);
        }

        return response()->json([
            'success' => true,
            'analysis' => $analysis['analysis'] ?? 'عذراً، لم نتمكن من تحليل الصورة بشكل دقيق.',
            'recommendations' => $analysis['recommendations'] ?? [],
            'reasoning' => $analysis['reasoning'] ?? '',
            'status' => 'analyzed'
        ]);
    }
}
