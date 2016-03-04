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
     * @param Conversation $conversation
     * @param int $offset
     * @param int $limit
     * @param string $sortOrder
     * @return Message[]
     */
    public function findMessagesByConversation(Conversation $conversation, $offset = 0, $limit = 20, $sortOrder = 'ASC');

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countMessages(Conversation $conversation);

    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countPeople(Conversation $conversation);


    public function persistConversation(Conversation $conversation);


    public function persistMessage(Message $message);

    /**
     * @param $messageEntity
     * @return Message
     */
    public function createMessageModel($messageEntity);

    /**
     * @param $conversationEntity
     * @param array $messageEntityList
     *
     * @return Conversation
     */
    public function createConversationModel($conversationEntity, $messageEntityList = array());

    public function createPersonModel($id);


}
