<?php


use App\Models\Member;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetTopResultsTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var array<array<mixed>>
     */
    protected array $topSelfStructure = [
        'data' => [
            'top' => [
                ['email','place','milliseconds']
            ],
            'self' => ['email','place','milliseconds'],
        ],
    ];

    /**
     * @var array<array<mixed>>
     */
    protected array $topOnlyStructure = [
        'data' => [
            'top' => [
                ['email','place','milliseconds']
            ],
        ],
    ];

    public function test_get_top_and_self_with_correct_structure():void
    {
        (new DatabaseSeeder())->run();
        $member = Member::query()->has('results')->first();

        $response = $this->get(route('results.top', ['email' => $member['email']]));

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(200);
        $response->assertJsonStructure($this->topSelfStructure);
    }

    public function test_get_top_only_with_correct_structure_email_is_empty():void
    {
        (new DatabaseSeeder())->run();

        $response = $this->get(route('results.top', ['email' => '']));

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(200);
        $response->assertJsonStructure($this->topOnlyStructure);
        $this->assertArrayNotHasKey('self', $response->json('data'));
    }

    public function test_get_top_only_with_correct_structure_email_has_not_been_sent():void
    {
        (new DatabaseSeeder())->run();

        $response = $this->get(route('results.top'));

        $response->assertSessionDoesntHaveErrors();
        $response->assertStatus(200);
        $response->assertJsonStructure($this->topOnlyStructure);
        $this->assertArrayNotHasKey('self', $response->json('data'));
    }

    public function test_incorrect_email_validation():void
    {
        $incorrectEmail = 'memberAexample.com';
        Member::factory()->create(['email' => $incorrectEmail]);

        $response = $this->get(route('results.top', ['email' => $incorrectEmail]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_email_must_be_existed_member_email():void
    {
        $memberEmail = 'member@example.com';
        $nonExistedEmail = 'nonexisted@example.com';
        Member::factory()->create(['email' => $memberEmail]);

        $response = $this->get(route('results.top', ['email' => $nonExistedEmail]));

        $response->assertStatus(302);
        $response->assertSessionHasErrors('email');
    }

    public function test_the_number_of_top_items_must_be_equal_to_the_limit():void
    {
        (new DatabaseSeeder())->run();

        $response = $this->get(route('results.top', ['email' => '']));

        $this->assertCount(config('results.top.limit') ?? 10, $response->json('data.top'));
    }
}
