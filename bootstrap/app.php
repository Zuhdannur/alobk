<?php

require_once __DIR__ . '/../vendor/autoload.php';

(new Laravel\Lumen\Bootstrap\LoadEnvironmentVariables(
    dirname(__DIR__)
))->bootstrap();

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| Here we will load the environment and create the application instance
| that serves as the central piece of this framework. We'll use this
| application as an "IoC" container and router for this framework.
|
*/

$app = new Laravel\Lumen\Application(
    dirname(__DIR__)
);

$app->withFacades();

$app->withEloquent();


$app->configure('cors');
/*
|--------------------------------------------------------------------------
| Register Container Bindings
|--------------------------------------------------------------------------
|
| Now we will register a few bindings in the service container. We will
| register the exception handler and the console kernel. You may add
| your own bindings here if you like or you can make another file.
|
*/

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->register(Flipbox\LumenGenerator\LumenGeneratorServiceProvider::class);



/*
|--------------------------------------------------------------------------
| Register Middleware
|--------------------------------------------------------------------------
|
| Next, we will register the middleware with the application. These can
| be global middleware that run before an   d after each request into a
| route or middleware that'll be assigned to some specific routes.
|
*/
$app->middleware([
    App\Http\Middleware\ExampleMiddleware::class,
    \Barryvdh\Cors\HandleCors::class,
]);

$app->routeMiddleware([
    'auth' => App\Http\Middleware\Authenticate::class
]);

/*
|--------------------------------------------------------------------------
| Register Service Providers
|--------------------------------------------------------------------------
|
| Here we will register all of the application's service providers which
| are used to bind services into the container. Service providers are
| totally optional, so you are not required to uncomment this line.
|
*/
class_alias('Berkayk\OneSignal\OneSignalFacade', 'OneSignals');
class_alias(SafeStudio\Firebase\Facades\FirebaseFacades::class, 'Firebase');
class_alias(Barryvdh\DomPDF\Facade::class, 'PDF');
$app->register(SafeStudio\Firebase\FirebaseServiceProvider::class);
$app->register(App\Providers\AppServiceProvider::class);    
$app->register(Berkayk\OneSignal\OneSignalServiceProvider::class);
$app->register(App\Providers\AuthServiceProvider::class);
$app->register(Orumad\ConfigCache\ServiceProviders\ConfigCacheServiceProvider::class);
$app->register(App\Providers\EventServiceProvider::class);
$app->register(Barryvdh\Cors\ServiceProvider::class);
//App\Providers\BroadcastServiceProvider::class,
$app->register(\Illuminate\Broadcasting\BroadcastServiceProvider::class);
$app->register(\Barryvdh\DomPDF\ServiceProvider::class);
$app->configure('broadcasting');
$app->configure('services');
$app->configure('dompdf');

// $app->register('broadcasting');
/*|--------------------------------------------------------------------------
| Load The Application Routes
|--------------------------------------------------------------------------
|
| Next we will include the routes file so that they can all be added to
| the application. This will provide all of the URLs the application
| can respond to, as well as the controllers that may handle them.
|
*/

$app->router->group([
    'namespace' => 'App\Http\Controllers',
], function ($router) {
    require __DIR__ . '/../routes/web.php';
});

return $app;
