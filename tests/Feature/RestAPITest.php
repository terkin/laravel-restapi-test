<?php

namespace Tests\Feature;

use App\Models\Video;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class RestAPITest extends TestCase
{

    public function testBasicTest()
    {
	    $response = $this->json('GET', '/api/request-token');
	    $response
		    ->assertStatus(200)
		    ->assertJson([
			    'statusCode' => 200,
			    'statusMessage' => "OK",
		    ]);
	    $response->assertSee("data");
	    $this->assertTrue(is_string($response->getOriginalContent()['data']['deviceToken']));
	    return $response->getOriginalContent()['data']['deviceToken'];
    }

	/**
	 * @depends testBasicTest
	 */
	public function testDeviceAuthMiddleware($deviceToken) {
		$this->json('GET', '/api/')
			->assertStatus(401)
			->assertSee('Please provide deviceToken.');

		$this->json('POST', '/api/trim')
			->assertStatus(401)
			->assertSee('Please provide deviceToken.');

		$this->withHeaders([
			'deviceToken' => 'badToken',
		])->json('GET', '/api/')
			->assertStatus(401)
			->assertSee('Please provide deviceToken.');

	}

	/**
	 * @depends testBasicTest
	 */
    public function testGetList($deviceToken) {

	    $response = $this->withHeaders([
		    'deviceToken' => $deviceToken,
	    ])->json('GET', '/api/');

	    $response->assertStatus(200);
	    $this->assertEquals([], $response->getOriginalContent()['data']['items']);
    }

	/**
	 * @depends testBasicTest
	 */
	public function testPostTrim($deviceToken)
	{
		Storage::fake('videos');
		$response = $this->withHeaders([
			'deviceToken' => $deviceToken,
		])->json('POST', '/api/trim', [
			'video' => UploadedFile::fake()->create('video.avi'),
			'start' => 10
		]);
		$response->assertStatus(200);
		$responseData = $response->getOriginalContent()['data'];
		$this->assertEquals(Video::STATUS_SCHEDULED, $responseData['status']);
		$this->assertTrue(is_string($responseData['videoId']));
		$this->assertTrue(is_string($responseData['url']));

		//since queue driver is sync in tests and jobs executes right away, lets check it here...
		$repo = resolve('App\Repositories\VideoRepository');
		$videoModel = $repo->getVideoById($responseData['videoId']);
		$this->assertNotNull($videoModel);
		$this->assertEquals(Video::STATUS_DONE, $videoModel->status);

		return [$deviceToken, $responseData['videoId']];
	}

	/**
	 * @depends testPostTrim
	 */
	public function testGetStatus(array $data)
	{
		[$deviceToken, $videoId] = $data;
		$response = $this->withHeaders([
			'deviceToken' => $deviceToken,
		])->json('GET', '/api/status/' . $videoId);
		$response->assertStatus(200);
		$this->assertEquals(Video::STATUS_DONE, $response->getOriginalContent()['data']['status']);
	}
}
