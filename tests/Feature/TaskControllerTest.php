<?php

namespace Tests\Feature;

use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskControllerTest extends TestCase
{
    use RefreshDatabase;

    private $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Authenticate the user
        $this->actingAs($this->user, 'api');
    }

    /** @test */
    public function it_should_return_paginated_tasks()
    {
        Task::factory()->count(20)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks');

        $response->assertStatus(200);
        $response->assertJsonStructure(['data', 'links', 'meta']);
    }

    /** @test */
    public function it_should_create_a_task()
    {
        $taskData = [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'priority' => 1
        ];

        $response = $this->postJson('/api/tasks', $taskData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_should_show_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/tasks/' . $task->id);

        $response->assertStatus(200);
        $response->assertJson(['data' => ['id' => $task->id]]);
    }

    /** @test */
    public function it_should_update_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);
        $updatedData = [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'status' => 'in_progress',
            'due_date' => now()->addDays(10),
            'priority' => 2
        ];

        $response = $this->putJson('/api/tasks/' . $task->id, $updatedData);

        $response->assertStatus(200);
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Task']);
        $response->assertJsonStructure(['data']);
    }

    /** @test */
    public function it_should_delete_a_task()
    {
        $task = Task::factory()->create(['user_id' => $this->user->id]);

        $response = $this->deleteJson('/api/tasks/' . $task->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
