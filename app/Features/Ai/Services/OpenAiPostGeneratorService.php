<?php

namespace App\Features\Ai\Services;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Post\Enums\PostCategory;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class OpenAiPostGeneratorService implements AiClientInterface
{
    public function generate(string $topic, int $count, ?string $category = null): array
    {
        $apiKey = config('openai.key');

        if (! $apiKey) {
            throw new RuntimeException('OPENAI_KEY belum dikonfigurasi di server.');
        }

        $response = Http::withToken($apiKey)
            ->baseUrl((string) config('openai.endpoint'))
            ->timeout(60)
            ->post('/chat/completions', [
                'model' => config('openai.model'),
                'response_format' => ['type' => 'json_object'],
                'messages' => [
                    ['role' => 'system', 'content' => $this->systemPrompt()],
                    ['role' => 'user', 'content' => $this->userPrompt($topic, $count, $category)],
                ],
            ]);

        if ($response->failed()) {
            throw new RuntimeException('Layanan OpenAI gagal merespons (HTTP '.$response->status().').');
        }

        $decoded = json_decode((string) $response->json('choices.0.message.content'), true);
        $rawItems = is_array($decoded) ? ($decoded['posts'] ?? null) : null;

        if (! is_array($rawItems)) {
            throw new RuntimeException('Format respons AI tidak valid.');
        }

        $items = [];
        foreach ($rawItems as $raw) {
            if (! is_array($raw) || empty($raw['title']) || empty($raw['content'])) {
                continue;
            }
            $items[] = GeneratedPostItem::fromArray($raw);
        }

        if ($items === []) {
            throw new RuntimeException('AI tidak menghasilkan artikel yang dapat digunakan.');
        }

        return array_slice($items, 0, $count);
    }

    private function systemPrompt(): string
    {
        $categories = implode(', ', PostCategory::values());

        return 'Kamu adalah penulis blog profesional berbahasa Indonesia. '
            .'Balas HANYA dengan JSON: {"posts":[{"title":"","category":"","excerpt":"","content":""}]}. '
            ."category harus salah satu dari: {$categories}. "
            .'excerpt berisi 1-2 kalimat pemikat. content ditulis dalam format Markdown (gunakan heading, paragraf, dan daftar).';
    }

    private function userPrompt(string $topic, int $count, ?string $category): string
    {
        $prompt = "Buat {$count} artikel blog tentang: {$topic}.";

        return $category ? $prompt." Gunakan kategori {$category}." : $prompt;
    }
}
