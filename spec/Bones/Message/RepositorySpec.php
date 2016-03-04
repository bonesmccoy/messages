<?php

namespace spec\Bones\Message;

use Bones\Message\DriverInterface;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepositorySpec extends ObjectBehavior
{
    /**
     * @var DriverInterface
     */
    private $driver ;

    function let(DriverInterface $driver)
    {
        $driver->beADoubleOf('Bones\Message\DriverInterface');
        $this->driver = $driver;
        $this->beConstructedWith($driver);
        $this->shouldHaveType('Bones\Message\Repository');
        $this->shouldHaveType('Bones\Message\RepositoryInterface');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bones\Message\Repository');
        $this->shouldHaveType('Bones\Message\RepositoryInterface');
    }

    function it_can_retrieve_a_conversation()
    {
        $id = 1;
        $this->driver->findConversationById($id)->willReturn(new Conversation());

        $this->getConversation($id)->shouldReturnAnInstanceOf('Bones\Message\Model\Conversation');
    }

    function it_can_retrieve_messages_for_a_given_conversation()
    {
        $conversation = new Conversation();
        $messageCollection = array(
            new Message($conversation, new Person(1), 'body'),
            new Message($conversation, new Person(2), 'body'),
            new Message($conversation, new Person(3), 'body'),
            new Message($conversation, new Person(4), 'body')
        );
        $this
            ->driver
            ->findMessagesByConversation($conversation, 0, 20, "ASC")
            ->willReturn($messageCollection);

        $this
            ->getConversationMessageList($conversation)
            ->shouldReturnMessageCollection($messageCollection);
    }

    function it_can_count_messages_for_a_given_conversation()
    {
        $conversation = new Conversation();
        $this
            ->driver
            ->countMessages($conversation)
            ->willReturn(4);

        $this
            ->countMessages($conversation)
            ->shouldReturn(4);
    }

    function it_can_count_people_for_a_given_conversation()
    {
        $conversation = new Conversation();
        $this
            ->driver
            ->countPeople($conversation)
            ->willReturn(4);

        $this
            ->countPeople($conversation)
            ->shouldReturn(4);
    }


    function it_can_get_people_for_a_given_conversation()
    {

    }


    public function getMatchers()
    {
        return array(
            'returnMessageCollection' => function($subject, $value) {
                foreach($value as $element) {
                    if (!($element instanceof Message)) {
                        throw new FailureException(
                            sprintf("%s expected, %s found",
                                'Bones\Message\Model\Message',
                                get_class($element)
                            )
                        );
                    }
                }
                return true;
            },
            'returnPersonCollection' => function($subject, $value) {
                foreach($value as $element) {
                    if (!($element instanceof Person)) {
                        throw new FailureException(
                            sprintf("%s expected, %s found",
                                'Bones\Message\Model\Person',
                                get_class($element)
                            )
                        );
                    }
                }
                return true;
            }
        );
    }
}
