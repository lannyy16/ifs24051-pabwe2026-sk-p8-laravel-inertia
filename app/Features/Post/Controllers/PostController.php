<?php

namespace App\Features\Post\Controllers;

use App\Features\Ai\Contracts\AiClientInterface;
use App\Features\Post\Enums\PostCategory;
use App\Features\Post\Models\Post;
use App\Features\Post\Services\PostExcelService;
use App\Features\Post\Services\PostService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use RuntimeException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PostController
{
    public function __construct(
        private readonly PostService $posts,
        private readonly PostExcelService $excel,
    ) {}

    public function index(Request $request): Response
    {
        $search = $request->query('search');

        return Inertia::render('Post/Index', [
            'posts' => $this->posts->listForUser($request->user(), $search),
            'stats' => $this->posts->stats($request->user()),
            'categories' => PostCategory::options(),
            'filters' => ['search' => $search ?? ''],
        ]);
    }

    public function show(Post $post): Response
    {
        return Inertia::render('Post/Show', [
            'post' => $post,
            'categories' => PostCategory::options(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->posts->create($request->user(), $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil dibuat.');
    }

    public function update(Request $request, Post $post): RedirectResponse
    {
        $this->posts->update($post, $this->validated($request), $request->file('cover'));

        return back()->with('success', 'Artikel berhasil diperbarui.');
    }

    public function destroy(Post $post): RedirectResponse
    {
        $this->posts->delete($post);

        return redirect('/dashboard/posts')->with('success', 'Artikel berhasil dihapus.');
    }

    public function publish(Post $post): RedirectResponse
    {
        $post = $this->posts->togglePublish($post);

        return back()->with('success', $post->is_published ? 'Artikel dipublikasikan.' : 'Artikel dijadikan draf.');
    }

    public function cover(Request $request, Post $post): RedirectResponse
    {
        $request->validate(['cover' => ['required', 'image', 'max:2048']]);
        $this->posts->setCover($post, $request->file('cover'));

        return back()->with('success', 'Foto sampul berhasil diunggah.');
    }

    public function destroyCover(Post $post): RedirectResponse
    {
        $this->posts->removeCover($post);

        return back()->with('success', 'Foto sampul dihapus.');
    }

    public function export(Request $request): BinaryFileResponse
    {
        $path = $this->excel->export($this->posts->forExport($request->user()->id));

        return response()->download($path, 'artikel-saya.xlsx')->deleteFileAfterSend(true);
    }

    public function import(Request $request): RedirectResponse
    {
        $request->validate(['file' => ['required', 'file', 'mimes:xlsx,xls', 'max:5120']]);

        $result = $this->excel->import($request->user(), $request->file('file'));

        return back()->with('success', "{$result['imported']} artikel diimpor, {$result['skipped']} baris dilewati.");
    }

    public function aiGenerate(Request $request, AiClientInterface $ai): JsonResponse
    {
        $data = $request->validate([
            'topic' => ['required', 'string', 'max:200'],
            'count' => ['required', 'integer', 'min:1', 'max:5'],
            'category' => ['nullable', Rule::in(PostCategory::values())],
        ]);

        try {
            $items = $ai->generate($data['topic'], (int) $data['count'], $data['category'] ?? null);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json(['items' => array_map(fn ($i) => $i->toArray(), $items)]);
    }

    public function aiSave(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:5'],
            'items.*.title' => ['required', 'string', 'max:255'],
            'items.*.category' => ['nullable', Rule::in(PostCategory::values())],
            'items.*.excerpt' => ['nullable', 'string'],
            'items.*.content' => ['required', 'string'],
            'publish' => ['nullable', 'boolean'],
        ]);

        $count = $this->posts->createMany($request->user(), $data['items'], (bool) ($data['publish'] ?? false));

        return back()->with('success', "{$count} artikel hasil AI berhasil disimpan.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', Rule::in(PostCategory::values())],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'content' => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
            'cover' => ['nullable', 'image', 'max:2048'],
        ]);
    }
}
