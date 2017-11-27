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
    public function addCommandeWithTransaction($user){
        $date_achat=date("Y-m-d H:i:s");
        try{
            $this->db->beginTransaction();
            $query = "SELECT SUM(prix*quantite) as prix from paniers where user_id = :idUser and commande_id is NULL";
            $result=mysqli_query($query);
            $row=mysqli_fetch_assoc($result);
            $prix=$row['userid'];
            $this->db->query("INSERT INTO commandes (user_id, prix, date_achat, etat_id) VALUES ('".$user."','".$prix."', '".$date_achat."', 1);");
            $this->db->commit();
        }
        catch (Exception $e){
            $this->db->rollback();
            echo 'Tout ne s\'est pas bien passé, voir les erreurs ci-dessous<br />';
            echo 'Erreur : '.$e->getMessage().'<br />';
            echo 'N° : '.$e->getCode();
            exit();
        }
    }
}