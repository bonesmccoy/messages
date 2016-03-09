<?php

namespace tests\Bones\Message;

use Bones\Component\Fixture\FixtureLoader;
use Bones\Message\Mailbox;
use Bones\Message\Model\Person;

class MailboxTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var FixtureLoader
     */
    protected $fixtureLoader;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $driver;

    /**
     * @var Mailbox
     */
    private $mailbox;

    public function setUp()
    {
        parent::setUp();
        $this->fixtureLoader = FixtureLoader::factoryInMemoryFixtureLoader();
        $this->driver = $this
            ->getMockBuilder('Bones\Message\Driver\Mongo\Driver')
            ->disableOriginalConstructor()
            ->getMock();

        $this->buildMailbox($this->driver);
    }

    public function testGetInbox()
    {
        $conversationIdList = array(
            array('_id' => '1'),
            array('_id' => '2'),
        );

        $this->mockAllConversation($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();
        foreach ($loadedFixtures['messages']  as $key => $message) {
            if (!in_array($message['_id'], array('2', '3', '6'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $this->driver
            ->method('findAllReceivedMessages')
            ->willReturn(array_values($loadedFixtures['messages']));

        $person = new Person(1);

        $inbox = $this->mailbox->getInbox($person);

        $this->assertCount(
            2,
            $inbox
        );

        foreach ($inbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }
    }

    public function testGetOutbox()
    {
        $conversationIdList = array(
            array('_id' => '1'),
        );

        $this->mockAllConversation($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();

        foreach ($loadedFixtures['messages'] as $key => $message) {
            if (!in_array($message['_id'], array('1'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }
        $this->mockAllSent($loadedFixtures);

        $person = new Person(1);
        $outbox = $this->mailbox->getOutbox($person);

        $this->assertCount(
            1,
            $outbox
        );

        foreach ($outbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }
    }

    public function testOutBoxForPerson2()
    {
        $conversationIdList = array(
            array('_id' => '1'),
            array('_id' => '6'),
        );

        $this->mockAllConversation($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();
        foreach ($loadedFixtures['messages']  as $key => $message) {
            if (!in_array($message['_id'], array('2', '6'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $this->mockAllSent($loadedFixtures);

        $person = new Person(2);
        $outbox = $this->mailbox->getOutbox($person);
        $this->assertCount(
            2,
            $outbox
        );

        foreach ($outbox as $conversation) {
            $this->assertInstanceOf('Bones\Message\Model\Conversation', $conversation);
        }
    }

    public function testDeletedMessage()
    {
        $loadedFixtures = $this->loadMessagesFixtures();
        foreach ($loadedFixtures['messages']  as $key => $message) {
            if (!in_array($message['_id'], array('8'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $conversation = $this->mailbox->createConversationModel(
            array('_id' => 4),
            $loadedFixtures['messages']
        );

        foreach ($conversation->getMessageList() as $message) {
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

    private function buildMailbox($driver)
    {
        $this->mailbox = new Mailbox($driver);
    }

    /**
     * @return array
     */
    private function loadMessagesFixtures()
    {
        $this->fixtureLoader->addFixturesFromFile(
            __DIR__.'/Fixtures/messages.yml'
        );
        $loadedFixtures = $this->fixtureLoader->getLoadedFixtures();

        return $loadedFixtures;
    }

    /**
     * @param $loadedFixtures
     */
    private function mockAllSent($loadedFixtures)
    {
        $this->driver
            ->method('findAllSentMessage')
            ->willReturn(array_values($loadedFixtures['messages']));
    }

    /**
     * @param $conversationIdList
     */
    private function mockAllConversation($conversationIdList)
    {
        $this->driver
            ->method('findAllConversationIdForPersonId')
            ->willReturn($conversationIdList);
    }
}
