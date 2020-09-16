<?php

namespace RouxtAccess\OpenApi\Testing\Laravel;

use ByJG\ApiTools\AbstractRequester;
use ByJG\ApiTools\Exception\DefinitionNotFoundException;
use ByJG\ApiTools\Exception\GenericSwaggerException;
use ByJG\ApiTools\Exception\HttpMethodNotFoundException;
use ByJG\ApiTools\Exception\InvalidDefinitionException;
use ByJG\ApiTools\Exception\InvalidRequestException;
use ByJG\ApiTools\Exception\NotMatchedException;
use ByJG\ApiTools\Exception\PathNotFoundException;
use ByJG\ApiTools\Exception\StatusCodeNotMatchedException;
use ByJG\Util\Helper\RequestJson;
use ByJG\Util\Psr7\MessageException;
use ByJG\Util\Uri;
use Illuminate\Support\Str;
use JsonException;
use Psr\Http\Message\RequestInterface;
use Illuminate\Http\Response;
use Tests\TestCase;

class LaravelRequester extends AbstractRequester
{
    protected Response $response;
    protected TestCase $testCase;

    /**
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection MagicMethodsValidityInspection
     * @param  TestCase  $testCase
     * @throws MessageException
     */
    public function __construct(TestCase $testCase)
    {
        $this->withPsr7Request(RequestJson::build(new Uri("/"), 'get', "[]"));
        $this->testCase = $testCase;
    }


    /**
     * @param  RequestInterface  $request
     * @return Response
     * @throws JsonException
     */
    protected function handleRequest(RequestInterface $request) : Response
    {
        $testResponse = $this->testCase->json(
            $request->getMethod(),
            (string) $request->getUri(),
            json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR),
            $this->getServerHeaders($request),
        );
        return $testResponse->baseResponse;
    }


    /**
     * @param  RequestInterface  $request
     * @return array
     */
    protected function getServerHeaders(RequestInterface $request) : array
    {
        // Convert headers to server headers
        return collect($request->getHeaders())->mapWithKeys(function ($value, $name) {
            $name = str_replace('-', '_', strtoupper($name));
            return [$this->formatServerHeaderKey($name) => $value];
        })->all();
    }
    /**
     * Format the header name for the server array.
     *
     * @param string $name
     *
     * @return string
     */
    protected function formatServerHeaderKey($name): string
    {
        if ($name !== 'CONTENT_TYPE' && $name !== 'REMOTE_ADDR' && !Str::startsWith($name, 'HTTP_')) {
            return 'HTTP_'.$name;
        }

        return $name;
    }

    /**
     * @return Response
     * @throws JsonException
     */
    public function send() : Response
    {
        // Process URI based on the OpenAPI schema
        $uriSchema = new Uri($this->schema->getServerUrl());

        if (empty($uriSchema->getScheme())) {
            $uriSchema = $uriSchema->withScheme($this->psr7Request->getUri()->getScheme());
        }

        if (empty($uriSchema->getHost())) {
            $uriSchema = $uriSchema->withHost($this->psr7Request->getUri()->getHost());
        }

        $uri = $this->psr7Request->getUri()
            ->withScheme($uriSchema->getScheme())
            ->withHost($uriSchema->getHost())
            ->withPort($uriSchema->getPort())
            ->withPath($uriSchema->getPath() . $this->psr7Request->getUri()->getPath());

        if (!preg_match("~^{$this->schema->getBasePath()}~",  $uri->getPath())) {
            $uri = $uri->withPath($this->schema->getBasePath() . $uri->getPath());
        }

        $this->psr7Request = $this->psr7Request->withUri($uri);
        // Handle Request
        $this->response = $this->handleRequest($this->psr7Request);

        return $this->response;
    }

    /**
     * @return bool
     * @throws InvalidRequestException
     * @throws JsonException
     * @throws HttpMethodNotFoundException
     * @throws PathNotFoundException
     */
    public function validateRequest(): bool
    {
        // Prepare Body to Match Against Specification
        $requestBody = $this->psr7Request->getBody();
        if ($requestBody !== null) {
            $requestBody = $requestBody->getContents();

            $contentType = $this->psr7Request->getHeaderLine("content-type");
            if (empty($contentType) || strpos($contentType, "application/json") !== false) {
                $requestBody = json_decode($requestBody, true, 512, JSON_THROW_ON_ERROR);
            } elseif (strpos($contentType, "multipart/") !== false) {
                $requestBody = $this->parseMultiPartForm($contentType, $requestBody);
            } else {
                throw new InvalidRequestException("Cannot handle Content Type '{$contentType}'");
            }
        }

        // Check if the body is the expected before request
        $bodyRequestDef = $this->schema->getRequestParameters($this->psr7Request->getUri()->getPath(), $this->psr7Request->getMethod());
        $bodyRequestDef->match($requestBody);
        return true;
    }

    /**
     * @param  Response  $response
     * @param  null  $status
     * @return bool
     * @throws JsonException
     * @throws NotMatchedException
     * @throws StatusCodeNotMatchedException
     * @throws DefinitionNotFoundException
     * @throws GenericSwaggerException
     * @throws HttpMethodNotFoundException
     * @throws InvalidDefinitionException
     * @throws PathNotFoundException
     */
    public function validateResponse(Response $response, $status = null): bool
    {
        $responseHeaderBag = $response->headers;
        $responseBodyStr = (string) $response->getContent();
        $responseBody = json_decode($responseBodyStr, true, 512, JSON_THROW_ON_ERROR);
        $statusReturned = $response->getStatusCode();

        // Assert results
        if ($status !== $statusReturned) {
            throw new StatusCodeNotMatchedException(
                "Status code not matched: Expected {$status}, got {$statusReturned}",
                $responseBody
            );
        }

        $bodyResponseDef = $this->schema->getResponseParameters(
            $this->psr7Request->getUri()->getPath(),
            $this->psr7Request->getMethod(),
            $status
        );
        $bodyResponseDef->match($responseBody);

        foreach ($this->assertHeader as $key => $value) {
            if ($responseHeaderBag->get($key)) {
                throw new NotMatchedException(
                    "Does not exists header '$key' with value '$value'",
                    $responseHeaderBag->all(),
                );
            }
        }

        if (!empty($responseBodyStr)) {
            foreach ($this->assertBody as $item) {
                if (strpos($responseBodyStr, $item) === false) {
                    throw new NotMatchedException("Body does not contain '{$item}'");
                }
            }
        }
        return true;
    }
}
