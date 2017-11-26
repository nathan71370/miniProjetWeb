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
            ->select('*')
            ->from('commandes');
        return $queryBuilder->execute()->fetchAll();
    }
    function getCommande2($user_id) {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder
            ->select('*')
            ->from('commandes')
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
}