<?php

namespace Database\Seeders;

use App\Enums\Serie;
use App\Models\Block;
use App\Models\Camera;
use App\Models\Experience;
use App\Models\Photography;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory()->create([
            'name' => 'Test Admin',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        User::factory()->create([
            'name' => 'Test Renaud',
            'email' => 'renaud@gmail.com',
            'password' => Hash::make('password'),
        ]);

        Experience::factory(12)->create();
        Camera::factory(12)->create();
        Photography::factory(12)->create([
            'series' => (string) fake()->randomElement(Serie::cases())->value,
            'camera_id' => Camera::factory()->create()->id,
        ]);
        Block::factory(12)->create();
        Project::factory(12)->create();
    }
}
