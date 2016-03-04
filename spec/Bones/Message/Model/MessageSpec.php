<?php

namespace spec\Bones\Message\Model;

use Bones\Message\Model\ConversationInterface;
use Bones\Message\Model\Person;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class MessageSpec extends ObjectBehavior
{
    function let(Person $person, ConversationInterface $conversation)
    {
        $person->beADoubleOf('Bones\Message\Model\Person');
        $conversation->beADoubleOf('Bones\Message\Model\Conversation');
        $this->beConstructedWith($conversation, $person, 'body');

    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Bones\Message\Model\Message');
    }

    function it_has_a_date()
    {
        $this->getDate()->shouldReturnAnInstanceOf('\DateTime');
    }

    function it_should_add_recipient()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->getRecipients()->shouldHaveCount(1);
    }

    function it_should_add_another_recipient()
    {
        $person = new Person(4);
        $this->addRecipient($person);

        $person = new Person(5);
        $this->addRecipient($person);

        $this->getRecipients()->shouldHaveCount(2);
    }

    function it_shouldnt_add_a_recipient_twice()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->addRecipient($person);
        $this->getRecipients()->shouldHaveCount(1);
    }

    function it_can_be_marked_as_read()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
    }

    function it_can_be_marked_as_read_only_from_existing_recipients()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $person = new Person(4);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);
    }

    function it_can_have_read_date_for_a_given_user()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $this->getReadDateForUser($person);
    }

    function it_can_be_set_unread_for_a_give_user()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $this->markAsUnreadForPerson($person);
        $this->getReaders()->shouldHaveCount(0);
    }



}
