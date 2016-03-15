Message
=======

Structure
---------

```
{
    "_id" : int,
    "status": draft | sent,
    "conversationId" : int,
    "senderId" : int,
    "recipientList" : [ 
        {
            "personId" : int
        }
    ],
    "deletedBy" : [ 
        {
            "personId" : int,
            "date" : DateTime
        }
    ],
    "readBy" : [
        {
            "personId" : int,
            "date" : DateTime
    ],
    "title" : "title 1 1",
    "body" : "body",
    "createdAt" : DateTime,
    "sentDate" : DateTime
}
```
