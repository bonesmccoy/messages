<?php

namespace tests\Bones\Message\Mongo\Driver;

use Bones\Component\Mongo\Utilities;
use Bones\Message\Driver\Mongo\Driver as MongoDriver;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;

/**
 * Class DriverTest
 */
class DriverTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoDriver
     */
    protected $driver;

    private $timestamp;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $dbName = getenv('test_db_name') ? getenv('test_db_name') : 'messages-test';
        $dbHost = getenv('test_db_host') ? getenv('test_db_host') : 'localhost';

        $this->driver = new MongoDriver($dbName, $dbHost);
        $this->timestamp = strtotime('today');
    }

    /**
     * Test instance
     */
    public function testInstance()
    {
        $this->assertInstanceOf('\Bones\Message\Driver\Mongo\Driver', $this->driver);
    }

    /**
     * Test Message List
     */
    public function testReturnMessagesListByConversationId()
    {
        $messageList = $this->driver->findMessagesByConversationId(new \MongoId('56eb45003639330941000001'));

        $this->assertCount(4, $messageList);

        foreach ($messageList as $messageDocument) {
            $this->assertArrayHasKey('_id', $messageDocument);
            $this->assertArrayHasKey('senderId', $messageDocument);
            $this->assertArrayHasKey('recipientList', $messageDocument);
            $this->assertArrayHasKey('title', $messageDocument);
            $this->assertArrayHasKey('body', $messageDocument);
        }
    }

    /**
     * Test all Messages
     */
    public function testFindAllMessages()
    {
        $messages = $this->driver->findAllMessages();
        $this->assertGreaterThan(0, $messages->count());

        foreach ($messages as $messageDocument) {
            $this->assertArrayHasKey('_id', $messageDocument);
            $this->assertArrayHasKey('senderId', $messageDocument);
            $this->assertArrayHasKey('recipientList', $messageDocument);
            $this->assertArrayHasKey('title', $messageDocument);
            $this->assertArrayHasKey('body', $messageDocument);
        }
    }

    /**
     * Test Limit messages
     */
    public function testLimitMessageQuery()
    {
        $messages = $this->driver->findMessagesByConversationId('56eb45003639330941000001', 0, 2);
        $this->assertCount(
            2,
            $messages
        );
    }

    /**
     * test Find all conversation for a given person
     */
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

    /**
     * test find all conversation for a person with limit
     */
    public function testFindConversationForAGivePersonLimited()
    {
        $conversations = $this->driver->findAllConversationIdForPersonId(20, 0, 2);
        $this->assertCount(
            2,
            $conversations
        );

        foreach ($conversations as $conversation) {
            $this->assertTrue(
                in_array($conversation['_id'], array('56eb45003639330941000010','56eb45003639330941000011')),
                "{$conversation['_id']} not found in [10,11]"
            );
        }


        $conversations = $this->driver->findAllConversationIdForPersonId(20, 2, 2);
        $this->assertCount(
            2,
            $conversations
        );

        foreach($conversations as $conversation) {
            $this->assertTrue(
                in_array($conversation['_id'], array('56eb45003639330941000012','56eb45003639330941000013')),
                "{$conversation['_id']} not found in [12,13]"
            );
        }
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
            new \MongoId('56eb45003639330941000001'),
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
        $message = new Message(new Person(1), 'Title Message', 'Body Message');

        $messageDocument = array(
            'sender' => $message->getSender()->getId(),
            'date' => (array) $message->getSentDate(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'conversation' => $message->getConversationId(),
        );

        $this->driver->persistMessage($messageDocument);
        $this->arrayHasKey($messageDocument['_id']);

        $newMessageId = (string) $messageDocument['_id'];
        $this->assertNotEmpty($newMessageId);

        $persistedMessage = $this->driver->getMessageById(new \MongoId($newMessageId));
        $this->assertEquals($messageDocument, $persistedMessage);

        $this->driver->removeMessageWithId($messageDocument['_id']);

        $removedMessage = $this->driver->getMessageById($messageDocument['_id']);
        $this->assertNull($removedMessage);
    }
}
