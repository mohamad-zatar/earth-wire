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

/**
 * @group Article Management
 * APIs for Article Management.
 *
 * @authenticated
 * All endpoints in this group require a Sanctum Bearer token.
 */
class ArticleController extends Controller
{
    protected ArticleService $articleService;

    public function __construct(ArticleService $articleService)
    {
        $this->articleService = $articleService;
    }

    /**
     * Get paginated list of articles
     * Retrieve a paginated list of articles with optional filters.
     *
     * @unauthenticated
     *
     *  This endpoint does not require authentication.
     * @queryParam page integer The page number. Example: 1
     * @queryParam category string Optional category filter. Example: Technology
     * @queryParam source string Optional source filter. Example: New York Times
     * @queryParam keyword string Optional search keyword. Example: Game
     * @queryParam date string Optional search keyword. Example: 2024-12-26
     *
     */
    public function index(ArticleSearchRequest $request): AnonymousResourceCollection
    {
        $page = $request->get('page', 1);
        $cacheKey = $this->generateCacheKey(array_merge($request->validated(), ['page' => $page]));

        $articles = Cache::remember(
            $cacheKey,
            config('cache.article_ttl', now()->addMinutes(10)),
            fn () => $this->articleService->getFilteredArticles($request->validated())
        );

        return ArticleResource::collection($articles);
    }

    /**
     * Get personalized feed
     *
     * Retrieve a personalized feed of articles for the authenticated user.
     *
     * @authenticated
     *  This endpoint require authentication.
     */
    public function personalizedFeed(Request $request): JsonResponse|AnonymousResourceCollection
    {
        $cacheKey = "personalized_feed_user_{$request->user()->id}";


        $result = Cache::remember(
            $cacheKey,
            config('cache.article_ttl', now()->addMinutes(10)),
            fn () => $this->articleService->getPersonalizedFeed($request->user()->id)
        );
        if (is_array($result)) {
            return response()->json($result, $result['status']);
        }

        return ArticleResource::collection($result);
    }

    /**
     * Get specific article by ID
     *
     * Retrieve the details of a specific article by its ID.
     * @authenticated
     * @urlParam id integer required The ID of the article. Example: 1
     */
    public function show(int $id): JsonResponse
    {
        try {
            $cacheKey = "article_{$id}";

            $article = Cache::remember(
                $cacheKey,
                config('cache.article_ttl', now()->addMinutes(10)),
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
     * Generate cache key
     *
     * Generate a cache key based on request parameters.
     *
     * @hideFromAPIDocumentation
     */
    protected function generateCacheKey(array $params): string
    {
        ksort($params);

        return 'articles_'.md5(json_encode($params));
    }
}
