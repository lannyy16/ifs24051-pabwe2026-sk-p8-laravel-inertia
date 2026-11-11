<?php

namespace Tests\Unit\Services;

use App\Features\Ai\DTO\GeneratedPostItem;
use App\Features\Ai\Services\OpenAiPostGeneratorService;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class OpenAiPostGeneratorServiceTest extends TestCase
{
    private function wrap(mixed $content): array
    {
        return ['choices' => [['message' => ['content' => is_string($content) ? $content : json_encode($content)]]]];
    }

    public function test_throws_without_api_key(): void
    {
        config(['openai.key' => '']);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('OPENAI_KEY belum dikonfigurasi di server.');
        (new OpenAiPostGeneratorService)->generate('x', 1);
    }

    public function test_generates_normalised_items_and_sends_expected_prompt(): void
    {
        config(['openai.key' => 'sk-test', 'openai.endpoint' => 'https://ai.test/v1/', 'openai.model' => 'm1']);
        Http::fake(['ai.test/*' => Http::response($this->wrap(['posts' => [
            ['title' => ' Satu ', 'category' => 'Bisnis', 'excerpt' => 'R', 'content' => '## A'],
            'bukan-array',
            ['title' => '', 'content' => 'kosong'],
            ['title' => 'Dua', 'category' => 'ngawur', 'content' => 'B'],
        ]]))]);

        $items = (new OpenAiPostGeneratorService)->generate('Laravel', 5, 'Bisnis');

        $this->assertCount(2, $items);
        $this->assertContainsOnlyInstancesOf(GeneratedPostItem::class, $items);
        $this->assertSame('Satu', $items[0]->title);
        $this->assertSame('Umum', $items[1]->category);
        Http::assertSent(fn ($r) => $r->url() === 'https://ai.test/v1/chat/completions'
            && $r->hasHeader('Authorization', 'Bearer sk-test')
            && $r->data()['model'] === 'm1'
            && str_contains($r->data()['messages'][1]['content'], 'Gunakan kategori Bisnis'));
    }

    public function test_limits_to_requested_count_without_category_hint(): void
    {
        config(['openai.key' => 'sk-test']);
        Http::fake(['*' => Http::response($this->wrap(['posts' => [
            ['title' => 'A', 'content' => 'a'], ['title' => 'B', 'content' => 'b'],
        ]]))]);

        $this->assertCount(1, (new OpenAiPostGeneratorService)->generate('x', 1));
        Http::assertSent(fn ($r) => ! str_contains($r->data()['messages'][1]['content'], 'kategori'));
    }

    public function test_failure_modes(): void
    {
        config(['openai.key' => 'sk-test']);
        Http::fake(['*' => Http::sequence()
            ->push('x', 500)
            ->push($this->wrap('bukan json'))
            ->push($this->wrap(['posts' => [['title' => '']]]))]);
        $service = new OpenAiPostGeneratorService;

        foreach (['Layanan OpenAI gagal merespons (HTTP 500).', 'Format respons AI tidak valid.', 'AI tidak menghasilkan artikel yang dapat digunakan.'] as $message) {
            try {
                $service->generate('x', 1);
                $this->fail('RuntimeException expected');
            } catch (RuntimeException $e) {
                $this->assertSame($message, $e->getMessage());
            }
        }
    }
}
