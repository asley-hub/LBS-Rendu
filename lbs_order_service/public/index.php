<?php
declare(strict_types=1);
use Slim\Factory\AppFactory;
use \Illuminate\Database\Capsule\Manager as Capsule;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\order\middleware\Middleware;
use DI\Container;

require_once __DIR__ . '/../vendor/autoload.php';
$settings = require_once __DIR__ . '/settings.php';

$config = parse_ini_file("../conf/order.db.conf.ini");

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

 //$app->put('/orders/{id}[/]', OrderUpdateAction::class)->setName('updateOrder');

// $app->get("/hello/{name}[/]", function(Request $rq, Response $rs, array $args):Response {
//     $name = $args['name'];
//     // $dbfile=$this->get('settings')['dbfile'];
//     $rs->getBody()->write("<h1>Hello</h1>");
//     return $rs;
// });

    // use lbs\order\models\Commande;
    // $app->get("/orders", '\lbs\order\controller\CommandeController:getAllCommande');
    // $app->get("/orders/{id}[/]", '\lbs\order\controller\CommandeController:oneCommande')->setName('commande');
    // $app->get("/sayHello", '\lbs\order\controller\CommandeController:sayHello');
    // $app->get("/listCommands", '\lbs\order\controller\CommandeController:listCommands');
    // $app->get("/commandes/{id}/items", '\lbs\order\controller\CommandeController:commandItems')->setName('commandItems');;
    $app->get('/orders[/]', lbs\order\actions\order\GetOrderAction::class)->setName('getOrders');
        $app->get('/orders/{id}[/]', lbs\order\actions\order\GetOrderByIdAction::class)->setName('getOrderById');
        $app->get('/orders/{id}/items[/]', lbs\order\actions\order\GetOrderItemsByIdAction::class)->setName('getOrderItemsById');
        $app->post('/orders[/]', lbs\order\actions\order\PostOrderAction::class)->setName('postOrder');
        $app->put('/orders/{id}[/]', lbs\order\actions\order\PutOrderByIdAction::class)->setName('putOrderById');
    
    // $app->get("/orders/{id}", function(Request $rq, Response $rs,$args):Response {
    //     $orders = [
    //                 [
    //                     "id" => "AuTR4-65ZTY",
    //                     "client_mail" => "jan.neymar@yaboo.fr",
    //                     "order_date" => "2022-01-05 12:00:23",
    //                     "total_amount" => 25.95
    //                 ],[
    //                     "id" => "657GT-I8G443",
    //                     "client_mail" => "jan.neplin@gmal.fr",
    //                     "order_date" => "2022-01-06 16:05:47",
    //                     "total_amount" => 42.95
    //                 ],[
    //                     "id" => "K9J67-4D6F5",
    //                     "client_mail" => "claude.francois@grorange.fr",
    //                     "order_date" => "2022-01-07 17:36:45",
    //                     "total_amount" => 14.95
    //                 ]
    //             ];
    //     $order = null;
    //     for ($i = 0; $i < count($orders); $i++) {
    //         if ($orders[$i]["id"] === $args["id"]) $order = $orders[$i];
    //     }
    //     $data = [
    //                 'type' => 'collection',
    //                 'count'=> count($orders),
    //                 'orders' => $order
    //             ];
       
    //     $rs = $rs->withStatus(201)
    //              ->withHeader('Content-Type','application/json');
    //     $rs->getBody()->write(json_encode($data));
    //     return $rs;
    // });
$app->run();
