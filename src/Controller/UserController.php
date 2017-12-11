<?php
namespace App\Controller;

use App\Model\UserModel;

use Silex\Application;
use Silex\Api\ControllerProviderInterface;   // modif version 2.0

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\User;   // pour utiliser request

class UserController implements ControllerProviderInterface {

	private $userModel;

	public function index(Application $app) {
		return $this->connexionUser($app);
	}

	public function connexionUser(Application $app)
	{
		return $app["twig"]->render('login.html.twig');
	}

    public function showUser(Application $app)
    {
        $this->userModel=new UserModel($app);
        $donnees= $this->userModel->showAllUser();
        return $app["twig"]->render('backOff/Client/showClient.html.twig', ['donnees'=>$donnees]);
    }

    public function updateUserAdmin(Application $app, $id){
            $this->userModel = new  UserModel($app);
            $donnees=$this->userModel->getUser($id);
            $donnees['id']=$id;
            $donnees['login']=$donnees['username'];
            $donnees['role']=$donnees['roles'];
            return $app["twig"]->render('backOff/Client/coordonnee.html.twig',['donnees'=>$donnees]);
    }

    public function updateUser(Application $app)
    {
        $this->userModel = new  UserModel($app);
            $donnees = $this->userModel->getUser($app['session']->get('user_id'));
            $donnees['login'] = $donnees['username'];
            $donnees['cp'] = $donnees['code_postal'];
            return $app["twig"]->render('frontOff/User/coordonnee.html.twig',['donnees'=>$donnees]);
    }

    public function validFormUpdateAdmin(Application $app, Request $req) {
        //$id=$app->escape($req->get('id'));
        $donnees = [
            'id' => htmlspecialchars($_POST['id']),
            'login' => htmlspecialchars($_POST['login']), //$app['request']-> ne fonctionne plus sur silex 2.0
            'role' => htmlspecialchars($_POST['role'])
        ];
        if ((! preg_match("/^[A-Za-z1-9 ]{4,100}/",$donnees['login']))) $erreurs['login']='Login composé de 4 lettres minimum';
        if(!empty($erreurs))
        {
            return $app["twig"]->render('backOff/Client/coordonnee.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs]);
        }
        else
        {
            $this->userModel = new UserModel($app);
            $this->userModel->updateUserAdmin($donnees,$donnees['id']);
            return $app->redirect($app["url_generator"]->generate("user.show"));
        }
    }

	public function showCoordonnee(Application $app){
        return $app["twig"]->render('frontOff/User/coordonnee.html.twig');
    }

	public function validFormConnexionUser(Application $app, Request $req)
	{

		$app['session']->clear();
		$donnees['login']=$req->get('login');
		$donnees['password']=$req->get('password');

		$this->userModel = new UserModel($app);
		$data=$this->userModel->verif_login_mdp_Utilisateur($donnees['login'],$donnees['password']);

		if($data != NULL)
		{
			$app['session']->set('roles', $data['roles']);  //dans twig {{ app.session.get('roles') }}
			$app['session']->set('username', $data['username']);
			$app['session']->set('logged', 1);
			$app['session']->set('user_id', $data['id']);
			if($app['session']->get('roles')=='ROLE_ADMIN'){
                return $app->redirect($app["url_generator"]->generate("produit.showAllProduits"));
            }
			else{
                return $app->redirect($app["url_generator"]->generate("produit.showProduits"));
            }
		}
		else
		{
			$app['session']->set('erreur','mot de passe ou login incorrect');
			return $app["twig"]->render('login.html.twig');
		}
	}
	public function deconnexionSession(Application $app)
	{
		$app['session']->clear();
		$app['session']->getFlashBag()->add('msg', 'vous êtes déconnecté');
		return $app->redirect($app["url_generator"]->generate("produit.showProduits"));
	}

    public function validFormUpdate(Application $app) {
        $donnees = [
            'email' => htmlspecialchars($_POST['email']),                    // echapper les entrées
            'login' => htmlspecialchars($_POST['login']), //$app['request']-> ne fonctionne plus sur silex 2.0
            'password' => htmlspecialchars($_POST['password']),
            'password2' => htmlspecialchars($_POST['password2']),
            'nom' => htmlspecialchars($_POST['nom']),
            'adresse' => htmlspecialchars($_POST['adresse']),
            'ville' => htmlspecialchars($_POST['ville']),
            'cp' => htmlspecialchars($_POST['cp'])
        ];
        if ((!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL))) $erreurs['email']='Verifiez l\' adresse email';
        if ((! preg_match("/^[A-Za-z1-9 ]{4,100}/",$donnees['login']))) $erreurs['login']='Login composé de 4 lettres minimum';
        if (strlen($donnees['password']) < 5) $erreurs['password']='Mot de passe composé de 4 lettres minimum';
        if ($donnees['password'] != $donnees['password2']) $erreurs['password2']='Mots de passe ne correspondent pas';
        if(! is_numeric($donnees['cp']) or ! preg_match("/^[0-9]{5}/",$donnees['cp']))$erreurs['cp']='Veuillez saisir un code postal valide';
        if(! preg_match("/[A-Za-z]{2,}/",$donnees['nom']))$erreurs['nom']='Veuillez saisir un nom valide';
        if(! preg_match("/[A-Za-z]{2,}/",$donnees['ville']))$erreurs['ville']='Veuillez saisir une ville valide';
        if (! preg_match("/[A-Za-z1-9]{2,}/",$donnees['adresse'])) $erreurs['adresse']='Veuillez saisir une adresse valide';
        if(!empty($erreurs))
        {
            return $app["twig"]->render('frontOff/User/coordonnee.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs]);
        }
        else
        {
            $this->userModel = new UserModel($app);
            $this->userModel->updateUser($donnees,$app['session']->get('user_id'));
            return $app->redirect($app["url_generator"]->generate("user.update"));
        }
    }

    public function validFormAddUser(Application $app, Request $req){
	    $this->userModel=new UserModel($app);
        if (isset($_POST['login']) and isset($_POST['password']) and isset($_POST['email'])) {
            $donnees = [
                'login' => htmlspecialchars($req->get('login')),                    // echapper les entrées
                'password' => htmlspecialchars($req->get('password')),
                'email' => htmlspecialchars($req->get('email'))
            ];
            if ((!preg_match("/^[A-Za-z ]{2,}/", $donnees['login']))) $erreurs['login'] = 'nom composé de 2 lettres minimum';
            if ((!preg_match("/^[A-Za-z ]{6,}/", $donnees['password']))) $erreurs['password'] = 'Mot de passe composé de 6 lettres minimum';
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) $erreurs['email'] = "Format email invalide";
            if(!$this->userModel->verif_login_email_Utilisateur($donnees['login'], $donnees['email'])) $erreurs['inscri']="Pseudo ou email deja utilisé";
            if (!empty($erreurs)) {
                return $app["twig"]->render('frontOff/User/inscription.html.twig', ['donnees' => $donnees, 'erreurs' => $erreurs, ]);
            } else {
                $this->userModel = new UserModel($app);
                $this->userModel->addUser($donnees['login'],$donnees['password'], $donnees['email']);
                return $app->redirect($app["url_generator"]->generate("user.login"));
            }
        }
        else{
            return $app->abort(404, 'error Pb data form Add');
        }
    }
	public function addUser(Application $app){
        return $app["twig"]->render('frontOff/User/inscription.html.twig');
    }

    public function deleteUser(Application $app, $id) {
        $this->userModel = new UserModel($app);
        $donnees = $this->userModel->getUser($id);
        return $app["twig"]->render('backOff/Client/deleteClient.html.twig',['donnees'=>$donnees]);
    }
    public function validFormdeleteUser(Application $app, Request $req){
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $this->userModel=new UserModel($app);
            $this->userModel->deleteUser($id);
            $donnees = $this->userModel->showAllUser();
            return $app["twig"]->render('backOff/Client/showClient.html.twig',['donnees'=>$donnees]);
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
        $controllers->get('/addUser', 'App\Controller\UserController::addUser')->bind('user.addUser');
        $controllers->post('/addUser', 'App\Controller\UserController::validFormAddUser')->bind('user.validFormAddUser');
        $controllers->get('/update', 'App\Controller\UserController::updateUser')->bind('user.update');
        $controllers->get('/update/{id}', 'App\Controller\UserController::updateUserAdmin')->bind('user.updateAdmin')->assert('id', '\d+');
        $controllers->get('/delete/{id}', 'App\Controller\UserController::deleteUser')->bind('user.delete')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\UserController::validFormDeleteUser')->bind('user.validFormDeleteUser');
        $controllers->get('/showUser', 'App\Controller\UserController::showUser')->bind('user.show');
        $controllers->put('/update', 'App\Controller\UserController::validFormupdate')->bind('user.validFormupdate');
        $controllers->post('/updateAdmin', 'App\Controller\UserController::validFormupdateAdmin')->bind('user.validFormupdateAdmin');

        return $controllers;
	}
}