<?php


namespace Bones\Message\Service;


use Bones\Message\Model\ModelInterface;


interface ModelTransformerInterface
{

    public function fromDocumentToModel($document);


    public function fromModelToDocument(ModelInterface $model);
}
