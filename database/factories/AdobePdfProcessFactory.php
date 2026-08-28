<?php

namespace ArielMejiaDev\LaravelAdobePdf\Database\Factories;

use ArielMejiaDev\LaravelAdobePdf\Enums\ProcessStatus;
use ArielMejiaDev\LaravelAdobePdf\Models\AdobePdfProcess;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<AdobePdfProcess>
 */
class AdobePdfProcessFactory extends Factory
{
    protected $model = AdobePdfProcess::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'operation' => 'create',
            'status' => ProcessStatus::Pending,
            'options' => [],
            'inputs' => [],
            'asset_ids' => null,
            'adobe_location' => null,
            'poll_attempts' => 0,
        ];
    }

    public function processing(): static
    {
        return $this->state(fn () => [
            'status' => ProcessStatus::Processing,
            'adobe_location' => 'https://pdf-services.adobe.io/operation/create/'.Str::random(10),
            'started_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn () => [
            'status' => ProcessStatus::Completed,
            'output_disk' => 'local',
            'output_path' => 'adobe-pdf/'.Str::random(10).'.pdf',
            'completed_at' => now(),
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => ProcessStatus::Failed,
            'error' => ['code' => 'INVALID_ASSET_ID', 'message' => 'The asset was not found.'],
            'failed_at' => now(),
        ]);
    }
}
