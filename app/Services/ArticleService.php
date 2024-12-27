<?php

namespace App\Services;

use App\Models\Article;
use Illuminate\Pagination\LengthAwarePaginator;

class ArticleService
{
    /**
     * Get filtered articles
     */
    public function getFilteredArticles(array $filters): LengthAwarePaginator
    {
        $query = Article::query();

        if (isset($filters['keyword'])) {
            $query->whereFullText(['title', 'content'], $filters['keyword']);
        }

        if (isset($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (isset($filters['source'])) {
            $query->where('source', $filters['source']);
        }

        if (isset($filters['date'])) {
            $query->whereDate('published_at', $filters['date']);
        }

        return $query->paginate(config('pagination.articles_per_page', 10));
    }

    /**
     * Find specific article
     */
    public function findArticle(int $id): Article
    {
        return Article::findOrFail($id);
    }
}
