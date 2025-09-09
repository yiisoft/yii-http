<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Event;

use Psr\Http\Message\ResponseInterface;

final class AfterRequest
{
    /**
     * @param ResponseInterface|null $response Response instance or null if response generation failed due to an error.
     */
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
