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
    public function insertPanier($user_id,$prix) {
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
}