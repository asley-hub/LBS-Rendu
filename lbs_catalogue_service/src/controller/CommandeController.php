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
                'message' => "Ressource not found : commande ID = " . $args['id']
            ];

            //Build response
            $jsonResp = new JsonResponse;
            return $jsonResp->buildResp($rq, $rs, 404, $data);
        }
     }
    
}
?>
