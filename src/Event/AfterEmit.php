<?php

declare(strict_types=1);

namespace Yiisoft\Yii\Http\Event;

use Psr\Http\Message\ResponseInterface;

final class AfterEmit
{
    public function __construct(private ?\Psr\Http\Message\ResponseInterface $response)
    {
    }

    public function getResponse(): ?ResponseInterface
    {
        return $this->response;
    }
}
