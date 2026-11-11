<?php

namespace Tests\Unit\DTO;

use App\Features\Ai\DTO\GeneratedPostItem;
use PHPUnit\Framework\TestCase;

class GeneratedPostItemTest extends TestCase
{
    public function test_from_array_trims_and_normalises_category(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => ' T ', 'content' => ' C ', 'category' => 'ngawur']);

        $this->assertSame(['title' => 'T', 'category' => 'Umum', 'excerpt' => null, 'content' => 'C'], $item->toArray());
    }

    public function test_from_array_keeps_valid_category_and_excerpt(): void
    {
        $item = GeneratedPostItem::fromArray(['title' => 'T', 'content' => 'C', 'category' => 'Bisnis', 'excerpt' => ' E ']);

        $this->assertSame('Bisnis', $item->category);
        $this->assertSame('E', $item->excerpt);
    }
}
