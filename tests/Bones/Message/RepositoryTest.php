<?php


namespace tests\Bones\Message;


use Bones\Message\Driver\MongoDriver;
use Bones\Message\Model\Person;
use Bones\Message\Repository;

class RepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Repository
     */
    private $repository;

    public function setUp()
    {
        $dbName = getenv('TestDbName') ? getenv('TestDbName') : 'message-test';
        $driver = new MongoDriver($dbName);
        $this->repository = new Repository($driver);
    }

    public function testFindConversationById()
    {
        $conversation = $this->repository->getConversation(1);

        $this->assertInstanceOf(
            'Bones\Message\Model\Conversation',
            $conversation
        );
    }

    public function testCountMessagesForAGivenConversation()
    {
        $conversation = $this->repository->getConversation(1);

        $this->assertEquals(
            4,
            $this->repository->countMessages($conversation)
        );
    }

    public function testCountPeopleForAGivenConversation()
    {
        $conversation = $this->repository->getConversation(1);

        $this->assertEquals(
            4,
            $this->repository->countPeople($conversation)
        );
    }

    public function testGetPeopleForAGiveConversation()
    {
        $conversation = $this->repository->getConversation(1);
        $people  = $this->repository->getPeople($conversation);
        $this->assertCount(
            4,
            $people
        );

        foreach($people as $person) {
            $this->assertInstanceof(
                'Bones\Message\Model\Person',
                $person
            );
        }
    }

    public function testGetAllConversationForAGivenPerson()
    {
        $conversationList = $this->repository->getConversationListForPerson(new Person(1));

        $this->assertCount(
            2,
            $conversationList
        );
    }
}
