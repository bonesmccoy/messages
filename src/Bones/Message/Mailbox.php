<?php


namespace Bones\Message;


use Bones\Message\Model\Conversation;
use Bones\Message\Model\Person;

class Mailbox extends Repository
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
     * @return Conversation[]
     */
    public function getInbox(Person $person)
    {
        $conversations = $this->driver->findAllConversationForPersonId($person->getId());

        $conversationList = iterator_to_array($conversations);

        $conversationIdList = array();
        foreach($conversationList as $conversationDocument) {
            $conversationIdList[] = $conversationDocument["_id"];
        }

        $messages = $this->driver->findAllReceivedMessages($person->getId(), $conversationIdList);

        $messagesByConversations = array();

        foreach($messages as $message) {
            $messagesByConversations[$message['conversation']][] = $message;
        }

        $inboxContent = array();

        foreach($messagesByConversations as $conversationId => $messageDocumentList) {
            $conversation = $this->createConversationModel(
                array("_id" => $conversationId),
                $messageDocumentList
            );

            $inboxContent[] = $conversation;
        }

        return $inboxContent;

    }



}
