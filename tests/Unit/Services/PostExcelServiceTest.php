<?php

namespace Tests\Unit\Services;

use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Tests\TestCase;

class PostExcelServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): PostExcelService
    {
        return app(PostExcelService::class);
    }

    private function xlsx(array $rows): UploadedFile
    {
        $sheet = ($book = new Spreadsheet)->getActiveSheet();
        $sheet->fromArray($rows, null, 'A1');
        $path = tempnam(sys_get_temp_dir(), 'imp_').'.xlsx';
        (new Xlsx($book))->save($path);

        return new UploadedFile($path, 'a.xlsx', null, null, true);
    }

    public function test_export_without_author(): void
    {
        $post = Post::factory()->published()->create(['title' => 'Satu', 'excerpt' => 'ex']);

        $path = $this->service()->export(collect([$post->load('user')]));
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        @unlink($path);

        $this->assertSame(PostExcelService::HEADERS, $rows[0]);
        $this->assertSame(['Satu', $post->category, 'ex', $post->content, 'Published'], $rows[1]);
    }

    public function test_export_with_author_and_draft_status(): void
    {
        $post = Post::factory()->draft()->create();

        $path = $this->service()->export(collect([$post->load('user')]), true);
        $rows = IOFactory::load($path)->getActiveSheet()->toArray();
        @unlink($path);

        $this->assertSame(PostExcelService::ADMIN_HEADERS, $rows[0]);
        $this->assertSame($post->user->name, $rows[1][0]);
        $this->assertSame('Draft', $rows[1][6]);
    }

    public function test_import_counts_imported_and_skipped(): void
    {
        $user = User::factory()->create();
        $file = $this->xlsx([
            ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'],
            ['A', 'Opini', 'r', 'isi', 'Ya'],
            ['B', 'x', '', 'isi', 'Draft'],
            ['', 'Umum', '', 'tanpa judul', ''],
        ]);

        $result = $this->service()->import($user, $file);

        $this->assertSame(['imported' => 2, 'skipped' => 1], $result);
        $this->assertTrue(Post::where('title', 'A')->first()->is_published);
        $this->assertFalse(Post::where('title', 'B')->first()->is_published);
    }
}
