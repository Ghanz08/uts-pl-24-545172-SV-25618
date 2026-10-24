<?php

namespace Tests\Feature;

use App\Models\ProjectIdea;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProjectIdeaApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        Sanctum::actingAs($this->user);
    }

    public function test_can_list_only_own_ideas_with_pinned_first(): void
    {
        ProjectIdea::factory()->for($this->user)->create(['title' => 'Biasa']);
        ProjectIdea::factory()->for($this->user)->create(['title' => 'Favorit', 'is_pinned' => true]);
        ProjectIdea::factory()->create(['title' => 'Punya orang lain']);

        $this->getJson('/api/project-ideas')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.title', 'Favorit');
    }

    public function test_can_create_idea_returns_201(): void
    {
        $payload = [
            'title' => 'KosMate',
            'tagline' => 'Tinder buat teman kos',
            'tech_stack' => ['Laravel', 'Vue'],
            'difficulty' => 'medium',
            'origin' => 'nongkrong',
            'hype_level' => 4,
        ];

        $this->postJson('/api/project-ideas', $payload)
            ->assertStatus(201)
            ->assertJsonPath('data.title', 'KosMate')
            ->assertJsonPath('data.status', 'spark')
            ->assertJsonPath('data.is_completed', false)
            ->assertJsonPath('data.tech_stack', ['Laravel', 'Vue']);

        $this->assertDatabaseHas('project_ideas', ['title' => 'KosMate', 'user_id' => $this->user->id]);
    }

    public function test_create_with_invalid_data_returns_422(): void
    {
        $this->postJson('/api/project-ideas', [
            'difficulty' => 'impossible',
            'hype_level' => 9,
            'repo_url' => 'bukan-url',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['title', 'difficulty', 'hype_level', 'repo_url']);
    }

    public function test_can_show_single_idea(): void
    {
        $idea = ProjectIdea::factory()->for($this->user)->create();

        $this->getJson("/api/project-ideas/{$idea->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $idea->id);
    }

    public function test_can_update_idea_and_shipped_means_completed(): void
    {
        $idea = ProjectIdea::factory()->for($this->user)->create(['status' => 'building']);

        $this->putJson("/api/project-ideas/{$idea->id}", ['status' => 'shipped'])
            ->assertOk()
            ->assertJsonPath('data.status', 'shipped')
            ->assertJsonPath('data.is_completed', true);
    }

    public function test_can_delete_idea(): void
    {
        $idea = ProjectIdea::factory()->for($this->user)->create();

        $this->deleteJson("/api/project-ideas/{$idea->id}")->assertOk();

        $this->assertDatabaseMissing('project_ideas', ['id' => $idea->id]);
    }

    public function test_cannot_touch_another_users_idea(): void
    {
        $foreign = ProjectIdea::factory()->create();

        $this->getJson("/api/project-ideas/{$foreign->id}")->assertNotFound();
        $this->deleteJson("/api/project-ideas/{$foreign->id}")->assertNotFound();
        $this->assertDatabaseHas('project_ideas', ['id' => $foreign->id]);
    }

    public function test_roulette_only_picks_open_ideas(): void
    {
        ProjectIdea::factory()->for($this->user)->create(['status' => 'shipped']);
        $open = ProjectIdea::factory()->for($this->user)->create(['status' => 'spark']);

        $this->getJson('/api/project-ideas/roulette')
            ->assertOk()
            ->assertJsonPath('data.id', $open->id);
    }

    public function test_stats_counts_by_status(): void
    {
        ProjectIdea::factory()->for($this->user)->count(3)->create(['status' => 'spark']);
        ProjectIdea::factory()->for($this->user)->create(['status' => 'shipped']);

        $this->getJson('/api/project-ideas/stats')
            ->assertOk()
            ->assertJsonPath('data.total', 4)
            ->assertJsonPath('data.by_status.spark', 3)
            ->assertJsonPath('data.shipping_rate', 25);
    }
}
