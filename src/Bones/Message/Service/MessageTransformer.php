<?php


namespace Bones\Message\Service;


use Bones\Message\Model\Message;
use Bones\Message\Model\MessageAction;
use Bones\Message\Model\ModelInterface;
use Bones\Message\Model\Person;

class MessageTransformer implements ModelTransformerInterface
{

    public function fromDocumentToModel($document)
    {
        $message = new Message(
            new Person($document['senderId']),
            $document['title'],
            $document['body'],
            $document['conversationId']
        );

        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $document['_id']);
        $reflectionProperty->setAccessible(false);

        $sentDateField = 'sentDate';
        $this->setDateField($document, $sentDateField, $message);

        $createdAtField = 'createdAt';
        $this->setDateField($document, $createdAtField, $message);

        $actionField = 'deletedBy';
        $document = $this->populateActionField($document, $actionField, MessageAction::ACTION_TYPE_DELETE, $message);

        $actionField = 'readBy';
        $document = $this->populateActionField($document, $actionField, MessageAction::ACTION_TYPE_READ, $message);


        if (isset($document['recipientList'])) {
            foreach ($document['recipientList'] as $recipient) {
                $message->addRecipient(new Person($recipient['personId']));
            }
        }

        return $message;
    }

    public function fromModelToDocument(ModelInterface $model)
    {
        throw new \BadMethodCallException("Not Implemented");
    }

    /**
     * @param $document
     * @param $dateField
     * @param $message
     */
    private function setDateField($document, $dateField, $message)
    {
        if (isset($document[$dateField])) {
            $date = null;
            if (is_array($document[$dateField])) {
                $dateTimeArray = $document[$dateField];
                if (!empty($dateTimeArray)) {
                    $date = new \DateTime(
                        $dateTimeArray[$dateField],
                        new \DateTimeZone($dateTimeArray['timezone'])
                    );
                }
            } elseif (($document[$dateField] instanceof \Datetime)) {
                $date = $document[$dateField];
            } else {
                $date = new \DateTime($document[$dateField]);
            }

            if ($date) {
                $reflectionProperty = new \ReflectionProperty($message, $dateField);
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($message, $date);
                $reflectionProperty->setAccessible(false);
            }
        }
    }

    /**
     * @param $action
     * @param $data
     */
    private function setDateToAction($action, $data)
    {
        $reflectionProperty = new \ReflectionProperty($action, 'date');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($action, $data['date']);
        $reflectionProperty->setAccessible(false);
    }

    /**
     * @param $document
     * @param $actionField
     * @param $message
     * @return mixed
     */
    private function populateActionField($document, $actionField, $actionType, $message)
    {
        if (!empty($document[$actionField])) {
            $deleted = array();
            foreach ($document[$actionField] as $id => $data) {
                $performer = new Person($data['personId']);
                if ($actionType == MessageAction::ACTION_TYPE_DELETE) {
                    $action = MessageAction::factoryDeleteAction($performer);
                } else {
                    $action = MessageAction::factoryReadAction($performer);
                }
                $this->setDateToAction($action, $data);
                $deleted[$data['personId']] = $action;
            }

            $reflectionProperty = new \ReflectionProperty($message, $actionField);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($message, $deleted);
            $reflectionProperty->setAccessible(false);
            return $document;
        }
        return $document;
    }
}
