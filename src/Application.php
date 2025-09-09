<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http;

use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Yii\Http\Event\AfterEmit;
use Yiisoft\Yii\Http\Event\AfterRequest;
use Yiisoft\Yii\Http\Event\ApplicationShutdown;
use Yiisoft\Yii\Http\Event\ApplicationStartup;
use Yiisoft\Yii\Http\Event\BeforeRequest;
use Yiisoft\Yii\Http\Handler\UnhandledRequestHandler;

/**
 * Application is the entry point for an HTTP application.
 *
 * For more details and usage information on `Application`, see the guide article on applications:
 *
 * @link https://github.com/yiisoft/docs/blob/master/guide/en/structure/application.md
 */
final class Application
{
    /**
     * @param MiddlewareDispatcher $dispatcher The middleware dispatcher instance.
     * @param EventDispatcherInterface|null $eventDispatcher The event dispatcher instance (optional).
     * @param RequestHandlerInterface $fallbackHandler The fallback handler that will be called
     * if no response was returned during request handling.
     */
    public function __construct(
        private readonly MiddlewareDispatcher $dispatcher,
        private readonly EventDispatcherInterface|null $eventDispatcher = null,
        private readonly RequestHandlerInterface $fallbackHandler = new UnhandledRequestHandler()
    ) {
    }

    /**
     * Dispatches an event {@see ApplicationStartup} to all relevant listeners for processing.
     */
    public function start(): void
    {
        $this->eventDispatcher?->dispatch(new ApplicationStartup());
    }

    /**
     * Dispatches an event {@see ApplicationShutdown} to all relevant listeners for processing.
     */
    public function shutdown(): void
    {
        $this->eventDispatcher?->dispatch(new ApplicationShutdown());
    }

    /**
     * Dispatches an event {@see AfterEmit} to all relevant listeners for processing.
     *
     * @param ResponseInterface|null $response Response instance or null if response generation failed due to an error.
     */
    public function afterEmit(?ResponseInterface $response): void
    {
        $this->eventDispatcher?->dispatch(new AfterEmit($response));
    }

    /**
     * Handles a request by passing it through the middleware stack {@see MiddlewareDispatcher} and returns a response.
     *
     * Dispatches {@see BeforeRequest} and {@see AfterRequest} events to all relevant listeners for processing.
     *
     * @param ServerRequestInterface $request The request instance to handle.
     *
     * @return ResponseInterface The resulting instance of the response.
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->eventDispatcher?->dispatch(new BeforeRequest($request));

        try {
            return $response = $this->dispatcher->dispatch($request, $this->fallbackHandler);
        } finally {
            $this->eventDispatcher?->dispatch(new AfterRequest($response ?? null));
        }
    }
}
