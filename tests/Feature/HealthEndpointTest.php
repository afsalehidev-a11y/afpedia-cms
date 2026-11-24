<?php
namespace Tests\Feature;
use Tests\TestCase;
class HealthEndpointTest extends TestCase {
    public function test_api_health_endpoint_returns_ok(): void {
        $this->getJson('/api/v1/health')->assertOk()->assertJsonPath('status','ok')->assertJsonPath('application','afpedia-cms');
    }
}
