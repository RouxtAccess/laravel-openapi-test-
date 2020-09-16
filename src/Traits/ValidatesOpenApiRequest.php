<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertTrue;

trait ValidatesOpenApiRequest
{
    use InteractsWithOpenApi;

    protected function validateRequest() : self
    {
        if(!isset($this->requester))
        {
            throw new \RuntimeException('Requester is not instantiated. Have you incorrectly overridden the setUp method?');
        }

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
