<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware;

use Assert\InvalidArgumentException;
use Closure;
use Illuminate\Http\Request;
use MerchantOfComplexity\MessageMiddleware\Support\Concerns\HasBusFactory;
use MerchantOfComplexity\MessageMiddleware\Support\Contracts\RequestMessage;
use MerchantOfComplexity\MessageMiddleware\Support\Contracts\ResponseStrategy;
use MerchantOfComplexity\Messaging\Contracts\MessageFactory;
use React\Promise\PromiseInterface;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;
use function is_array;

class HttpMessageMiddleware
{
    use HasBusFactory;

    private ResponseStrategy $responseStrategy;
    private MessageFactory $messageFactory;

    public function __construct(ResponseStrategy $responseStrategy,
                                MessageFactory $messageFactory)
    {
        $this->responseStrategy = $responseStrategy;
        $this->messageFactory = $messageFactory;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function __invoke(Request $request)
    {
        $payload = $request->json()->all();

        $messageName = $this->detectMessageName($request, $payload);

        try {
            $message = $this->messageFactory->createMessageFromArray($messageName, $payload);

            $result = $this->dispatch($message);

            if ($result instanceof PromiseInterface) {
                return $this->responseStrategy->fromPromise($result);
            }

            return $this->responseStrategy->withStatus(Response::HTTP_ACCEPTED);

        } catch (InvalidArgumentException $assertException) {
            throw new RuntimeException(
                $assertException->getMessage(), Response::HTTP_BAD_REQUEST, $assertException
            );
        } catch (Throwable $exception) {
            throw new RuntimeException(
                "An error occurred during dispatch of message $messageName",
                Response::HTTP_INTERNAL_SERVER_ERROR,
                $exception
            );
        }
    }

    protected function detectMessageName(Request $request, $payload): string
    {
        $messageName = 'invalid_message_name';
        $messageNameKey = RequestMessage::MESSAGE_PAYLOAD_KEY;

        if (is_array($payload) && isset($payload[$messageNameKey])) {
            $messageName = $payload[$messageNameKey];
        }

        return $request->get($messageNameKey, $messageName);
    }
}
