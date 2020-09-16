<?php

namespace RouxtAccess\OpenApi\Testing\Laravel;

use ByJG\ApiTools\AbstractRequester;
use ByJG\ApiTools\Exception\GenericSwaggerException;
use ByJG\ApiTools\Exception\NotMatchedException;
use ByJG\ApiTools\Exception\StatusCodeNotMatchedException;
use ByJG\ApiTools\Laravel\Response\LaravelResponse;
use ByJG\ApiTools\Response\ResponseInterface;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Support\Str;

class LaravelRequester extends AbstractRequester
{
    protected ResponseInterface $response;
    protected TestCase $testCase;

    public function __construct(TestCase $testCase)
    {
        parent::__construct();
        $this->testCase = $testCase;
        $this->withRequestHeader(['content-type' => 'application/json']);
    }

    protected function handleRequest($path, $headers)
    {
        $testResponse = $this->testCase->call(
            $this->method,
            $path,
            [],
            [],
            [],
            // Convert headers to server headers
            collect($headers)->mapWithKeys(function ($value, $name) {
                $name = strtr(strtoupper($name), '-', '_');

                return [$this->formatServerHeaderKey($name) => $value];
            })->all(),
            json_encode($this->requestBody)
        );
        return new LaravelResponse($testResponse->baseResponse);
    }


    /**
     * Format the header name for the server array.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatServerHeaderKey($name)
    {
        if (!Str::startsWith($name, 'HTTP_') && $name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR') {
            return 'HTTP_'.$name;
        }

        return $name;
    }

    public function send()
    {
        // Preparing Parameters
        $paramInQuery = null;
        if (!empty($this->query)) {
            $paramInQuery = '?' . http_build_query($this->query);
        }

        // Preparing Header
        if (empty($this->requestHeader)) {
            $this->requestHeader = [];
        }
        $header = array_merge(
            [
                'Accept' => 'application/json'
            ],
            $this->requestHeader
        );

        // Defining Variables
        $pathName = $this->path;

        $statusReturned = null;

        // Run the request
        $this->response = $this->handleRequest($pathName . $paramInQuery, $header);
        return $this->response;
    }

    public function validateRequest(): bool
    {
        $basePath = $this->schema->getBasePath();
        $pathName = $this->path;

        // Check if the body is the expected before request
        $bodyRequestDef = $this->schema->getRequestParameters("$basePath$pathName", $this->method);
        $bodyRequestDef->match($this->requestBody);
        return true;
    }

    public function validateResponse(ResponseInterface $response, $status = null): bool
    {
        // Defining Variables
        $basePath = $this->schema->getBasePath();
        $pathName = $this->path;

        // Get the response
        $responseHeader = $response->getHeaders();
        $responseBody = json_decode((string) $response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $statusReturned = $response->getStatusCode();

        // Assert results
        if ($status && $status !== $statusReturned) {
            throw new StatusCodeNotMatchedException(
                "Status code not matched: Expected {$status}, got {$statusReturned}",
                $responseBody
            );
        }

        $bodyResponseDef = $this->schema->getResponseParameters(
            "$basePath$pathName",
            $this->method,
            $status
        );
        $bodyResponseDef->match($responseBody);

        if (count($this->assertHeader) > 0) {
            foreach ($this->assertHeader as $key => $value) {
                if (!isset($responseHeader[$key]) || strpos($responseHeader[$key][0], $value) === false) {
                    throw new NotMatchedException(
                        "Does not exists header '$key' with value '$value'",
                        $responseHeader
                    );
                }
            }
        }
        return true;
    }

    /**
     * @throws GenericSwaggerException
     */
    protected function checkSchema(): void
    {
        if (!$this->schema) {
            throw new GenericSwaggerException('You have to configure a schema for either the request or the testcase');
        }
    }
}
