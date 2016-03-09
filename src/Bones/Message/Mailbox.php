<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Person;

class Mailbox extends AbstractRepository
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * @param Person $person
     *
     * @return Conversation[]
     */
    public function getInbox(Person $person)
    {
        $conversationIdList = $this->fetchConversationIdListForPerson($person);

        $messages = $this->driver->findAllReceivedMessages($person->getId(), $conversationIdList);

        $messageDocumentGroupedByConversation = $this->groupMessagesByConversationId($messages);

        $inboxContent = array();
        foreach ($messageDocumentGroupedByConversation as $conversationId => $messageDocumentList) {
            $conversation = $this->createConversationModel(
                array('_id' => $conversationId),
                $messageDocumentList
            );

            $inboxContent[] = $conversation;
        }

        return $inboxContent;
    }

    public function getOutbox(Person $person)
    {
        $conversationIdList = $this->fetchConversationIdListForPerson($person);

        $messages = $this->driver->findAllSentMessage($person->getId(), $conversationIdList);

        $messageDocumentGroupedByConversation = $this->groupMessagesByConversationId($messages);

        $outboxContent = array();
        foreach ($messageDocumentGroupedByConversation as $conversationId => $messageDocumentList) {
            $conversation = $this->createConversationModel(
                array('_id' => $conversationId),
                $messageDocumentList
            );

            $outboxContent[] = $conversation;
        }

        return $outboxContent;
    }

    /**
     * @param Person $person
     *
     * @return array
     */
    private function fetchConversationIdListForPerson(Person $person)
    {
        $conversations = $this->driver->findAllConversationIdForPersonId($person->getId());

        $conversationIdList = array();

        foreach ($conversations as $conversationDocument) {
            $conversationIdList[] = $conversationDocument['_id'];
        }

        return $conversationIdList;
    }

    /**
     * @param $messages
     *
     * @return array
     */
    private function groupMessagesByConversationId($messages)
    {
        $messageDocumentGroupedByConversation = array();
        foreach ($messages as $messageDocument) {
            $messageDocumentGroupedByConversation[$messageDocument['conversation']][] = $messageDocument;
        }

        return $messageDocumentGroupedByConversation;
    }
}
