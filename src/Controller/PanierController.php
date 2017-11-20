<?php
namespace App\Controller;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request
use App\Model\PanierModel;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use Symfony\Component\Security;
class PanierController implements ControllerProviderInterface
{
    private $panierModel;
    private $produitModel;
    public function index(Application $app) {
        return $this->showPaniers($app);
    }
    public function showPaniers(Application $app) {
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $this->panierModel = new PanierModel($app);
        //$app['session']->get('id')
        $panier = $this->panierModel->getPanier2(1);
        return $app["twig"]->render('backOff/Produit/showProduits.html.twig',['data'=>$produits, 'data2'=>$panier]);
    }
    public function insertPanier(Application $app, Request $req){
        $id = $_GET['produit_id'];
        $quantite = $_GET['quantite'];
        $this->panierModel = new PanierModel($app);
        $this->panierModel->insertPanier($id, $quantite, 1);
        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }
    public function deletePanier (Application $app,$id) {
        if (is_numeric($id)) {
            $this->panierModel = new PanierModel($app);
            $this->panierModel->deletePanier($id);
        }
        return $app->redirect($app["url_generator"]->generate("panier.index"));
    }
    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'App\Controller\panierController::index')->bind('panier.index');
        $controllers->get('/remove/{id}', 'App\Controller\panierController::deletePanier')->bind('panier.remove')->assert('id', '\d+');
        return $controllers;
    }
}