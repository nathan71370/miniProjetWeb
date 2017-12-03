<?php
namespace App\Model;

use Silex\Application;
use Doctrine\DBAL\Query\QueryBuilder;;

class UserModel {

	private $db;

	public function __construct(Application $app) {
		$this->db = $app['db'];
	}

	public function verif_login_mdp_Utilisateur($login,$mdp){
		$sql = "SELECT id,username,motdepasse,roles FROM users WHERE username = ? AND motdepasse = ?";
		$res=$this->db->executeQuery($sql,[$login,$mdp]);   //md5($mdp);
		if($res->rowCount()==1)
			return $res->fetch();
		else
			return false;
	}

	public function getUser($user_id) {
		$queryBuilder = new QueryBuilder($this->db);
		$queryBuilder
			->select('*')
			->from('users')
			->where('id = :idUser')
			->setParameter('idUser', $user_id);
		return $queryBuilder->execute()->fetch();

	}

	public function addUser($login, $password, $email){
        $queryBuilder = new QueryBuilder($this->db);
        if(!$this->verif_login_mdp_Utilisateur($login,$password)){
        $queryBuilder->insert('users')
            ->values([
                'username' => '?',
                'motdepasse' => '?',
                'email' => '?',
                'roles' => '?'
            ])
            ->setParameter(0, $login)
            ->setParameter(1, $password)
            ->setParameter(2, $email)
            ->setParameter(3, 'ROLE_CLIENT');
        return $queryBuilder->execute();
        }
        else{
            echo "pseudo déjà utilisé";
            die();
        }
    }
}