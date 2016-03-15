<?php

namespace Bones\Message\Model;

class Person implements ModelInterface
{
    /**
     * @var int
     */
    private $id;

    public function __construct($id)
    {
        $this->id = (int) $id;
    }

    public function getId()
    {
        return $this->id;
    }
}
