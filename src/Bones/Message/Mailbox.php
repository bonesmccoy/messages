<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use Bones\Message\Service\ConversationTransformer;
use Bones\Message\Service\MessageTransformer;

class Mailbox
{
    /**
     * @var DriverInterface
     */
    protected $driver;

    /**
     * @var MessageTransformer
     */
    private $messageTransformer;

    /**
     * Mailbox constructor.
     * @param DriverInterface $driver
     * @param MessageTransformer $messageTransformer
     */
    public function __construct(DriverInterface $driver, MessageTransformer $messageTransformer)
    {
        $this->driver = $driver;
        $this->messageTransformer = $messageTransformer;
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
                $messageDocumentList
            );

            $outboxContent[] = $conversation;
        }

        return $outboxContent;
    }

    /**
     * @param $id
     *
     * @return Conversation
     */
    public function getConversation($id)
    {
        $messages = $this->driver->findMessagesByConversationId($id);

        return $this->createConversationModel(
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
            $messageDocumentGroupedByConversation[$messageDocument['conversationId']][] = $messageDocument;
        }

        return $messageDocumentGroupedByConversation;
    }

    public function sendMessage(Message $message)
    {
        $messageDocument = array(
            'sender' => $message->getSender()->getId(),
            'date' => (array) $message->getSentDate(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'conversation' => $message->getConversationId(),
        );

        $recipients = array();
        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = array('id' => $recipient->getId());
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

    /**
     * @param $messageDocument
     *
     * @return Message
     */
    public function createMessageModel($messageDocument)
    {
        return $this->messageTransformer->fromDocumentToModel($messageDocument);
    }

    /**
     * @param array $messageDocumentList
     *
     * @return Conversation
     */
    public function createConversationModel($messageDocumentList = array())
    {
        $messageList = array();
        foreach ($messageDocumentList as $messageDocument) {
            $messageList[] = $this->messageTransformer->fromDocumentToModel($messageDocument);
        }

        return new Conversation($messageList);
    }
}
