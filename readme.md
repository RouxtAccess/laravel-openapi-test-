# Laravel OpenApi Test

> Underlying logic uses [PHP Swagger Test](https://github.com/byjg/php-swagger-test) from [byjg](https://github.com/byjg)
> Built for use with [L5-Swagger](https://github.com/DarkaOnLine/L5-Swagger/)
> This was based upon [Laravel Swagger Test](https://github.com/pionl/laravel-swagger-test) from [pionl](https://github.com/pionl)


Test your routes using Laravel's underlying request testing against your API schema.

## Support

> For how the assertions work against your documentation, please check the [PHP Swagger Test](https://github.com/byjg/php-swagger-test).
 
 Currently, this only supports json api's, it should be very easy to override any required functionality
 
 ## Install

 1. Require the package
    
    ```
    composer require --dev rouxtaccess/laravel-openapi-test
    ```
 
 ## Usage
 
 Use the Laravel's TestCase and add the `ImplementsOpenApiFunctions` trait.
 
 Add `$this->setUpOpenApiTester();` to your test's setUp function
 
 Uses same "request building" as `ApiRequester`. For more details check the [PHP Swagger Test](https://github.com/byjg/php-swagger-test).
 
 For validation and testing, there are methods for `validateRequest()`, `validateRequestFails()`, `sendRequest()`, `validateResponse(Response::HTTP_OK);`
 
 For asserting response data on top of the OpenApi required spec you can use the `assertResponseHas()` helper method
 See example below:
 
 ```php
<?php

namespace Tests\Feature\Api;

use App\User;
use RouxtAccess\OpenApi\Testing\Laravel\Traits\ImplementsOpenApiFunctions;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class AuthLoginTest extends TestCase
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

        $this->assertResponseHas('errors.email');
        $this->assertResponseHas('errors.password');
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
