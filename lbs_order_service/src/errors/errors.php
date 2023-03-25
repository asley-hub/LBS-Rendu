<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\order\utils\JsonResponse;

return [
    /**URL NOT FOUND 400 */
    'notFoundHandler' => function (\Slim\Container $c) {
        return function (Request $req, Response $res): Response {
            $args = [
                'type' => 'error',
                'error' => 400,
                'message' => $req->getUri() . " : Request not found"
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($req, $res, 400, $args);
        };
    },

    /**NOT ALLOWED METHOD (405) */
    'notAllowedHandler' => function (\Slim\Container $c) {
        return function (Request $req, Response $res, array $methods): Response {
            $res = $res->withStatus(405)
                ->withHeader('Content-Type', 'application/json')
                ->withHeader('Allow', implode(', ', $methods));
            $res->getBody()->write(json_encode([
                'type' => 'error',
                'error' => 405,
                'message' => "Method " . $req->getMethod() . " not allowed for uri " . $req->getUri() . " : Allowed methods " . implode(', ', $methods)
            ]));
            return $res;
        };
    },

    /**Internal Server Error (500) */
    'phpErrorHandler' => function (\Slim\Container $c) {
        return function (Request $req, Response $res, \Throwable $error): Response {
            $args = [
                'type' => 'error',
                'error' => 500,
                'message' => "Internal Server Error " . $error->getMessage(),
                'trace' => $error->getTraceAsString(),
                'file' => $error->getFile(),
                'line' => $error->getLine(),
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($req, $res, 500, $args);
        };
    }
];
