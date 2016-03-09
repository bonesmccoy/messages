<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
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
     * @param null   $offset
     * @param null   $limit
     *
     * @return Model\Conversation[]
     */
    public function getInbox(Person $person, $offset = null, $limit = null)
    {
        $conversationIdList = $this->fetchConversationIdListForPerson($person, $offset, $limit);

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

    public function getOutbox(Person $person, $offset = null, $limit = null)
    {
        $conversationIdList = $this->fetchConversationIdListForPerson($person, $offset, $limit);

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
     * @param $id
     * @return Conversation
     */
    public function getConversation($id)
    {
        $messages = $this->driver->findMessagesByConversationId($id);

        return $this->createConversationModel(
            array("_id" => $id),
            $messages
        );
    }

    /**
     * @param Person $person
     * @param null   $offset
     * @param null   $limit
     *
     * @return array
     */
    private function fetchConversationIdListForPerson(Person $person, $offset = null, $limit = null)
    {
        $conversations = $this->driver->findAllConversationIdForPersonId($person->getId(), $offset, $limit);

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

    public function sendMessage(Message $message)
    {
        $messageDocument = array(
            'sender' => $message->getSender()->getId(),
            'date' => (array) $message->getDate(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'conversation' => $message->getConversationId(),
        );

        $recipients = array();
        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = array("id" => $recipient->getId());
        }
        $messageDocument['recipient'] = $recipients;


        $this->driver->persistMessage($messageDocument);

        $property = new \ReflectionProperty($message, 'id');
        $property->setAccessible(true);
        $property->setValue($message, (string) $messageDocument['_id']);
        $property->setAccessible(false);
    }

    public function removeMessage(Message $message)
    {
        if ($message->getId() != null) {
            $this->driver->removeMessageWithId($message->getId());
        }
    }
}
