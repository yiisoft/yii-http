<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Tests\Handler;

use HttpSoft\Message\ServerRequest;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Yiisoft\Yii\Http\Handler\ThrowableHandler;

final class ThrowableHandlerTest extends TestCase
{
    public function testHandle(): void
    {
        $exception = new RuntimeException('Some error.', 0);
        $handler = new ThrowableHandler($exception);

        $this->expectException($exception::class);
        $this->expectExceptionCode($exception->getCode());
        $this->expectExceptionMessage($exception->getMessage());

        $handler->handle(new ServerRequest());
    }
}
