<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Tests;

use Exception;
use HttpSoft\Message\ResponseFactory;
use HttpSoft\Message\Response;
use HttpSoft\Message\ServerRequestFactory;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\Http\Method;
use Yiisoft\Http\Status;
use Yiisoft\Middleware\Dispatcher\Event\AfterMiddleware;
use Yiisoft\Middleware\Dispatcher\Event\BeforeMiddleware;
use Yiisoft\Middleware\Dispatcher\MiddlewareDispatcher;
use Yiisoft\Middleware\Dispatcher\MiddlewareFactory;
use Yiisoft\Test\Support\Container\SimpleContainer;
use Yiisoft\Test\Support\EventDispatcher\SimpleEventDispatcher;
use Yiisoft\Yii\Http\Event\AfterEmit;
use Yiisoft\Yii\Http\Event\AfterRequest;
use Yiisoft\Yii\Http\Event\ApplicationShutdown;
use Yiisoft\Yii\Http\Event\ApplicationStartup;
use Yiisoft\Yii\Http\Event\BeforeRequest;
use Yiisoft\Yii\Http\Application;
use Yiisoft\Yii\Http\Handler\NotFoundHandler;

final class ApplicationTest extends TestCase
{
    public function testStartMethodDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $this
            ->createApplication($eventDispatcher)
            ->start();
        $this->assertSame([ApplicationStartup::class], $eventDispatcher->getEventClasses());
    }

    public function testShutdownMethodDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $this
            ->createApplication($eventDispatcher)
            ->shutdown();
        $this->assertSame([ApplicationShutdown::class], $eventDispatcher->getEventClasses());
    }

    public function testAfterEmitMethodDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $this
            ->createApplication($eventDispatcher)
            ->afterEmit(null);
        $this->assertSame([AfterEmit::class], $eventDispatcher->getEventClasses());
        $this->assertNull($eventDispatcher
            ->getEvents()[0]
            ->getResponse());
    }

    public function testAfterEmitMethodWithResponseDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $this
            ->createApplication($eventDispatcher)
            ->afterEmit(new Response());
        $this->assertSame([AfterEmit::class], $eventDispatcher->getEventClasses());
        $this->assertInstanceOf(Response::class, $eventDispatcher
            ->getEvents()[0]
            ->getResponse());
    }

    public function testHandleMethodDispatchEvents(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $response = $this
            ->createApplication($eventDispatcher, Status::NOT_FOUND)
            ->handle($this->createRequest());

        $this->assertSame(
            [
                BeforeRequest::class,
                BeforeMiddleware::class,
                AfterMiddleware::class,
                AfterRequest::class,
            ],
            $eventDispatcher->getEventClasses(),
        );

        $this->assertSame(Status::NOT_FOUND, $response->getStatusCode());
    }

    public function testHandleMethodWithExceptionDispatchEvents(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();

        try {
            $this
                ->createApplication($eventDispatcher, Status::OK, true)
                ->handle($this->createRequest());
        } catch (Exception) {
        }

        $this->assertSame(
            [
                BeforeRequest::class,
                BeforeMiddleware::class,
                AfterMiddleware::class,
                AfterRequest::class,
            ],
            $eventDispatcher->getEventClasses(),
        );
    }

    public function testBeforeAndAfterRequestWithResponseDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();
        $this
            ->createApplication($eventDispatcher)
            ->handle($this->createRequest());
        $this->assertCount(4, $eventDispatcher->getEvents());
        $this->assertInstanceOf(ServerRequestInterface::class, $eventDispatcher
            ->getEvents()[0]
            ->getRequest());
        $this->assertInstanceOf(ResponseInterface::class, $eventDispatcher
            ->getEvents()[3]
            ->getResponse());
    }

    public function testAfterRequestWithExceptionDispatchEvent(): void
    {
        $eventDispatcher = new SimpleEventDispatcher();

        try {
            $this
                ->createApplication($eventDispatcher, Status::OK, true)
                ->handle($this->createRequest());
        } catch (Exception) {
        }

        $this->assertCount(4, $eventDispatcher->getEvents());
        $this->assertNull($eventDispatcher
            ->getEvents()[3]
            ->getResponse());
    }

    private function createApplication(
        EventDispatcherInterface $eventDispatcher,
        int $responseCode = Status::OK,
        bool $throwException = false
    ): Application {
        if ($throwException === false) {
            $middlewareDispatcher = $this->createMiddlewareDispatcher(
                $this->createContainer($eventDispatcher),
                $responseCode,
            );
        } else {
            $middlewareDispatcher = $this->createMiddlewareDispatcherWithException(
                $this->createContainer($eventDispatcher)
            );
        }

        return new Application(
            $middlewareDispatcher,
            $eventDispatcher,
            new NotFoundHandler(new ResponseFactory())
        );
    }

    private function createMiddlewareDispatcher(
        ContainerInterface $container,
        int $responseCode = Status::OK
    ): MiddlewareDispatcher {
        return (new MiddlewareDispatcher(
            new MiddlewareFactory($container),
            $container->get(EventDispatcherInterface::class)
        )
        )->withMiddlewares([
            static fn () => new class ($responseCode) implements MiddlewareInterface {
                public function __construct(private int $responseCode)
                {
                }

                public function process(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $handler
                ): ResponseInterface {
                    return new Response($this->responseCode);
                }
            },
        ]);
    }

    private function createMiddlewareDispatcherWithException(ContainerInterface $container): MiddlewareDispatcher
    {
        return (new MiddlewareDispatcher(
            new MiddlewareFactory($container),
            $container->get(EventDispatcherInterface::class)
        )
        )->withMiddlewares([
            static fn () => new class () implements MiddlewareInterface {
                public function process(
                    ServerRequestInterface $request,
                    RequestHandlerInterface $handler
                ): ResponseInterface {
                    throw new Exception();
                }
            },
        ]);
    }

    private function createContainer(EventDispatcherInterface $eventDispatcher): ContainerInterface
    {
        return new SimpleContainer(
            [
                ResponseFactoryInterface::class => new ResponseFactory(),
                EventDispatcherInterface::class => $eventDispatcher,
            ]
        );
    }

    private function createRequest(): ServerRequestInterface
    {
        return (new ServerRequestFactory())->createServerRequest(Method::GET, 'https://example.com');
    }
}
