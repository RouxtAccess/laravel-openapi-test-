<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

trait ValidatesOpenApiResponse
{
    use InteractsWithOpenApi;

    protected function validateResponse($status = null) : self
    {
        $this->checkResponseIsInstantiated();
        assertInstanceOf(Response::class, $this->response);
        assertTrue($this->requester->validateResponse($this->response, $status));

        return $this;
    }
}
