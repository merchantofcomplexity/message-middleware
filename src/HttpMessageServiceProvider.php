<?php
declare(strict_types=1);

namespace MerchantOfComplexity\MessageMiddleware;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use MerchantOfComplexity\MessageMiddleware\Support\JsonResponse;
use MerchantOfComplexity\Messaging\Factory\FQCNMessageFactory;
use MerchantOfComplexity\ServiceBus\Support\Contracts\Manager\ServiceBusManager;

class HttpMessageServiceProvider extends ServiceProvider
{
    /**
     * @var bool
     */
    protected $defer = true;

    public function boot(Router $router): void
    {
        $router->aliasMiddleware('http-message', HttpMessageMiddleware::class);
    }

    public function register(): void
    {
        $this->app->bind(HttpMessageMiddleware::class, function (Application $app) {
            $middleware = new HttpMessageMiddleware(
                new JsonResponse(),
                new FQCNMessageFactory()
            );

            $middleware->setServiceBusManager($app->make(ServiceBusManager::class));

            return $middleware;
        });
    }

    public function provides(): array
    {
        return [HttpMessageMiddleware::class];
    }
}
