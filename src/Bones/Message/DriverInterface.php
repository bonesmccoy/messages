<?php


namespace Bones\Message;


use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;

interface DriverInterface
{

    public function findAllMessages();

    public function findAllSentMessage($personId);

    public function findAllReceivedMessages($personId);

    public function findAllConversationForPersonId($personId);

    public function findAllConversations();

    /**
     * @param $id
     * @return array
     */
    public function findConversationById($id);

    /**
     * @param int $conversationId
     * @param null $offset
     * @param null $limit
     * @param string $sortOrder
     * @return array|\MongoCursor
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
