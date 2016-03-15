<?php

namespace spec\Bones\Message\Model;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Person;
use PhpSpec\ObjectBehavior;

class MessageSpec extends ObjectBehavior
{
    public function let(Person $person)
    {
        $person->beADoubleOf('Bones\Message\Model\Person');
        $this->beConstructedWith($person, 'title', 'body');
    }

    public function it_is_initializable()
    {
        $this->shouldHaveType('Bones\Message\Model\Message');
    }

    public function it_can_be_constructed_with_a_conversation_id(Person $person)
    {
        $this->beConstructedWith($person, 'title', 'body', 1);

        $this->getConversationId()->shouldReturn(1);
    }

    public function it_has_status_draft()
    {
        $this->getStatus()->shouldBeLike('draft');
    }

    public function it_should_add_recipient()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->getRecipients()->shouldHaveCount(1);
    }

    public function it_should_add_another_recipient()
    {
        $person = new Person(4);
        $this->addRecipient($person);

        $person = new Person(5);
        $this->addRecipient($person);

        $this->getRecipients()->shouldHaveCount(2);
    }

    public function it_shouldnt_add_a_recipient_twice()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->addRecipient($person);
        $this->getRecipients()->shouldHaveCount(1);
    }

    public function it_cannot_be_sent_is_recipient_list_is_empty()
    {
        $this->shouldThrow('\LogicException')->duringSend();
    }

    public function it_can_be_sent_if_it_has_recipients()
    {
        $person = new Person(3);
        $this->addRecipient($person);

        $this->send();
        $this->getSentDate()->shouldReturnAnInstanceOf('\DateTime');
    }

    public function it_can_be_marked_as_read()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
    }

    public function it_can_be_marked_as_read_only_from_existing_recipients()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $person = new Person(4);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);
    }

    public function it_can_have_read_date_for_a_given_user()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $this->getReadDateForUser($person);
    }

    public function it_can_be_set_unread_for_a_give_user()
    {
        $person = new Person(3);
        $this->addRecipient($person);
        $this->markAsReadForPerson($person);
        $this->getReaders()->shouldHaveCount(1);

        $this->markAsUnreadForPerson($person);
        $this->getReaders()->shouldHaveCount(0);
    }
}
