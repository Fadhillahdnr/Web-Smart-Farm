<?php

namespace Tests\Feature;

use App\Models\SensorData;
use App\Models\SoilPlot;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SoilMonitoringTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_a_soil_plot(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('soil-plots.store'), [
            'name' => '  Tanah A  ',
        ]);

        $soilPlot = $user->soilPlots()->first();
        $response->assertRedirect(route('dashboard', ['soil' => $soilPlot->id]));
        $this->assertSame('Tanah A', $soilPlot->name);
        $this->assertSame(48, strlen($soilPlot->getRawOriginal('sensor_token')));
    }

    public function test_sensor_data_is_saved_to_the_soil_matching_its_token(): void
    {
        $soilPlot = $this->soilPlotFor(User::factory()->create(), 'Tanah A');

        $response = $this->postJson('/api/sensor', [
            'moisture' => 62,
            'ph' => 6.8,
            'color' => 'COKLAT',
            'status' => 'SUBUR',
            'battery' => 87,
        ], ['X-Soil-Token' => $soilPlot->getRawOriginal('sensor_token')]);

        $response->assertCreated()->assertJsonPath('soil', 'Tanah A');
        $this->assertDatabaseHas('sensor_data', [
            'soil_plot_id' => $soilPlot->id,
            'moisture' => 62,
        ]);
    }

    public function test_sensor_endpoint_rejects_an_invalid_token_and_invalid_ranges(): void
    {
        $this->postJson('/api/sensor', [
            'moisture' => 101,
            'ph' => 15,
            'color' => 'COKLAT',
            'status' => 'SUBUR',
            'battery' => -1,
        ], ['X-Soil-Token' => 'invalid'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['moisture', 'ph', 'battery']);
    }

    public function test_user_cannot_read_or_modify_another_users_soil(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $soilPlot = $this->soilPlotFor($owner, 'Tanah Rahasia');

        $this->actingAs($otherUser)
            ->get(route('dashboard.history', $soilPlot))
            ->assertNotFound();

        $this->actingAs($otherUser)
            ->patch(route('soil-plots.update', $soilPlot), ['name' => 'Diambil'])
            ->assertNotFound();
    }

    public function test_each_soil_only_returns_its_own_history(): void
    {
        $user = User::factory()->create();
        $soilA = $this->soilPlotFor($user, 'Tanah A');
        $soilB = $this->soilPlotFor($user, 'Tanah B');
        SensorData::create($this->sensorPayload($soilA, 30));
        SensorData::create($this->sensorPayload($soilB, 80));

        $this->actingAs($user)
            ->getJson(route('dashboard.history', $soilA))
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.moisture', 30);
    }

    private function soilPlotFor(User $user, string $name): SoilPlot
    {
        return $user->soilPlots()->create([
            'name' => $name,
            'sensor_token' => substr(hash('sha256', $user->id.'|'.$name), 0, 48),
        ]);
    }

    private function sensorPayload(SoilPlot $soilPlot, int $moisture): array
    {
        return [
            'soil_plot_id' => $soilPlot->id,
            'moisture' => $moisture,
            'ph' => 6.5,
            'color' => 'COKLAT',
            'status' => 'SUBUR',
            'battery' => 90,
        ];
    }
}
