<?php

namespace Bones\Message\Model;

class Conversation implements ConversationInterface
{
    /**
     * @var Message[]
     */
    protected $messageList = array();

    /**
     * @var Person[]
     */
    protected $personList = array();

    /** @return string */
    public function getId()
    {
        // TODO: Implement getId() method.
    }

    /** @return Person[] */
    public function getPersonList()
    {
        return $this->personList;
    }

    /** @return Message[] */
    public function getMessageList()
    {
        return $this->messageList;
    }

    /**
     * @param Message $message
     */
    public function addMessage(Message $message)
    {
        $this->messageList[$message->getDate()->format("Ymdhist")] = $message;
        $this->addPersonFromMessage($message);

     }

    private function addPersonFromMessage(Message $message)
    {
        $sender = $message->getSender();
        $this->addPersonIfNotExists($sender);
        foreach($message->getRecipients() as $recipient) {
            $this->addPersonIfNotExists($recipient);
        }
    }

    /**
     * @param Person $user
     */
    private function addPersonIfNotExists(Person $user)
    {
        if (!isset($this->personList[$user->getId()])) {
            $this->personList[$user->getId()] = $user;
        }
    }
}
