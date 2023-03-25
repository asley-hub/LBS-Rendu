<?php

namespace lbs\order\actions\order;

use lbs\order\services\utils\CommandeService;
use orders\errors\exceptions\OrderExceptionNotFound;
use Slim\Routing\RouteContext;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

final class GetOrderByIdAction{

    public function __invoke(Request $request, Response $response , array $args): Response
    {

        try {

            $embed = $request->getQueryParams()['embed']?? null;


            $commande = new CommandeService();
            $route = RouteContext::fromRequest($request)->getRouteParser();
            $order = $commande->getOrdersById($args['id'],$embed);
        } catch (OrderExceptionNotFound $e) {
            throw new HttpNotFoundException($request, $e->getMessage());
        }

        $data = [
            'type' => 'resource',
            'order' => $order,
            'links' => [
                'items' =>[
                    'href' => $route->urlFor('getOrderItemsById', ['id'=>$order['id']])
                ],
                'self' =>[
                    'href' => $route->urlFor('getOrderById', ['id'=>$order['id']])
                ]
            ]
        ];

        $response = $response->withHeader('Content-type', 'application/json;charset=utf-8')->withStatus(202);
        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
