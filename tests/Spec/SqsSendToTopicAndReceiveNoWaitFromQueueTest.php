<?php

namespace Brighte\Sqs\Tests\Spec;

use Brighte\Sqs\SqsContext;
use Brighte\Sqs\SqsDestination;
use Enqueue\Test\RetryTrait;
use Enqueue\Test\SqsExtension;
use Interop\Queue\Context;
use Interop\Queue\Spec\SendToTopicAndReceiveNoWaitFromQueueSpec;

/**
 * @group functional
 * @retry 5
 */
class SqsSendToTopicAndReceiveNoWaitFromQueueTest extends SendToTopicAndReceiveNoWaitFromQueueSpec
{
    use RetryTrait;
    use SqsExtension;
    use CreateSqsQueueTrait;

    /**
     * @var SqsContext
     */
    private $context;

    protected function tearDown()
    {
        parent::tearDown();

        if ($this->context && $this->queue) {
            $this->context->deleteQueue($this->queue);
        }
    }

    protected function createContext(): SqsContext
    {
        return $this->context = $this->buildSqsContext();
    }

    protected function createTopic(Context $context, $queueName): SqsDestination
    {
        return $this->createSqsQueue($context, $queueName);
    }

    protected function createQueue(Context $context, $queueName): SqsDestination
    {
        return $this->createSqsQueue($context, $queueName);
    }
}
