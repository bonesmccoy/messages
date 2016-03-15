<?php

namespace Bones\Message\Model;

class Message implements ModelInterface
{
    const STATUS_DRAFT = 'draft';
    const STATUS_SENT = 'sent';

    protected $id;

    protected $title;

    protected $body;

    /** @var Person  */
    protected $sender;

    /** @var string  */
    protected $status;



    /** @var Person */
    protected $recipients = array();

    /** @var MessageAction[] */
    protected $readers = array();

    /** @var MessageAction[] */
    protected $deleted = array();

    /** @var int */
    private $conversationId;

    /** @var  \DateTime */
    private $createdAt;

    /** @var \DateTime  */
    private $sentDate;

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
        $this->sentDate = new \DateTime();
        $this->title = $title;
        $this->status = self::STATUS_DRAFT;
        $this->conversationId = $conversationId;
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
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
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
        $action = MessageAction::factoryReadAction($person);
        if (isset($this->recipients[$person->getId()]) &&
            !isset($this->readers[$person->getId()])
        ) {
            $this->readers[$person->getId()] = $action;
        }
    }

    public function isReadFromPerson(Person $person)
    {
        return isset($this->readers[$person->getId()]);
    }

    public function getReadDateForUser(Person $person)
    {
        return  $this->isReadFromPerson($person) ? $this->readers[$person->getId()]->getDate() : null;
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
        $action = MessageAction::factoryDeleteAction($person);
        if (!isset($this->deleted[$person->getId()])) {
            $this->deleted[$person->getId()] = $action;
        }
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function send()
    {
        if (empty($this->recipients)) {
            throw new \LogicException("Cannot send message without recipients");
        }

        if ($this->status == self::STATUS_DRAFT) {
            $this->status = self::STATUS_SENT;
            $this->sentDate = new \DateTime();
        }
    }

}
