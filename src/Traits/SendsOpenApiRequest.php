<?php

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

trait SendsOpenApiRequest
{
    use InteractsWithOpenApi;

    protected function sendRequest() : self
    {
        if(!isset($this->requester))
        {
            throw new \RuntimeException('Requester is not instantiated. Have you incorrectly overridden the setUp method?');
        }
        $this->response = $this->requester->send();
        $this->responseBody = json_decode($this->response->getBody(), true, 512, JSON_THROW_ON_ERROR);

        return $this;
    }
}
