<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support;

use Illuminate\Http\JsonResponse as IlluminateJsonResponse;
use MerchantOfComplexity\MessageMiddleware\Support\Contracts\ResponseStrategy;
use MerchantOfComplexity\ServiceBus\Support\Concerns\PromiseHandler;
use React\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Response;

final class JsonResponse implements ResponseStrategy
{
    use PromiseHandler;

    public function fromPromise(PromiseInterface $promise): Response
    {
        $result = $this->handlePromise($promise);

        return new IlluminateJsonResponse($result);
    }

    public function withStatus(int $statusCode): Response
    {
        return new IlluminateJsonResponse([], $statusCode);
    }
}
