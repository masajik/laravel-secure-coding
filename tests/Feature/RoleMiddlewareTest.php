<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Task;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $admin;
    private $user;
    private $task;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test users
        $this->admin = User::factory()->create([
            'email' => 'admin@example.com',
            'role' => 'admin',
        ]);

        $this->user = User::factory()->create([
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        // Create a test task
        $this->task = Task::factory()->create([
            'user_id' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_should_allow_get_request_by_admin_user()
    {
        $this->actingAs($this->user);

        $response = $this->getJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Sorry, you are unauthorized for this!']);
    }

    /** @test */
    public function it_should_reject_get_request_by_non_admin_user()
    {
        $this->actingAs($this->admin);

        $response = $this->getJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_should_allow_put_request_by_owner()
    {
        $this->actingAs($this->user);

        $response = $this->putJson('/api/tasks/' . $this->task->id, [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_should_reject_put_request_by_non_owner()
    {
        $this->actingAs($this->admin);

        $response = $this->putJson('/api/tasks/' . $this->task->id, [
            'title' => 'Updated Task',
        ]);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Sorry, you are unauthorized for this!']);
    }

    /** @test */
    public function it_should_allow_delete_request_by_owner()
    {
        $this->actingAs($this->user);

        $response = $this->deleteJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_should_reject_delete_request_by_non_owner()
    {
        $this->actingAs($this->admin);

        $response = $this->deleteJson('/api/tasks/' . $this->task->id);

        $response->assertStatus(403);
        $response->assertJson(['message' => 'Sorry, you are unauthorized for this!']);
    }
}
