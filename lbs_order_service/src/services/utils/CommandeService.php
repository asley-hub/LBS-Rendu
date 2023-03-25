<?php

namespace lbs\order\services\utils;

use lbs\order\models\Commande;
use lbs\order\models\Item;

use orders\errors\exceptions\OrderExceptionNotFound;
use Illuminate\Database\Eloquent\ModelNotFoundException;

final class CommandeService {

    public function getOrders(?string $client=null, ?string $sort=null): Array
    {

        $query = Commande::select('id', 'mail as client_mail', 'created_at as order_date', 'montant as total_amount');

        if($client)
            $query->where('mail', $client);

        if ($sort) {
            if ($sort === 'price')
                $query->orderBy('montant', 'desc');

            if ($sort === 'date')
                $query->orderBy('created_at', 'desc');
        }




        try {

            return $query->get()->toArray();

        }catch (ModelNotFoundException $e) {
            throw new OrderExceptionNotFound("orders not found");
        }
    }

    public function getOrdersById(string $id, ?string $embed=null): array
    {
        $query = Commande::select('id', 'mail as client_mail','nom as client_name', 'created_at as order_date', 'livraison as delivery_date','montant as total_amount')->where('id', '=', $id);
        if ($embed === 'items'){
            $query = $query->with('items');
        }
        try {
            return $query->firstOrFail()->toArray();
        }catch (ModelNotFoundException $e) {
            throw new OrderExceptionNotFound("order $id not found");
        }
    }

    public function orderUpdate(string $id,array $data): void
    {
        try {
            $order = Commande::findOrFail($id);
        }catch (ModelNotFoundException $e){
            throw new OrderExceptionNotFound("order $id not found");
        }

        $order->nom = $data['client_name'];
        $order->mail = $data['client_mail'];
        $order->livraison = $data['delivery'];
        $order->save();
    }
    
    public function getOrderItems(string $id): array
    {
        $items = Item::select('id','uri','libelle as name','tarif as price','quantite as quatity')->where('command_id', '=', $id)->get();
        return $items->toArray();
    }

    public function postOrder(array $data): Commande
    {
        $order = new Commande;
        $order->id = uniqid();
        $order->nom = $data['client_name'];
        $order->mail = $data['client_mail'];
        $order->montant = $data['price'];
        $order->status = $data['status'];
        $order->livraison = $data['delivery'];

        try {
            $order->save();
        } catch (ModelNotFoundException $e) {
            throw new OrderExceptionNotFound("post order not resolvable");
        }

        return $order;
    }

}
