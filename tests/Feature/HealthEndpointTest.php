<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

final class HealthEndpointTest extends TestCase
{
    public function test_api_health_endpoint_returns_ok(): void
    {
        $this->getJson('/api/v1/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('application', 'afpedia-cms');
    }

    public function test_csharp_compatible_health_endpoint_returns_ok(): void
    {
        $this->getJson('/health')
            ->assertOk()
            ->assertJsonPath('status', 'ok')
            ->assertJsonPath('application', 'AFPedia CMS');
    }
}
