<?php

namespace spec\Bones\Message\Model;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use PhpSpec\Exception\Example\FailureException;
use PhpSpec\ObjectBehavior;

class ConversationSpec extends ObjectBehavior
{
    public function let(Message $message)
    {
        $message->beADoubleOf('Bones\Message\Model\Message');
        $message->getId()->willReturn(1);
        $message->getConversationId()->willReturn(1);
        $message->getTitle()->willReturn("Message Title");

        $this->beConstructedWith(array($message));
    }

    public function it_is_initializable()
    {
        $this->beConstructedWith(array());
        $this->shouldHaveType('Bones\Message\Model\Conversation');
    }

    public function it_has_a_title()
    {
        $this->getTitle()->shouldBeLike("Message Title");
    }

    public function it_has_a_first_message()
    {
        $this->getFirstMessage()->shouldReturnAnInstanceOf('Bones\Message\Model\Message');
        $message = $this->getFirstMessage()->getWrappedObject();
        if ($this->getWrappedObject()->getId() != $message->getId()) {
            throw new FailureException(
                sprintf('Conversation id %s should match fist message id %s',
                    $this->getWrappedObject()->getId(),
                    $message->getId()
                )
            );
        }

        if ($message->getId() != 1) {
            throw new FailureException('first message id doesn\'t match');
        }
    }


    public function it_can_add_a_message()
    {
        $sender = new Person(1);
        $message = new Message($sender, 'title', 'body');

        $this->addMessage($message);
        $this->getMessageList()->shouldHaveCount(1);
    }

    public function it_should_add_users_from_the_inserted_message()
    {
        $sender = new Person(1);
        $message = new Message($sender, 'title', 'body');

        $firstRecipient = new Person(2);
        $message->addRecipient($firstRecipient);

        $this->addMessage($message);
        $this->getPersonList()->shouldHaveCount(2);
    }

    public function it_should_have_unread_messages_for_a_given_person()
    {
        $sender = new Person(1);
        $recipient = new Person(2);

        $message = new Message($sender, 'title 1', 'body');
        $message->addRecipient($recipient);
        $this->addMessage($message);

        $message = new Message($sender, 'title 2', 'body');
        $message->markAsReadForPerson($sender);

        $this->modifyMessageDate($message, new \DateTime('2016-01-01'));

        $message->addRecipient($recipient);
        $this->addMessage($message);

        $this->getMessageList()->shouldHaveCount(2);

        $this->hasUnreadMessagesForPerson($sender)->shouldReturn(true);
    }

    public function it_should_have_messages_order_by_date_desc()
    {
        $sender = new Person(1);
        $recipient = new Person(2);

        $message = new Message($sender, 'title 1', 'body');
        $message->addRecipient($recipient);
        $message->send();
        $this->addMessage($message);

        $message = new Message($sender, 'title 2', 'body');
        $message->addRecipient($recipient);
        $message->send();
        $this->addMessage($message);

        $messageList = $this->getMessageList()->getWrappedObject();
        $fistMessage = array_shift(array_values($messageList));

        if ('title 2' !== $fistMessage->getTitle()) {
            throw new FailureException(sprintf('failed to assert that expected %s !== %s', 'title 2', $fistMessage->getTitle()));
        }
    }

    private function modifyMessageDate($message, \DateTime $date)
    {
        $reflectionClass = new \ReflectionClass($message);
        $property = $reflectionClass->getProperty('sentDate');
        $property->setAccessible(true);
        $property->setValue($message, $date);
        $property->setAccessible(false);
    }
}
