<?php


namespace Bones\Message\Model;


class Message
{

    protected $id;

    protected $title;

    protected $body;

    /** @var Person  */
    protected $sender;

    /**
     * @var \DateTime
     */
    private $date;

    /**
     * @var Person[]
     */
    protected $recipients = array();

    /**
     * @var Person[]
     */
    protected $readers = array();

    /**
     * @var Conversation
     */
    private $conversation;


    /**
     * Message constructor.
     * @param Conversation $conversation
     * @param Person $sender
     * @param $body
     */
    public function __construct(Conversation $conversation, Person $sender, $body)
    {
        $this->conversation = $conversation;
        $this->sender = $sender;
        $this->body = $body;
        $this->date = new \DateTime();
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @return mixed
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }



    /**
     * @return Person
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @return Person[]
     */
    public function getRecipients()
    {
        return $this->recipients;
    }


    public function addRecipient(Person $person)
    {
        $this->recipients[$person->getId()] = $person;
    }


    /**
     * @return Person[]
     */
    public function getReaders()
    {
        return $this->readers;
    }


    public function markAsReadForPerson(Person $person)
    {
        if (isset($this->recipients[$person->getId()]) &&
            !isset($this->readers[$person->getId()])
        ) {
            $this->readers[$person->getId()] = new \DateTime();
        }
    }

    public function isReadFromPerson(Person $person)
    {
        return (isset($this->readers[$person->getId()]));
    }

    public function getReadDateForUser(Person $person)
    {
        return  $this->isReadFromPerson($person)? $this->readers[$person->getId()] : null;
    }

    public function markAsUnreadForPerson(Person $person)
    {
        if (isset($this->readers[$person->getId()])) {
            unset($this->readers[$person->getId()]);
        }
    }


}
