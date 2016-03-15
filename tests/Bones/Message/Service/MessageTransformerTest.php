<?php


namespace tests\Bones\Message\Service;


use Bones\Message\Model\MessageAction;
use Bones\Message\Model\Person;
use Bones\Message\Service\MessageTransformer;

class MessageTransformerTest extends \PHPUnit_Framework_TestCase
{

    protected $messageDocumentJson =<<<MSG
{
	"_id": 1,
	"status": "draft",
	"conversationId": 1,
	"senderId": 1,
	"recipientList": [{
		"personId": 2
	}],
	"deletedBy": [{
		"personId": 2,
		"date": "2016-01-01 12:00:00"
	}],
	"readBy": [{
		"personId": 2,
		"date": "2016-01-01 11:00:00"
	}],
	"title": "message title",
	"body": "message body",
	"createdAt": "2016-01-01 10:00:00",
	"sentDate": "2016-01-01 10:05:00"
}
MSG;

    /** @var  array */
    protected $document;

    /** @var  MessageTransformer */
    protected $transformer;

    public function setUp()
    {
        $this->document = json_decode($this->messageDocumentJson, 1);
        $this->transformer = new MessageTransformer();
    }

    public function testFromDocumentToModel()
    {
        $message = $this->transformer->fromDocumentToModel($this->document);
        $this->assertInstanceOf('Bones\Message\Model\Message', $message);

        $this->assertEquals($message->getId(),  1);
        $this->assertEquals($message->getstatus(), 'draft');
        $this->assertEquals($message->getConversationId(),   1);
        $this->assertEquals($message->getSender(),   new Person(1));
        $this->assertEquals($message->getTitle(),   "message title");
        $this->assertEquals($message->getBody(),   "message body");
        $this->assertEquals($message->getCreatedAt(),  new \DateTime('2016-01-01 10:00:00'));
        $this->assertEquals($message->getSentDate(),   new \DateTime('2016-01-01 10:05:00'));

        $this->assertCount(
            1,
            $message->getRecipients()
        );

        foreach($message->getRecipients() as $recipient) {
            $this->assertInstanceOf('Bones\Message\Model\Person', $recipient);
        }

        $this->assertCount(
            1,
            $message->getDeleted()
        );
        foreach ($message->getDeleted() as $personId => $deletedAction) {
            $this->assertInstanceOf('Bones\Message\Model\MessageAction', $deletedAction);
            $this->assertTrue($deletedAction->isDelete());
            $this->assertEquals($personId, $deletedAction->getPerformer()->getId());
        }

        $this->assertCount(
            1,
            $message->getReaders()
        );
        foreach ($message->getReaders() as $personId => $readAction) {
            $this->assertInstanceOf('Bones\Message\Model\MessageAction', $readAction);
            $this->assertTrue($readAction->isRead());
            $this->assertEquals($personId, $readAction->getPerformer()->getId());
        }

    }
}
