<?php

namespace tests\Bones\Message;

use Bones\Component\Fixture\Mongo\Data\MongoDataStore;
use Bones\Message\Driver\Mongo\Driver;
use Bones\Message\Mailbox;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Mailbox
     */
    protected $mailbox;

    public function setUp()
    {
        $dbName = 'integration-test';
        $this->driver = new Driver($dbName);
        $this->mailbox = new Mailbox($this->driver);
        $mongoDataStore = new MongoDataStore(
            array('mongo_data_store' => array('db_name' => $dbName))
        );

        $mongoDataStore->emptyDataStore('messages');
    }

    public function testAddNewMessageAndCreateAConversation()
    {
        $person = new Person(1);
        $conversationList = $this->mailbox->getOutbox($person);

        $this->assertCount(0, $conversationList);

        $message = new Message(new Conversation(), $person, 'first message', 'of a conversation');
        $this->mailbox->sendMessage($message);

        $this->assertNotNull($message->getId());

        $conversationList = $this->mailbox->getOutbox($person);
        $this->assertCount(1, $conversationList);

        $firstConversation = array_shift(array_values($conversationList));
        $this->assertEquals(
            $firstConversation->getId(),
            $message->getConversationId()
        );
    }


    public function testSenderSendMessageAndRecipientReadIt()
    {

        $sender = new Person(1);
        $recipient = new Person(2);
        $message = new Message(new Conversation(), $sender, 'first message', 'of a conversation');
        $message->addRecipient($recipient);

        $this->mailbox->sendMessage($message);

        $recipientInboxConversation = $this->mailbox->getInbox($recipient);
        $this->assertCount(
            1,
            $recipientInboxConversation
        );

        /** @var Conversation $firstConversation */
        $firstConversation = array_shift(array_values($recipientInboxConversation));

        $messages = $firstConversation->getMessageList();
        $this->assertCount(
            1,
            $messages
        );

        $this->assertCount(
            2,
            $firstConversation->getPersonList()
        );

        /** @var Message $firstMessage */
        $firstMessage = array_shift(array_values($messages));

        $this->assertEquals($firstMessage->getSender(), $sender);
        $this->assertEquals($firstMessage->getTitle(), $message->getTitle());
        $this->assertCount(
            1,
            $message->getRecipients()
        );

        $firstRecipient = array_shift(array_values($message->getRecipients()));
        $this->assertEquals($firstRecipient, $recipient);

    }

    public function testSendReplyToMessage()
    {
        $neil = new Person(1);

        $geddy = new Person(2);

        $message = new Message(new Conversation(), $neil, 'first message', 'of a conversation');
        $this->overridePrivateProperty($message, 'date', new \DateTime('2016-01-01'));
        $message->addRecipient($geddy);
        $this->mailbox->sendMessage($message);

        $geddyInboxConversationList = $this->mailbox->getInbox($geddy);
        $this->assertCount(
            1,
            $geddyInboxConversationList
        );

        /** @var Conversation $conversation */
        $conversation = array_shift(array_values($geddyInboxConversationList));

        $message = $conversation->createReplyMessage($geddy, 'reply message', 'of a conversation');
        $this->overridePrivateProperty($message, 'date', new \DateTime('2016-01-02'));
        $message->addRecipient($neil);

        $this->mailbox->sendMessage($message);

        $neilInboxConversationList = $this->mailbox->getInbox($neil);

        $this->assertCount(
            1,
            $neilInboxConversationList
        );

        /** @var Conversation $conversation */
        $conversation = array_shift(array_values($neilInboxConversationList));

        $conversation = $this->mailbox->getConversation($conversation->getId());

        $messageList = $conversation->getMessageList();
        $this->assertCount(
            2,
            $messageList
        );

        $this->assertCount(
            2,
            $conversation->getPersonList()
        );

        $messageList = array_values($messageList);

        $this->assertEquals(
            $messageList[0]->getTitle(),
            'first message'
        );

        $this->assertEquals(
            $messageList[1]->getTitle(),
            'reply message'
        );
    }

    /**
     * @param $object
     * @param $propertyName
     * @param $propertyValue
     */
    private function overridePrivateProperty($object, $propertyName, $propertyValue)
    {
        $dateProperty = new \ReflectionProperty($object, $propertyName);
        $dateProperty->setAccessible(true);
        $dateProperty->setValue($object, $propertyValue);
        $dateProperty->setAccessible(false);
    }
}
