<?php
require "config.php";

//On initialise le timeZone
ini_set('date.timezone', 'Europe/Paris');

//On ajoute l'autoloader (compatible winwin)
$loader = require_once join(DIRECTORY_SEPARATOR,[dirname(__DIR__), 'vendor', 'autoload.php']);

//dans l'autoloader nous ajoutons notre répertoire applicatif
$loader->addPsr4('App\\',join(DIRECTORY_SEPARATOR,[dirname(__DIR__), 'src']));

//Nous instancions un objet Silex\Application
$app = new Silex\Application();

// connexion à la base de données
$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => array(
        'driver' => 'pdo_mysql',
        'dbhost' => hostname,
        'host' => hostname,
        'dbname' => database,
        'user' => username,
        'password' => password,
        'charset'   => 'utf8mb4',
    ),
));

//utilisation de twig
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => join(DIRECTORY_SEPARATOR, [dirname(__DIR__), 'src', 'View'])
));

// utilisation des sessoins
$app->register(new Silex\Provider\SessionServiceProvider());

//en dev, nous voulons voir les erreurs
$app['debug'] = true;

// rajoute la méthode asset dans twig

$app->register(new Silex\Provider\AssetServiceProvider(), array(
    'assets.named_packages' => array(
        'css' => array(
            'version' => 'css2',
            'base_path' => __DIR__.'/../web/'
        ),
    ),
));

// par défaut les méthodes DELETE PUT ne sont pas prises en compte
use Symfony\Component\HttpFoundation\Request;
Request::enableHttpMethodParameterOverride();

//validator      => php composer.phar  require symfony/validator
$app->register(new Silex\Provider\ValidatorServiceProvider());

// Montage des controleurs sur le routeur
include('routing.php');

use Silex\Provider\CsrfServiceProvider;
$app->register(new CsrfServiceProvider());

use Silex\Provider\FormServiceProvider;
$app->register(new FormServiceProvider());

use Symfony\Component\Security\Csrf\CsrfToken;

//MiddleWare pour les droits
$app->before(function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $nomRoute=$request->get("_route");

    if ($app['session']->get('roles') != 'ROLE_ADMIN'  && ($nomRoute=="commande.showAll" || $nomRoute=="commande.exp" || $nomRoute=="commande.prep" || $nomRoute=="produit.addProduit" || $nomRoute=="produit.deleteProduit")) {
        return $app->redirect($app["url_generator"]->generate("index.errorDroit"));
    }
    if ($app['session']->get('roles') == 'ROLE_ADMIN'  && ($nomRoute=="panier.index")) {
        return $app->redirect($app["url_generator"]->generate("index.errorDroit"));
    }
    if ($app['session']->get('logged') != 1 && ($nomRoute!="produit.showProduits" && $nomRoute!="user.login" && $nomRoute!="user.addUser" && $nomRoute!="accueil" &&
            $nomRoute!="index.errorLogin" && $nomRoute!="index.errorDroit" && $nomRoute!="user.validFormAddUser" && $nomRoute!="user.validFormlogin")) {
        return $app->redirect($app["url_generator"]->generate("index.errorLogin"));
    }
});

//MiddleWare pour tester la validité du token
$app->before(function (\Symfony\Component\HttpFoundation\Request $request) use ($app) {
    $methode = $request->get("_method");
    if ($methode == "put" || $methode == "delete" || $methode == "post") {
        $token = $request->get("_csrf_token") ? $request->get("_csrf_token")  : null;
        if (!$token) return $app->redirect($app["url_generator"]->generate("user.login"));

        $csrf_token = new CsrfToken('csrf_token', $token);
        $csrf_token_ok = $app['csrf.token_manager']->isTokenValid($csrf_token);

        if (!$csrf_token_ok) {
            $app['session']->set('erreur', 'Erreur CSRF');
            return $app->redirect($app["url_generator"]->generate("user.login"));
        }
    }
});


//On lance l'application
$app->run();