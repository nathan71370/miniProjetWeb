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
		if($res->rowCount()==1) {
            return $res->fetch();
        }
		else
			return false;
	}

    public function verif_login_email_Utilisateur($login,$email){
        $sql = "SELECT id,username,email,roles FROM users WHERE username = ? or email = ?";
        $res=$this->db->executeQuery($sql,[$login,$email]);   //md5($mdp);
        if($res->rowCount()==1) {
            $res->fetch();
            return false;
        }
        else
            return true;
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
        if($this->verif_login_email_Utilisateur($login,$email)){
        $queryBuilder->insert('users')
            ->values([
                'username' => '?',
                'motdepasse' => '?',
                'password' => '?',
                'email' => '?',
                'roles' => '?'
            ])
            ->setParameter(0, $login)
            ->setParameter(1, $password)
            ->setParameter(2, md5($password))
            ->setParameter(3, $email)
            ->setParameter(4, 'ROLE_CLIENT');
        $queryBuilder->execute();
        return true;
        }
        else{
            return false;
        }
    }

    public function updateUser($donnees,$id)
    {
        $queryBuilder = new QueryBuilder($this->db);
        $queryBuilder->update('users')
            ->set('username' , '?')
            ->set('password' , '?')
            ->set('email' , '?')
            ->set('nom' , '?')
            ->set('code_postal' , '?')
            ->set('ville' , '?')
            ->set('adresse' , '?')
            ->set('motdepasse' , '?')
            ->where('id=?')
            ->setParameter(0, $donnees['login'])
            ->setParameter(1, md5($donnees['password']))
            ->setParameter(2, $donnees['email'])
            ->setParameter(3, $donnees['nom'])
            ->setParameter(4, $donnees['cp'])
            ->setParameter(5, $donnees['ville'])
            ->setParameter(7, $donnees['password'])
            ->setParameter(6, $donnees['adresse'])
            ->setParameter(8, $id);
        return $queryBuilder->execute();
    }
}