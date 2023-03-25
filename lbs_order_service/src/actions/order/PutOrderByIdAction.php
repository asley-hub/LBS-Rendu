<?php

namespace lbs\order\actions\order;


use orders\errors\exceptions\OrderExceptionNotFound;
use lbs\order\services\utils\CommandeService;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

class PutOrderByIdAction
{
    public function __invoke(Request $request,
                             Response $response , array $args): Response{
        $commande = new CommandeService();

        $body = $request->getParsedBody();

        try {
            $commande->orderUpdate($args['id'], $body);
        }catch ( OrderExceptionNotFound $e){
            throw new HttpNotFoundException($request, $e->getMessage());
        }

        return $response->withHeader('Content-type', 'application/json;charset=utf-8')->withStatus(204);
    }
}
