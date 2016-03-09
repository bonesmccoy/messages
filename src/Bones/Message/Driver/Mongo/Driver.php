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
        return QueryBuilder::NotEqual('deleted.id', $personId);
    }

    public function getMessageById($id)
    {
        return $this->getMessageCollection()->findOne(QueryBuilder::Equal('_id', new \MongoId($id)));
    }

    public function findAllMessages()
    {
        return $this->queryMessageCollection();
    }

    public function findAllSentMessage($personId, $conversationIdList = array())
    {
        $andQuery = array(
            array('sender' => $personId),
            $this->messageIsNotDeletedByPersonId($personId),
        );

        if (!empty($conversationIdList)) {
            $andQuery[] = QueryBuilder::GetIn('conversation', $conversationIdList);
        }

        return $this->queryMessageCollection(QueryBuilder::GetAnd($andQuery));
    }

    public function findAllReceivedMessages($personId, $conversationIdList = array())
    {
        $andQuery = array(
            array('recipient.id' => $personId),
            $this->messageIsNotDeletedByPersonId($personId),
        );

        if (!empty($conversationIdList)) {
            $andQuery[] = QueryBuilder::GetIn('conversation', $conversationIdList);
        }

        return $this->queryMessageCollection(QueryBuilder::GetAnd($andQuery));
    }

    public function findAllConversationIdForPersonId($personId, $offset = null, $limit = null)
    {
        $senderOrRecipientQuery = QueryBuilder::GetOr(
            array(
                QueryBuilder::Equal('sender', $personId),
                QueryBuilder::Equal('recipient.id', $personId),
            )
        );

        $query = QueryBuilder::GetAnd(
            array(
                $senderOrRecipientQuery,
                $this->messageIsNotDeletedByPersonId($personId),
            )
        );

        $pipeline = array(
            array('$match' => $query),
            array('$sort' => array('date' => -1)),
            array('$group' => array(
                '_id' => '$conversation',
                'title' => array('$first' => '$title'),
                'date' => array('$first' => '$date'),
            )),
        );

        if ($offset) {
            $pipeline[] = array('$skip' => $offset);
        }

        if ($limit) {
            $pipeline[] = array('$limit' => $limit);
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
            'date' => $sortDateOrder,
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
            $this->getMessageCollection()
                ->update(
                    array('_id' => new \MongoId($messageDocument['_id'])),
                    $messageDocument
                );
        }
    }

    public function removeMessageWithId($id)
    {
        $this->getMessageCollection()->remove(
            QueryBuilder::Equal('_id', new \MongoId($id))
        );
    }
}
