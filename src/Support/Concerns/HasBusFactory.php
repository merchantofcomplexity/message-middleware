<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support\Concerns;

use MerchantOfComplexity\Messaging\Contracts\DomainMessage;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Manager\ServiceBusManager;
use React\Promise\PromiseInterface;
use RuntimeException;

trait HasBusFactory
{
    private ServiceBusManager $serviceBusManager;

    /**
     * Service bus manager setter
     *
     * @param ServiceBusManager $serviceBusManager
     */
    public function setServiceBusManager(ServiceBusManager $serviceBusManager): void
    {
        $this->serviceBusManager = $serviceBusManager;
    }

    /**
     * Dispatch message through bus according to message type
     *
     * @param Message $message
     * @return PromiseInterface|void
     */
    private function dispatchThroughBus(Message $message)
    {
        $busManager = $this->serviceBusManager ?? app(ServiceBusManager::class);

        switch ($message->messageType()) {
            case DomainMessage::TYPE_COMMAND:
                $busManager->command()->dispatch($message);
                break;
            case DomainMessage::TYPE_EVENT:
                $busManager->event()->dispatch($message);
                break;
            case DomainMessage::TYPE_QUERY:
                return $busManager->query()->dispatch($message);
            default:
                $exceptionMessage = "Invalid message type {$message->messageType()} ";

                throw new RuntimeException($exceptionMessage);
        }
    }

    /**
     * Dispatch message
     *
     * Only query message should return result
     *
     * @param Message $message
     * @return PromiseInterface|void
     */
    protected function dispatch(Message $message)
    {
        return $this->dispatchThroughBus($message);
    }
}
