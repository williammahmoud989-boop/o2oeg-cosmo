<?php

namespace App\Services\AI;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;

class GeminiService
{
    protected $client;
    protected $apiKey;
    // gemini-2.0-flash: supports image analysis (Vision) and is available globally
    protected $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.key');
        $this->client = new Client(['verify' => false]);
    }

    /**
     * Analyze an image and provide beauty advice based on salon services.
     */
    public function analyzeImage(string $imagePath, array $availableServices): array
    {
        try {
            if (!file_exists($imagePath) || !is_readable($imagePath)) {
                Log::error("Gemini Image Path Issue: File does not exist or is not readable at {$imagePath}");
                return ['error' => 'الصورة غير موجودة أو لا يمكن قراءتها.'];
            }

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
                
                اجعل أسلوبك مشجعاً ومهنياً. لا تضع أي نصوص خارج تنسيق JSON.
            ";

            // Retry logic: attempt up to 2 times to handle transient 503 errors
            $response = null;
            $lastError = null;
            for ($attempt = 1; $attempt <= 2; $attempt++) {
                try {
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
                        ],
                        'timeout' => 30,
                    ]);
                    break; // Success, exit retry loop
                } catch (GuzzleException $retryEx) {
                    $lastError = $retryEx;
                    if ($attempt < 2) sleep(2); // Wait 2 seconds before retry
                }
            }
            if (!$response) throw $lastError;

            $body = $response->getBody()->getContents();
            $result = json_decode($body, true);
            
            $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            // Critical Fix: Strip possible Markdown JSON wrapper
            $responseText = preg_replace('/^```json\s*|```\s*$/', '', trim($responseText));
            
            $parsedResponse = json_decode($responseText, true);
            
            if (json_last_error() !== JSON_ERROR_NONE) {
                Log::error("Gemini JSON Parse Error: " . json_last_error_msg());
                Log::error("Raw Response Text: " . $responseText);
                return ['error' => 'تعذر تحليل الرد من الذكاء الاصطناعي.'];
            }

            return $parsedResponse;

        } catch (GuzzleException $e) {
            $msg = $e->getMessage();
            if ($e->hasResponse()) {
                $status = $e->getResponse()->getStatusCode();
                $body = $e->getResponse()->getBody()->getContents();
                Log::error("Gemini API HTTP Error [{$status}]: " . $body);
                return ['error' => "API Error ({$status}): " . substr($body, 0, 100)];
            }
            Log::error('Gemini API Error (Guzzle): ' . $msg);
            return ['error' => 'Guzzle Error: ' . substr($msg, 0, 100)];
        } catch (\Exception $e) {
            Log::error('Gemini Service Error: ' . $e->getMessage());
            return ['error' => 'Internal Service Error: ' . $e->getMessage()];
        }
    }
}
