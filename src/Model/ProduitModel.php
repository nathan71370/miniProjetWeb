<?php

namespace App\Model;

use Doctrine\DBAL\Query\QueryBuilder;
use Silex\Application;

class ProduitModel {

    private $db;

    public function __construct(Application $app) {
        $this->db = $app['db'];
    }
    // http://docs.doctrine-project.org/projects/doctrine-dbal/en/latest/reference/query-builder.html#join-clauses
    public function getAllProduits() {
//        $sql = "SELECT p.id, t.libelle, p.nom, p.prix, p.photo
//            FROM produits as p,typeProduits as t
//            WHERE p.typeProduit_id=t.id ORDER BY p.nom;";
//        $req = $this->db->query($sql);
//        return $req->fetchAll();
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('p.id', 't.libelle', 'p.nom', 'p.prix', 'p.photo')
            ->from('produits', 'p')
            ->innerJoin('p', 'typeProduits', 't', 'p.typeProduit_id=t.id')
            ->addOrderBy('p.nom', 'ASC');
        return $queryBuilder->execute()->fetchAll();

    }

    public function insertProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->insert('produits')
            ->values([
                'nom' => '?',
                'typeProduit_id' => '?',
                'prix' => '?',
                'photo' => '?',
                'dispo' => '0',  //à modifier
                'stock' => '0'
            ])
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
        ;
        return $queryBuilder->execute();
    }

    function getProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'typeProduit_id', 'nom', 'prix', 'photo')
            ->from('produits')
            ->where('id= :id')
            ->setParameter('id', $id);
        return $queryBuilder->execute()->fetch();
    }

    public function updateProduit($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('produits')
            ->set('nom', '?')
            ->set('typeProduit_id','?')
            ->set('prix','?')
            ->set('photo','?')
            ->where('id= ?')
            ->setParameter(0, $donnees['nom'])
            ->setParameter(1, $donnees['typeProduit_id'])
            ->setParameter(2, $donnees['prix'])
            ->setParameter(3, $donnees['photo'])
            ->setParameter(4, $donnees['id']);
        return $queryBuilder->execute();
    }

    public function deleteProduit($id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('produits')
            ->where('id = :id')
            ->setParameter('id',(int)$id)
        ;
        return $queryBuilder->execute();
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
                'dateAjoutPanier' => '?',
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