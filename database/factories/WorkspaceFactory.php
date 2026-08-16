<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Workspace>
 */
class WorkspaceFactory extends Factory
{
    protected $model = Workspace::class;

    public function definition(): array
    {
        return [
            'name' => fake()->company().' workspace',
            'slug' => Str::slug(fake()->unique()->company()).'-'.Str::lower(Str::random(4)),
            'owner_id' => User::factory(),
        ];
    }
}
