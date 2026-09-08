<?php

namespace Database\Seeders;

use App\Enums\Serie;
use App\Enums\Stack;
use App\Models\Article;
use App\Models\Camera;
use App\Models\Category;
use App\Models\Photography;
use App\Models\Project;
use Illuminate\Database\Seeder;

class BrunoSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'bruno-test-category'],
            [
                'name' => 'Bruno Test Category',
                'active' => true,
            ]
        );

        Article::query()->updateOrCreate(
            ['slug' => 'bruno-test-article'],
            [
                'title' => 'Bruno Test Article',
                'content' => [
                    ['type' => 'paragraph', 'content' => 'Test content for Bruno CI.'],
                ],
                'active' => true,
                'category_id' => $category->id,
            ]
        );

        $camera = Camera::query()->updateOrCreate(
            ['slug' => 'bruno-test-camera'],
            [
                'name' => 'Bruno Test Camera',
                'serie' => (string) Serie::s35->value,
                'content' => 'Test camera for Bruno CI.',
                'active' => true,
            ]
        );

        if ($camera->documents()->doesntExist()) {
            $camera->documents()->create(['image' => 'camera/bruno-test.jpg']);
        }

        Photography::query()->updateOrCreate(
            ['slug' => 'bruno-test-photography'],
            [
                'name' => 'Bruno Test Photography',
                'date' => now(),
                'series' => (string) Serie::s35->value,
                'city' => 'Paris',
                'image' => 'photography/bruno-test.jpg',
                'camera_id' => $camera->id,
                'active' => true,
            ]
        );

        $project = Project::query()->updateOrCreate(
            ['slug' => 'bruno-test-project'],
            [
                'name' => 'Bruno Test Project',
                'description' => 'Test project for Bruno CI.',
                'url' => 'https://example.com',
                'url_github' => 'https://github.com/example/bruno-test',
                'stack' => [Stack::Laravel->value],
                'active' => true,
            ]
        );

        if ($project->documents()->doesntExist()) {
            $project->documents()->create(['image' => 'project/bruno-test.jpg']);
        }
    }
}
