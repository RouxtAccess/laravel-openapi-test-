<?php

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

trait ImplementsOpenApiFunctions
{
    use InteractsWithOpenApi, ValidatesOpenApiRequest, SendsOpenApiRequest, ValidatesOpenApiResponse, AssertsOpenApiResponse;
}
