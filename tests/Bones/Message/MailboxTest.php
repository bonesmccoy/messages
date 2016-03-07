<?php


namespace tests\Bones\Message;


use Bones\Message\Driver\Mongo\Driver as MongoDriver;
use Bones\Message\Mailbox;
use Bones\Message\Model\Person;
use Bones\Message\Repository;

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

        $this->mailbox->getInbox($person);
    }



}
