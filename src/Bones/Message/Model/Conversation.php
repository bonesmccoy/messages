<?php

namespace Bones\Message\Model;

class Conversation
{

    public $id;

    /**
     * @var Message[]
     */
    protected $messageList = array();

    /**
     * @var Person[]
     */
    protected $personList = array();


    /**
     * Returns the Id of the Conversation
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns all the person involved in this conversation
     *
     * @return Person[]
     */
    public function getPersonList()
    {
        return $this->personList;
    }

    /**
     * Returns all the messages in this conversation
     *
     * @return Message[]
     */
    public function getMessageList()
    {
        ksort($this->messageList);
        return $this->messageList;
    }

    /**
     * Adds a message in this conversation
     *
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messageList[$message->getDate()->format("Ymdhis")] = $message;
        $this->addPersonsFromMessage($message);
    }

    /**
     * Adds all the person object involved in the message
     * to the conversation
     *
     * @param Message $message
     */
    private function addPersonsFromMessage(Message $message)
    {
        $sender = $message->getSender();
        $this->addPersonIfNotExists($sender);
        foreach($message->getRecipients() as $recipient) {
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
     * @return bool
     */
    public function hasUnreadMessagesForPerson(Person $person)
    {
        foreach($this->messageList as $message) {
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

    /**
     * return Message
     */
    private function getFirstMessage()
    {
        $messageList = $this->getMessageList();
        return array_shift(array_values($messageList));
    }

}
