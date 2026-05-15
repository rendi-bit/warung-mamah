<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1000',
        ]);

        $userMessage = trim($request->message);

        /*
        |--------------------------------------------------------------------------
        | Deteksi pertanyaan produk
        |--------------------------------------------------------------------------
        */

$messageLower = strtolower($userMessage);

$isProductQuestion =
    str_contains($messageLower, 'produk') ||
    str_contains($messageLower, 'kategori') ||
    str_contains($messageLower, 'stok') ||
    str_contains($messageLower, 'harga') ||
    str_contains($messageLower, 'sembako') ||
    str_contains($messageLower, 'bumbu') ||
    str_contains($messageLower, 'dapur') ||
    str_contains($messageLower, 'rumah tangga') ||
    str_contains($messageLower, 'kebutuhan rumah') ||
    str_contains($messageLower, 'beras') ||
    str_contains($messageLower, 'minyak') ||
    str_contains($messageLower, 'gula') ||
    str_contains($messageLower, 'tepung');
        /*
        |--------------------------------------------------------------------------
        | Default ringan
        |--------------------------------------------------------------------------
        */

        $categories = [
            'Sembako',
            'Bumbu Dapur',
            'Kebutuhan Rumah Tangga'
        ];
        $products = [];

        /*
        |--------------------------------------------------------------------------
        | Ambil data hanya jika perlu
        |--------------------------------------------------------------------------
        */

        if ($isProductQuestion) {

            $categories = Cache::remember(
                'chatbot_categories',
                300,
                function () {
                    return Category::limit(5)
                        ->pluck('category_name')
                        ->toArray();
                }
            );

            $products = Cache::remember(
                'chatbot_products',
                300,
                function () {
                    return Product::where('status', 'active')
                        ->limit(5)
                        ->get([
                            'name',
                            'price',
                            'stock_quantity'
                        ])
                        ->toArray();
                }
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Context toko
        |--------------------------------------------------------------------------
        */

        $storeContext = [
            'nama_toko'  => 'Toko Tika',
            'deskripsi'  => 'Toko UMKM modern untuk kebutuhan harian.',
            'jam_buka'   => '08:00 - 18:00 WIB',
            'alamat'     => 'Pasar Rawa Kalong, Bekasi',
            'kontak'     => '0821-2505-2233',
            'kategori'   => $categories,
            'produk'     => $products,
        ];

        /*
        |--------------------------------------------------------------------------
        | System Prompt
        |--------------------------------------------------------------------------
        */

        $systemPrompt = <<<PROMPT
Kamu adalah asisten AI customer service Toko Tika.

Aturan:
1. Jawab dalam Bahasa Indonesia.
2. Jawab singkat, jelas, ramah, natural.
3. Maksimal 3-5 kalimat kecuali user meminta detail.
4. Jangan mengarang stok atau harga.
5. Gunakan konteks toko jika relevan.
6. Kalau tidak tahu, jujur.
PROMPT;

        $inputMessages = [
            [
                'role' => 'system',
                'content' => $systemPrompt
            ],
            [
                'role' => 'system',
                'content' => 'Data toko: ' .
                    json_encode($storeContext, JSON_UNESCAPED_UNICODE)
            ],
            [
                'role' => 'user',
                'content' => $userMessage
            ]
        ];

        /*
        |--------------------------------------------------------------------------
        | Request OpenRouter
        |--------------------------------------------------------------------------
        */

        try {

            $response = Http::retry(2, 1000)
                ->timeout(15)
                ->acceptJson()
                ->withHeaders([
                    'Authorization' => 'Bearer ' . env('OPENROUTER_API_KEY'),
                    'HTTP-Referer'  => env('APP_URL'),
                    'X-Title'       => env('APP_NAME'),
                ])
                ->post(
                    'https://openrouter.ai/api/v1/chat/completions',
                    [
                        'model' => env(
                            'OPENROUTER_MODEL',
                            'deepseek/deepseek-chat-v3-0324'
                        ),

                        'messages' => $inputMessages,

                        'max_tokens' => 150,

                        'temperature' => 0.7,
                    ]
                );

        } catch (\Throwable $e) {

            Log::error('Chatbot request gagal', [
                'message' => $e->getMessage()
            ]);

            return response()->json([
                'reply' => 'Maaf, chatbot sedang gangguan. Coba lagi sebentar ya.'
            ], 500);
        }

        /*
        |--------------------------------------------------------------------------
        | Validasi response
        |--------------------------------------------------------------------------
        */

        if (!$response->successful()) {

            Log::error('OpenRouter error', [
                'status' => $response->status(),
            ]);

            return response()->json([
                'reply' => 'Maaf, AI sedang sibuk. Coba lagi sebentar.'
            ], 500);
        }

        $reply = data_get(
            $response->json(),
            'choices.0.message.content'
        );

        if (!$reply) {
            $reply = 'Maaf, saya belum bisa menjawab itu.';
        }

        return response()->json([
            'reply' => trim($reply)
        ]);
    }
}