<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use \Illuminate\Database\Capsule\Manager as Capsule;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\order\middleware\Middleware;
use DI\Container;

require_once __DIR__ . '/../src/vendor/autoload.php';
//$settings = require_once __DIR__ . '/../settings.php';

$config = parse_ini_file(__DIR__ . "/../src/conf/auth.db.conf.ini");

$capsule = new Capsule;
$capsule->addConnection($config);
$capsule->setAsGlobal();
$capsule->bootEloquent();

$container = new \DI\Container();

AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false);

/**
 * configuring API Routes
 */

//Authenticate

$app->post(
    '/auth[/]',
    '\lbs\auth\controller\AuthController:authenticate'
)->setName('authenticate');

//Check authentification

$app->get(
    '/checkAuth[/]',
    '\lbs\auth\controller\AuthController:checkAuthentication'
)->setName('checkAuth');


$app->run();
