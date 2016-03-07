<?php


namespace Bones\Message;


use Bones\Message\Driver\Mongo\QueryBuilder;
use Bones\Message\Model\Conversation;

interface RepositoryInterface
{

    /**
     * Return Single Conversation
     * @param $id
     * @return Conversation
     */
    public function getConversation($id);

    /**
     * get all messages from a Conversation
     *
     * @param Conversation $conversation
     * @param int $offset
     * @param int $limit
     * @param int $sorting
     *
     * @return mixed
     */
    public function getConversationMessageList(Conversation $conversation, $offset = null, $limit = null, $sorting = QueryBuilder::ORDER_DESC);


    /**
     * Return total Messages from Conversation
     * @param Conversation $conversation
     * @return int
     */
    public function countMessages(Conversation $conversation);


    /**
     * @param Conversation $conversation
     * @return int
     */
    public function countPeople(Conversation $conversation);


    /**
     * @param Conversation $conversation
     * @return int
     */
    public function getPeople(Conversation $conversation);

}
