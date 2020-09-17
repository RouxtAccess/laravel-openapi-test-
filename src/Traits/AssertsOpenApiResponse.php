<?php
/** @noinspection PhpUnhandledExceptionInspection */
/** @noinspection PhpParamsInspection */

namespace RouxtAccess\OpenApi\Testing\Laravel\Traits;

use function PHPUnit\Framework\assertTrue;

trait AssertsOpenApiResponse
{
    use InteractsWithOpenApi;

    protected function assertResponseHas(string $property, string $delimiter = '.') : self
    {
        $this->checkResponseIsInstantiated();
        $results = explode($delimiter, $property);
        $property = array_pop($results);
        $class = $this->response->getData();
        if(!empty($results))
        {
            $class = $this->getNestedClass($class, $results);
        }
        assertTrue(property_exists($class, $property), "Request doesn't contain expected match: $property");
        return $this;
    }

    protected function getNestedClass($data, array $results)
    {
        $append = array_shift($results);
        assertTrue(property_exists($this->response->getData(), $append), "Request doesn't contain expected match: $append");
        if(!empty($results))
        {
            return $this->getNestedClass($data->$append, $results);
        }
        return $data->$append;
    }
}
