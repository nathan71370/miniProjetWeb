<?php
/**
 * Created by PhpStorm.
 * User: Nathan
 * Date: 25/11/2017
 * Time: 16:24
 */

namespace App\Model;
use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class CommandeModel
{
    private $db;
    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    public function insertCommande($user_id,$prix) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('commandes')
            ->values([
                'user_id' => '?',
                'prix' => '?',
                'date_achat' => '?',
                'etat_id' => '?'
            ])
            ->setParameter(0, $user_id)
            ->setParameter(1, $prix)
            ->setParameter(2, date("Y-m-d H:i:s")   )
            ->setParameter(3, 1);
        return $queryBuilder->execute();
    }


    public function getDetailCommande($id){
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('pa.quantite', 'pa.prix', 'pa.dateAjoutPanier', 'tp.libelle as tlibelle','p.nom','p.prix','p.photo', 'pa.commande_id' ,'pa.produit_id' )
            ->from('paniers','pa')
            ->innerJoin('pa', 'produits', 'p', 'p.id=pa.produit_id')
            ->innerJoin('p', 'typeProduits', 'tp', 'tp.id=p.id')
            ->where('pa.commande_id=:idc')
            ->setParameter('idc', $id);
        return $queryBuilder->execute()->fetchAll();
    }


    function getCommande() {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id','c.user_id','c.prix','c.date_achat','c.etat_id','e.libelle')
            ->from('commandes', 'c')
            ->innerJoin('c','etats', 'e','c.etat_id=e.id');
        return $queryBuilder->execute()->fetchAll();
    }
    function getCommande2($user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('c.id','c.user_id','c.prix','c.date_achat','c.etat_id','e.libelle')
            ->from('commandes','c')
            ->innerJoin('c','etats', 'e','c.etat_id=e.id')
            ->where('user_id=:userid')
        ->setParameter('userid', $user_id);
        return $queryBuilder->execute()->fetchAll();
    }
    public function deleteCommande($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('commandes')
            ->where('id = :idc')
            ->setParameter('idc',(int)$id);
        return $queryBuilder->execute();
    }

    public function expeditionCommande($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('etat_id',2)
            ->where('id = :idc')
            ->setParameter('idc',(int)$id);
        return $queryBuilder->execute();
    }

    public function preparationCommande($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('commandes')
            ->set('etat_id',1)
            ->where('id = :idc')
            ->setParameter('idc',(int)$id);
        return $queryBuilder->execute();
    }

    //AVEC TRANSACTION
    public function addCommandeWithTransaction($user)
    {
        $conn = $this->db;
        $conn->beginTransaction();
        $requestSQL = $conn->prepare('SELECT SUM(prix) as prix_total from paniers where user_id = :idUser and commande_id is NULL');
        $requestSQL->execute(['idUser' => $user]);
        $prix = $requestSQL->fetch()['prix_total'];
        $conn->commit();
        $conn->beginTransaction();
        $requestSQL = $conn->prepare('INSERT INTO commandes(user_id, prix, etat_id) VALUES (?,?,?)');
        $requestSQL->execute([$user, $prix, 1]);
        $lastinsertid = $conn->lastInsertId();
        $requestSQL = $conn->prepare('update paniers set commande_id=? where user_id=? and commande_id is null');
        $requestSQL->execute([$lastinsertid, $user]);
        $conn->commit();
    }
}