<?php

$fixtures[\Bones\Message\Driver\MongoDriver::MESSAGE_COLLECTION]  = array(
    array("_id" => 1, "conversation" => 1, "sender" => 1, "recipient" => array(2, 3, 4), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
    array("_id" => 2, "conversation" => 1, "sender" => 2, "recipient" => array(1, 3, 4), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
    array("_id" => 3, "conversation" => 1, "sender" => 3, "recipient" => array(2, 1, 4), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
    array("_id" => 4, "conversation" => 1, "sender" => 4, "recipient" => array(2, 3, 1), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
    array("_id" => 5, "conversation" => 2, "sender" => 1, "recipient" => array(2), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
    array("_id" => 6, "conversation" => 2, "sender" => 2, "recipient" => array(1), 'title' => 'title', 'body' => 'body', 'date' => new MongoDate()),
);
