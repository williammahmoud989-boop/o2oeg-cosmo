<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $client;
    protected $apiKey;
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->client = new Client();
    }

    /**
     * Analyze an image and provide beauty advice based on salon services.
     */
    public function analyzeImage(string $imagePath, array $availableServices): array
    {
        try {
            $imageData = base64_encode(file_get_contents($imagePath));
            $servicesList = implode(', ', array_map(fn($s) => $s['name_ar'] ?? $s['name'], $availableServices));

            $prompt = "
                أنت خبير تجميل ذكي ومحترف في منصة O2OEG Cosmo. 
                حلل الصورة المرفقة (شعر أو بشرة) واكتشف الاحتياجات الجمالية للعميلة.
                بناءً على ذلك، اختر أفضل 2-3 خدمات من القائمة التالية فقط: [{$servicesList}].
                
                يجب أن يكون الرد بتنسيق JSON يحتوي على:
                1. 'analysis': تحليل موجز وودي للحالة (نثري باللغة العربية).
                2. 'recommendations': مصفوفة بأسماء الخدمات المقترحة.
                3. 'reasoning': لماذا اخترت هذه الخدمات تحديداً.
                
                اجعل أسلوبك مشجعاً ومهنياً.
            ";

            $response = $this->client->post($this->apiUrl . '?key=' . $this->apiKey, [
                'json' => [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt],
                                [
                                    'inline_data' => [
                                        'mime_type' => 'image/jpeg',
                                        'data' => $imageData
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'response_mime_type' => 'application/json',
                    ]
                ]
            ]);

            $result = json_decode($response->getBody()->getContents(), true);
            $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '{}';
            
            return json_decode($responseText, true);

        } catch (GuzzleException $e) {
            Log::error('Gemini API Error: ' . $e->getMessage());
            return ['error' => 'حدث خطأ أثناء التواصل مع خبير الذكاء الاصطناعي.'];
        } catch (\Exception $e) {
            Log::error('Gemini Service Error: ' . $e->getMessage());
            return ['error' => 'خطأ فني في معالجة الطلب.'];
        }
    }
}
