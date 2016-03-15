<?php

namespace tests\Bones\Message;

use Bones\Component\Fixture\Mongo\Data\MongoDataStore;
use Bones\Message\Driver\Mongo\Driver;
use Bones\Message\Mailbox;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use Bones\Message\Service\ConversationTransformer;
use Bones\Message\Service\MessageTransformer;

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
        $messageTransformer = new MessageTransformer();
        $conversationTransformer = new ConversationTransformer();
        $this->mailbox = new Mailbox($this->driver, $conversationTransformer, $messageTransformer);
        $mongoDataStore = new MongoDataStore(
            array('mongo_data_store' => array('db_name' => $dbName))
        );

        $mongoDataStore->emptyDataStore('messages');
    }

    public function testAddNewMessageAndCreateAConversation()
    {
        $sender = new Person(1);
        $conversationList = $this->mailbox->getOutbox($sender);
        $this->assertCount(0, $conversationList);

        $message = $this->sendMessageToConversationFromSender($sender,'first message', 'of a conversation');

        $this->assertNotNull($message->getId());

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

        $this->sendMessageToConversationFromSender($neil, 'first message', 'of a conversation', array($geddy), new \DateTime('2016-01-01'));

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
        $messageDate = ($messageDate) ? $messageDate :  new \DateTime();
        $message = new Message($sender, $title, $body);
        $this->overridePrivateProperty($message, 'date', $messageDate);
        foreach($recipientList as $recipient) {
            $message->addRecipient($recipient);
        }

        $this->mailbox->sendMessage($message);

        return $message;
    }
}
