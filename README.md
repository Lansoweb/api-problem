# Api Problem Middleware for PHP

This middleware returns a formatted json in case of error. It's inspired on the [ApiProblem](https://github.com/zfcampus/zf-api-problem) library, but with fewer dependencies. 

## Usage

Just add the middleware as the last in your application.

For example:
```php
$app->pipe(new \LosMiddleware\ApiProblem\ApiProblem();
```

It will return:
```
{
  "type": "http://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html",
  "title": "Method Not Allowed",
  "status": 405,
  "detail": "error",
  "code": "14868ef1-7ef4-4feb-a7ae-9a12c9097375"
}
```

### Zend Expressive

If you are using [expressive-skeleton](https://github.com/zendframework/zend-expressive-skeleton), you can copy `config/los-api-problem.global.php.dist` to `config/autoload/los-api-problem.global.php`.

It uses the FinalHandler feature from Expressive. If you prefer to use other FinalHandler, you can manually add this middleware:

```php
return [
    'dependencies' => [
        'invokables' => [
            LosMiddleware\ApiProblem\ApiProblem::class => LosMiddleware\ApiProblem\ApiProblem::class,
        ],
    ],
    'middleware_pipeline' => [
        'error' => [
            'middleware' => [
                LosMiddleware\ApiProblem\ApiProblem::class, 
            ]
            'error' => true,
        ],
    ],
];    
``` 

But 404 errors will not be handled but this, only by the FinalHandler.
