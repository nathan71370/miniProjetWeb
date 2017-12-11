<?php
namespace App\Controller;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;   // pour utiliser request
use App\Model\PanierModel;
use App\Model\ProduitModel;
use App\Model\TypeProduitModel;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Security;
class ProduitController implements ControllerProviderInterface
{
    private $produitModel;
    private $panierModel;
    private $typeProduitModel;
    public function index(Application $app) {
        return $this->showProduits($app);
    }
    public function showProduits(Application $app) {//client
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $this->panierModel = new PanierModel($app);
        //$app['session']->get('id')
        if($app['session']->get('user_id')!=null){

            $user_id=$app['session']->get('user_id');
        }
        else{
            $user_id=0;
        }
        $panier = $this->panierModel->getPanier2($user_id);
        return $app["twig"]->render('frontOff/Produit/showProduits.html.twig',['data'=>$produits, 'data2'=>$panier]);
    }

    public function showAllProduits(Application $app) {//Admin
        $this->produitModel = new ProduitModel($app);
        $produits = $this->produitModel->getAllProduits();
        $this->panierModel = new PanierModel($app);
        //$app['session']->get('id')
        if($app['session']->get('user_id')!=null){

            $user_id=$app['session']->get('user_id');
        }
        else{
            $user_id=0;
        }
        $panier = $this->panierModel->getPanier2($user_id);
        return $app["twig"]->render('backOff/Produit/showProduits.html.twig',['data'=>$produits, 'data2'=>$panier]);
    }

    public function addProduit(Application $app) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        //  dump($typeProduits);
        return $app["twig"]->render('backOff/Produit/addProduit.html.twig',['typeProduits'=>$typeProduits]);
    }
    public function validFormAddProduit(Application $app, Request $req) {
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['nom']) and isset($_POST['photo'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo'))
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';
            if(! empty($erreurs))
            {
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/addProduit.html.twig',['donnees'=>$donnees,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->ProduitModel = new ProduitModel($app);
                $this->ProduitModel->insertProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.showAllProduits"));
            }
        }
        else
            return $app->abort(404, 'error Pb data form Add');
    }
    public function deleteProduit(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/deleteProduit.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }
    public function validFormDeleteProduit(Application $app, Request $req) {
        $id=$app->escape($req->get('id'));
        if (is_numeric($id)) {
            $user_id=$app['session']->get('user_id');
            $this->produitModel = new ProduitModel($app);
            $this->panierModel = new PanierModel($app);
            $this->panierModel->deletePanier($id,$user_id);
            $this->produitModel->deleteProduit($id);
            return $app->redirect($app["url_generator"]->generate("produit.showAllProduits"));
        }
        else
            return $app->abort(404, 'error Pb id form Delete');
    }
    public function editProduit(Application $app, $id) {
        $this->typeProduitModel = new TypeProduitModel($app);
        $typeProduits = $this->typeProduitModel->getAllTypeProduits();
        $this->produitModel = new ProduitModel($app);
        $donnees = $this->produitModel->getProduit($id);
        return $app["twig"]->render('backOff/Produit/editProduit.html.twig',['typeProduits'=>$typeProduits,'donnees'=>$donnees]);
    }
    public function validFormEditProduit(Application $app, Request $req) {
        if (isset($_POST['nom']) && isset($_POST['typeProduit_id']) and isset($_POST['nom']) and isset($_POST['photo']) and isset($_POST['id'])) {
            $donnees = [
                'nom' => htmlspecialchars($_POST['nom']),                    // echapper les entrées
                'typeProduit_id' => htmlspecialchars($req->get('typeProduit_id')),  //$app['request']-> ne focntionne plus
                'prix' => htmlspecialchars($req->get('prix')),
                'photo' => $app->escape($req->get('photo')),  //$req->query->get('photo')-> ne focntionne plus
                'id' => $app->escape($req->get('id'))//$req->query->get('photo')
            ];
            if ((! preg_match("/^[A-Za-z ]{2,}/",$donnees['nom']))) $erreurs['nom']='nom composé de 2 lettres minimum';
            if(! is_numeric($donnees['typeProduit_id']))$erreurs['typeProduit_id']='veuillez saisir une valeur';
            if(! is_numeric($donnees['prix']))$erreurs['prix']='saisir une valeur numérique';
            if (! preg_match("/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/",$donnees['photo'])) $erreurs['photo']='nom de fichier incorrect (extension jpeg , jpg ou png)';
            if(! is_numeric($donnees['id']))$erreurs['id']='saisir une valeur numérique';
            $contraintes = new Assert\Collection(
                [
                    'id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'typeProduit_id' => [new Assert\NotBlank(),new Assert\Type('digit')],
                    'nom' => [
                        new Assert\NotBlank(['message'=>'saisir une valeur']),
                        new Assert\Length(['min'=>2, 'minMessage'=>"Le nom doit faire au moins {{ limit }} caractères."])
                    ],
                    //http://symfony.com/doc/master/reference/constraints/Regex.html
                    'photo' => [
                        new Assert\Length(array('min' => 5)),
                        new Assert\Regex([ 'pattern' => '/[A-Za-z0-9]{2,}.(jpeg|jpg|png)/',
                            'match'   => true,
                            'message' => 'nom de fichier incorrect (extension jpeg , jpg ou png)' ]),
                    ],
                    'prix' => new Assert\Type(array(
                        'type'    => 'numeric',
                        'message' => 'La valeur {{ value }} n\'est pas valide, le type est {{ type }}.',
                    ))
                ]);
            $errors = $app['validator']->validate($donnees,$contraintes);  // ce n'est pas validateValue
            if (count($errors) > 0) {
                $this->typeProduitModel = new TypeProduitModel($app);
                $typeProduits = $this->typeProduitModel->getAllTypeProduits();
                return $app["twig"]->render('backOff/Produit/editProduit.html.twig',['donnees'=>$donnees,'errors'=>$errors,'erreurs'=>$erreurs,'typeProduits'=>$typeProduits]);
            }
            else
            {
                $this->ProduitModel = new ProduitModel($app);
                $this->ProduitModel->updateProduit($donnees);
                return $app->redirect($app["url_generator"]->generate("produit.showAllProduits"));
            }
        }
        else
            return $app->abort(404, 'error Pb id form edit');
    }
    public function connect(Application $app) {  //http://silex.sensiolabs.org/doc/providers.html#controller-providers
        $controllers = $app['controllers_factory'];
        $controllers->get('/', 'App\Controller\produitController::index')->bind('produit.index');
        $controllers->get('/showAll', 'App\Controller\produitController::showAllProduits')->bind('produit.showAllProduits');
        $controllers->get('/show', 'App\Controller\produitController::showProduits')->bind('produit.showProduits');
        $controllers->get('/add', 'App\Controller\produitController::addProduit')->bind('produit.addProduit');
        $controllers->post('/add', 'App\Controller\produitController::validFormAddProduit')->bind('produit.validFormAddProduit');
        $controllers->get('/delete/{id}', 'App\Controller\produitController::deleteProduit')->bind('produit.deleteProduit')->assert('id', '\d+');
        $controllers->delete('/delete', 'App\Controller\produitController::validFormDeleteProduit')->bind('produit.validFormDeleteProduit');
        $controllers->get('/edit/{id}', 'App\Controller\produitController::editProduit')->bind('produit.editProduit')->assert('id', '\d+');
        $controllers->put('/edit', 'App\Controller\produitController::validFormEditProduit')->bind('produit.validFormEditProduit');
        $controllers->get('/insert', 'App\Controller\panierController::insertPanier')->bind('panier.insert');
        $controllers->get('/payer', 'App\Controller\commandeController::insertCommande')->bind('commande.insert');
        return $controllers;
    }
}