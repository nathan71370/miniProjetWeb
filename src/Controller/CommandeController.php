<?php
/**
 * Created by PhpStorm.
 * User: Nathan
 * Date: 25/11/2017
 * Time: 16:10
 */

namespace App\Controller;
use App\Model\PanierModel;
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
    private $paniersModel;

    public function index(Application $app) {
        return $this->showCommandes2($app);
    }
    public function showCommandes(Application $app) {//ADMIN
        $this->commandeModel = new CommandeModel($app);
        //$app['session']->get('id')
        $commande = $this->commandeModel->getCommande();
        return $app["twig"]->render('backOff/Commande/showCommandes.html.twig',['data'=>$commande]);
    }
    public function showCommandes2(Application $app) {//CLIENT
        $this->commandeModel = new CommandeModel($app);
        $commande = $this->commandeModel->getCommande2($app['session']->get('user_id'));
        return $app["twig"]->render('frontOff/Commande/showCommandes.html.twig',['data'=>$commande]);
    }

    public function insertCommande(Application $app){
        if($app['session']->get('user_id')!=null){
            $user_id=$app['session']->get('user_id');
        }
        else{
            $user_id=0;
        }
        $this->commandeModel = new CommandeModel($app);
        $this->paniersModel = new PanierModel($app);
        $this->commandeModel->addCommandeWithTransaction($user_id);
        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }

    public function removeCommande (Application $app,$id) {
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->deleteCommande($id);
        return $app->redirect($app["url_generator"]->generate("commande.show2"));
    }

    public function expCommande(Application $app, $id){
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->expeditionCommande($id);
        return $app->redirect($app["url_generator"]->generate("commande.showAll"));
    }

    public function prepCommande(Application $app, $id){
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->preparationCommande($id);
        return $app->redirect($app["url_generator"]->generate("commande.showAll"));
    }

    public function detailCommande(Application $app,$id)
    {
        if (is_numeric($id)) {
            $this->commandeModel = new CommandeModel($app);
            $commande = $this->commandeModel->getDetailCommande($id);
        }
        return $app["twig"]->render('backOff/Commande/showCommandeDetail.html.twig', ['data' => $commande]);
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
        $controllers->get('/', 'App\Controller\commandeController::index')->bind('commande.index');
        $controllers->get('/showCommandes', 'App\Controller\commandeController::showCommandes')->bind('commande.showAll');
        $controllers->get('/showCommandesClient', 'App\Controller\commandeController::showCommandes2')->bind('commande.show2');
        $controllers->get('/removeCommande/{id}', 'App\Controller\commandeController::removeCommande')->bind('commande.remove')->assert('id', '\d+');
        $controllers->get('/expCommande/{id}', 'App\Controller\commandeController::expCommande')->bind('commande.exp')->assert('id', '\d+');
        $controllers->get('/prepCommande/{id}', 'App\Controller\commandeController::prepCommande')->bind('commande.prep')->assert('id', '\d+');
        $controllers->get('/details/{id}', 'App\Controller\CommandeController::detailCommande')->bind('commande.detail')->assert('id', '\d+');

        return $controllers;
        // TODO: Implement connect() method.
    }
}