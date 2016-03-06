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

    /**
     * @param array $query
     * @param array $fields
     * @return \MongoCursor
     */
    private function queryMessageCollection($query = array(), $fields = array())
    {
        return $this->getMessageCollection()->find($query, $fields);
    }

    private function queryConversationCollection($query = array(), $fields = array())
    {
        return $this
            ->getConversationCollection()
            ->find(
                $query,
                $fields
            );
    }


    private function isNotDeletedByPersonId($personId)
    {
        return array('deleted.id' => array( '$ne' =>  $personId ));
    }

    public function findAllMessages()
    {
        return $this->queryMessageCollection();
    }

    public function findAllSentMessage($personId)
    {
        return $this->queryMessageCollection(
                array('$and' => array(
                        array('sender' => $personId),
                        $this->isNotDeletedByPersonId($personId)
                        )
                    )
                );
    }

    public function findAllReceivedMessages($personId)
    {
        return $this
            ->queryMessageCollection(
                array('$and' => array(
                            array('recipient.id' => $personId),
                            $this->isNotDeletedByPersonId($personId)
                        )
                     )
                );
    }

    public function findAllConversationForPerson($personId)
    {
        $cursor =  $this->queryMessageCollection(array(
                        '$and' => array(
                                array( '$or' => array(
                                            array('sender' => $personId),
                                            array('recipient.id' => $personId)
                                        )
                                ),
                                $this->isNotDeletedByPersonId($personId)
                        )
                    ),
                    array('conversation')
                );
        $conversationIdList = array();

        foreach($cursor as $cId) {
            $conversationIdList[] = $cId['conversation'];
        }

        return $this
            ->getConversationCollection()
            ->find(array(
                    '_id' => array(
                        '$in' => $conversationIdList
                    )
                )
            );
    }

    public function findAllConversations()
    {
        return $this->queryConversationCollection();
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
            ->queryMessageCollection(
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
        return $this->queryMessageCollection(
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

        $senders = $this
            ->getMessageCollection()
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

