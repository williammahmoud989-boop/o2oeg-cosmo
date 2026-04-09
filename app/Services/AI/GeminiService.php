<?php

namespace App\Services\AI;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
    }

    /**
     * Analyze an image and provide beauty advice based on salon services.
     */
    public function analyzeImage(string $imagePath, array $availableServices): array
    {
        try {
            if (!file_exists($imagePath) || !is_readable($imagePath)) {
                Log::error("Gemini: File not found at {$imagePath}");
                return ['error' => 'الصورة غير موجودة أو لا يمكن قراءتها.'];
            }

            $imageData = base64_encode(file_get_contents($imagePath));
            $servicesList = implode(', ', array_map(fn($s) => $s['name_ar'] ?? $s['name'], $availableServices));

            $prompt = "أنت خبير تجميل محترف. حلل هذه الصورة واقترح 2-3 خدمات مناسبة من: [{$servicesList}]. أجب بـ JSON فقط بهذا الشكل: {\"analysis\": \"وصف الحالة\", \"recommendations\": [\"خدمة1\", \"خدمة2\"], \"reasoning\": \"سبب الاختيار\"}";

            $response = Http::timeout(30)
                ->withoutVerifying()
                ->post($this->apiUrl . '?key=' . $this->apiKey, [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => 'image/jpeg',
                                        'data' => $imageData,
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 512,
                    ]
                ]);

            if (!$response->successful()) {
                $error = $response->json('error.message', 'Unknown API error');
                $status = $response->status();
                Log::error("Gemini API Error [{$status}]: {$error}");
                return ['error' => "API Error ({$status}): {$error}"];
            }

            $responseText = $response->json('candidates.0.content.parts.0.text', '');

            // Strip markdown code blocks if present
            $responseText = preg_replace('/^```json\s*|\s*```$/m', '', trim($responseText));

            $parsed = json_decode($responseText, true);

            if (json_last_error() !== JSON_ERROR_NONE || !is_array($parsed)) {
                Log::error("Gemini JSON Parse Error. Raw: " . $responseText);
                return ['error' => 'تعذر قراءة رد الذكاء الاصطناعي.'];
            }

            return [
                'analysis'        => $parsed['analysis'] ?? 'تحليل جاهز.',
                'recommendations' => $parsed['recommendations'] ?? [],
                'reasoning'       => $parsed['reasoning'] ?? '',
            ];

        } catch (\Exception $e) {
            Log::error('GeminiService Exception: ' . $e->getMessage());
            return ['error' => $e->getMessage()];
        }
    }
}
