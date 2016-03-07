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
        $conversationIdList = array();
        foreach($conversations as $conversationDocument) {
            $conversationIdList[] = $conversationDocument["_id"];
        }

        $messages = $this->driver->findAllReceivedMessages($person->getId(), $conversationIdList);

        $messageDocumentGroupedByConversation = array();
        foreach($messages as $messageDocument) {
            $messageDocumentGroupedByConversation[$messageDocument['conversation']][] = $messageDocument;
        }

        $inboxContent = array();
        foreach($messageDocumentGroupedByConversation as $conversationId => $messageDocumentList) {
            $conversation = $this->createConversationModel(
                array("_id" => $conversationId),
                $messageDocumentList
            );

            $inboxContent[] = $conversation;
        }

        return $inboxContent;

    }


}
