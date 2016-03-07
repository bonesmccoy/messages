<?php


namespace Bones\Message;


class Mailbox
{
    /**
     * @var DriverInterface
     */
    protected $driver;


    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }



}
