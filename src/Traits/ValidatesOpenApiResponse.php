<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use Illuminate\Http\Response;
use function PHPUnit\Framework\assertInstanceOf;
use function PHPUnit\Framework\assertTrue;

trait ValidatesOpenApiResponse
{
    use InteractsWithOpenApi;

    protected function validateResponse($status = null) : self
    {
        $this->checkRequesterIsInstantiated();
        assertInstanceOf(Response::class, $this->response);
        assertTrue($this->requester->validateResponse($this->response, $status));

        return $this;
    }
}
