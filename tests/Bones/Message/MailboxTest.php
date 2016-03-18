<?php

namespace tests\Bones\Message;

use Bones\Component\Fixture\FixtureLoader;
use Bones\Message\Mailbox;
use Bones\Message\Model\Person;
use Bones\Message\Service\MessageTransformer;

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

        $messageTransformer = new MessageTransformer();
        $this->buildMailbox($this->driver,$messageTransformer);
    }

    public function testGetInbox()
    {
        $conversationIdList = array(
            array('_id' => '56eb45003639330941000001'),
            array('_id' => '56eb45003639330941000005'),
        );

        $this->mockDriverGetAllConversationAsRecipient($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();

        foreach ($loadedFixtures['messages']  as $key => $message) {
            if (!in_array($message['conversationId'], array('56eb45003639330941000001', '56eb45003639330941000005'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $this->driver
            ->method('findAllMessagesByConversationIdList')
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
            array('_id' => '56eb45003639330941000001'),
        );

        $this->mockDriverGetAllConversationAsSender($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();

        foreach ($loadedFixtures['messages'] as $key => $message) {
            if (!in_array($message['conversationId'], array('56eb45003639330941000001'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }
        $this->mockFindAllMessageByConversationId($loadedFixtures);

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
            array('_id' => '56eb45003639330941000001'),
            array('_id' => '56eb45003639330941000005'),
        );

        $this->mockDriverGetAllConversationAsSender($conversationIdList);

        $loadedFixtures = $this->loadMessagesFixtures();
        foreach ($loadedFixtures['messages']  as $key => $message) {
            if (!in_array($message['conversationId'], array('56eb45003639330941000001', '56eb45003639330941000005'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $this->mockFindAllMessageByConversationId($loadedFixtures);

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
            if (!in_array($message['_id'], array('56eb45003639330941000008'))) {
                unset($loadedFixtures['messages'][$key]);
            }
        }

        $conversation = $this->mailbox->createConversationModel(
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

    private function buildMailbox($driver, $messageTransformer)
    {
        $this->mailbox = new Mailbox($driver, $messageTransformer);
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
    private function mockFindAllMessageByConversationId($loadedFixtures)
    {
        $this->driver
            ->method('findAllMessagesByConversationIdList')
            ->willReturn(array_values($loadedFixtures['messages']));
    }

    /**
     * @param $conversationIdList
     */
    private function mockDriverGetAllConversationAsRecipient($conversationIdList)
    {
        $this->driver
            ->method('findAllConversationIdForPersonIdAsRecipient')
            ->willReturn($conversationIdList);
    }

    /**
     * @param $conversationIdList
     */
    private function mockDriverGetAllConversationAsSender($conversationIdList)
    {
        $this->driver
            ->method('findAllConversationIdForPersonIdAsSender')
            ->willReturn($conversationIdList);
    }
}
