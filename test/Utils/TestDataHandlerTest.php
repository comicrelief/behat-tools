<?php

namespace Comicrelief\Behat\Unit\Utils;

use Comicrelief\Behat\Utils\TestDataHandler;
use PHPUnit\Framework\TestCase;

class TestDataHandlerTest extends TestCase
{
    /* @var TestDataHandler */
    private $testDataHandler;

    public function setUp()
    {
        $this->testDataHandler = new TestDataHandler();
    }

    public function testCanInitialise()
    {
        $this->assertInstanceOf(TestDataHandler::class, $this->testDataHandler);
    }

    public function testCanAddNewTestData()
    {
        $this->testDataHandler->addTestData('foo', 'bar');
        $this->assertTrue($this->testDataHandler->hasTestData('foo'));
        $this->assertEquals('bar', $this->testDataHandler->getTestData('foo'));
    }

    public function testTestDataSet()
    {
        $data = [
            'foofoo' => 'barbar',
            'foofoofoo' => 'barbarbar',
        ];

        $this->testDataHandler::setTestData($data);

        $this->assertTrue($this->testDataHandler->hasTestData('foofoo'));
        $this->assertEquals('barbar', $this->testDataHandler->getTestData('foofoo'));

        $this->assertTrue($this->testDataHandler->hasTestData('foofoofoo'));
        $this->assertEquals('barbarbar', $this->testDataHandler->getTestData('foofoofoo'));
    }

    public function testSetDataDeletesOldData()
    {
        $data = [
            'foofoo' => 'barbar',
        ];

        $this->testDataHandler->addTestData('foo', 'bar');
        $this->assertTrue($this->testDataHandler->hasTestData('foo'));

        $this->testDataHandler::setTestData($data);
        $this->assertTrue($this->testDataHandler->hasTestData('foofoo'));

        $this->assertFalse($this->testDataHandler->hasTestData('foo'));
    }
}
