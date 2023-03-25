<?php
namespace lbs\order\controller;


use lbs\order\models\Commande;
use lbs\order\models\Item;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use lbs\order\utils\JsonResponse;
use DI\Container;
class CommandeController{

    private $container;

    public function __construct(Container $c)
    {
        $this->c = $c;
    }

    //  Toutes les commandes
    function getAllCommande(Request $rq, Response $rs, array $args): Response
     {
 
         // Récupérer les commandes depuis le model
         $commandes = Commande::all();
 
         // Construction des donnés à retourner dans le body
         $data = [
             "type" => "collection",
             // "count" => count($datas['commandes']),
             "count" => count($commandes),
             "commandes" => $commandes
         ];
 
         $rs = $rs->withStatus(200);
         $rs = $rs->withHeader('application-header', 'TD 1');
         $rs = $rs->withHeader("Content-Type", "application/json;charset=utf-8");
 
         $rs->getBody()->write(json_encode($data));
 
         return $rs;
     }

     /**
     * Get commande by id
     */
     function oneCommande(Request $rq, Response $rs, array $args): Response
     {
        try {
            //Get token from req
         //   $token = $rq->getAttribute('token');

            //Search for commande
            $commande = Commande::where([
                ['id', $args['id']],
              //  ['token', $token]
            ])->firstOrFail();

            //Format commande result (depending on TD requiment)
            $commandFormated = array(
                "id" => $commande->id,
                "mail" => $commande->mail,
                "nom" => $commande->nom,
                "date_commande" => $commande->created_at,
                "date_livraison" => $commande->livraison,
                "montant" => $commande->montant,
            );

            //Check if items is required by the client (attach it to commande if exist)
            if ($rq->getQueryParams('embed') && $rq->getQueryParams('embed')['embed'] == "items") {
                $commandFormated['items'] = $commande->items()->select('id', 'libelle', 'tarif', 'quantite')->get();
            }

            //Build the response data
            $data = [
                'type' => 'resource',
                'commande' => $commandFormated,
                'links' => array(
                    "items" => array(
                        "href" =>'order',
                            ['id' => $args['id']]
                    ),
                    "self" => array(
                        "href" => 'order',
                        ['id' => $args['id']]
                    )
                )
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($rq, $rs, 200, $data);
        } catch (\illuminate\Database\Eloquent\ModelNotFoundException $th) {

            $data = [
                'type' => 'error',
                'error' => 404,
                'message' => "la ressource demandée n'existe pas : commande ID = " . $args['id']
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($rq, $rs, 404, $data);
        }
     }

     /**
     * Get a commande items
     */
    function commandItems(Request $req, Response $res, array $args): Response
    {
        try {
            $commande = Commande::findOrFail($args['id']);
            $data = [
                'type' => 'collection',
                'count' => count($commande->items),
                'items' => $commande->items()->select('id', 'libelle', 'tarif', 'quantite')->get(),
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($req, $res, 200, $data);
        } catch (\illuminate\Database\Eloquent\ModelNotFoundException $th) {

            $data = [
                'type' => 'error',
                'error' => 404,
                'message' => "Ressource not found : commande ID = " . $args['id']
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($req, $res, 404, $data);
        }
    }

     /*
        PAGINATION DE TOUTE LES COMMANDES
     */
    function listCommands(Request $req, Response $res, array $args): Response
    {
        //Init pagination
        $size = 10;
        $page = 0;

        //Check if pagination required (get page number)
        if (isset($req->getQueryParams()['page']) != null && is_numeric($req->getQueryParams()['page']) && $req->getQueryParams()['page'] > 0) {
            $page = intval($req->getQueryParams()['page']);
        }

        //Check if pagination required (get size number)
        if (isset($req->getQueryParams()['size']) && is_numeric($req->getQueryParams()['size']) && $req->getQueryParams()['size'] > 0) {
            $size = intval($req->getQueryParams()['size']);
        }

        //Get all records
        $allCommands = Commande::select('id', 'nom', 'created_at', 'livraison', 'status');

        //Check if status filter exists (add where status...)
        if (isset($req->getQueryParams()['s']) && is_numeric($req->getQueryParams()['s'])) {
            $allCommands = $allCommands->where('status', intval($req->getQueryParams()['s']));
        }

        //Count all found records
        $recordCount = $allCommands->count();

        /**
         * Init pages number (next/prev/last)
         */
        //Last page
        $lastPage = round($recordCount / $size);

        //Next page
        if ($page > 1) {
            if ($page < $lastPage) {
                $nextPage = $page + 1;
            } else {
                $nextPage = $lastPage;
            }
        } else {
            $nextPage = 2;
        }

        //Prev page
        if ($page > 1) {
            $prevPage = $page - 1;
        } else {
            $prevPage = 1;
        }

        //Calculate the offset
        $offset = ($page - 1) * $size;
        if ($offset > $lastPage) {
            $offset = $lastPage;
        }

        //Get commands (based on conditions [where,limit,ofsset,...])
        $commands = $allCommands->limit($size)->offset($offset)->get();

        //Format response
        $formatedCom = [];
        foreach ($commands as $key => $c) {
            array_push(
                $formatedCom,
                [
                    'command' => $c,
                    'links' => array(
                        "self" => array(
                            "href" => 
                                'command',
                                ['id' => $c->id]
                            
                        ),
                        "next" => array(
                            "href" =>
                                'commands',
                                [],
                                [
                                    'page' => $nextPage,
                                    'size' => $size
                                ]
                            
                        ),
                        "prev" => array(
                            "href" => 
                                'commands',
                                [],
                                [
                                    'page' => $prevPage,
                                    'size' => $size
                                ]
                        
                        ),
                        "first" => array(
                            "href" =>
                                'commands',
                                [],
                                [
                                    'page' => 1,
                                    'size' => $size
                                ]
                            
                        ),
                        "last" => array(
                            "href" =>
                                'commands',
                                [],
                                [
                                    'page' => $lastPage,
                                    'size' => $size
                                ]
                            
                        )
                    )
                ]
            );
        }

        $data = [
            "type" => "collection",
            "count" => $recordCount,
            "size" => count($commands),
            "commands" => $formatedCom,
        ];

        //Build response
        $jsonResp = new JsonResponse;
        return $jsonResp->buildResp($req, $res, 200, $data);
    }
    
}
?>
