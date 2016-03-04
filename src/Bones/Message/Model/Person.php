<?php


namespace Bones\Message\Model;


class Person
{

    /**
     * @var integer
     */
    private $id;


    public function __construct($id)
    {
        $this->id = $id;
    }


    public function getId()
    {
        return $this->id;
    }
}
