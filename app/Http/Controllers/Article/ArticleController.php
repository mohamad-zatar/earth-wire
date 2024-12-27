<?php

namespace App\Http\Controllers\Article;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleSearchRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Symfony\Component\HttpFoundation\Response;

class ArticleController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Get paginated list of articles with optional filters
     */
    public function index(ArticleSearchRequest $request): AnonymousResourceCollection
    {
        $page = $request->get('page', 1);
        $cacheKey = $this->generateCacheKey(array_merge($request->validated(), ['page' => $page]));

        $articles = Cache::remember(
            $cacheKey,
            config('cache.article_ttl', now()->addDays(10)),
            fn () => $this->articleService->getFilteredArticles($request->validated())
        );

        return ArticleResource::collection($articles);
    }

    /**
     * Get personalized feed for authenticated user
     */
    public function personalizedFeed(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $cacheKey = "personalized_feed_user_{$request->user()->id}";

        //        Redis::flushall();

        $result = Cache::remember(
            $cacheKey,
            config('cache.article_ttl', now()->addDays(10)),
            fn () => $this->articleService->getPersonalizedFeed($request->user()->id)
        );
        if (is_array($result)) {
            return response()->json($result, $result['status']);
        }

        return ArticleResource::collection($result);
    }

    /**
     * Get specific article by ID
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cacheKey = "article_{$id}";

            $article = Cache::remember(
                $cacheKey,
                config('cache.article_ttl', now()->addDays(10)),
                fn () => $this->articleService->findArticle($id)
            );

            return response()->json(new ArticleResource($article));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Article not found',
            ], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'An error occurred while fetching the article',
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Generate cache key based on request parameters
     */
    protected function generateCacheKey(array $params): string
    {
        ksort($params);

        return 'articles_'.md5(json_encode($params));
    }
}
