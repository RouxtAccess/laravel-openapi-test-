<?php
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use ByJG\ApiTools\AssertRequestAgainstSchema;
use ByJG\ApiTools\Base\Schema;
use ByJG\ApiTools\Response\ResponseInterface;
use RouxtAccess\OpenApi\Testing\Laravel\LaravelRequester;


trait InteractsWithOpenApi
{
    use AssertRequestAgainstSchema;

    protected LaravelRequester $requester;
    protected ResponseInterface $response;
    protected array $responseBody;
    protected array $requestHeader;
    protected static Schema $cachedSchema;


    protected function setUpOpenApiTester(): void
    {
        $this->loadSchema();
        $this->requester = new LaravelRequester($this);
    }

    protected function loadSchema(): void
    {
        // Load only once, must be made in setup to be able to use base_path
        if (null !== $this->schema) {
            return;
        }

        // Load only once per phpunit instance
        if (!isset(self::$cachedSchema)) {
            self::$cachedSchema = Schema::getInstance($this->getSchemaContents());
        }

        // Set the schema
        $this->setSchema(self::$cachedSchema);
    }

    protected function getSchemaContents()
    {
        return file_get_contents(storage_path('api-docs/api-docs.json'));
    }

}
