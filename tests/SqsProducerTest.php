<?php

namespace Brighte\Sqs\Tests;

use Aws\Result;
use Brighte\Sqs\SqsClient;
use Brighte\Sqs\SqsContext;
use Brighte\Sqs\SqsDestination;
use Brighte\Sqs\SqsMessage;
use Brighte\Sqs\SqsProducer;
use Enqueue\Test\ClassExtensionTrait;
use Interop\Queue\Destination;
use Interop\Queue\Exception\InvalidDestinationException;
use Interop\Queue\Exception\InvalidMessageException;
use Interop\Queue\Producer;
use PHPUnit\Framework\TestCase;

class SqsProducerTest extends TestCase
{
    use ClassExtensionTrait;

    public function testShouldImplementProducerInterface()
    {
        $this->assertClassImplements(Producer::class, SqsProducer::class);
    }

    public function testCouldBeConstructedWithRequiredArguments()
    {
        new SqsProducer($this->createSqsContextMock());
    }

    public function testShouldThrowIfBodyOfInvalidType()
    {
        $this->expectException(InvalidMessageException::class);
        $this->expectExceptionMessage('The message body must be a non-empty string.');

        $producer = new SqsProducer($this->createSqsContextMock());

        $message = new SqsMessage('');

        $producer->send(new SqsDestination(''), $message);
    }

    public function testShouldThrowIfDestinationOfInvalidType()
    {
        $this->expectException(InvalidDestinationException::class);
        $this->expectExceptionMessage('The destination must be an instance of Brighte\Sqs\SqsDestination but got Mock_Destinat');

        $producer = new SqsProducer($this->createSqsContextMock());

        $producer->send($this->createMock(Destination::class), new SqsMessage());
    }

    public function testShouldThrowIfSendMessageFailed()
    {
        $client = $this->createSqsClientMock();
        $client
            ->expects($this->once())
            ->method('sendMessage')
            ->willReturn(new Result())
        ;

        $context = $this->createSqsContextMock();
        $context
            ->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn('theQueueUrl')
        ;
        $context
            ->expects($this->once())
            ->method('getSqsClient')
            ->willReturn($client)
        ;

        $destination = new SqsDestination('queue-name');
        $message = new SqsMessage('foo');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Message was not sent');

        $producer = new SqsProducer($context);
        $producer->send($destination, $message);
    }

    public function testShouldSendMessage()
    {
        $expectedArguments = [
            '@region' => null,
            'MessageBody' => 'theBody',
            'QueueUrl' => 'theQueueUrl',
            'DelaySeconds' => 12345,
            'MessageDeduplicationId' => 'theDeduplicationId',
            'MessageGroupId' => 'groupId',
            'MessageAttributes' => [
                'Headers' => [
                    'DataType' => 'String',
                    'StringValue' => '[{"hkey":"hvaleu"}]',
                ],
                'key' => [
                    'DataType' => 'String',
                    'StringValue' => 'value'
                ],
            ],
        ];

        $client = $this->createSqsClientMock();
        $client
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->identicalTo($expectedArguments))
            ->willReturn(new Result(['MessageId' => 'theMessageId']))
        ;

        $context = $this->createSqsContextMock();
        $context
            ->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn('theQueueUrl')
        ;
        $context
            ->expects($this->once())
            ->method('getSqsClient')
            ->willReturn($client)
        ;

        $destination = new SqsDestination('queue-name');
        $message = new SqsMessage('theBody', ['key' => 'value'], ['hkey' => 'hvaleu']);
        $message->setDelaySeconds(12345);
        $message->setMessageDeduplicationId('theDeduplicationId');
        $message->setMessageGroupId('groupId');

        $producer = new SqsProducer($context);
        $producer->send($destination, $message);
    }

    public function testShouldSendMessageWithCustomRegion()
    {
        $expectedArguments = [
            '@region' => 'theRegion',
            'MessageBody' => 'theBody',
            'QueueUrl' => 'theQueueUrl',
        ];

        $client = $this->createSqsClientMock();
        $client
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->identicalTo($expectedArguments))
            ->willReturn(new Result(['MessageId' => 'theMessageId']))
        ;

        $context = $this->createSqsContextMock();
        $context
            ->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn('theQueueUrl')
        ;
        $context
            ->expects($this->once())
            ->method('getSqsClient')
            ->willReturn($client)
        ;

        $destination = new SqsDestination('queue-name');
        $destination->setRegion('theRegion');

        $message = new SqsMessage('theBody');

        $producer = new SqsProducer($context);
        $producer->send($destination, $message);
    }

    public function testShouldSendDelayedMessage()
    {
        $expectedArguments = [
            '@region' => null,
            'MessageBody' => 'theBody',
            'QueueUrl' => 'theQueueUrl',
            'DelaySeconds' => 12345,
            'MessageDeduplicationId' => 'theDeduplicationId',
            'MessageGroupId' => 'groupId',
            'MessageAttributes' => [
                'Headers' => [
                    'DataType' => 'String',
                    'StringValue' => '[{"hkey":"hvaleu"}]',
                ],
                'key' => [
                    'DataType' => 'String',
                    'StringValue' => 'value'
                ],
            ],
        ];

        $client = $this->createSqsClientMock();
        $client
            ->expects($this->once())
            ->method('sendMessage')
            ->with($this->identicalTo($expectedArguments))
            ->willReturn(new Result(['MessageId' => 'theMessageId']))
        ;

        $context = $this->createSqsContextMock();
        $context
            ->expects($this->once())
            ->method('getQueueUrl')
            ->willReturn('theQueueUrl')
        ;
        $context
            ->expects($this->once())
            ->method('getSqsClient')
            ->willReturn($client)
        ;

        $destination = new SqsDestination('queue-name');
        $message = new SqsMessage('theBody', ['key' => 'value'], ['hkey' => 'hvaleu']);
        $message->setDelaySeconds(12345);
        $message->setMessageDeduplicationId('theDeduplicationId');
        $message->setMessageGroupId('groupId');

        $producer = new SqsProducer($context);
        $producer->setDeliveryDelay(5000);
        $producer->send($destination, $message);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SqsContext
     */
    private function createSqsContextMock(): SqsContext
    {
        return $this->createMock(SqsContext::class);
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|SqsClient
     */
    private function createSqsClientMock(): SqsClient
    {
        return $this->createMock(SqsClient::class);
    }
}
