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
    public function insertPanier($id, $quantite, $user_id) {
        $requete="SELECT quantite FROM paniers WHERE produit_id=$id and commande_id is null";
        $select = $this->db->query($requete);
        $res = $select->fetch();
        if($res != null){
            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder
                ->update('paniers')
                ->set('quantite', ($quantite+intval($res['quantite'],10)))
                ->where('produit_id= :id')
                ->andWhere('commande_id IS NULL')
                ->andWhere('user_id= :userid')
                ->setParameter('userid', $user_id)
                ->setParameter('id', $id);
        }else{
            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder
                ->select('prix')
                ->from('produits', 'p')
                ->where('p.id= :id')
                ->setParameter('id', $id);
            $queryBuilder->execute()->fetch();
            $nb = $queryBuilder;
            $queryBuilder = new QueryBuilder($this->db);
            $queryBuilder->insert('paniers')
                ->values([
                    'produit_id' => '?',
                    'prix' => '?',
                    'quantite' => '?',
                    'user_id' => '?',
                    'dateAjoutPanier' => '?'
                ])
                ->setParameter(0, $id)
                ->setParameter(1, $quantite)
                ->setParameter(2, $nb*$quantite)
                ->setParameter(3, $user_id)
                ->setParameter(4, date("Y-m-d H:i:s")   );
        }
        return $queryBuilder->execute();
    }
    function getPanier($user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('id', 'quantite', 'prix', 'dateAjoutPanier')
            ->from('paniers')
            ->where('user_id= :userid')
            ->andWhere('commande_id = NULL')
            ->setParameter('userid', $user_id);
        return $queryBuilder->execute()->fetchAll();
    }
    function getPanier2($user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('paniers', 'pa')
            ->innerJoin('pa','produits', 'pr', 'pa.produit_id = pr.id')
            ->where('pa.user_id= :userid')
            ->andWhere('pa.commande_id IS NULL')
            ->setParameter('userid', $user_id);
        return $queryBuilder->execute()->fetchAll();
    }
    public function removeOnePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', 'quantite-1')
            ->where('produit.id= ?')
            ->andWhere('user_id= ?')
            ->setParameter(1, $donnees['id'])
            ->setParameter(2, $donnees['user_id']);
        return $queryBuilder->execute();
    }
    public function addOnePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', 'quantite+1')
            ->where('produit.id= ?')
            ->andWhere('user_id= ?')
            ->setParameter(1, $donnees['id'])
            ->setParameter(2, $donnees['user_id']);
        return $queryBuilder->execute();
    }
    public function updatePanier($donnees) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->update('paniers')
            ->set('quantite', 'quantite-1')
            ->where('id= ?')
            ->andWhere('user_id= ?')
            ->setParameter(0, $donnees['quantite'])
            ->setParameter(1, $donnees['id'])
            ->setParameter(2, $donnees['user_id']);
        return $queryBuilder->execute();
    }

    public function deletePanier($id,$user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->delete('paniers')
            ->where('produit_id = :id')
            ->andWhere('user_id= :userid')
            ->setParameter('id',(int)$id)
            ->setParameter('userid', $user_id);
        return $queryBuilder->execute();
    }
}