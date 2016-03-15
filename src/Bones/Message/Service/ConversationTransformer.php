<?php


namespace Bones\Message\Service;


use Bones\Message\Model\Conversation;
use Bones\Message\Model\ModelInterface;

class ConversationTransformer implements ModelTransformerInterface
{
    public function __construct()
    {

    }

    public function fromDocumentToModel($document)
    {
        $conversation = new Conversation();
        $reflectionProperty = new \ReflectionProperty($conversation, 'id');
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($conversation, $document['_id']);
        $reflectionProperty->setAccessible(false);

        foreach ($document['messageDocumentList'] as $messageDocument) {
            $message = $this->createMessageModel($messageDocument, $conversation);
            $conversation->addMessage($message);
        }

        return $conversation;
    }

    public function fromModelToDocument(ModelInterface $model)
    {
        return array();
    }
}
