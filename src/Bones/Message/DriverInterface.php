<?php

namespace Bones\Message;

use Bones\Message\Driver\Mongo\QueryBuilder;

interface DriverInterface
{
    public function getMessageById($id);

    public function findAllMessages();

    public function findAllSentMessage($personId, $conversationIdList = array());

    public function findAllReceivedMessages($personId, $conversationIdList = array());

    public function findAllConversationIdForPersonId($personId, $offset = null, $limit = null);

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

    public function findAllMessagesByConversationIdList($conversationIdList);

    public function persistMessage($messageDocument);

    public function removeMessageWithId($id);
}
