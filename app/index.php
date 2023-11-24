<?php
// Error Handling
error_reporting(-1);
ini_set('display_errors', 1);

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Factory\AppFactory;
use Slim\Routing\RouteCollectorProxy;
use Slim\Routing\RouteContext;

require __DIR__ . '/../vendor/autoload.php';

require_once './db/AccesoDatos.php';

require_once './controllers/CuentaController.php';
require_once './controllers/DepositoController.php';
require_once './controllers/RetiroController.php';
require_once './controllers/AjusteController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Set base path
//$app->setBasePath('/app');

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

$app->group('/Cuenta', function (RouteCollectorProxy $group) {
    $group->get('/', \CuentaController::class . ':TraerTodos');
    $group->get('/ConsultarMovimientoF', \CuentaController::class . ':ConsultarMovimientoF');
    $group->get('/{nroDeCuenta}', \CuentaController::class . ':TraerUno');
    $group->post('/', \CuentaController::class . ':CargarUno');
    $group->post('/Modificar', \CuentaController::class . ':ModificarUno');
    $group->post('/TraerUnoTipoYCuenta', \CuentaController::class . ':TraerUnoTipoYCuenta');
    $group->delete('/', \CuentaController::class . ':EliminarUno');
});

$app->group('/Deposito', function (RouteCollectorProxy $group) {
    $group->get('/', \DepositoController::class . ':TraerTodos');
    $group->get('/ConsultarMovimientoA', \DepositoController::class . ':ConsultarMovimientoA');
    $group->get('/ConsultarMovimientoB', \DepositoController::class . ':ConsultarMovimientoB');
    $group->get('/ConsultarMovimientoC', \DepositoController::class . ':ConsultarMovimientoC');
    $group->get('/ConsultarMovimientoD', \DepositoController::class . ':ConsultarMovimientoD');
    $group->get('/ConsultarMovimientoE', \DepositoController::class . ':ConsultarMovimientoE');
    $group->get('/{nroDeDeposito}', \DepositoController::class . ':TraerUno');
    $group->post('/', \DepositoController::class . ':CargarUno');
});

$app->group('/Retiro', function (RouteCollectorProxy $group) {
    $group->get('/', \RetiroController::class . ':TraerTodos');
    $group->get('/ConsultarMovimientoA', \RetiroController::class . ':ConsultarMovimientoA');
    $group->get('/ConsultarMovimientoB', \RetiroController::class . ':ConsultarMovimientoB');
    $group->get('/ConsultarMovimientoC', \RetiroController::class . ':ConsultarMovimientoC');
    $group->get('/ConsultarMovimientoD', \RetiroController::class . ':ConsultarMovimientoD');
    $group->get('/ConsultarMovimientoE', \RetiroController::class . ':ConsultarMovimientoE');
    $group->get('/{nroDeRetiro}', \RetiroController::class . ':TraerUno');
    $group->post('/', \RetiroController::class . ':CargarUno');
});

$app->group('/Ajuste', function (RouteCollectorProxy $group) {
    $group->get('/', \AjusteController::class . ':TraerTodos');
    $group->post('/', \AjusteController::class . ':CargarUno');
});

$app->run();