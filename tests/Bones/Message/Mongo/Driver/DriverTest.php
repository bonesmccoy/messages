<?php


namespace tests\Bones\Message\Driver;


use Bones\Message\Driver\Mongo\Driver as MongoDriver;

class DriverTest extends \PHPUnit_Framework_TestCase
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
        $this->assertInstanceOf('\Bones\Message\Driver\Mongo\Driver', $this->driver);
    }

    public function testReturnMessagesListByConversationId()
    {
        $messageList = $this->driver->findMessagesByConversationId(1);

        $this->assertCount(4, $messageList);

        foreach($messageList as $messageDocument) {
            $this->assertArrayHasKey('_id', $messageDocument);
            $this->assertArrayHasKey('sender', $messageDocument);
            $this->assertArrayHasKey('recipient', $messageDocument);
            $this->assertArrayHasKey('title', $messageDocument);
            $this->assertArrayHasKey('body', $messageDocument);
        }
    }

    public function testCountMessages()
    {
        $this->assertEquals(
            4,
            $this->driver->countMessages(1)
        );

        $this->assertEquals(
            2,
            $this->driver->countMessages(2)
        );
    }

    public function testCountPeople()
    {
        $this->assertEquals(
            4,
            $this->driver->countPeople(1)
        );

        $this->assertEquals(
            3,
            $this->driver->countPeople(2)
        );
    }

    public function testFindAllMessages()
    {
        $messages = $this->driver->findAllMessages();
        $this->assertCount(
            8,
            $messages
        );
        foreach($messages as $messageDocument) {
            $this->assertArrayHasKey('_id', $messageDocument);
            $this->assertArrayHasKey('sender', $messageDocument);
            $this->assertArrayHasKey('recipient', $messageDocument);
            $this->assertArrayHasKey('title', $messageDocument);
            $this->assertArrayHasKey('body', $messageDocument);
        }
    }

    public function testLimitMessageQuery()
    {
        $messages = $this->driver->findMessagesByConversationId(1, 0, 2);
        $this->assertCount(
            2,
            $messages
        );
    }

    public function testFindAllConversationsForAGivenPerson()
    {
        $conversations = $this->driver->findAllConversationIdForPersonId(10);
        $this->assertCount(
            1,
            $conversations
        );

        $conversations = $this->driver->findAllConversationIdForPersonId(15);
        $this->assertCount(
            1,
            $conversations
        );
    }

    public function testFindConversationForAGivePersonLimited()
    {
        $conversations = $this->driver->findAllConversationIdForPersonId(1, 0 ,1);
        $this->assertCount(
            1,
            $conversations
        );

    }

    public function testFindAllConversationForPersonOrderedByDateDesc()
    {
        $conversations = $this->driver->findAllConversationIdForPersonId(1);

        $this->assertCount(
            2,
            $conversations
        );

        $firstConversation = current($conversations);
        $this->assertEquals(
            2,
            $firstConversation["_id"]
        );

    }


    public function testFindAllSentMessages()
    {
        $messages = $this->driver->findAllSentMessage(10);
        $this->assertEquals(
            1,
            $messages->count()
        );

        $messages = $this->driver->findAllSentMessage(2);
        $this->assertEquals(
            2,
            $messages->count()
        );


    }

    public function testFindAllReceivedMessages()
    {
        $messages = $this
                    ->driver
                    ->findAllReceivedMessages(1);
        $this->assertCount(
            4,
            $messages
        );
    }
}
