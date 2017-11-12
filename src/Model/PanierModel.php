<?php
namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;
class PanierModel{
    /**
     * Created by PhpStorm.
     * User: Nathan
     * Date: 12/11/2017
     * Time: 13:55
     */

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }

    public function getAllPanier()
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 'p.quantite', 'p.prix', 'p.dateAjoutPanier', 'p.user_id', 'p.produit_id', 'p.commande_id')
            ->from('paniers', 'p')
            ->innerJoin('p', 'users', 'u', 'p.user_id=u.id')
            ->innerJoin('p', 'produits', 'pr', 'p.produit_id=pr.id')
            ->innerJoin('p', 'commandes', 'c', 'p.commande_id=c.id')
            ->addOrderBy('p.id', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertPanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('paniers')
            ->values([
                'quantite' => '?',
                'prix' => '?',
                //'dateAjoutPanier' => '?',
                'user_id' => '0',  //à modifier
                'produit_id' => '0',
                'commande_id' => '0'
            ])
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['dateAjoutPanier'])
        ;
        return $queryBuilder->execute();
    }

    function getPanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'quantite', 'prix', 'dateAjoutPanier')
            ->from('paniers')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updatePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', '?')
            ->set('prix','?')
            ->set('dateAjoutPanier','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['prix'])
            ->setParameter(2, $donnees['dateAjoutPanier'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deletePanier($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('id = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
    }
}