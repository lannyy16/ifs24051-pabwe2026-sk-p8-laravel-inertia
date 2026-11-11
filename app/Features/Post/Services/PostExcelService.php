<?php

namespace App\Features\Post\Services;

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class PostExcelService
{
    public const HEADERS = ['Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status'];

    public const ADMIN_HEADERS = ['Penulis', 'Username', 'Judul', 'Kategori', 'Ringkasan', 'Isi', 'Status', 'Dibuat'];

    public function __construct(private readonly PostService $posts) {}

    /** Hasilkan berkas .xlsx sementara dan kembalikan path-nya. */
    public function export(Collection $posts, bool $withAuthor = false): string
    {
        $spreadsheet = new Spreadsheet;
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Artikel');

        $headers = $withAuthor ? self::ADMIN_HEADERS : self::HEADERS;
        $sheet->fromArray($headers, null, 'A1');

        $row = 2;
        foreach ($posts as $post) {
            $status = $post->is_published ? 'Published' : 'Draft';
            $values = $withAuthor
                ? [
                    $post->user->name, $post->user->username, $post->title, $post->category,
                    $post->excerpt, $post->content, $status, $post->created_at?->format('Y-m-d H:i'),
                ]
                : [$post->title, $post->category, $post->excerpt, $post->content, $status];

            $sheet->fromArray($values, null, 'A'.$row++);
        }

        $lastColumn = chr(64 + count($headers));
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true);
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()
            ->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E0E7FF');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $path = tempnam(sys_get_temp_dir(), 'posts_').'.xlsx';
        (new Xlsx($spreadsheet))->save($path);

        return $path;
    }

    /**
     * Impor artikel dari Excel. Baris pertama adalah header.
     *
     * @return array{imported: int, skipped: int}
     */
    public function import(User $user, UploadedFile $file): array
    {
        $rows = IOFactory::load($file->getRealPath())->getActiveSheet()->toArray(null, true, true, false);
        array_shift($rows);

        $items = [];
        $skipped = 0;

        foreach ($rows as $row) {
            $title = trim((string) ($row[0] ?? ''));
            $content = trim((string) ($row[3] ?? ''));

            if ($title === '' || $content === '') {
                $skipped++;

                continue;
            }

            $items[] = [
                'title' => $title,
                'category' => trim((string) ($row[1] ?? '')),
                'excerpt' => trim((string) ($row[2] ?? '')) ?: null,
                'content' => $content,
                'is_published' => in_array(strtolower(trim((string) ($row[4] ?? ''))), ['published', 'terbit', 'ya', '1'], true),
            ];
        }

        return ['imported' => $this->posts->createMany($user, $items), 'skipped' => $skipped];
    }
}
