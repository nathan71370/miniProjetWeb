<?php
/**
 * Created by PhpStorm.
 * User: Nathan
 * Date: 12/11/2017
 * Time: 14:48
 */

namespace App\Controller;
use App\Model\PanierModel;

use Silex\Api\ControllerProviderInterface;
use Silex\Application;

class PanierController implements ControllerProviderInterface
{
    private $panierModel;

    public function showPanier(Application $app) {
        $this->panierModel = new PanierModel($app);
        $paniers = $this->panierModel->getAllPanier();
        return $app["twig"]->render('backOff/Produit/showPanier.html.twig',['data2'=>$paniers]);
    }

    public function deleteProduit(Application $app, $id) {
        $this->panierModel = new PanierModel($app);
        $donnees = $this->panierModel->getPanier($id);
        return $app["twig"]->render('backOff/Produit/deleteProduit.html.twig',['donnees'=>$donnees]);
    }

    public function connect(Application $app){
        $controllers = $app['controllers_factory'];

        $controllers->get('/', 'App\Controller\produitController::index')->bind('produit.index');
        $controllers->get('/show', 'App\Controller\produitController::showPanier')->bind('paniers.showPanier');

        $controllers->get('/delete/{id}', 'App\Controller\produitController::deleteProduit')->bind('paniers.deleteProduit')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\produitController::validFormDeleteProduit')->bind('paniers.validFormDeletePanier');

        return $controllers;
    }

}