<?php

namespace App\Features\Ai\Contracts;

use App\Features\Ai\DTO\GeneratedPostItem;

interface AiClientInterface
{
    /**
     * @return list<GeneratedPostItem>
     *
     * @throws \RuntimeException jika layanan AI gagal / tidak dikonfigurasi
     */
    public function generate(string $topic, int $count, ?string $category = null): array;
}
