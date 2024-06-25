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
require_once './controllers/ComentarioController.php';
require_once './middlewares/validarComentarios.php';

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
  $group->put('[/]', \PedidoController::class.':ModificarUno')->add(\ValidarPedidos::class.':ValidarEstado')->add(\ValidarPedidos::class.':ValidarMesaExistente')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->delete('[/]', \PedidoController::class.':BorrarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->get('/listar/pendientes', \PedidoController::class.':ListarPendientesDetallePedido')->add(\ValidarUsuario::class.':validarRolesListar');
  $group->get('/entregar/pedido/{idPedido}', \PedidoController::class.':EntregarPedidoFinalizado')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->post('/mostrar/tiempo', \PedidoController::class.':ListarTiempoPendiente');
  $group->get('/mostrar/todos-tiempo', \PedidoController::class . ':SocioListarTiempoPendiente');
})->add(\Logger::class.':ValidarSesion');

$app->group('/hacer', function (RouteCollectorProxy $group) {
  $group->get('/pedidos/detalle', \PedidoController::class . ':PrepararDetallePedido')->add(\ValidarUsuario::class.':validarRoles');
})->add(\Logger::class.':ValidarSesion');


$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class.':TraerTodos')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->get('/{id}', \MesaController::class.':TraerUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->post('[/]', \MesaController::class.':CargarUno')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->put('[/]', \MesaController::class.':ModificarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->delete('[/]', \MesaController::class.':BorrarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
  $group->get('/mostrar/todos-estado', \MesaController::class . ':SocioTraeEstadoMesa');
})->add(\Logger::class.':ValidarSesion');

$app->group('/cobrar', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class.':cobrarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocioOMozo');
  $group->post('/cerrar', \MesaController::class.':cerrarMesa')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');
})->add(\Logger::class.':ValidarSesion');


$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/descargarUsuarios', \UsuarioController::class.'::DescargarCSV');
    $group->post('/cargarUsuarios', \UsuarioController::class.'::CargarCSV');
    $group->get('/descargarProductos', \ProductoController::class.'::DescargarCSV');
    $group->post('/cargarProductos', \ProductoController::class.'::CargarCSV');
    $group->get('/descargarMesas', \MesaController::class.'::DescargarCSV');
    $group->get('/descargarPdfUsuarios', \UsuarioController::class.':DescargarPDF');
})->add(\Logger::class.':ValidarSesion')->add(\ValidarUsuario::class.':ValidarPermisosDeRolSocio');


$app->post('/comentar', \ComentarioController::class.':CargarUno')
->add(\ValidarMesas::class.':ValidarMesaCerrada')
->add(\ValidarComentarios::class.':ValidarCodigoMesa')
->add(\ValidarMesas::class.':ValidarMesaCodigoMesa')
->add(\ValidarComentarios::class.':ValidarCamposComentario');

$app->get('/mejores-comentarios', \ComentarioController::class.':TraerMejores')->add(\validarUsuario::class.':ValidarPermisosDeRolSocio');

$app->run();