<?php
namespace lbs\order\models;

class Commande extends \Illuminate\Database\Eloquent\Model {

       protected $table      = 'commande';  /* le nom de la table */
       protected $primaryKey = 'id';     /* le nom de la clé primaire */
       public    $timestamps = false;    /* si vrai la table doit contenir
                                            les deux colonnes updated_at,
                                            created_at */
       protected $fillable = array(
       'id', 'livraison', 'nom', 'mail', 'montant', 'token', 'status'
       );

       public function items()
       {
       return $this->hasMany('lbs\order\models\Item');
       }
}