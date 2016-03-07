<?php


namespace Bones\Message;


use Bones\Message\Driver\Mongo\QueryBuilder;
use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;

interface DriverInterface
{

    public function findAllMessages();

    public function findAllSentMessage($personId, $conversationIdList = array());

    public function findAllReceivedMessages($personId, $conversationIdList = array());

    public function findAllConversationIdForPersonId($personId, $offset = null, $limit = null);

    /**
     * @param int $conversationId
     * @param null $offset
     * @param null $limit
     * @param int $sortDateOrder
     * @return array|\MongoCursor
     */
    public function findMessagesByConversationId($conversationId, $offset = null, $limit = null, $sortDateOrder = QueryBuilder::ORDER_DESC);


    public function persistMessage(Message $message);

}
