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

    public function __construct($username, $password, $host, $port, $db, $connect = true)
    {
        $url = sprintf("mongodb://%s%s%s%s/%s",
            ($username) ? "$username:" : "",
            ($password) ? "$password:" : "",
            $host,
            $port,
            $db
        );

        $options = array(
            'connect' => $connect
        );

        $this->client = new \MongoClient($url, $options);

    }

    /**
     * @param $id
     * @return Conversation
     */
    public function findConversationById($id)
    {
        // TODO: Implement findConversationById() method.
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
    public function createConversationModel($conversationEntity, $messageEntityList)
    {
        // TODO: Implement createConversationModel() method.
    }

    public function createPersonModel($id)
    {
        // TODO: Implement createPersonModel() method.
    }
}
