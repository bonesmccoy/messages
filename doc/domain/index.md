Domain
======

Person
------
*is* the entity that can receive or send message
*must* have access to a mailbox

[Structure](./person.md)

Message
-------
*is* a communication holder 
*must* have a title and a body
*must* have a sender
*must* be part of conversation, either as a reply message or a new message
*must* have a recipient in order to be sent.

[Structure](./message.md)

Conversation
------------
*is* a list of messages related by topic.
*must* contain at least one sent message
*is* identified by the id of the message which doesn't have a parentId 

Mailbox
-------
*must* have a owner
*is* a container of messages where the owner can be sender or recipient
*must* have a inbox and an outbox
*can* have labels





