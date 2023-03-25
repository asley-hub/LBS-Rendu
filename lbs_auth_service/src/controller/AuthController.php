<?php

namespace lbs\auth\controller;

use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use lbs\auth\models\User;
use Firebase\JWT\ExpiredException;
use lbs\auth\utils\JsonResponse;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\SignatureInvalidException;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\Message\ServerRequestInterface as Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthController
{
    private $c;

    public function __construct(\Slim\Container $c)
    {
        $this->c = $c;
    }

    /**
     * Create authentification
     */
    public function authenticate(Request $req, Response $res, $args): Response
    {
        //Init json Response
        $jsonResp = new JsonResponse;

        if (!$req->hasHeader('Authorization')) {

            $res = $res->withHeader('WWW-authenticate', 'Basic realm="commande_api api" ');

            //Format response
            $data = [
                'type' => 'error',
                'error' => 401,
                'message' => 'No Authorization header present'
            ];
            return $jsonResp->buildResp($req, $res, 401, $data);
        };

        $authstring = base64_decode(explode(" ", $req->getHeader('Authorization')[0])[1]);
        list($email, $pass) = explode(':', $authstring);

        try {
            $user = User::select('id', 'email', 'username', 'passwd', 'refresh_token', 'level')
                ->where('email', '=', $email)
                ->firstOrFail();

            if (!password_verify($pass, $user->passwd))
                throw new \Exception("password check failed");

            unset($user->passwd);
        } catch (ModelNotFoundException $e) {
            $res = $res->withHeader('WWW-authenticate', 'Basic realm="lbs auth" ');
            //Format response
            $data = [
                'message' => 'Erreur authentification'
            ];
            return $jsonResp->buildResp($req, $res, 401, $data);
        } catch (\Exception $e) {
            $res = $res->withHeader('WWW-authenticate', 'Basic realm="lbs auth" ');
            //Format response
            $data = [
                'message' => 'Erreur authentification'
            ];
            return $jsonResp->buildResp($req, $res, 401, $data);
        }


        $secret = $this->c->settings['secret'];
        $token = JWT::encode(
            [
                'iss' => 'http://api.auth.local/auth',
                'aud' => 'http://api.backoffice.local',
                'iat' => time(),
                'exp' => time() + (12 * 30 * 24 * 3600),
                'upr' => [
                    'email' => $user->email,
                    'username' => $user->username,
                    'level' => $user->level
                ]
            ],
            $secret,
            'HS512'
        );

        $user->refresh_token = bin2hex(random_bytes(32));
        $user->save();
        $data = [
            'access-token' => $token,
            'refresh-token' => $user->refresh_token
        ];

        return $jsonResp->buildResp($req, $res, 200, $data);
    }

    /**
     * Check authentification
     */
    public function checkAuthentication(Request $req, Response $res, $args): Response
    {
        //Init json Response
        $jsonResp = new JsonResponse;

        //Check if header has authorization
        if (!$req->hasHeader('Authorization')) {

            $res = $res->withHeader('WWW-authenticate', 'Basic realm="commande_api api" ');

            //Format response
            $data = [
                'type' => 'error',
                'error' => 401,
                'message' => 'No Authorization header present'
            ];
            return $jsonResp->buildResp($req, $res, 401, $data);
        };

        //Check token validity
        try {
            $secret = $this->c->settings['secret'];
            $h = $req->getHeader('Authorization')[0];
            $tokenString = sscanf($h, "Bearer %s")[0];
            $key = new Key($secret, 'HS512');
            $token = JWT::decode($tokenString, $key);

            $data = [
                'user-mail' => $token->upr->email,
                'user-username' => $token->upr->username,
                'user_level' => $token->upr->level,
            ];

            return $jsonResp->buildResp($req, $res, 200, $data);
        } catch (ExpiredException $e) {
            //Expired token
            $errors = [
                'type' => 'error',
                'error' => 401,
                'message' => 'Token expired'
            ];
            return $jsonResp->buildResp($req, $res, 401, $errors);
        } catch (SignatureInvalidException $e) {
            //Unvalid signature
            $errors = [
                'type' => 'error',
                'error' => 401,
                'message' => 'Unvalid signature'
            ];
            return $jsonResp->buildResp($req, $res, 401, $errors);
        } catch (BeforeValidException $e) {
            //Before valid exception
            $errors = [
                'type' => 'error',
                'error' => 401,
                'message' => 'Before Valid Exception'
            ];
            return $jsonResp->buildResp($req, $res, 401, $errors);
        } catch (\UnexpectedValueException $e) {
            //Unvalid token
            $errors = [
                'type' => 'error',
                'error' => 401,
                'message' => 'Unvalid token'
            ];
            return $jsonResp->buildResp($req, $res, 401, $errors);
        } catch (Exception $e) {
            //General exception
            $errors = [
                'type' => 'error',
                'error' => 401,
                'message' => 'Bad request'
            ];
            return $jsonResp->buildResp($req, $res, 401, $errors);
        }
    }
}
