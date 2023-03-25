<?php

namespace lbs\order\actions\order;


use lbs\order\services\utils\CommandeService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

final class GetOrderItemsByIdAction
{
    public function __invoke(Request $request, Response $response, array $args): Response
    {
        try{
            $commande = new CommandeService();
            $items = $commande->getOrderItems($args['id']);
        } catch (OrderExceptionNotFound $e) {
            throw new HttpNotFoundException($request, $e->getMessage());
        }

        $data = [
            'type' => 'collection',
            'count' => count($items),
            'items' => $items,

        ];

        $response = $response->withHeader('Content-type', 'application/json;charset=utf-8')->withStatus(200);
        $response->getBody()->write(json_encode($data));

        return $response;
    }
}
