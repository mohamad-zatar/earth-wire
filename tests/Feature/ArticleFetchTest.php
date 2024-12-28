<?php

namespace Tests\Feature;

use App\Models\Article;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ArticleFetchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();

        $this->token = $this->user->createToken('auth_token')->plainTextToken;
    }

    public function test_users_can_access_articles()
    {

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200);
    }

    public function it_can_filter_articles_by_keyword()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Article::factory()->create([
            'title' => 'Laravel',
            'content' => 'This is about Laravel',
        ]);
        Article::factory()->create([
            'title' => 'Symfony',
            'content' => 'This is about something else',
        ]);

        $response = $this->getJson('/api/articles');
        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['title' => 'Special Laravel Article']);
    }

    public function test_it_can_filter_articles_by_category()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Article::factory()->create(['category' => 'technology']);
        Article::factory()->create(['category' => 'sports']);

        $response = $this->getJson('/api/articles?category=technology');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['category' => 'technology']);
    }

    public function test_it_can_filter_articles_by_source()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Article::factory()->create(['source' => 'blog']);
        Article::factory()->create(['source' => 'news']);

        $response = $this->getJson('/api/articles?source=blog');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment(['source' => 'blog']);
    }

    public function test_it_can_filter_articles_by_date()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        Article::factory()->create([
            'published_at' => '2024-01-01',
        ]);
        Article::factory()->create([
            'published_at' => '2024-01-02',
        ]);

        $response = $this->getJson('/api/articles?date=2024-01-01');

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data');
    }

    public function test_it_validates_date_format()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->getJson('/api/articles?date=invalid-date');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['date']);
    }

    public function test_it_can_show_specific_article()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $article = Article::factory()->create();

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonStructure(
                [
                    'id',
                    'title',
                    'content',
                    'category',
                    'source',
                    'published_at',
                    'created_at',
                    'updated_at',
                ]
            )
            ->assertJsonFragment(['id' => $article->id]);
    }

    public function test_it_returns_404_for_non_existent_article()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $response = $this->getJson('/api/articles/99999');

        $response->assertStatus(404)
            ->assertJson(['message' => 'Article not found']);
    }

    public function test_it_caches_article_list()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $count = Article::count();

        $this->getJson('/api/articles');

        Article::factory()->create();

        $response = $this->getJson('/api/articles');

        $response->assertStatus(200)
            ->assertJsonCount($count, 'data'); // Should show cached count, not $count + 1
    }

    public function test_it_caches_single_article()
    {
        Sanctum::actingAs(
            User::factory()->create(),
            ['*']
        );

        $article = Article::factory()->create();

        $this->getJson("/api/articles/{$article->id}");

        $article->update(['title' => 'Updated Title']);

        $response = $this->getJson("/api/articles/{$article->id}");

        $response->assertStatus(200)
            ->assertJsonMissing(['title' => 'Updated Title']);
    }
}
