<?php

namespace Bones\Message\Model;

class Conversation implements ModelInterface
{
    /**
     * @var Message[]
     */
    protected $messageList = array();

    /**
     * @var Person[]
     */
    protected $personList = array();

    /** @var  Message */
    protected $firstMessage;

    /** @var  string */
    protected $title;

    /**
     * @param  Message[] $messageList
     */
    public function __construct($messageList)
    {
        if (empty($messageList)) {
            throw new \LogicException("Conversation must be constructed with at least one message");
        }

        foreach($messageList as $message) {
            if ($message->getId() == $message->getConversationId()) {
                $this->title = $message->getTitle();
                $this->firstMessage = $message;
            }
            $this->addMessage($message);
        }

        if (!($this->firstMessage instanceof Message)) {
            throw new \InvalidArgumentException('Message List doesn\'t contain the first message of a conversation');
        }
    }

    /**
     * Returns the Id of the Conversation.
     *
     * @return string
     */
    public function getId()
    {
        return $this->firstMessage->getId();
    }

    /**
     * Returns all the person involved in this conversation.
     *
     * @return Person[]
     */
    public function getPersonList()
    {
        return $this->personList;
    }

    /**
     * @return Message
     */
    public function getFirstMessage()
    {
        return $this->firstMessage;
    }


    /**
     * Returns all the messages in this conversation.
     *
     * @return Message[]
     */
    public function getMessageList()
    {
        ksort($this->messageList);

        return $this->messageList;
    }

    /**
     * Adds a message in this conversation.
     *
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messageList[$message->getSentDate()->format('Ymdhis')] = $message;
        $this->addPersonsFromMessage($message);
    }

    /***
     * @param Person $sender
     * @param $title
     * @param $body
     * @return Message
     */
    public function createReplyMessage(Person $sender, $title, $body)
    {
        $replyMessage = new Message($sender, $title, $body, $this->getId());

        return $replyMessage;
    }

    /**
     * Adds all the person object involved in the message
     * to the conversation.
     *
     * @param Message $message
     */
    private function addPersonsFromMessage(Message $message)
    {
        $sender = $message->getSender();
        $this->addPersonIfNotExists($sender);
        foreach ($message->getRecipients() as $recipient) {
            $this->addPersonIfNotExists($recipient);
        }
    }

    /**
     * Add person to list if is not already present.
     *
     * @param Person $person
     */
    private function addPersonIfNotExists(Person $person)
    {
        if (!isset($this->personList[$person->getId()])) {
            $this->personList[$person->getId()] = $person;
        }
    }

    /**
     * @param Person $person
     *
     * @return bool
     */
    public function hasUnreadMessagesForPerson(Person $person)
    {
        foreach ($this->messageList as $message) {
            if (!$message->isReadFromPerson($person)) {
                return true;
            }
        }

        return false;
    }

    public function getTitle()
    {
        if ($message = $this->getFirstMessage()) {
            return $message->getTitle();
        }
    }

}
