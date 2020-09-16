<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use ByJG\ApiTools\Response\ResponseInterface;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

trait ValidatesOpenApiResponse
{
    use InteractsWithOpenApi;

    protected function validateResponse($status = null) : self
    {
        if(!isset($this->response))
        {
            throw new \RuntimeException('Request needs to be sent before it can be validated. See function [sendRequest]');
        }
        assertInstanceOf(ResponseInterface::class, $this->response);
        assertTrue($this->requester->validateResponse($this->response, $status));

        return $this;
    }
}
