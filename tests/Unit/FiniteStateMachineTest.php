<?php

namespace Tests\Unit;

use App\Models\Video;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Acacha\Stateful\Exceptions\IllegalStateTransitionException;

class FiniteStateMachineTest extends TestCase
{
    /**
     * Test that state machine works over our mongodb model.
     *
     * @return void
     */
    public function testModelStateMachine()
    {
    	$video = new Video();
    	$this->assertInstanceOf('Illuminate\Database\Eloquent\Model', $video);
	    $this->assertEquals(Video::STATUS_SCHEDULED, $video->getInitialState());
        $this->assertTrue($video->scheduled());
        $this->assertFalse($video->processing());
    }

    public function testInvalidStateTransaction() {
	    $video = new Video();
	    $this->expectException(IllegalStateTransitionException::class);
	    $this->assertFalse($video->done());
    }

    public function testResetState() {
	    $video = new Video();
	    $video->fileName = 'test';
	    $video->save();
	    $this->assertNotNull($video->getKey());
	    $this->assertTrue($video->scheduled());
	    $video->process();
	    $this->assertTrue($video->processing(), $video->status);
	    $video->fail();
	    $this->assertTrue($video->failed());
	    $this->assertTrue($video->restart());
	    $this->assertTrue($video->scheduled());
    }
}
