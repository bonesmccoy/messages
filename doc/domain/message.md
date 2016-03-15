Message
=======

Structure
---------

```
{
    "id" : int,
    "parentId" : int|null,
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
    "createdDate" : DateTime,
    "sentDate" : DateTime
}
```
