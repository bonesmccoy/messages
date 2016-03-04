<?php


namespace Bones\Message\Driver;


use Bones\Message\DriverInterface;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;

class MongoDriver implements DriverInterface
{

    const CONVERSATION_COLLECTION = 'conversation';
    const MESSAGE_COLLECTION = 'message';


    /**
     * @var \MongoClient
     */
    private $client;
    private $dbName;

    public function __construct($dbName, $host = 'localhost', $port = 27017, $username = null, $password = null, $connect = true)
    {
        $url = sprintf("mongodb://%s%s%s%s/%s",
            !empty($username) ? "$username:" : "",
            !empty($password) ? "$password:" : "",
            $host,
            !empty($port) ? ":$port" : "",
            $dbName
        );

        $options = array(
            'connect' => $connect
        );

        $this->client = new \MongoClient($url, $options);
        $this->client->connect();
        $this->dbName = $dbName;
    }

    private function getDb()
    {
        return $this->client->{$this->dbName};
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
        // TODO: Implement findMessagesByConversation() method.
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
     * @return Message
     */
    public function createMessageModel($messageEntity)
    {
        // TODO: Implement createMessageModel() method.
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

        return $conversation;
    }

    public function createPersonModel($id)
    {
        // TODO: Implement createPersonModel() method.
    }
}
