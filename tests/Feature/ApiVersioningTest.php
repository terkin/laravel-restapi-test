<?php

namespace Tests\Feature;
use Tests\TestCase;


class ApiVersioningTest extends TestCase
{
	public function testBothVersions() {
		$response = $this->json('GET', '/api/version');
		$response->assertStatus(200);

		$this->assertEquals(1, $response->getOriginalContent()['data']['version']);

		$response = $this->json('GET', '/api/v2/version');
		$response->assertStatus(200);

		$this->assertEquals(2, $response->getOriginalContent()['data']['version']);
	}
}