<?php

use App\Models\Article;
use App\Models\Camera;
use App\Models\Category;
use App\Models\Experience;
use App\Models\Photography;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

it('returns the api home message', function () {
    $this->getJson('/api')
        ->assertOk()
        ->assertJsonStructure(['message']);
});

it('reports a healthy api and database', function () {
    $this->getJson('/api/health')
        ->assertOk()
        ->assertJsonPath('status', 'healthy')
        ->assertJsonPath('database', 'connected')
        ->assertJsonStructure(['timestamp']);
});

it('reports an unhealthy api when the database is disconnected', function () {
    DB::shouldReceive('connection')->andThrow(new PDOException('down'));

    $this->getJson('/api/health')
        ->assertServiceUnavailable()
        ->assertJsonPath('status', 'unhealthy')
        ->assertJsonPath('database', 'disconnected');
});

it('lists only active cameras and shows one by slug', function () {
    Camera::factory()->create(['active' => true, 'slug' => 'visible-camera']);
    Camera::factory()->create(['active' => false, 'slug' => 'hidden-camera']);

    $this->getJson('/api/cameras')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-camera');

    $this->getJson('/api/camera/visible-camera')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-camera');

    $this->getJson('/api/camera/missing-camera')->assertNotFound();
    $this->getJson('/api/camera/hidden-camera')->assertNotFound();
});

it('lists only active projects and shows one by slug', function () {
    Project::factory()->create(['active' => true, 'slug' => 'visible-project']);
    Project::factory()->create(['active' => false, 'slug' => 'hidden-project']);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-project');

    $this->getJson('/api/project/visible-project')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-project');

    $this->getJson('/api/project/missing-project')->assertNotFound();
    $this->getJson('/api/project/hidden-project')->assertNotFound();
});

it('lists active projects in the chosen order', function () {
    Project::factory()->create(['active' => true, 'slug' => 'shown-last', 'position' => 2]);
    Project::factory()->create(['active' => true, 'slug' => 'shown-first', 'position' => 1]);
    Project::factory()->create(['active' => false, 'slug' => 'hidden-between', 'position' => 1]);

    $this->getJson('/api/projects')
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.slug', 'shown-first')
        ->assertJsonPath('data.1.slug', 'shown-last');
});

it('lists only active photographies and shows one by slug', function () {
    $camera = Camera::factory()->create();

    Photography::factory()->create([
        'active' => true,
        'slug' => 'visible-photo',
        'camera_id' => $camera->id,
    ]);
    Photography::factory()->create([
        'active' => false,
        'slug' => 'hidden-photo',
        'camera_id' => $camera->id,
    ]);

    $this->getJson('/api/photographies')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-photo');

    $this->getJson('/api/photography/visible-photo')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-photo');

    $this->getJson('/api/photography/missing-photo')->assertNotFound();
    $this->getJson('/api/photography/hidden-photo')->assertNotFound();
});

it('lists only active experiences and shows one by slug', function () {
    Experience::factory()->create(['active' => true, 'slug' => 'visible-experience']);
    Experience::factory()->create(['active' => false, 'slug' => 'hidden-experience']);

    $this->getJson('/api/experiences')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-experience');

    $this->getJson('/api/experience/visible-experience')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-experience');

    $this->getJson('/api/experience/missing-experience')->assertNotFound();
    $this->getJson('/api/experience/hidden-experience')->assertNotFound();
});

it('lists only active articles and shows one by slug', function () {
    Article::factory()->create(['active' => true, 'slug' => 'visible-article']);
    Article::factory()->create(['active' => false, 'slug' => 'hidden-article']);

    $this->getJson('/api/articles')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-article');

    $this->getJson('/api/article/visible-article')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-article');

    $this->getJson('/api/article/missing-article')->assertNotFound();
    $this->getJson('/api/article/hidden-article')->assertNotFound();
});

it('returns the requested article language and falls back to french', function () {
    Article::factory()->create([
        'active' => true,
        'slug' => 'minolta-x700',
        'title' => [
            'fr' => 'Minolta FR',
            'en' => 'Minolta EN',
            'ky' => 'Minolta KY',
        ],
        'content' => [
            'fr' => [
                'block-fr' => ['type' => 'paragraph', 'data' => ['content' => '<p>Bonjour</p>']],
            ],
            'en' => [
                'block-en' => ['type' => 'paragraph', 'data' => ['content' => '<p>Hello</p>']],
            ],
            'ky' => [
                'block-ky' => ['type' => 'paragraph', 'data' => ['content' => '<p>Салам</p>']],
            ],
        ],
    ]);

    $this->getJson('/api/articles')
        ->assertOk()
        ->assertJsonPath('data.0.lang', 'fr')
        ->assertJsonPath('data.0.title', 'Minolta FR')
        ->assertJsonPath('data.0.content.0.content', '<p>Bonjour</p>')
        ->assertJsonPath('data.0.available_langs', ['fr', 'en', 'ky']);

    $this->getJson('/api/articles?lang=en')
        ->assertOk()
        ->assertJsonPath('data.0.lang', 'en')
        ->assertJsonPath('data.0.title', 'Minolta EN')
        ->assertJsonPath('data.0.content.0.content', '<p>Hello</p>');

    $this->getJson('/api/article/minolta-x700?lang=ky')
        ->assertOk()
        ->assertJsonPath('data.lang', 'ky')
        ->assertJsonPath('data.title', 'Minolta KY')
        ->assertJsonPath('data.content.0.content', '<p>Салам</p>');

    $this->getJson('/api/article/minolta-x700?lang=de')
        ->assertOk()
        ->assertJsonPath('data.lang', 'fr')
        ->assertJsonPath('data.title', 'Minolta FR')
        ->assertJsonPath('data.content.0.content', '<p>Bonjour</p>');

    $this->getJson('/api/article/minolta-x700?lang=it')
        ->assertOk()
        ->assertJsonPath('data.lang', 'fr')
        ->assertJsonPath('data.title', 'Minolta FR');
});

it('counts one view and one completed read per visitor and language', function () {
    Article::factory()->create([
        'active' => true,
        'slug' => 'read-me',
        'title' => ['fr' => 'À lire'],
    ]);
    Article::factory()->create([
        'active' => false,
        'slug' => 'hidden-read',
    ]);

    $this->getJson('/api/article/read-me')->assertOk();
    $this->getJson('/api/article/read-me')->assertOk();
    $this->getJson('/api/article/read-me?lang=it')->assertOk();
    $this->getJson('/api/article/read-me?lang=en')->assertOk();

    $article = Article::query()->where('slug', 'read-me')->firstOrFail();

    expect($article->readingStats()->where('locale', 'fr')->first()?->views_count)->toBe(1)
        ->and($article->readingStats()->where('locale', 'en')->first()?->views_count)->toBe(1)
        ->and($article->readingStats()->where('locale', 'en')->first()?->last_viewed_at)->not->toBeNull();

    $this->postJson('/api/article/read-me/read')->assertNoContent();
    $this->postJson('/api/article/read-me/read')->assertNoContent();
    $this->postJson('/api/article/read-me/read?lang=ky')->assertNoContent();

    $article->unsetRelation('readingStats');

    expect($article->readingStats()->where('locale', 'fr')->first()?->reads_count)->toBe(1)
        ->and($article->readingStats()->where('locale', 'fr')->first()?->last_read_at)->not->toBeNull()
        ->and($article->readingStats()->where('locale', 'ky')->first()?->reads_count)->toBe(1);

    $this->getJson('/api/article/hidden-read')->assertNotFound();
    $this->postJson('/api/article/hidden-read/read')->assertNotFound();
    expect($article->readingStats()->count())->toBe(3);
});

it('lists only active categories and shows one by slug', function () {
    Category::factory()->create(['active' => true, 'slug' => 'visible-category']);
    Category::factory()->create(['active' => false, 'slug' => 'hidden-category']);

    $this->getJson('/api/categories')
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.slug', 'visible-category');

    $this->getJson('/api/category/visible-category')
        ->assertOk()
        ->assertJsonPath('data.slug', 'visible-category');

    $this->getJson('/api/category/missing-category')->assertNotFound();
    $this->getJson('/api/category/hidden-category')->assertNotFound();
});
