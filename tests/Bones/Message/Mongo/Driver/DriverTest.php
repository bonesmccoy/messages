<?php

namespace tests\Bones\Message\Mongo\Driver;

use Bones\Message\Driver\Mongo\Driver as MongoDriver;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;

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

        foreach ($messageList as $messageDocument) {
            $this->assertArrayHasKey('_id', $messageDocument);
            $this->assertArrayHasKey('sender', $messageDocument);
            $this->assertArrayHasKey('recipient', $messageDocument);
            $this->assertArrayHasKey('title', $messageDocument);
            $this->assertArrayHasKey('body', $messageDocument);
        }
    }

    public function testFindAllMessages()
    {
        $messages = $this->driver->findAllMessages();
        $this->assertGreaterThan(0, $messages);

        foreach ($messages as $messageDocument) {
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
        $conversations = $this->driver->findAllConversationIdForPersonId(1, 0, 1);
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
            $firstConversation['_id']
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

    public function testAddAndRemoveMessage()
    {
        $message = new Message(new Conversation(), new Person(1), 'Title Message', 'Body Message');

        $messageDocument = array(
            'sender' => $message->getSender()->getId(),
            'date' => (array) $message->getDate(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'conversation' => $message->getConversationId(),
        );

        $this->driver->persistMessage($messageDocument);
        $this->arrayHasKey($messageDocument['_id']);

        $newMessageId = (string) $messageDocument['_id'];
        $this->assertNotEmpty($newMessageId);

        $persistedMessage = $this->driver->getMessageById($newMessageId);
        $this->assertEquals($messageDocument, $persistedMessage);

        $this->driver->removeMessageWithId($messageDocument['_id']);

        $removedMessage = $this->driver->getMessageById($messageDocument['_id']);
        $this->assertNull($removedMessage);
    }
}
