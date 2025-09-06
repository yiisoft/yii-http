<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Handler;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use RuntimeException;

/**
 * Handler that throws a `RuntimeException` when invoked.
 * Used as a default fallback handler when none is provided.
 */
final class UnhandledRequestHandler implements RequestHandlerInterface
{
    /**
     * @throws RuntimeException
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        throw new RuntimeException('No response was generated.');
    }
}
