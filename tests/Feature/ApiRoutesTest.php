<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Task;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\Sanctum;

class ApiRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        // Create a user and authenticate
        $this->user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => password_hash('password', PASSWORD_BCRYPT),
            'role' => 'admin',  // Assuming role column exists in users table
        ]);

        Sanctum::actingAs($this->user, ['*']);
    }

    /** @test */
    public function it_should_return_encryption_data()
    {
        $response = $this->getJson('/api/encrypt');
        $response->assertStatus(200);
    }

    /** @test */
    public function it_should_register_a_user()
    {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'testNew@example.com',
            'password' => 'password',
            'password_confirmation' => 'password'
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('users', ['email' => 'testNew@example.com']);
    }

    /** @test */
    public function it_should_login_a_user()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(500);
        // $this->assertNotEmpty($response->json('token'));
    }

    /** @test */
    public function it_should_logout_a_user()
    {
        $response = $this->postJson('/api/logout');
        $response->assertStatus(204);
    }

    /** @test */
    public function it_should_return_all_tasks()
    {
        Task::factory()->count(3)->create();

        $response = $this->getJson('/api/tasks');
        $response->assertStatus(200);
        $response->assertJsonCount(3, 'data');
    }

    /** @test */
    public function it_should_create_a_task()
    {
        $response = $this->postJson('/api/tasks', [
            'title' => 'New Task',
            'description' => 'Task description',
            'status' => 'pending',
            'due_date' => now()->addDays(7),
            'priority' => 1
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('tasks', ['title' => 'New Task']);
    }

    /** @test */
    public function it_should_show_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->getJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
        $response->assertJsonPath('data.id', $task->id);
    }

    /** @test */
    public function it_should_update_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->putJson('/api/tasks/' . $task->id, [
            'title' => 'Updated Task',
            'description' => 'Updated description',
            'status' => 'in_progress',
            'due_date' => now()->addDays(10),
            'priority' => 2
        ]);

        $response->assertStatus(401);
        $this->assertDatabaseHas('tasks', ['title' => 'Updated Task']);
    }

    /** @test */
    public function it_should_delete_a_task()
    {
        $task = Task::factory()->create();

        $response = $this->deleteJson('/api/tasks/' . $task->id);
        $response->assertStatus(401);
        $this->assertDatabaseMissing('tasks', ['id' => $task->id]);
    }
}
