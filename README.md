# SmartController

SmartController is a controller you can use with League/Route.

Features:
- `Request` and `Response` instances are injected into the controller
- Route parameters are injected as well
- You may define a default namespace for you controllers
- A basic URL builder lets you create a URL to a named route

## Installation

Using Composer, just:

```shell
composer require xocotlah/smart-controller ^1.0.0-alpha
```

Until 1.0.0 stable is released, you need to add the following lines to your `composer.json`:

```json
    "minimum-stability": "alpha",
    "prefer-stable": true,
```

If you already have `"minimum-stability": "dev"`, leave it.

## Request and Response injection

The easiest way to have `Request` and `Response` instances injected into your `SmartController`s is to use the `ControllerFactory`.

For that, it has to be a delegate of `League/Container`:

```php
$factory = (new ControllerFactory);
$container = new League\Container\Container;
$container->delegate($factory);
```

If you don't use League/Container, you need to use a PSR-11 compliant container that accepts *delegate* containers. Such container are also known as *composite containers* (actually, I have no idea which ones offer this feature other than `League/Container`).

Henceforth, having `Request` and `Response` instances injected is as easy as writing this:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class HomeController extends SmartController
{
    public function showHomePage()
    {
        $this->response->getBody()->write('Welcome home!');
        return $this->response;
    }
}
```

You don't need anything else, just define your route as you did before:

```php
$routeCollection->get('/', 'App\Http\Controllers\HomeController::showHomePage');
```

To access the route parameters, you still need to use the controller action arguments:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class WelcomeController extends SmartController
{
    public function sayHello($request, $response, $params)
    {
        $this->response->getBody()->write(sprintf('Welcome %s!', $params['name']));
        return $this->response;
    }
}
```

Yeah, I know, I promised a default namespace, route parameters injection and a lazy return from the controller... Be patient, and read further ;)

You can achieve this without the ControllerFactory. You need to configure the Container so that it can inject the Request, Response and RouteCollection instances into the SmartController constructor. Here is an example with `League/Container`, you may adapt it for any other PSR-11 Container:

```php
$container = new League\Container\Container;
$container->add('Psr\Http\Message\ServerRequestInterface', function () {
    // Return some implementation of Psr\Http\Message\ServerRequestInterface
});
$container->add('Psr\Http\Message\ResponseInterface', function () {
    // Return some implementation of Psr\Http\Message\ResponseInterface
})
$container->add('League\Route\RouteCollection', function () use ($container) {
    return new League\Route\RouteCollection($container);
})
```

From now, when a `SmartController` will be instanciated, the appropriate objects will be injected into its constructor.

## URL builder

For your convenience, the `SmartController` class makes it easier to generate a URL to a named route:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class UserController extends SmartController
{
    public function showProfile()
    {
        return view('user.profile', 'editUrl' => $this->url('profile.edit', ['userId', $this->params['userId']]));
    }
}
```

In the example above, `url()` method may build a url like `/admin/users/edit/42` provided that the `profile.edit` route has been defined like this:

```php
$routeCollection->get('/admin/users/edit/{id}', 'UserController::editProfile')->name('profile.edit');
```

> *The URL builder does not handle optional parts.*

## Default controllers namespace

To set the default namespace for your controllers, using the `ControllerFactory` is mandatory:

```php
$factory = (new ControllerFactory)->setDefaultNamespace('App\\Http\\Controllers\\');
$container = new League\Container\Container;
$container->delegate($factory);
```

Make sure you don't forget the trailing escaped backslash, since the prefix is simply concatenated with the class name to resolve it.

Now, let's see the previous route definition with default namespace set:

```php
$routeCollection->get('/', 'HomeController::showHomePage']);
```

Of course, you may use controllers from another namespace. Just use their FQCN:

```
$routeCollection->get('/example', 'Module\Foo\Http\BarController::doSomething');
```

Actually, the `ControllerFactory` tries to instanciate the controller without then with the prefix.

## The SmartStrategy

The `SmartStrategy` injects the Route parameters into your controller and allows you to lazily return the content for the response: it will write it for you.

Set the `SmartStrategy` on a single route, on a group or globally:

```php
// On a route:
$routeCollection->get('/', 'HomeController::showHomePage')->setStrategy(new SmartStrategy);
```
See [League/Route:Strategies](http://route.thephpleague.com/strategies/) for more details.

### Lazy return

Now, with the `SmartStrategy`, you can write your controller like this:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class HomeController extends SmartController
{
    public function showHomePage()
    {
        return 'Welcome home!';
    }
}
```

Easy.

### Route parameters

If you want to access the parameters, just read them, they have been injected:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class WelcomeController extends SmartController
{
    public function sayHello()
    {
        return sprintf('Welcome %s!', $this->params['name']);
    }
}
```

Nicer, right? You may use a method to format the content, build a view...

### Smartness

The `SmartStrategy` is actually smart. What if you are really lazy and want to just return a view object? If you View class has a `__toString()` method, it's as easy as returning a string:

```php
<?php

namespace App\Http\Controllers;

use Xocotlah\SmartController\SmartController;

class WelcomeController extends SmartController
{
    public function sayHello()
    {
        return $this->view('HelloPage', $this->params);
    }

    protected function view($name, array $params)
    {
        // Build your view object
        return $view;
    }
}
```

Yeah, that's all...

If you need to return JSON and are still lazy, just return an array:

```php
<?php

namespace App\Http\Controllers\Api;

use Xocotlah\SmartController\SmartController;

class UsersController extends SmartController
{
    public function list()
    {
        return Users::all();
    }
}
```

The `SmartStrategy` will `json_encode()` the array and add the appropriate header to the response.

Of course, you can still override the `SmartStrategy` by applying another one on a single route or on a route group.
