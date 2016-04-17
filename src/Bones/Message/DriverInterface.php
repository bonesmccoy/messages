<?php

namespace Bones\Message;

use Bones\Message\Driver\Mongo\QueryBuilder;

/**
 * Interface DriverInterface
 */
interface DriverInterface
{
    /**
     * @param string|\MongoId $id
     *
     * @return array|null
     */
    public function getMessageById($id);

    /**
     * @return mixed
     */
    public function findAllMessages();

    /**
     * @param int   $personId
     * @param array $conversationIdList
     *
     * @return mixed
     */
    public function findAllSentMessage($personId, $conversationIdList = array());

    /**
     * @param int   $personId
     * @param array $conversationIdList
     *
     * @return mixed
     */
    public function findAllReceivedMessages($personId, $conversationIdList = array());

    /**
     * @param int  $personId
     * @param null $offset
     * @param null $limit
     *
     * @return mixed
     */
    public function findAllConversationIdForPersonId($personId, $offset = null, $limit = null);

    /**
     * @param int $personId
     * @param null|int $offset
     * @param null|int  $limit
     *
     * @return mixed
     */
    public function findAllConversationIdForPersonIdAsSender($personId, $offset = null, $limit = null);

    public function findAllConversationIdForPersonIdAsRecipient($personId, $offset = null, $limit = null);

    /**
     * @param int  $conversationId
     * @param null $offset
     * @param null $limit
     * @param int  $sortDateOrder
     *
     * @return array|\MongoCursor
     */
    public function findMessagesByConversationId($conversationId, $offset = null, $limit = null, $sortDateOrder = QueryBuilder::ORDER_DESC);

    /**
     * @param array $conversationIdList
     *
     * @return mixed
     */
    public function findAllMessagesByConversationIdList($conversationIdList);

    /**
     * @param array $messageDocument
     *
     * @return mixed
     */
    public function persistMessage($messageDocument);

    /**
     * @param $id
     */
    public function removeMessageWithId($id);
}
