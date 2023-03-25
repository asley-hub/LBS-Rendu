<?php

namespace lbs\order\actions\order;

use lbs\order\services\utils\CommandeService;
use lbs\order\errors\exceptions\OrderExceptionNotFound;
use Slim\Routing\RouteContext;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;


final class GetOrderAction
{
    const PAGE_SIZE = 10;

    public function __invoke(Request $request, Response $response, array $args): Response
    {


        $client = $request->getQueryParams()['c'] ?? null;
        $sort = $request->getQueryParams()['sort'] ?? null;
        $page = $request->getQueryParams()['page'] ?? 1;

        try {
            $commande = new CommandeService();
            $orders = $commande->getOrders($client,$sort);
        } catch (OrderExceptionNotFound $e) {
            throw new HttpNotFoundException($request, $e->getMessage());
        }



        $count = count($orders);
        $lastPage = ceil($count/self::PAGE_SIZE);


        if ($page < 1)
            $page = 1;

        if ($page > $lastPage)
            $page = ceil($count/self::PAGE_SIZE);

        $data_pagination =  array_slice($orders, ($page-1)*self::PAGE_SIZE, self::PAGE_SIZE);



        $orders_data = [];
        $route = RouteContext::fromRequest($request)->getRouteParser();

        $size = 0;
        foreach ($data_pagination as $order) {
            $size++;
            $orders_data[] = ['order' => $order,
                'links' => [
                    'self' => ['href' => $route->urlFor('getOrderById', ['id' => $order['id']])],
                    'items' => ['href' => $route->urlFor('getOrderItemsById', ['id' => $order['id']])]
                ]
            ];
        }


        $data = [
            'type' => 'collection',
            'count' => $count,
            'size' => $size,
            'orders' => $orders_data,
            'links' => [
                'self' => ['href' => $route->urlFor('getOrders',[], ['page' => $page])],
                'next' => ['href' => $route->urlFor('getOrders',[], ['page' => $page + 1 >= $lastPage ? 1 : $page + 1 ])],
                'prev' => ['href' => $route->urlFor('getOrders',[], ['page' => $page - 1 <= 1 ? $lastPage : $page - 1])],
                'first' => ['href' => $route->urlFor('getOrders',[], ['page' => 1])],
                'last' => ['href' => $route->urlFor('getOrders',[], ['page' => $lastPage])],
            ]
        ];





        $response = $response->withHeader('Content-type', 'application/json;charset=utf-8')->withStatus(202);
        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
