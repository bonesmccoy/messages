<?php

namespace Bones\Message;

use Bones\Message\Driver\Mongo\QueryBuilder;
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
        $conversationDocument["_id"] = $id;

        return $this->createConversationModel(
            $conversationDocument,
            $this->driver->findMessagesByConversationId($id)
        );
    }

    /**
     * @param Conversation $conversation
     * @param null $offset
     * @param null $limit
     * @param int|string $sorting
     * @return Conversation
     */
    public function getConversationMessageList(Conversation $conversation, $offset = null, $limit = null, $sorting = QueryBuilder::ORDER_DESC)
    {
        foreach($this->driver->findMessagesByConversationId($conversation->getId(), $offset, $limit, $sorting) as $message) {
            $message = $this->createMessageModel($message, $conversation);
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
        return $this->driver->countMessages($conversation->getId());
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countPeople(Conversation $conversation)
    {
       return $this->driver->countPeople($conversation->getId());
    }

    /**
     * @param Conversation $conversation
     *
     * @return Person[]
     */
    public function getPeople(Conversation $conversation)
    {
        $conversationDocument["_id"] = $conversation->getId();
        $conversation = $this->createConversationModel(
            $conversationDocument["_id"],
            $this->driver->findMessagesByConversationId($conversation->getId())
        );

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
            $messageDocument['title'],
            $messageDocument['body']
        );



        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $messageDocument['_id']);
        $reflectionProperty->setAccessible(false);


        $reflectionProperty = new \ReflectionProperty($message, 'date');
        $reflectionProperty->setAccessible(true);
        $date = ($messageDocument['date'] instanceof \Datetime ) ? $messageDocument['date'] : new \DateTime($messageDocument['date']);
        $reflectionProperty->setValue($message, $date);
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
            $message = $this->createMessageModel($messageDocument, $conversation);
            $conversation->addMessage($message);
        }

        return $conversation;
    }


    public function getConversationListForPerson(Person $person)
    {
        $conversationList = array();

        foreach ($this->driver->findAllConversationIdForPersonId($person->getId()) as $conversationDocument) {
            $conversationList[] = $this->createConversationModel(
                $conversationDocument,
                $this->driver->findMessagesByConversationId($conversationDocument["_id"])
            );
        }

        return $conversationList;
    }
}
