<?php


namespace Bones\Message;


use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;

interface DriverInterface
{

    /**
     * @param $id
     * @return Conversation
     */
    public function findConversationById($id);

    /**
     * @param int $conversationId
     * @param int $offset
     * @param int $limit
     * @param string $sortOrder
     * @return array
     */
    public function findMessagesByConversationId($conversationId, $offset = null, $limit = null, $sortOrder = 'ASC');

    /**
     * @param $conversationId
     * @return int
     */
    public function countMessages($conversationId);

    /**
     * @param $conversationId
     * @return int
     */
    public function countPeople($conversationId);


    public function persistConversation(Conversation $conversation);


    public function persistMessage(Message $message);

}
