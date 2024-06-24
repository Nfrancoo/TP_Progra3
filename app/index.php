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
require_once './middlewares/JWS.php';
require_once './models/pdf.php';

// Load ENV
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

// Instantiate App
$app = AppFactory::create();

// Add error middleware
$app->addErrorMiddleware(true, true, true);

// Add parse body
$app->addBodyParsingMiddleware();

// php -S localhost:666 -t app


$app->group('/sesion', function (RouteCollectorProxy $group) {
  $group->post('[/]', \Logger::class.'::LoguearUsuario');
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
  $group->post('[/]', \ProductoController::class.':CargarUno')->add(\ValidarProductos::class.':ValidarTipo')->add(\Logger::class.':ValidarSesion')
  ->add(\ValidarProductos::class.':ValidarCampos')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->put('[/]', ProductoController::class.':ModificarUno')->add(\ValidarProductos::class.':ValidarTipo')->add(\Logger::class.':ValidarSesion')
  ->add(\ValidarProductos::class.':ValidarCampos')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->delete('[/]', \ProductoController::class.':BorrarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio')->add(\Logger::class.':ValidarSesion');
});


$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class.':TraerTodos')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->get('/{id}', \PedidoController::class.':TraerUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->post('[/]', \PedidoController::class.':CargarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->put('[/]', \PedidoController::class.':ModificarUno')->add(\ValidarPedidos::class.':ValidarEstado')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->delete('[/]', \PedidoController::class.':BorrarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->get('/por/sector', \PedidoController::class.':TraerTodosPorSector');
  $group->get('/sector/preparar/{idPedido}', \PedidoController::class.':RecibirPedidos');
  $group->get('/sector/preparado/{idPedido}', \PedidoController::class.':PrepararPedido')->add(\ValidarUsuario::class.':VerificarUsuario');
  $group->get('/entregar/pedido/{idPedido}', \PedidoController::class.':EntregarPedidoFinalizado')->add(\ValidarUsuario::class.':VerificarUsuario');;
})->add(\Logger::class.':ValidarSesion');

$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class.':TraerTodos')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->get('/{id}', \MesaController::class.':TraerUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->post('[/]', \MesaController::class.':CargarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->put('[/]', \MesaController::class.':ModificarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->delete('[/]', \MesaController::class.':BorrarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
})->add(\Logger::class.':ValidarSesion');

$app->group('/cobrar', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class.':CerrarMesa');
})->add(\Logger::class.':ValidarSesion')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');


$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/descargarUsuarios', \UsuarioController::class.'::DescargarCSV');
    $group->post('/cargarUsuarios', \UsuarioController::class.'::CargarCSV');
    $group->get('/descargarProductos', \ProductoController::class.'::DescargarCSV');
    $group->post('/cargarProductos', \ProductoController::class.'::CargarCSV');
    $group->get('/descargarMesas', \MesaController::class.'::DescargarCSV');
    $group->get('/descargarPedidos', \PedidoController::class.'::DescargarCSV');
    $group->post('/cargarPedidos', \PedidoController::class.'::CargarCSV');
    $group->get('/descargarPdfUsuarios', \UsuarioController::class.':DescargarPDF');
})->add(\Logger::class.':ValidarSesion')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');

$app->run();