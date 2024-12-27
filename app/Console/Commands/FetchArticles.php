<?php

namespace App\Console\Commands;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FetchArticles extends Command
{
    protected $signature = 'fetch:articles';

    protected $description = 'Fetch articles from external news APIs and store them locally in the database';

    public function handle()
    {
        $newsAPICount = $this->fetchFromNewsAPI();
        $guardianCount = $this->fetchFromGuardian();
        $nytCount = $this->fetchFromNYT();

        Log::info('Article Import Summary', [
            'NewsAPI' => $newsAPICount,
            'The Guardian' => $guardianCount,
            'New York Times' => $nytCount,
        ]);

        $this->info("Articles imported: NewsAPI - $newsAPICount, The Guardian - $guardianCount, New York Times - $nytCount");

        $this->info('Articles fetched successfully.');
    }

    private function fetchFromNewsAPI(): int
    {
        $count = 0;
        $response = Http::get('https://newsapi.org/v2/top-headlines', [
            'apiKey' => env('NEWS_API_KEY'),
            'category' => 'technology',
            'country' => 'us',
        ]);

        if ($response->successful()) {
            foreach ($response->json('articles') as $article) {
                Article::updateOrCreate(
                    ['title' => $article['title']],
                    [
                        'content' => $article['content'],
                        'author' => $article['author'],
                        'source' => $article['source']['name'],
                        'category' => 'technology',
                        'url' => $article['url'],
                        'published_at' => Carbon::parse($article['publishedAt'])->format('Y-m-d H:i:s'),

                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    private function fetchFromGuardian(): int
    {
        $count = 0;
        $response = Http::get('https://content.guardianapis.com/search', [
            'api-key' => env('GUARDIAN_API_KEY'),
            'section' => 'technology',
        ]);

        if ($response->successful()) {
            foreach ($response->json('response.results') as $article) {
                Article::updateOrCreate(
                    ['title' => $article['webTitle']],
                    [
                        'content' => null, // Guardian API doesn't provide content in basic API
                        'author' => null,
                        'source' => 'The Guardian',
                        'category' => 'technology',
                        'url' => $article['webUrl'],
                        'published_at' => Carbon::parse($article['webPublicationDate'])->format('Y-m-d H:i:s'),
                    ]
                );
                $count++;
            }
        }

        return $count;
    }

    private function fetchFromNYT(): int
    {
        $count = 0;
        $response = Http::get('https://api.nytimes.com/svc/topstories/v2/technology.json', [
            'api-key' => env('NYT_API_KEY'),
        ]);

        if ($response->successful()) {
            foreach ($response->json('results') as $article) {
                Article::updateOrCreate(
                    ['title' => $article['title']],
                    [
                        'content' => $article['abstract'],
                        'author' => $article['byline'],
                        'source' => 'New York Times',
                        'category' => 'technology',
                        'url' => $article['url'],
                        'published_at' => Carbon::parse($article['published_date'])->format('Y-m-d H:i:s'),

                    ]
                );
                $count++;
            }
        }

        return $count;
    }
}
