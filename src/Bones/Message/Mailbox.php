<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;
use Bones\Message\Service\MessageTransformer;
use MongoId;

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
        $conversations = $this->driver->findAllConversationIdForPersonIdAsRecipient((int)$person->getId(), $offset, $limit);
        $conversationIdList = $this->createConversationIdList($conversations);

        $messages = $this->driver->findAllMessagesByConversationIdList($conversationIdList);

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
        $conversations = $this->driver->findAllConversationIdForPersonIdAsSender((int)$person->getId(), $offset, $limit);
        $conversationIdList = $this->createConversationIdList($conversations);


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

        return $this->createConversationIdList($conversations);
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
        $message->send();
        $messageDocument = array(
            'senderId' => $message->getSender()->getId(),
            'sentDate' => (array) $message->getSentDate(),
            'title' => $message->getTitle(),
            'body' => $message->getBody(),
            'conversationId' => (string) $message->getConversationId(),
        );



        $recipients = array();

        foreach ($message->getRecipients() as $recipient) {
            $recipients[] = array('personId' => $recipient->getId());
        }

        $messageDocument['recipientList'] = $recipients;

        $this->driver->persistMessage($messageDocument);

        $property = new \ReflectionProperty($message, 'id');
        $property->setAccessible(true);
        $property->setValue($message, (string) $messageDocument['_id']);
        $property->setAccessible(false);

        if (null == $message->getConversationId()) {
            $messageDocument['conversationId'] = (string) $messageDocument['_id'];
            $this->driver->persistMessage($messageDocument);
        }

        $property = new \ReflectionProperty($message, 'conversationId');
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

    /**
     * @param $conversations
     * @return array
     */
    private function createConversationIdList($conversations)
    {
        $conversationIdList = array();

        foreach ($conversations as $conversationDocument) {
            $conversationId = $conversationDocument['_id'];
            if ($conversationId instanceof MongoId) {
                $conversationId = (string)$conversationId;
            }
            $conversationIdList[] = $conversationId;
        }

        return $conversationIdList;
    }
}
