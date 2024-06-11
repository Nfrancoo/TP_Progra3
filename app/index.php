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
require_once './middlewares/Logger.php';
require_once './controllers/UsuarioController.php';
require_once './middlewares/validarUsuario.php';
require_once './controllers/ProductoController.php';
require_once './middlewares/validarProducto.php';
require_once './controllers/PedidoController.php';
require_once './middlewares/validarPedidos.php';
require_once './middlewares/validarMesas.php';
require_once './controllers/MesaController.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();


$app->group('/sesion', function (RouteCollectorProxy $group) {
  $group->post('[/]', \Logger::class.'::Loguear');
  $group->get('[/]', \Logger::class.'::Salir');
});

// Routes
$app->group('/usuarios', function (RouteCollectorProxy $group) {
    $group->get('[/]', \UsuarioController::class . ':TraerTodos');
    $group->get('/{id}', \UsuarioController::class . ':TraerUno');
    $group->get('/DarBaja/{id}', \UsuarioController::class . ':BajarUno');
    $group->post('[/]', \UsuarioController::class . ':CargarUno')->add(\ValidarUsuario::class.':ValidarCampos');
    $group->put('[/]', \UsuarioController::class . ':ModificarUno')->add(\ValidarUsuario::class.':ValidarCampos');
    $group->delete('[/]', \UsuarioController::class . ':BorrarUno');
  });


$app->group('/productos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \ProductoController::class.':TraerTodos');
  $group->get('/{id}', \ProductoController::class.':TraerUno');
  $group->post('[/]', \ProductoController::class.':CargarUno')->add(\ValidarProductos::class.':ValidarCampos');
  $group->put('[/]', ProductoController::class.':ModificarUno')->add(\ValidarProductos::class.':ValidarCampos');
  $group->delete('[/]', \ProductoController::class.':BorrarUno');
});


$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class.':TraerTodos');
  $group->get('/{codigo}', \PedidoController::class.':TraerUno');
  $group->post('[/]', \PedidoController::class.':CargarUno');
  $group->put('[/]', \PedidoController::class.':ModificarUno');
  $group->delete('[/]', \PedidoController::class.':BorrarUno');
  $group->get('/sector/preparar/{idPedido}', \PedidoController::class.':RecibirPedidos');
  $group->get('/sector/preparado/{idPedido}', \PedidoController::class.':PrepararPedido');
  $group->get('/entregar/pedido/{idPedido}', \PedidoController::class.':EntregarPedidoFinalizado');
});

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class.':TraerTodos');
  $group->get('/{id}', \MesaController::class.':TraerUno');
  $group->post('[/]', \MesaController::class.':CargarUno');
  $group->put('[/]', \MesaController::class.':ModificarUno')->add(\ValidarMesas::class.':ValidarMesa');
  $group->delete('[/]', \MesaController::class.':BorrarUno')->add(\ValidarMesas::class.':ValidarMesa');
  
});

$app->group('/cobrar', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class.':CerrarMesa');
});

$app->run();