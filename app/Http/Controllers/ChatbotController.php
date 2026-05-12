<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\ChatMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ChatbotController extends Controller
{
    public function ask(Request $request)
    {
        $request->validate([
            'message' => 'required|string|max:1500',
        ]);

        $userMessage = trim($request->message);
        $userId = Auth::id();

        // pastikan guest juga punya session id
        if (!$request->session()->has('chat_session_id')) {
            $request->session()->put('chat_session_id', (string) \Illuminate\Support\Str::uuid());
        }

        $sessionId = $request->session()->get('chat_session_id');

        if ($this->isResetCommand($userMessage)) {
            $this->clearConversationHistory($userId, $sessionId);

            return response()->json([
                'reply' => 'Riwayat chat sudah saya reset. Silakan mulai pertanyaan baru ya.'
            ]);
        }

        // simpan pesan user dulu
        ChatMessage::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'role' => 'user',
            'message' => $userMessage,
        ]);

        // ambil data toko dari database
        $categories = Category::pluck('category_name')->take(10)->toArray();

        $products = Product::where('status', 'active')
            ->latest()
            ->take(12)
            ->get(['name', 'price', 'stock_quantity'])
            ->map(function ($product) {
                return [
                    'name' => $product->name,
                    'price' => (int) $product->price,
                    'stock' => (int) $product->stock_quantity,
                ];
            })
            ->toArray();

        // ambil 12 pesan terakhir untuk memory
        $historyQuery = ChatMessage::query()
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }, function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->latest()
            ->take(12)
            ->get()
            ->reverse()
            ->values();

        $conversationHistory = $historyQuery->map(function ($item) {
            return [
                'role' => $item->role,
                'content' => $item->message,
            ];
        })->toArray();

        $storeContext = [
            'nama_toko' => 'Warung Mamah',
            'deskripsi' => 'Toko UMKM modern untuk kebutuhan harian dan produk pilihan.',
            'jam_buka' => 'Setiap hari sekitar pukul 08:00 - 18:00 WIB',
            'alamat' => 'Pasar Rawa Kalong, Bekasi',
            'kontak' => '0821-2505-2233 / rendiprano15@gmail.com',
            'cara_order' => 'User dapat melihat produk, menambahkan ke keranjang, lalu checkout di website.',
            'kategori' => $categories,
            'produk' => $products,
        ];

        $systemPrompt = <<<PROMPT
Kamu adalah asisten AI customer service untuk website Warung Mamah.

Aturan:
1. Jawab dalam Bahasa Indonesia.
2. Jawab dengan ramah, natural, jelas, dan membantu.
3. Kamu boleh menjawab pertanyaan umum, bukan hanya soal toko.
4. Tapi jika pertanyaan terkait toko, produk, harga, stok, checkout, pembayaran, pengiriman, utamakan konteks toko yang diberikan.
5. Jangan mengarang harga atau stok jika tidak ada di data.
6. Kalau user bertanya sambung-menyambung, gunakan riwayat percakapan untuk memahami konteks.
7. Kalau tidak yakin, jujur dan arahkan ke halaman produk atau admin.
8. Jangan terlalu panjang kecuali user meminta penjelasan detail.
PROMPT;

        $inputMessages = [
            [
                'role' => 'system',
                'content' => $systemPrompt,
            ],
            [
                'role' => 'system',
                'content' => 'Konteks toko: ' . json_encode($storeContext, JSON_UNESCAPED_UNICODE),
            ],
        ];

        $userPreferenceInstruction = $this->buildUserPreferenceInstruction($userMessage);
        if ($userPreferenceInstruction !== null) {
            $inputMessages[] = [
                'role' => 'system',
                'content' => $userPreferenceInstruction,
            ];
        }

        foreach ($conversationHistory as $history) {
            $inputMessages[] = [
                'role' => $history['role'],
                'content' => $history['content'],
            ];
        }

        $provider = strtolower((string) env('AI_PROVIDER', 'openai'));

        try {
            $response = $provider === 'gemini'
                ? $this->sendGeminiRequest($inputMessages)
                : $this->sendOpenAiRequest($inputMessages);
        } catch (\RuntimeException $e) {
            return response()->json([
                'reply' => $e->getMessage(),
            ], 500);
        } catch (\Throwable $e) {
            Log::error('Request AI provider gagal.', [
                'provider' => $provider,
                'message' => $e->getMessage(),
            ]);

            return response()->json([
                'reply' => 'Maaf, chatbot sedang mengalami gangguan jaringan. Coba lagi sebentar ya.'
            ], 500);
        }

        if (!$response->successful()) {
            Log::error('AI provider mengembalikan error.', [
                'provider' => $provider,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            $status = $response->status();
            $errorCode = data_get($response->json(), 'error.code');

            if ($status === 401) {
                return response()->json([
                    'reply' => $provider === 'gemini'
                        ? 'API key Gemini tidak valid. Hubungi admin untuk memperbarui GEMINI_API_KEY.'
                        : 'API key OpenAI tidak valid. Hubungi admin untuk memperbarui OPENAI_API_KEY.'
                ], 500);
            }

            if ($provider === 'gemini' && $status === 404) {
                return response()->json([
                    'reply' => 'Model Gemini tidak ditemukan. Coba ubah GEMINI_MODEL ke gemini-2.0-flash di file .env.'
                ], 500);
            }

            if ($status === 429 || $errorCode === 'insufficient_quota') {
                return response()->json([
                    'reply' => 'Limit/kredit API sedang habis. Silakan isi billing atau coba lagi setelah kuota tersedia.'
                ], 500);
            }

            return response()->json([
                'reply' => 'Maaf, chatbot sedang mengalami gangguan. Coba lagi sebentar ya.'
            ], 500);
        }

        $data = $response->json();
        $reply = $provider === 'gemini'
            ? $this->extractTextFromGeminiResponse($data)
            : $this->extractTextFromResponse($data);
        if (empty($reply)) {
            $reply = 'Maaf, saya belum bisa menangkap jawabannya dengan baik. Coba ulangi pertanyaan dengan kalimat lain ya.';
        }

        // simpan balasan bot
        ChatMessage::create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'role' => 'assistant',
            'message' => $reply,
        ]);

        return response()->json([
            'reply' => $reply
        ]);
    }

    private function extractTextFromResponse(array $data): ?string
    {
        return data_get($data, 'choices.0.message.content');
    }

    private function isResetCommand(string $message): bool
    {
        $normalized = strtolower(trim($message));

        return in_array($normalized, ['/reset', 'reset chat', 'hapus riwayat chat', 'mulai ulang chat'], true);
    }

    private function clearConversationHistory(?int $userId, string $sessionId): void
    {
        ChatMessage::query()
            ->when($userId, function ($query) use ($userId) {
                $query->where('user_id', $userId);
            }, function ($query) use ($sessionId) {
                $query->where('session_id', $sessionId);
            })
            ->delete();
    }

    private function buildUserPreferenceInstruction(string $userMessage): ?string
    {
        $message = strtolower($userMessage);
        $instructions = [];

        if ($this->containsAny($message, ['singkat', 'ringkas', 'pendek', 'to the point'])) {
            $instructions[] = 'Buat jawaban singkat, langsung ke inti, maksimal 2-4 kalimat.';
        }

        if ($this->containsAny($message, ['detail', 'lengkap', 'jelaskan', 'mendalam'])) {
            $instructions[] = 'Berikan penjelasan lebih detail, tetap terstruktur dan mudah dipahami.';
        }

        if ($this->containsAny($message, ['poin', 'bullet', 'daftar', 'list'])) {
            $instructions[] = 'Gunakan format poin-poin agar mudah dibaca.';
        }

        if ($this->containsAny($message, ['langkah', 'step by step', 'tahapan', 'cara'])) {
            $instructions[] = 'Jika relevan, jelaskan dalam urutan langkah yang jelas.';
        }

        if ($this->containsAny($message, ['formal', 'resmi'])) {
            $instructions[] = 'Gunakan gaya bahasa formal dan sopan.';
        }

        if ($this->containsAny($message, ['santai', 'casual', 'gaul'])) {
            $instructions[] = 'Gunakan gaya bahasa santai namun tetap sopan.';
        }

        if (empty($instructions)) {
            return null;
        }

        return 'Preferensi gaya jawaban dari user: ' . implode(' ', $instructions);
    }

    private function containsAny(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function sendOpenAiRequest(array $inputMessages)
    {
        $apiKey = (string) env('OPENROUTER_API_KEY', '');

        return Http::timeout(45)
            ->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://openrouter.ai/api/v1/chat/completions', [

                'model' => env('OPENROUTER_MODEL'),

                'messages' => $inputMessages,

            ]);
    }

    private function sendGeminiRequest(array $inputMessages)
    {
        $apiKey = (string) env('GEMINI_API_KEY', '');
        if ($apiKey === '' || $apiKey === 'isi_api_key_kamu') {
            Log::warning('Gemini API key belum dikonfigurasi dengan benar.');
            throw new \RuntimeException('Konfigurasi chatbot belum lengkap. Hubungi admin untuk mengisi GEMINI_API_KEY.');
        }

        $model = env('GEMINI_MODEL', 'gemini-2.0-flash');
        $url = 'https://generativelanguage.googleapis.com/v1beta/models/' . $model . ':generateContent?key=' . urlencode($apiKey);

        return Http::timeout(45)->post($url, [
            'contents' => [
                [
                    'role' => 'user',
                    'parts' => [
                        [
                            'text' => $this->buildGeminiPrompt($inputMessages),
                        ],
                    ],
                ],
            ],
        ]);
    }

    private function buildGeminiPrompt(array $inputMessages): string
    {
        $lines = [];

        foreach ($inputMessages as $message) {
            $role = strtoupper((string) data_get($message, 'role', 'USER'));
            $content = (string) data_get($message, 'content', '');
            $lines[] = '[' . $role . '] ' . $content;
        }

        return implode("\n\n", $lines);
    }

    private function extractTextFromGeminiResponse(array $data): ?string
    {
        return data_get($data, 'candidates.0.content.parts.0.text');
    }
}