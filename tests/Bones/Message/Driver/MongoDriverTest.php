<?php


namespace tests\Bones\Message\Driver;


use Bones\Message\Driver\MongoDriver;

class MongoDriverTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var MongoDriver
     */
    protected $driver;

    public function setUp()
    {
        $dbName = getenv('TestDbName') ? getenv('TestDbName') : 'message-test';
        $this->driver = new MongoDriver($dbName);
    }

    public function testInstance()
    {
        $this->assertInstanceOf('\Bones\Message\Driver\MongoDriver', $this->driver);
    }

    public function testReturnConversationById()
    {
        $conversation = $this->driver->findConversationById(1);

        $this->assertInstanceOf('\Bones\Message\Conversation', $conversation);
    }



}
