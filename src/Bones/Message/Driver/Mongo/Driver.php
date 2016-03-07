<?php


namespace Bones\Message\Driver\Mongo;


use Bones\Message\DriverInterface;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Driver\Mongo\QueryBuilder;

class Driver implements DriverInterface
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


    private function messageIsNotDeletedByPersonId($personId)
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
                        $this->messageIsNotDeletedByPersonId($personId)
                        )
                    )
                );
    }

    public function findAllReceivedMessages($personId, $conversationIdList = array())
    {
        $andConditions = array(
            array('recipient.id' => $personId),
            $this->messageIsNotDeletedByPersonId($personId)
        );

        if (!empty($conversationIdList)) {
            $andConditions[] = QueryBuilder::GetIn('conversation', $conversationIdList);
        }

        return $this->queryMessageCollection(QueryBuilder::GetAnd($andConditions));
    }

    public function findAllConversationForPersonId($personId)
    {

        $senderOrRecipientQuery = QueryBuilder::GetOr(
            array(
                QueryBuilder::Equal('sender', $personId),
                QueryBuilder::Equal('recipient.id', $personId)
            )
        );

        $query = QueryBuilder::GetAnd(
            array(
                $senderOrRecipientQuery,
                $this->messageIsNotDeletedByPersonId($personId)
            )
        );

        $pipeline = array(
            array('$match' => $query),
            array('$sort' => array("date" => -1 )),
            array('$group' => array(
                "_id" => '$conversation',
                "title" => array( '$first' => '$title'),
                "date" => array( '$first' => '$date'),
            ))
        );

        $cursor = $this
            ->getMessageCollection()
            ->aggregate(
                $pipeline,
                array(
                    "allowDiskUse" => true
                )
            );

        $conversationIdList = array();
        if (isset($cursor['result'])) {
            foreach($cursor['result'] as $cId) {
                $conversationIdList[] = $cId['_id'];
            }
        }

        return $this
            ->getConversationCollection()
            ->find(QueryBuilder::GetIn("_id", $conversationIdList));
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
            ->findOne(QueryBuilder::Equal("_id", $id));

        return $conversationDocument;
    }

    /**
     * @param int $conversationId
     * @param null $offset
     * @param null $limit
     * @param int $sortDateOrder
     * @return array|\MongoCursor
     */
    public function findMessagesByConversationId(
        $conversationId,
        $offset = null,
        $limit = null,
        $sortDateOrder = QueryBuilder::ORDER_ASC
    ) {
        $cursor = $this
            ->queryMessageCollection(
                        QueryBuilder::Equal('conversation', $conversationId)
                    );

        if ($offset) {
            $cursor->skip($offset);
        }
        if ($limit) {
            $cursor->limit($limit);
        }

        $cursor->sort(array(
            'date' => $sortDateOrder
        ));


        return $cursor;
    }

     /**
     * @param $conversationId
     * @return int
     */
    public function countMessages($conversationId)
    {
        return $this->queryMessageCollection(
                QueryBuilder::Equal('conversation', $conversationId)
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
            ->distinct('recipient.id', QueryBuilder::Equal('conversation', $conversationId));

        $senders = $this
            ->getMessageCollection()
            ->distinct("sender", QueryBuilder::Equal('conversation', $conversationId));

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

