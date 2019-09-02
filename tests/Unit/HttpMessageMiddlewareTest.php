<?php
declare(strict_types=1);

namespace MerchantOfComplexityTest\MessageMiddleware\Unit;

use Assert\InvalidArgumentException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use MerchantOfComplexity\MessageMiddleware\HttpMessageMiddleware;
use MerchantOfComplexity\MessageMiddleware\Support\Contracts\ResponseStrategy;
use MerchantOfComplexity\Messaging\Contracts\Message;
use MerchantOfComplexity\Messaging\Contracts\MessageFactory;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Bus\Messager;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Manager\ServiceBusManager;
use MerchantOfComplexityTest\MessageMiddleware\TestCase;
use React\Promise\Deferred;
use RuntimeException;
use Symfony\Component\HttpFoundation\ParameterBag;

class HttpMessageMiddlewareTest extends TestCase
{
    /**
     * @test
     */
    public function it_handle_command(): void
    {
        $payload = ['message_name' => 'foo',];

        $request = Request::create('/');

        $request->setJson(new ParameterBag($payload));

        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $busManager = $this->prophesize(ServiceBusManager::class);
        $messager = $this->prophesize(Messager::class);
        $message = $this->prophesize(Message::class);

        $message->messageType()->willReturn('command');
        $messager->dispatch($message);
        $busManager->command()->willReturn($messager);

        $expectedResponse = new JsonResponse(['foo']);
        $strategy->withStatus(202)->willReturn($expectedResponse);

        $messageFactory->createMessageFromArray('foo', $payload)->willReturn($message);

        $middleware = new HttpMessageMiddleware($strategy->reveal(), $messageFactory->reveal());
        $middleware->setServiceBusManager($busManager->reveal());
        $response = $middleware($request);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function it_handle_event(): void
    {
        $payload = ['message_name' => 'foo',];

        $request = Request::create('/');

        $request->setJson(new ParameterBag($payload));

        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $busManager = $this->prophesize(ServiceBusManager::class);
        $messager = $this->prophesize(Messager::class);
        $message = $this->prophesize(Message::class);


        $message->messageType()->willReturn('event');
        $messager->dispatch($message);
        $busManager->event()->willReturn($messager);

        $expectedResponse = new JsonResponse(['foo']);
        $strategy->withStatus(202)->willReturn($expectedResponse);

        $messageFactory->createMessageFromArray('foo', $payload)->willReturn($message);

        $middleware = new HttpMessageMiddleware($strategy->reveal(), $messageFactory->reveal());
        $middleware->setServiceBusManager($busManager->reveal());
        $response = $middleware($request);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     */
    public function it_handle_query(): void
    {
        $payload = ['message_name' => 'foo',];

        $request = Request::create('/');

        $request->setJson(new ParameterBag($payload));

        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $busManager = $this->prophesize(ServiceBusManager::class);
        $messager = $this->prophesize(Messager::class);
        $message = $this->prophesize(Message::class);

        $deferred = new Deferred();
        $deferred->resolve('baz');
        $promise = $deferred->promise();

        $message->messageType()->willReturn('query');
        $messager->dispatch($message)->willReturn($promise);
        $busManager->query()->willReturn($messager);

        $expectedResponse = new JsonResponse(['foo']);
        $strategy->fromPromise($promise)->willReturn($expectedResponse);

        $messageFactory->createMessageFromArray('foo', $payload)->willReturn($message);

        $middleware = new HttpMessageMiddleware($strategy->reveal(), $messageFactory->reveal());
        $middleware->setServiceBusManager($busManager->reveal());
        $response = $middleware($request);

        $this->assertEquals($expectedResponse, $response);
    }

    /**
     * @test
     * @expectedException RuntimeException
     * @expectedExceptionMessage foobar
     */
    public function it_raise_exception_on_assertion_message_error(): void
    {
        $payload = ['message_name' => 'foo',];

        $request = Request::create('/');

        $request->setJson(new ParameterBag($payload));

        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $message = $this->prophesize(Message::class);

        $message->messageType()->shouldNotBeCalled();
        $strategy->withStatus(202)->shouldNotBeCalled();

        $exception = new InvalidArgumentException("foobar", 500, 'baz', 'bar');
        $messageFactory->createMessageFromArray('foo', $payload)->willThrow($exception);

        $middleware = new HttpMessageMiddleware($strategy->reveal(), $messageFactory->reveal());
        $middleware($request);
    }

    /**
     * @test
     * @expectedExceptionMessage An error occurred during dispatch of message foo
     * @expectedException RuntimeException
     * @dataProvider provideExceptions
     */
    public function it_catch_any_throwable_exceptions($exception): void
    {
        $payload = ['message_name' => 'foo',];

        $request = Request::create('/');

        $request->setJson(new ParameterBag($payload));

        $strategy = $this->prophesize(ResponseStrategy::class);
        $messageFactory = $this->prophesize(MessageFactory::class);
        $message = $this->prophesize(Message::class);

        $message->messageType()->shouldNotBeCalled();
        $strategy->withStatus(202)->shouldNotBeCalled();

        $messageFactory->createMessageFromArray('foo', $payload)->willThrow($exception);

        $middleware = new HttpMessageMiddleware($strategy->reveal(), $messageFactory->reveal());
        $middleware($request);
    }

    public function provideExceptions(): array
    {
        return [
            [new \InvalidArgumentException("foobar")],
            [new RuntimeException("foobar")],
        ];
    }
}
