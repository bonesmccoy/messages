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

        $this->assertInstanceOf('\Bones\Message\Model\Conversation', $conversation);
    }


    public function testReturnMessagesListByConversationId()
    {
        $conversation = $this->driver->findConversationById(1);

        $messageList = $this->driver->findMessagesByConversation($conversation);

        $this->assertCount(4, $messageList);

        foreach($messageList as $message) {
            $this->assertInstanceof('\Bones\Message\Model\Message', $message);
        }
    }

    public function testCountMessages()
    {
        $conversation = $this->driver->findConversationById(1);
        $this->assertEquals(
            4,
            $this->driver->countMessages($conversation)
        );

        $conversation = $this->driver->findConversationById(2);

        $this->assertEquals(
            2,
            $this->driver->countMessages($conversation)
        );
    }



}
