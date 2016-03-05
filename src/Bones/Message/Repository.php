<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
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
    public function getConversationMessageList(Conversation $conversation, $offset = null, $limit = null, $sorting = 'ASC')
    {
        foreach($this->driver->findMessagesByConversationId($conversation, $offset, $limit, $sorting) as $message) {
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
        foreach ($this->driver->findMessagesByConversationId($conversation, null, null) as $messageEntity) {
            $messageModel = $this->driver->createMessageModel($messageEntity, $conversation);
            $conversation->addMessage($messageModel);
        }

        return $conversation->getPersonList();
    }

    /**
     * @param $messageDocument
     * @param Conversation $conversation
     * @return Message
     */
    public function createMessageModel($messageDocument, Conversation $conversation)
    {
        $message = new Message(
            $conversation,
            new Person($messageDocument['sender']),
            $messageDocument['body']
        );
        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $messageDocument['_id']);
        $reflectionProperty->setAccessible(false);

        if (!empty($messageDocument)) {
            foreach ($messageDocument['recipient'] as $recipient) {
                $message->addRecipient(new Person($recipient['id']));
            }
        }

        return $message;
    }

    /**
     * @param $conversationDocument
     * @param array $messageDocumentList
     *
     * @return Conversation
     */
    public function createConversationModel($conversationDocument, $messageDocumentList = array())
    {
        $conversation = new Conversation();
        $reflectionProperty = new \ReflectionProperty($conversation, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($conversation, $conversationDocument["_id"]);
        $reflectionProperty->setAccessible(false);

        foreach($messageDocumentList as $messageDocument) {
            $message = $this->createMessageModel($conversation, $messageDocument);
            $conversation->addMessage($message);
        }

        return $conversation;
    }
}
