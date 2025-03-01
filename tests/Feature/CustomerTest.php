<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class CustomerTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_user_can_store_customer()
    {
        // Fake user authentication
        $user = User::factory()->create();
        $this->actingAs($user, 'api'); // Authenticate user for API request
    
        // Generate dynamic customer data
        $customerData = Customer::factory()->make()->toArray();
    
        // Send POST request
        $response = $this->postJson('/api/customers', $customerData);
    
        // Assert response
        $response->assertStatus(201)
                 ->assertJson(['message' => 'Customer created successfully']);
    
        // Verify customer is stored in the database
        $this->assertDatabaseHas('customers', [
            'email' => $customerData['email'],
        ]);
    }

    public function test_user_can_update_customer()
    {
        // Fake user authentication
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        // Create a customer in the database
        $customer = Customer::factory()->create();

        // Generate new customer data dynamically
        $updatedData = Customer::factory()->make()->toArray();

        // Send PUT request to update customer
        $response = $this->putJson("/api/customers/{$customer->id}", $updatedData);

        // Assert response
        $response->assertStatus(200)
                ->assertJson(['message' => 'Customer updated successfully']);

        // Verify update in database
        $this->assertDatabaseHas('customers', [
            'id' => $customer->id,
            'email' => $updatedData['email'],
        ]);
    }

    public function test_user_can_delete_customer()
    {
        // Fake user authentication
        $user = User::factory()->create();
        $this->actingAs($user, 'api');

        // Create a customer dynamically
        $customer = Customer::factory()->create();

        // Send DELETE request
        $response = $this->deleteJson("/api/customers/{$customer->id}");

        // Assert response
        $response->assertStatus(200)
                ->assertJson(['message' => 'Customer deleted successfully']);

        // Verify customer is deleted
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
    }


    
}
