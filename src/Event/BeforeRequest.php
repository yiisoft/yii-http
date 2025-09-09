<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Event;

use Psr\Http\Message\ServerRequestInterface;

final class BeforeRequest
{
    public function __construct(
        public readonly ServerRequestInterface $request,
    ) {
    }

    /**
     * @deprecated Use readonly property instead.
     */
    public function getRequest(): ServerRequestInterface
    {
        return $this->request;
    }
}
