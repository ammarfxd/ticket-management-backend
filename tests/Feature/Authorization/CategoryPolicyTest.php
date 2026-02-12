<?php

namespace Tests\Feature\Authorization;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
            'password' => 'password',
        ]);
    }

    
    public function test_any_authenticated_user_can_view_categories(): void
    {
        $customer = $this->makeUser(User::ROLE_CUSTOMER);

        $this->actingAs($customer, 'sanctum')
            ->getJson('/api/categories')
            ->assertOk();
    }

    public function test_non_admin_cannot_create_update_or_delete_category(): void
    {
        $agent = $this->makeUser(User::ROLE_AGENT);
        $category = Category::factory()->create();

        // create
        $this->actingAs($agent, 'sanctum')
            ->postJson('/api/categories', ['name' => 'New Category'])
            ->assertStatus(403);

        // update
        $this->actingAs($agent, 'sanctum')
            ->patchJson("/api/categories/{$category->id}", ['name' => 'Updated'])
            ->assertStatus(403);

        // delete
        $this->actingAs($agent, 'sanctum')
            ->deleteJson("/api/categories/{$category->id}")
            ->assertStatus(403);
    }

    
    public function test_admin_can_create_update_and_delete_category(): void
    {
        $admin = $this->makeUser(User::ROLE_ADMIN);

        // create
        $create = $this->actingAs($admin, 'sanctum')
            ->postJson('/api/categories', ['name' => 'Billing'])
            ->assertCreated()
            ->assertJsonStructure(['message', 'data' => ['id', 'name', 'slug']]);

        $categoryId = $create->json('data.id');

        // update
        $this->actingAs($admin, 'sanctum')
            ->patchJson("/api/categories/{$categoryId}", ['name' => 'Billing & Payment'])
            ->assertOk();

        // delete
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/categories/{$categoryId}")
            ->assertOk();

        $this->assertDatabaseMissing('categories', ['id' => $categoryId]);
    }
}
