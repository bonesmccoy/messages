<?php


namespace tests\Bones\Message;


use Bones\Message\Mailbox;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Person;
use Bones\Message\Driver\Mongo\Driver as MongoDriver;


class MailboxTestAbstract extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MongoDriver
     */
    protected $driver;


    /**
     * @var Mailbox
     */
    private $mailbox;

    public function setUp()
    {
        parent::setUp();
        $dbName = getenv('TestDbName') ? getenv('TestDbName') : 'message-test';
        $this->driver = new MongoDriver($dbName);
        $this->mailbox = new Mailbox($this->driver);
    }

    public function testGetInbox()
    {
        $person = new Person(1);

        $inbox = $this->mailbox->getInbox($person);

        $this->assertCount(
            2,
            $inbox
        );

        foreach($inbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }
    }

    public function testGetOutbox()
    {
        $person = new Person(1);

        $outbox = $this->mailbox->getOutbox($person);

        $this->assertCount(
            1,
            $outbox
        );

        foreach($outbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }

        $person = new Person(2);

        $outbox = $this->mailbox->getOutbox($person);

        $this->assertCount(
            2,
            $outbox
        );

        foreach($outbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }
    }

    public function testDeletedMessage()
    {
        $messageDocumentList = $this->driver->findMessagesByConversationId(4);
        $conversation =  $this->mailbox->createConversationModel(
            array("_id" => 4),
            $messageDocumentList
        );

        foreach($conversation->getMessageList() as $message) {
            if ($message->getId() == 8) {
                $this->assertCount(
                    3,
                    $message->getDeleted()
                );

                $message->markDeleteForPerson(new Person(10));

                $this->assertCount(
                    3,
                    $message->getDeleted()
                );

                $message->markDeleteForPerson(new Person(12));

                $this->assertCount(
                    4,
                    $message->getDeleted()
                );

            }
        }
    }


}
