<?php

namespace Database\Factories;

use App\Models\ProjectIdea;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProjectIdea>
 */
class ProjectIdeaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title' => fake()->catchPhrase(),
            'tagline' => fake()->sentence(6),
            'description' => fake()->paragraph(),
            'tech_stack' => fake()->randomElements(['Laravel', 'Vue', 'FrankenPHP', 'MySQL', 'Docker', 'Flutter', 'Go'], 3),
            'difficulty' => fake()->randomElement(ProjectIdea::DIFFICULTIES),
            'status' => fake()->randomElement(ProjectIdea::OPEN_STATUSES),
            'origin' => fake()->randomElement(ProjectIdea::ORIGINS),
            'hype_level' => fake()->numberBetween(1, 5),
            'target_event' => null,
            'deadline' => null,
            'repo_url' => null,
            'is_pinned' => false,
        ];
    }
}
