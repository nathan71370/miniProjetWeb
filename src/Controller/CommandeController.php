<?php
/**
 * Created by PhpStorm.
 * User: Nathan
 * Date: 25/11/2017
 * Time: 16:10
 */

namespace App\Controller;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Silex\ControllerCollection;
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request
use App\Model\CommandeModel;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;

class CommandeController implements ControllerProviderInterface
{
    private $commandeModel;
    public function index(Application $app) {
        return $this->showCommandes($app);
    }
    public function showCommandes(Application $app) {
        $this->commandeModel = new CommandeModel($app);
        //$app['session']->get('id')
        $commande = $this->commandeModel->getCommande();
        return $app["twig"]->render('backOff/Produit/showCommandes.html.twig',['data'=>$commande]);
    }
    public function showCommandes2(Application $app) {
        $this->commandeModel = new CommandeModel($app);
        $commande = $this->commandeModel->getCommande2($app['session']->get('user_id'));
        return $app["twig"]->render('backOff/Produit/showCommandes.html.twig',['data'=>$commande]);
    }

    public function insertCommande(Application $app){
        if($app['session']->get('user_id')!=null){
            $user_id=$app['session']->get('user_id');
        }
        else{
            $user_id=1;
        }
        $prix=150;
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->insertCommande($user_id, $prix);
        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }
    public function removeCommande (Application $app) {
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->deleteCommande($_POST['id']);
        return $app->redirect($app["url_generator"]->generate("commande.show"));
    }

    /**
     * Returns routes to connect to the given application.
     *
     * @param Application $app An Application instance
     *
     * @return ControllerCollection A ControllerCollection instance
     */
    public function connect(Application $app)
    {
        $controllers = $app['controllers_factory'];
        $controllers->get('/showcommandeclient', 'App\Controller\produitController::index')->bind('produit.index');
        return $controllers;
        // TODO: Implement connect() method.
    }
}