<?php


namespace tests\Bones\Message;


use Bones\Message\Driver\Mongo\Driver as MongoDriver;
use Bones\Message\Mailbox;
use Bones\Message\Model\Person;


class MailboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Mailbox
     */
    private $mailbox;

    public function setUp()
    {
        $dbName = getenv('TestDbName') ? getenv('TestDbName') : 'message-test';
        $driver = new MongoDriver($dbName);
        $this->mailbox = new Mailbox($driver);
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



}
