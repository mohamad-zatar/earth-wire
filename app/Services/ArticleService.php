<?php

namespace App\Services;

use App\Models\Article;
use App\Models\UserPreference;
use Illuminate\Database\Eloquent\Builder;
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
     * Get personalized feed based on user preferences
     */
    public function getPersonalizedFeed(int $userId): array|LengthAwarePaginator
    {
        $preference = $this->getUserPreference($userId);

        if (! $preference) {
            return [
                'status' => 404,
                'message' => 'No preferences set yet.',
            ];
        }

        $query = $this->buildPersonalizedQuery($preference);

        return $query->paginate(config('pagination.articles_per_page', 10));
    }

    /**
     * Find specific article
     */
    public function findArticle(int $id): Article
    {
        return Article::findOrFail($id);
    }

    /**
     * Get user preference
     */
    protected function getUserPreference(int $userId): ?UserPreference
    {
        return UserPreference::where('user_id', $userId)->first();
    }

    /**
     * Build query based on user preferences
     */
    protected function buildPersonalizedQuery(UserPreference $preference): Builder
    {
        $query = Article::query();

        if ($preference->sources) {
            $query->orWhereIn('source', $preference->sources);
        }

        if ($preference->categories) {
            $query->orWhereIn('category', $preference->categories);
        }

        if ($preference->authors) {
            $query->orWhereIn('author', $preference->authors);
        }

        return $query;
    }
}
