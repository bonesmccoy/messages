<?php


namespace Bones\Message\Driver;


use Bones\Message\DriverInterface;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;

class MongoDriver implements DriverInterface
{

    const CONVERSATION_COLLECTION = 'conversations';
    const MESSAGE_COLLECTION = 'messages';


    /**
     * @var \MongoClient
     */
    private $client;
    private $databaseName;

    public function __construct($databaseName, $host = 'localhost', $port = 27017, $username = null, $password = null, $connect = true)
    {
        $url = sprintf("mongodb://%s%s%s%s/%s",
            !empty($username) ? "$username:" : "",
            !empty($password) ? "$password:" : "",
            $host,
            !empty($port) ? ":$port" : "",
            $databaseName
        );

        $options = array(
            'connect' => $connect
        );

        $this->client = new \MongoClient($url, $options);
        $this->databaseName = $databaseName;
    }

    private function getDb()
    {
        return $this->client->{$this->databaseName};
    }
    /**
     * @return \MongoCollection
     */
    private function getConversationCollection()
    {
        return $this->getDb()->{self::CONVERSATION_COLLECTION};
    }

    /**
     * @return \MongoCollection
     */
    private function getMessageCollection()
    {
        return $this->getDb()->{self::MESSAGE_COLLECTION};
    }

    /**
     * @param $id
     * @return Conversation
     */
    public function findConversationById($id)
    {
        $conversation = $this
            ->getConversationCollection()
            ->findOne(
                array('_id' => $id)
            );

        return $this->createConversationModel($conversation);
    }

    /**
     * @param Conversation $conversation
     * @param int $offset
     * @param int $limit
     * @param string $sortOrder
     * @return Message[]
     */
    public function findMessagesByConversation(Conversation $conversation, $offset = 0, $limit = 20, $sortOrder = 'ASC')
    {
        $cursor = $this
            ->getMessageCollection()
            ->find(
                array('conversation' => $conversation->getId())
            );

        $messages = array();

        foreach ($cursor as $messageEntity) {
            $message = $this->createMessageModel($messageEntity, $conversation);
            $messages[$message->getId()] = $message;
        }

        return $messages;
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countMessages(Conversation $conversation)
    {
        // TODO: Implement countMessages() method.
    }

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countPeople(Conversation $conversation)
    {
        // TODO: Implement countPeople() method.
    }

    public function persistConversation(Conversation $conversation)
    {
        // TODO: Implement persistConversation() method.
    }

    public function persistMessage(Message $message)
    {
        // TODO: Implement persistMessage() method.
    }

    /**
     * @param $messageEntity
     * @param Conversation $conversation
     * @return Message
     */
    public function createMessageModel($messageEntity, Conversation $conversation)
    {
        $message = new Message(
            $conversation,
            new Person($messageEntity['sender']),
            $messageEntity['body']
        );
        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $messageEntity['_id']);
        $reflectionProperty->setAccessible(false);

        if (!empty($messageEntity)) {
            foreach ($messageEntity['recipient'] as $recipientId) {
                $message->addRecipient(new Person($recipientId));
            }
        }

        return $message;
    }

    /**
     * @param $conversationEntity
     * @param array $messageEntityList
     *
     * @return Conversation
     */
    public function createConversationModel($conversationEntity, $messageEntityList = array())
    {
        $conversation = new Conversation();
        $reflectionProperty = new \ReflectionProperty($conversation, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($conversation, $conversationEntity["_id"]);
        $reflectionProperty->setAccessible(false);

        return $conversation;
    }

    public function createPersonModel($id)
    {
        // TODO: Implement createPersonModel() method.
    }
}
