<?php


namespace Bones\Message\Service;


use Bones\Message\Model\Message;
use Bones\Message\Model\ModelInterface;
use Bones\Message\Model\Person;

class MessageTransformer implements ModelTransformerInterface
{

    public function fromDocumentToModel($document)
    {
        $message = new Message(
            new Person($document['sender']),
            $document['title'],
            $document['body'],
            $document['conversation']
        );

        $reflectionProperty = new \ReflectionProperty($message, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($message, $document['_id']);
        $reflectionProperty->setAccessible(false);

        if (isset($document['date'])) {
            $date = null;
            if (is_array($document['date'])) {
                $dateTimeArray = $document['date'];
                if (!empty($dateTimeArray)) {
                    $date = new \DateTime(
                        $dateTimeArray['date'],
                        new \DateTimeZone($dateTimeArray['timezone'])
                    );
                }
            } elseif (($document['date'] instanceof \Datetime)) {
                $date = $document['date'];
            } else {
                $date = new \DateTime($document['date']);
            }

            if ($date) {
                $reflectionProperty = new \ReflectionProperty($message, 'date');
                $reflectionProperty->setAccessible(true);
                $reflectionProperty->setValue($message, $date);
                $reflectionProperty->setAccessible(false);
            }
        }

        if (!empty($document['deleted'])) {
            $deleted = array();
            foreach ($document['deleted'] as $id => $data) {
                $deleted[$data['id']] = $data['date'];
            }

            $reflectionProperty = new \ReflectionProperty($message, 'deleted');
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($message, $deleted);
            $reflectionProperty->setAccessible(false);
        }

        if (isset($document['recipient'])) {
            foreach ($document['recipient'] as $recipient) {
                $message->addRecipient(new Person($recipient['id']));
            }
        }

        return $message;
    }

    public function fromModelToDocument(ModelInterface $model)
    {
        throw new \BadMethodCallException("Not Implemented");
    }
}
