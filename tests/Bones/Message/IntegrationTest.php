<?php

namespace tests\Bones\Message;

use Bones\Component\Fixture\FixtureLoader;
use Bones\Component\Fixture\Mongo\Data\MongoDataStore;
use Bones\Component\Fixture\Mongo\FixtureParser;
use Bones\Message\Driver\Mongo\Driver;
use Bones\Message\Mailbox;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use Bones\Message\Service\MessageTransformer;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    protected $mongoDataStore;
    /**
     * @var Driver
     */
    protected $driver;

    /**
     * @var Mailbox
     */
    protected $mailbox;

    /**
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    public function setUp()
    {
        $dbName = 'integration-test';
        $this->driver = new Driver($dbName);
        $messageTransformer = new MessageTransformer();
        $this->mailbox = new Mailbox($this->driver, $messageTransformer);

        $this->mongoDataStore = new MongoDataStore(
            array('mongo_data_store' => array('db_name' => $dbName))
        );

        $this->fixtureLoader = new FixtureLoader(
            $this->mongoDataStore,
            new FixtureParser()
        );

        $this->mongoDataStore->emptyDataStore('messages');
    }

    public function testAddNewMessageAndCreateAConversation()
    {
        $sender = new Person(1);
        $conversationList = $this->mailbox->getOutbox($sender);
        $this->assertCount(0, $conversationList);

        $message = $this->sendMessageToConversationFromSender($sender,'first message', 'of a conversation', array(new Person(20)));

        $this->assertNotNull($message->getId());
        $this->assertNotNull($message->getConversationId());
        $this->assertEquals($message->getId(), $message->getConversationId());

        $conversationList = $this->mailbox->getOutbox($sender);
        $this->assertCount(1, $conversationList);

        $firstConversation = current($conversationList);
        $this->assertEquals(
            $firstConversation->getId(),
            $message->getConversationId()
        );
    }


    public function testSenderSendMessageAndRecipientReadIt()
    {

        $sender = new Person(1);
        $recipient = new Person(2);
        $message = $this->sendMessageToConversationFromSender($sender,'first message', 'of a conversation', array($recipient));

        $recipientInboxConversation = $this->mailbox->getInbox($recipient);
        $this->assertCount(
            1,
            $recipientInboxConversation
        );

        /** @var Conversation $firstConversation */
        $firstConversation = current($recipientInboxConversation);

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
        $firstMessage = current($messages);

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

        $message = array(
            "_id" => '56eb45003639330941000013',
            "conversationId" => 'ref:56eb45003639330941000013',
            "senderId" => $neil->getId(),
            "recipientList" => array(
                array("personId" => $geddy->getId())
            ),
            "title" => 'Hello Geddy',
            "body" => 'Are you up for the rehearsal today?',
            "sentDate" => '2015-01-01',
            "createdAt" => '2015-01-01'
        );

        $this->fixtureLoader->addFixturesWithCollection(
            array("messages" => array($message) )
        );

        $this->fixtureLoader->persistLoadedFixtures();

        $geddyInboxConversationList = $this->mailbox->getInbox($geddy);

        $this->assertCount(
            1,
            $geddyInboxConversationList
        );

        /** @var Conversation $conversation */
        $conversation = current($geddyInboxConversationList);

        $message = $conversation->createReplyMessage($geddy, 'reply message', 'of a conversation');
        $message->addRecipient($neil);
        sleep(1);
        $this->mailbox->sendMessage($message);

        $neilInboxConversationList = $this->mailbox->getInbox($neil);

        $this->assertCount(
            1,
            $neilInboxConversationList
        );

        /** @var Conversation $conversation */
        $conversation = current($neilInboxConversationList);

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
            'Hello Geddy'
        );

        $this->assertEquals(
            $messageList[1]->getTitle(),
            'reply message'
        );
    }

    /**
     * @param $sender
     * @param $title
     * @param $body
     * @param array $recipientList
     * @param null $messageDate
     * @return Message
     */
    private function sendMessageToConversationFromSender($sender, $title, $body, $recipientList = array(), $messageDate = null)
    {
        $message = new Message($sender, $title, $body);
        foreach($recipientList as $recipient) {
            $message->addRecipient($recipient);
        }

        $this->mailbox->sendMessage($message);

        return $message;
    }
}
