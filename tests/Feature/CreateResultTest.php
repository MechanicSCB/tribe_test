<?php


use App\Models\Member;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateResultTest extends TestCase
{
    use RefreshDatabase;

    public function test_results_can_be_created_without_email(): void
    {
        $attributes = [
            'milliseconds' => rand(1000, 50000),
        ];

        $response = $this->post(route('results.store'), $attributes);

        $response->assertStatus(200);
        $this->assertDatabaseHas('results', $attributes);
    }

    public function test_results_can_be_created_with_nullable_email(): void
    {
        $milliseconds = rand(1000, 50000);

        $response = $this->post(route('results.store'), [
            'email' => null,
            'milliseconds' => $milliseconds,
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('results', [
            'member_id' => null,
            'milliseconds' => $milliseconds,
        ]);
    }

    public function test_results_can_be_created_with_real_member_email_and_attached_to_member(): void
    {
        $member = Member::factory()->create(['id' => rand(1, 500)]);

        $milliseconds = rand(1000, 50000);

        $response = $this->post(route('results.store'), [
            'email' => $member['email'],
            'milliseconds' => $milliseconds,
        ]);

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(200);
        $this->assertDatabaseHas('results', [
            'member_id' => $member['id'],
            'milliseconds' => $milliseconds,
        ]);
    }

    public function test_email_must_be_existed_member_email(): void
    {
        $memberEmail = 'member@example.com';
        $nonExistedEmail = 'nonexisted@example.com';
        Member::factory()->create(['email' => $memberEmail]);

        $milliseconds = rand(1000, 50000);

        $response = $this->post(route('results.store'), [
            'email' => $nonExistedEmail,
            'milliseconds' => $milliseconds,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_results_incorrect_email_validation(): void
    {
        $incorrectEmail = 'memberAexample.com';
        Member::factory()->create(['email' => $incorrectEmail]);

        $response = $this->post(route('results.store'), [
            'email' => $incorrectEmail,
            'milliseconds' => rand(1000, 50000),
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_results_milliseconds_is_required_validation(): void
    {
        $response = $this->post(route('results.store'));

        $response->assertStatus(302);
        $response->assertSessionHasErrors('milliseconds');
    }

    public function test_results_milliseconds_is_positive_validation(): void
    {
        $response = $this->post(route('results.store'), ['milliseconds' => -1000,]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('milliseconds');
    }

    public function test_results_milliseconds_is_numeric_validation(): void
    {
        $response = $this->post(route('results.store'), ['milliseconds' => '1123a',]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors('milliseconds');
    }
}
