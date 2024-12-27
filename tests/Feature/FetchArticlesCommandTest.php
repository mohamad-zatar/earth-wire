<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class FetchArticlesCommandTest extends TestCase
{
    public function test_command_fetches_articles_and_stores_them_in_database()
    {
        Http::fake([
            'https://newsapi.org/*' => Http::response([
                'articles' => [
                    [
                        'title' => 'Article 1',
                        'content' => 'Content 1',
                        'author' => 'Author 1',
                        'source' => ['name' => 'Source 1'],
                        'url' => 'https://newsapi.org/article1',
                        'publishedAt' => '2024-12-27T10:00:00Z',
                    ],
                    [
                        'title' => 'Article 2',
                        'content' => 'Content 2',
                        'author' => 'Author 2',
                        'source' => ['name' => 'Source 2'],
                        'url' => 'https://newsapi.org/article2',
                        'publishedAt' => '2024-12-27T11:00:00Z',
                    ],
                ],
            ], 200),
            'https://content.guardianapis.com/*' => Http::response([
                'response' => [
                    'results' => [
                        [
                            'webTitle' => 'Article 1',
                            'webUrl' => 'https://theguardian.com/article1',
                            'webPublicationDate' => '2024-12-27T12:00:00Z',
                        ],
                    ],
                ],
            ], 200),
            'https://api.nytimes.com/*' => Http::response([
                'results' => [
                    [
                        'title' => 'Article 1',
                        'abstract' => 'NYT Article 1',
                        'byline' => 'Author 1',
                        'url' => 'https://nytimes.com/article1',
                        'published_date' => '2024-12-27T13:00:00Z',
                    ],
                ],
            ], 200),
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with('Article Import Summary', [
                'NewsAPI' => 2,
                'The Guardian' => 1,
                'New York Times' => 1,
            ]);

        Artisan::call('fetch:articles');

        $this->assertDatabaseHas('articles', [
            'title' => 'Article 1',
            'content' => 'NYT Article 1',
            'author' => 'Author 1',
            'source' => 'New York Times',
        ]);

        $this->assertStringContainsString('Articles imported', Artisan::output());
    }
}
