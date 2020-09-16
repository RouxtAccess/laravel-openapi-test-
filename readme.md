# Laravel OpenApi Test

> Underlying logic uses [PHP Swagger Test](https://github.com/byjg/php-swagger-test) from [byjg](https://github.com/byjg)

> This was based upon [Laravel Swagger Test](https://github.com/pion/laravel-swagger-test) from [pion](https://github.com/pion)


Test your routes using Laravel's underlying request testing against your API schema.

## Support

> How to make tests and which OpenAPI is supported check the [PHP Swagger Test](https://github.com/byjg/php-swagger-test).
 
 At the time of writing this readme OpenAPI 3 is partially supported.
 
 ## Install
 
 1. Add a custom repository for php-swagger-test with internal improvements. (In future it could be merged).
 
    ```
    "repositories": [
        {
            "type": "git",
            "url": "https://github.com/rouxtaccess/laravel-openapi-test"
        }
    ]
    ```
 2. Require the package
    
    ```
    compsoer require pion/laravel-swagger-test
    ```
 
 ## Usage
 
 Use the Laravel's TestCase and add the `ImplementsOpenApiFunctions` trait.
 
 Add `$this->setUpOpenApiTester();` to your test's setUp function
 
 Uses same "request building" as `ApiRequester`. For more details check the [PHP Swagger Test](https://github.com/byjg/php-swagger-test).
 
 For validation and testing, there are methods for `validateRequest()`, `validateRequestFails()`, `sendRequest()`, `validateResponse(Response::HTTP_OK);`
 
 See example below:
 
 ```php
<?php

namespace Tests\Feature\Api;

use App\User;
use RouxtAccess\OpenApi\Testing\Laravel\Traits\ImplementsOpenApiFunctions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use ImplementsOpenApiFunctions;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->setUpOpenApiTester();
    }

    public function testLoginWithoutDetails()
    {
        $this->requester->withMethod('POST')
            ->withPath('/api/auth/login');

        $this->validateRequestFails()
            ->sendRequest()
            ->validateResponse(Response::HTTP_UNPROCESSABLE_ENTITY);

        self::assertArrayHasKey('email', $this->responseBody['errors']);
        self::assertArrayHasKey('password', $this->responseBody['errors']);
    }

    public function testLoginIncorrectDetails()
    {
        $this->requester->withMethod('POST')
            ->withPath('/api/auth/login')
            ->withRequestBody(['email' => 'not_a_real_users_email@notreal.com', 'password' => 'not_a_valid_password']);

        $this->validateRequestFails()
            ->sendRequest()
            ->validateResponse(Response::HTTP_UNAUTHORIZED);
    }

    public function testLoginSuccess()
    {
        $user = factory(User::class)->create(['name' => 'test-user', 'email' => 'testemail@example.com', 'password' => bcrypt('bestpassword')]);

        $this->requester->withMethod('POST')
            ->withPath('/api/auth/login')
            ->withRequestBody(['email' => $user->email, 'password' => 'bestpassword']);

        $this->validateRequest()
            ->sendRequest()
            ->validateResponse(Response::HTTP_OK);
    }
}

``` 
