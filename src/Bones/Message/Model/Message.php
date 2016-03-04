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


    public function addRecipient(Person $user)
    {
        $this->recipients[$user->getId()] = $user;
    }


    /**
     * @return Person[]
     */
    public function getReaders()
    {
        return $this->readers;
    }


    public function markAsReadForPerson(Person $user)
    {
        if (isset($this->recipients[$user->getId()]) &&
            !isset($this->readers[$user->getId()])
        ) {
            $this->readers[$user->getId()] = new \DateTime();
        }
    }

    public function isReadFromPerson(Person $user)
    {
        return (isset($this->readers[$user->getId()]));
    }

    public function getReadDateForUser(Person $user)
    {
        return  $this->isReadFromPerson($user)? $this->readers[$user->getId()] : null;
    }

    public function markAsUnreadForPerson(Person $user)
    {
        if (isset($this->readers[$user->getId()])) {
            unset($this->readers[$user->getId()]);
        }
    }


}
