<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Tests\Handler;

use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Yii\Http\Handler\UnhandledRequestHandler;

final class UnhandledRequestHandlerTest extends TestCase
{
    public function testBase(): void
    {
        $handler = new UnhandledRequestHandler();
        $request = new ServerRequest();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No response was generated.');
        $handler->handle($request);
    }
}
