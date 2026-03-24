<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * HelpController — AUDIT FIX: Support, Help Center & Notification System
 */
class HelpController extends Controller
{
    public function index(Request $request)
    {
        $category = $request->get('category');
        $search   = $request->get('q');

        $articles = DB::table('help_articles')
            ->where('is_published', true)
            ->when($category, fn($q) => $q->where('category', $category))
            ->when($search, fn($q) => $q->where(function ($q2) use ($search) {
                $q2->where('title', 'ilike', "%{$search}%")
                   ->orWhere('excerpt', 'ilike', "%{$search}%");
            }))
            ->orderBy('is_featured', 'desc')
            ->orderBy('sort_order')
            ->get();

        $categories = DB::table('help_articles')
            ->where('is_published', true)
            ->select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->orderBy('category')
            ->pluck('count', 'category');

        return view('help.index', compact('articles', 'categories', 'category', 'search'));
    }

    public function article(string $slug)
    {
        $article = DB::table('help_articles')
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        // Increment view count
        DB::table('help_articles')->where('id', $article->id)
            ->increment('view_count');

        $related = DB::table('help_articles')
            ->where('category', $article->category)
            ->where('id', '!=', $article->id)
            ->where('is_published', true)
            ->limit(4)
            ->get();

        return view('help.article', compact('article', 'related'));
    }

    public function feedback(Request $request, int $articleId)
    {
        $data = $request->validate(['helpful' => 'required|boolean']);

        $col = $data['helpful'] ? 'helpful_count' : 'not_helpful_count';
        DB::table('help_articles')->where('id', $articleId)->increment($col);

        return response()->json(['thanks' => true]);
    }
}
