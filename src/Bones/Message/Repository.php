<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Person;

class Repository implements RepositoryInterface
{

    /**
     * @var DriverInterface
     */
    protected $driver;


    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Return Single Conversation
     * @param $id
     * @return Conversation
     */
    public function getConversation($id)
    {
        return $this->driver->findConversationById($id);
    }

    /**
     * get all messages from a Conversation
     *
     * @param Conversation $conversation
     * @param int $offset
     * @param int $limit
     * @param string $sorting
     *
     * @return Conversation
     */
    public function getConversationMessageList(Conversation $conversation, $offset = 0, $limit = 20, $sorting = 'ASC')
    {
        foreach($this->driver->findMessagesByConversation($conversation, $offset, $limit, $sorting) as $message) {
            $conversation->addMessage($message);
        }

        return $conversation;

    }

    /**
     * Return total Messages from Conversation
     * @param Conversation $conversation
     * @return int
     */
    public function countMessages(Conversation $conversation)
    {
        return $this->driver->countMessages($conversation);
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countPeople(Conversation $conversation)
    {
       return $this->driver->countPeople($conversation);
    }

    /**
     * @param Conversation $conversation
     *
     * @return Person[]
     */
    public function getPeople(Conversation $conversation)
    {
        foreach ($this->driver->findMessagesByConversation($conversation, null, null) as $messageEntity) {
            $messageModel = $this->driver->createMessageModel($messageEntity);
            $conversation->addMessage($messageModel);
        }

        return $conversation->getPersonList();
    }
}
