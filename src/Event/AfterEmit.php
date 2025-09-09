<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Event;

use Psr\Http\Message\ResponseInterface;

final class AfterEmit
{
    public function __construct(
        public readonly ResponseInterface|null $response,
    ) {
    }

    /**
     * @deprecated Use readonly property instead.
     */
    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
