<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class PanierModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses

    public function insertPanier($donnees,$user) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('paniers')
            //if($donnees['produit_id'])
            ->values([
                'produit_id' => '?',
                'quantite' => '?',
                'prix' => '?',
                //'dateAjoutPanier' => '?',
                'user_id' => '?'
            ])
            ->setParameter(0, $donnees['produit_id'])
            ->setParameter(1, '1')
            ->setParameter(2, '1')
            //->setParameter(3, 'null')
            ->setParameter(3, $user["user_id"])
        ;
        return $queryBuilder->execute();
    }

    function getPanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'quantite', 'prix', 'dateAjoutPanier')
            ->from('paniers')
            ->where('user_id= :id')
            ->andWhere('commande_id = NULL')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetchAll();
    }

    function getPanier2($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('paniers', 'pa')
            ->innerJoin('pa','produits', 'pr', 'pa.produit_id = pr.id')
            ->where('pa.user_id= :id')
            ->andWhere('pa.commande_id IS NULL')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetchAll();
    }


    public function removeOnePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', 'quantite-1')
            ->where('id= ?')
            ->setParameter(1, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function addOnePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', 'quantite+1')
            ->where('id= ?')
            ->setParameter(1, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function updatePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', '?')
            ->where('id= ?')
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deletePanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('produit_id = :produit_id')
            ->setParameter('produit_id',(int)$id)
        ;
        //var_dump($id);
        //die();
        return $queryBuilder->execute();
    }




}