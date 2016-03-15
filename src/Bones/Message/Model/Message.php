<?php

namespace Bones\Message\Model;

class Message implements ModelInterface
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

    protected $deleted = array();

    /**
     * @var int
     */
    private $conversationId;

    /**
     * Message constructor.
     *
     * @param Person $sender
     * @param string $title
     * @param string $body
     * @param null $conversationId
     */
    public function __construct(Person $sender, $title, $body, $conversationId = null)
    {
        $this->sender = $sender;
        $this->body = $body;
        $this->date = new \DateTime();
        $this->title = $title;
        $this->conversationId = (null === $conversationId) ? $this->generateConversationId() : $conversationId;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    public function getConversationId()
    {
        return $this->conversationId;
    }

    private function generateConversationId()
    {
        return sprintf('%s%s%s',
            $this->date->format('Ymdhis'),
            ('s.'.$this->sender->getId()),
            base64_encode($this->title)
        );
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

    /**
     * @return array
     */
    public function getDeleted()
    {
        return $this->deleted;
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
        return isset($this->readers[$person->getId()]);
    }

    public function getReadDateForUser(Person $person)
    {
        return  $this->isReadFromPerson($person) ? $this->readers[$person->getId()] : null;
    }

    public function markAsUnreadForPerson(Person $person)
    {
        if (isset($this->readers[$person->getId()])) {
            unset($this->readers[$person->getId()]);
        }
    }

    /**
     * @param Person $person
     */
    public function markDeleteForPerson(Person $person)
    {
        if (!isset($this->deleted[$person->getId()])) {
            $this->deleted[$person->getId()] = new \DateTime();
        }
    }


}
