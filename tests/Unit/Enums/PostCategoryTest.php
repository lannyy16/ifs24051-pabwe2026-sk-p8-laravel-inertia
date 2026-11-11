<?php

namespace Tests\Unit\Enums;

use App\Features\Post\Enums\PostCategory;
use PHPUnit\Framework\TestCase;

class PostCategoryTest extends TestCase
{
    public function test_values_and_options(): void
    {
        $this->assertSame(['Teknologi', 'Pendidikan', 'Gaya Hidup', 'Bisnis', 'Opini', 'Umum'], PostCategory::values());
        $this->assertCount(6, PostCategory::options());
        $this->assertSame(['value' => 'Umum', 'label' => 'Umum'], PostCategory::options()[5]);
    }

    public function test_descriptions(): void
    {
        $expected = [
            'Teknologi' => 'Teknologi & Koding', 'Pendidikan' => 'Pendidikan & Tutorial',
            'Gaya Hidup' => 'Gaya Hidup & Produktivitas', 'Bisnis' => 'Bisnis & Karir',
            'Opini' => 'Opini & Pemikiran', 'Umum' => 'Umum',
        ];
        foreach (PostCategory::cases() as $case) {
            $this->assertSame($expected[$case->value], $case->description());
        }
    }

    public function test_from_or_default(): void
    {
        $this->assertSame(PostCategory::Bisnis, PostCategory::fromOrDefault('Bisnis'));
        $this->assertSame(PostCategory::Umum, PostCategory::fromOrDefault('x'));
        $this->assertSame(PostCategory::Umum, PostCategory::fromOrDefault(null));
    }
}
