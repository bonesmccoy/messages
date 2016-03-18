<?php

namespace Bones\Message\Driver\Mongo;

use Bones\Message\DriverInterface;

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
        $url = sprintf('mongodb://%s%s%s%s/%s',
            !empty($username) ? "$username:" : '',
            !empty($password) ? "$password:" : '',
            $host,
            !empty($port) ? ":$port" : '',
            $databaseName
        );

        $options = array(
            'connect' => $connect,
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
    private function getMessageCollection()
    {
        return $this->getDb()->{self::MESSAGE_COLLECTION};
    }

    /**
     * @param array $query
     * @param array $fields
     *
     * @return \MongoCursor
     */
    private function queryMessageCollection($query = array(), $fields = array())
    {
        return $this->getMessageCollection()->find($query, $fields);
    }

    private function messageIsNotDeletedByPersonId($personId)
    {
        return QueryBuilder::NotEqual('deletedBy.personId', $personId);
    }

    public function getMessageById($id)
    {
        $id = $this->ensureMongoIdInstance($id);
        return $this->getMessageCollection()->findOne(QueryBuilder::Equal('_id', $id));
    }

    public function findAllMessages()
    {
        return $this->queryMessageCollection();
    }

    public function findAllSentMessage($personId, $conversationIdList = array())
    {
        $personId = (int) $personId;
        $andQuery = array(
            array('senderId' => $personId),
            $this->messageIsNotDeletedByPersonId($personId),
        );

        if (!empty($conversationIdList)) {
            $andQuery[] = QueryBuilder::GetIn('conversationId', $conversationIdList);
        }

        return $this->queryMessageCollection(QueryBuilder::GetAnd($andQuery));
    }

    public function findAllReceivedMessages($personId, $conversationIdList = array())
    {
        $personId = (int) $personId;
        $andQuery = array(
            array('recipientList.personId' => $personId),
            $this->messageIsNotDeletedByPersonId($personId),
        );

        if (!empty($conversationIdList)) {
            $andQuery[] = QueryBuilder::GetIn('conversationId', $conversationIdList);
        }

        return $this->queryMessageCollection(QueryBuilder::GetAnd($andQuery));
    }

    public function findAllConversationIdForPersonId($personId, $offset = null, $limit = null)
    {
        $personId = (int) $personId;
        $senderOrRecipientQuery = QueryBuilder::GetOr(
            array(
                QueryBuilder::Equal('senderId', $personId),
                QueryBuilder::Equal('recipientList.personId', $personId),
            )
        );

        return $this->queryAllConversationForPersonId($personId, $offset, $limit, $senderOrRecipientQuery);
    }

    public function findAllConversationIdForPersonIdAsSender($personId, $offset = null, $limit = null)
    {
        $personId = (int) $personId;
        $personAsSenderQuery = QueryBuilder::Equal('senderId', $personId);

        return $this->queryAllConversationForPersonId($personId, $offset, $limit, $personAsSenderQuery);

    }

    public function findAllConversationIdForPersonIdAsRecipient($personId, $offset = null, $limit = null)
    {
        $personId = (int) $personId;
        $personAsRecipientQuery = QueryBuilder::Equal('recipientList.personId', $personId);

        return $this->queryAllConversationForPersonId($personId, $offset, $limit, $personAsRecipientQuery);
    }

    public function findAllMessagesByConversationIdList($conversationIdList)
    {
        $queryIn = QueryBuilder::GetIn('conversationId', $conversationIdList);

        return $this->queryMessageCollection($queryIn);
    }


    /**
     * @param int  $conversationId
     * @param null $offset
     * @param null $limit
     * @param int  $sortDateOrder
     *
     * @return array|\MongoCursor
     */
    public function findMessagesByConversationId(
        $conversationId,
        $offset = null,
        $limit = null,
        $sortDateOrder = QueryBuilder::ORDER_DESC
    ) {
        $conversationId = $this->ensureMongoIdInstance($conversationId);

        $cursor = $this
            ->queryMessageCollection(
                        QueryBuilder::Equal('conversationId', $conversationId)
                    );

        if ($offset !== null) {
            $cursor->skip((int)$offset);
        }
        if ($limit) {
            $cursor->limit((int)$limit);
        }

        $cursor->sort(array(
            'sentDate' => $sortDateOrder,
        ));

        return $cursor;
    }

    public function persistMessage($messageDocument)
    {
        $hasId = !empty($messageDocument['_id']);
        if (!$hasId) {
            $this->getMessageCollection()
                ->insert(
                    $messageDocument
                );
        } else {
            $documentId = $messageDocument['_id'];
            $id = $this->ensureMongoIdInstance($documentId);
            $this->getMessageCollection()
                ->update(
                    array('_id' => $id),
                    $messageDocument,
                    array('fsync' => true)
                );
        }
    }

    public function removeMessageWithId($id)
    {
        $id = $this->ensureMongoIdInstance($id);
        $this->getMessageCollection()->remove(
            QueryBuilder::Equal('_id', $id)
        );
    }

    /**
     * @param $personId
     * @param $offset
     * @param $limit
     * @param $senderOrRecipientQuery
     * @return array
     */
    private function queryAllConversationForPersonId($personId, $offset, $limit, $senderOrRecipientQuery)
    {
        $query = QueryBuilder::GetAnd(
            array(
                $senderOrRecipientQuery,
                $this->messageIsNotDeletedByPersonId($personId),
            )
        );

        $pipeline = array(
            array('$match' => $query),
            array('$sort' => array('sentDate' => QueryBuilder::ORDER_DESC))
        );


        $pipeline[] = array('$group' => array(
            '_id' => '$conversationId',
            'title' => array('$first' => '$title'),
            'date' => array('$first' => '$sentDate'),
        ));

        $pipeline[] = array(
            '$sort' => array('date' => QueryBuilder::ORDER_DESC)
        );

        if ($offset !== null) {
            $pipeline[] = array('$skip' => (int)$offset);
        }

        if ($limit) {
            $pipeline[] = array('$limit' => (int)$limit);
        }

        $cursor = $this
            ->getMessageCollection()
            ->aggregate(
                $pipeline,
                array(
                    'allowDiskUse' => true,
                )
            );

        return (isset($cursor['result'])) ? $cursor['result'] : array();
    }

    /**
     * @param $documentId
     * @return \MongoId
     */
    public function ensureMongoIdInstance($documentId)
    {
        $id = ($documentId instanceof \MongoId) ? $documentId : new \MongoId($documentId);
        return $id;
    }
}
