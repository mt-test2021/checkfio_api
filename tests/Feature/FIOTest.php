<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;

class FIOTest extends TestCase
{
    use RefreshDatabase;

    private array $existing_fio;

    protected function setUp(): void
    {
        parent::setUp();

        $this->existing_fio = [
            'last_name' => "Иванов",
            'first_name' => "Иван",
            'surname' => "Иванович"
        ];

        DB::table('fio')->insert($this->existing_fio);

        $this->new_fio = [
            'last_name' => "Сидоров",
            'first_name' => "Петр",
            'surname' => "Петрович"
        ];
    }

    public function testNotJsonRequest(): void
    {
        $response = $this->post('api/checkfio', $this->existing_fio);
        $response->assertStatus(404);
    }

    public function testCheckFioRequiredFields(): void
    {
        $newFIO = Arr::only($this->new_fio, ['first_name', 'surname']);
        $response = $this->postJson('api/checkfio', $newFIO);
        $response->assertSessionHasNoErrors();
        $this->assertFalse($response['success']);
    }

    public function testCheckFioCyrillic(): void
    {
        $this->new_fio['last_name'] = 'Sidorov';
        $response = $this->postJson('api/checkfio', $this->new_fio);
        $response->assertSessionHasNoErrors();
        $this->assertFalse($response['success']);
    }

    public function testCheckFio(): void
    {
        $response = $this->postJson('api/checkfio', $this->new_fio);
        $response->assertSessionHasNoErrors();
        $this->assertTrue($response['success']);
    }

    public function testSaveFio(): void
    {
        $response = $this->postJson('api/savefio', $this->new_fio);
        $response->assertSessionHasNoErrors();
        $this->assertTrue($response['success']);
        $this->assertDatabaseHas('fio', $this->new_fio);
    }

    public function testSaveFioIfExists(): void
    {
        $response = $this->postJson('api/savefio', $this->existing_fio);
        $response->assertSessionHasNoErrors();
        $this->assertFalse($response['success']);
    }
}
