<?php

namespace lbs\order\middleware;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\command\app\utils\JsonResponse;

class Middleware
{
    public function checkCommdToken(Request $req, Response $res, callable $next)
    {
        //Get token from header
        $token = $req->getHeader('X-lbs-token');

        if ($token == null) {
            //Get token from url
            if (isset($req->getQueryParams()['token'])) {
                $token = $req->getQueryParams()['token'];
            } else {
                $data = [
                    'type' => 'error',
                    'error' => 403,
                    'message' => "Access forbidden! please insert valid token"
                ];

                //Build response
                $jsonResp = new JsonResponse;
                return $jsonResp->buildResp($req, $res, 403, $data);
            }
        }

        //Pass token to next request (commandController->function?)
        $req = $req->withAttribute('token', $token);
        $res = $next($req, $res);
        return $res;
    }
}
