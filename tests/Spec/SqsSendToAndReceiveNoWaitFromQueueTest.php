<?php

namespace Brighte\Sqs\Tests\Spec;

use Brighte\Sqs\SqsContext;
use Brighte\Sqs\SqsDestination;
use Enqueue\Test\RetryTrait;
use Enqueue\Test\SqsExtension;
use Interop\Queue\Context;
use Interop\Queue\Spec\SendToAndReceiveNoWaitFromQueueSpec;

/**
 * @group functional
 * @retry 5
 */
class SqsSendToAndReceiveNoWaitFromQueueTest extends SendToAndReceiveNoWaitFromQueueSpec
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

    protected function createQueue(Context $context, $queueName): SqsDestination
    {
        return $this->createSqsQueue($context, $queueName);
    }
}
