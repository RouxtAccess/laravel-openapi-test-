<?php

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use JsonException;

trait SendsOpenApiRequest
{
    use InteractsWithOpenApi;

    /**
     * @return $this
     * @throws JsonException
     */
    protected function sendRequest() : self
    {
        $this->checkRequesterIsInstantiated();
        $this->response = $this->requester->send();

        return $this;
    }
}
