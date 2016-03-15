<?php


namespace Bones\Message\Model;


class MessageAction
{

    const ACTION_TYPE_DELETE = 'delete';
    const ACTION_TYPE_READ = 'read';

    protected $date;

    protected $type;

    private function __construct($type, Person $actionPerformer)
    {
        $this->type = $type;
        $this->date = new \DateTime();
    }

    public static function factoryDeleteAction(Person $person)
    {
        return new self(self::ACTION_TYPE_DELETE, $person);
    }

    public static function factoryReadAction(Person $person)
    {
        return new self(self::ACTION_TYPE_READ, $person);
    }

    public function isDelete()
    {
        return self::ACTION_TYPE_DELETE == $this->type;
    }

    public function isRead()
    {
        return self::ACTION_TYPE_READ == $this->type;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

}
