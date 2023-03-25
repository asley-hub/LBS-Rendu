<?php

namespace lbs\order\utils;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class JsonResponse
{

    public function buildResp(Request $req, Response $res, int $code, array $args): Response
    {
        $res = $res->withStatus($code)->withHeader('Content-Type', 'application/json');
        $res->getBody()->write(json_encode($args));
        return $res;
    }
}
