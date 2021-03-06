<?php

namespace Bones\Message;

use Bones\Message\Model\Conversation;
use Bones\Message\Model\Message;
use Bones\Message\Model\Person;

abstract class AbstractRepository
{
    /**
     * @param $messageDocument
     * @param Conversation $conversation
     *
     * @return Message
     */
    public function createMessageModel($messageDocument, Conversation $conversation)
    {
        $message = new Message(
            $conversation,
            new Person($messageDocument['sender']),
            $messageDocument['title'],
            $messageDocument['body']
        );

        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $messageDocument['_id']);
        $reflectionProperty->setAccessible(false);

        if (isset($messageDocument['date'])) {
            $date = null;
            if (is_array($messageDocument['date'])) {
                $dateTimeArray = $messageDocument['date'];
                if (!empty($dateTimeArray)) {
                    $date = new \DateTime(
                        $dateTimeArray['date'],
                        new \DateTimeZone($dateTimeArray['timezone'])
                    );
                }
            } elseif (($messageDocument['date'] instanceof \Datetime)) {
                $date = $messageDocument['date'];
            } else {
                $date = new \DateTime($messageDocument['date']);
            }

            if ($date) {
                $reflectionProperty = new \ReflectionProperty($message, 'date');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($message, $date);
                $reflectionProperty->setAccessible(false);
            }
        }

        if (!empty($messageDocument['deleted'])) {
            $deleted = array();
            foreach ($messageDocument['deleted'] as $id => $data) {
                $deleted[$data['id']] = $data['date'];
            }

            $reflectionProperty = new \ReflectionProperty($message, 'deleted');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($message, $deleted);
            $reflectionProperty->setAccessible(false);
        }

        if (isset($messageDocument['recipient'])) {
            foreach ($messageDocument['recipient'] as $recipient) {
                $message->addRecipient(new Person($recipient['id']));
            }
        }

        return $message;
    }

    /**
     * @param $conversationDocument
     * @param array $messageDocumentList
     *
     * @return Conversation
     */
    public function createConversationModel($conversationDocument, $messageDocumentList = array())
    {
        $conversation = new Conversation();
        $reflectionProperty = new \ReflectionProperty($conversation, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($conversation, $conversationDocument['_id']);
        $reflectionProperty->setAccessible(false);

        foreach ($messageDocumentList as $messageDocument) {
            $message = $this->createMessageModel($messageDocument, $conversation);
            $conversation->addMessage($message);
        }

        return $conversation;
    }
}
