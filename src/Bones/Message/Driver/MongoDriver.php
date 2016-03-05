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

    public function __construct(
        $databaseName,
        $host = 'localhost',
        $port = 27017,
        $username = null,
        $password = null,
        $connect = true
    ) {
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

    public function findAllMessages()
    {
        $cursor = $this
            ->getMessageCollection()
            ->find();

        return $cursor;
    }

    public function findAllConversations()
    {
        $cursor = $this
            ->getConversationCollection()
            ->find();

        return $cursor;
    }

    /**
     * @param $id
     * @return array
     */
    public function findConversationById($id)
    {
        $conversationDocument = $this
            ->getConversationCollection()
            ->findOne(
                array('_id' => $id)
            );

        return $conversationDocument;
    }

    /**
     * @param int $conversationId
     * @param null $offset
     * @param null $limit
     * @param string $sortOrder
     * @return array|\MongoCursor
     */
    public function findMessagesByConversationId(
        $conversationId,
        $offset = null,
        $limit = null,
        $sortOrder = 'ASC'
    ) {
        $cursor = $this
            ->getMessageCollection()
            ->find(
                array('conversation' => $conversationId)
            );

        if ($offset) {
            $cursor->skip($offset);
        }
        if ($limit) {
            $cursor->limit($limit);
        }

        return $cursor;
    }

    /**
     * @param $conversationId
     * @return int
     */
    public function countMessages($conversationId)
    {
        return $this
            ->getMessageCollection()
            ->find(
                array('conversation' => $conversationId)
            )->count();
    }

    /**
     * @param $conversationId
     * @return int
     */
    public function countPeople($conversationId)
    {
        $recipients = $this
            ->getMessageCollection()
            ->distinct('recipient.id', array('conversation' => $conversationId));

        $senders = $this->
        getMessageCollection()
            ->distinct("sender", array('conversation' => $conversationId));

        $peopleInvolvedInConversation = array_unique(
            array_merge($senders, $recipients)
        );

        return count($peopleInvolvedInConversation);
    }

    public function persistConversation(Conversation $conversation)
    {
        // TODO: Implement persistConversation() method.
    }

    public function persistMessage(Message $message)
    {
        // TODO: Implement persistMessage() method.
    }
}

