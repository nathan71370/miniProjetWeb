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
			return $app->redirect($app["url_generator"]->generate("accueil"));
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
		return $app->redirect($app["url_generator"]->generate("accueil"));
	}

    public function validFormAddUser(Application $app, Request $req){
        if (isset($_POST['login']) and isset($_POST['password']) and isset($_POST['email'])) {
            $donnees = [
                'login' => htmlspecialchars($req->get('login')),                    // echapper les entrées
                'password' => htmlspecialchars($req->get('password')),
                'email' => htmlspecialchars($req->get('email'))
            ];
            if ((!preg_match("/^[A-Za-z ]{2,}/", $donnees['login']))) $erreurs['login'] = 'nom composé de 2 lettres minimum';
            if ((!preg_match("/^[A-Za-z ]{6,}/", $donnees['password']))) $erreurs['password'] = 'Mot de passe composé de 6 lettres minimum';
            if (!filter_var($donnees['email'], FILTER_VALIDATE_EMAIL)) $erreurs['email'] = "Format email invalide";
            if (!empty($erreurs)) {
                $this->userModel = new UserModel($app);
                return $app["twig"]->render('inscription.html.twig', ['donnees' => $donnees, 'erreurs' => $erreurs, ]);
            } else {
                $this->userModel = new UserModel($app);
                $this->userModel->addUser($donnees['login'],$donnees['password'], $donnees['email']);
                return $app->redirect($app["url_generator"]->generate("accueil"));
            }
        }
        else{
            return $app->abort(404, 'error Pb data form Add');
        }
    }
	public function addUser(Application $app){
        return $app["twig"]->render('inscription.html.twig');
    }

	public function connect(Application $app) {
		$controllers = $app['controllers_factory'];
		$controllers->match('/', 'App\Controller\UserController::index')->bind('user.index');
		$controllers->get('/login', 'App\Controller\UserController::connexionUser')->bind('user.login');
		$controllers->post('/login', 'App\Controller\UserController::validFormConnexionUser')->bind('user.validFormlogin');
		$controllers->get('/logout', 'App\Controller\UserController::deconnexionSession')->bind('user.logout');
        $controllers->get('/addUser', 'App\Controller\UserController::addUser')->bind('user.addUser');
        $controllers->post('/addUser', 'App\Controller\UserController::validFormAddUser')->bind('user.validFormAddUser');

        return $controllers;
	}
}