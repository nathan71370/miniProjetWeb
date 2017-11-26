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
    public function insertPanier(Application $app, Request $req){
        //$id = $_GET['produit_id'];
        //$quantite = $_GET['quantite'];
        $this->commandeModel = new CommandeModel($app);
        $this->commandeModel->insertCommande($id, $quantite, 1);
        return $app->redirect($app["url_generator"]->generate("panier.index"));
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
        // TODO: Implement connect() method.
    }
}