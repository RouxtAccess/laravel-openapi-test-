<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use ByJG\ApiTools\Exception\GenericSwaggerException;
use ByJG\ApiTools\Exception\HttpMethodNotFoundException;
use ByJG\ApiTools\Exception\InvalidRequestException;
use ByJG\ApiTools\Exception\PathNotFoundException;
use JsonException;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

trait ValidatesOpenApiRequest
{
    use InteractsWithOpenApi;

    /**
     * @return $this
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidRequestException
     * @throws PathNotFoundException
     * @throws JsonException
     */
    protected function validateRequest() : self
    {
        $this->checkRequesterIsInstantiated();

        // Add own schema if nothing is passed.
        if (!$this->requester->hasSchema()) {
            $this->checkSchema();
            $this->requester->withSchema($this->schema);
        }
        // Preparing Header
        if (empty($this->requestHeader)) {
            $this->requestHeader = [];
        }

        assertTrue($this->requester->validateRequest());

        return $this;
    }

    /**
     * @return $this
     * @throws GenericSwaggerException
     */
    protected function validateRequestFails() : self
    {
        // Add own schema if nothing is passed.
        if (!$this->requester->hasSchema()) {
            $this->checkSchema();
            $this->requester->withSchema($this->schema);
        }
        // Preparing Header
        if (empty($this->requestHeader)) {
            $this->requestHeader = [];
        }
        try {
            assertFalse($this->requester->validateRequest());
            return $this;
        }
        catch(\Exception $exception)
        {
            return $this;
        }
    }
}
