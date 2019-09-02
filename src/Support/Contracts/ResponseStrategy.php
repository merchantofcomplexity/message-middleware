<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware\Support\Contracts;

use React\Promise\PromiseInterface;
use Symfony\Component\HttpFoundation\Response;

interface ResponseStrategy
{
    public function fromPromise(PromiseInterface $promise): Response;

    public function withStatus(int $statusCode): Response;
}
