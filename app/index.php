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
require_once './middlewares/JWT.php';
require_once './models/pdf.php';
require_once './controllers/ComentarioController.php';
require_once './middlewares/validarComentarios.php';
require_once './middlewares/ConfirmarRol.php';
require_once './middlewares/ValidarRolesListar.php';
require_once './middlewares/ConfirmarRolEntrega.php';
require_once './middlewares/log_middleware.php';

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
  ->add(\ValidarProductos::class.':ValidarCampos')->add(new ConfirmarRol('socio'));
  $group->put('[/]', ProductoController::class.':ModificarUno')->add(\ValidarProductos::class.':ValidarTipo')->add(\Logger::class.':ValidarSesion')
  ->add(\ValidarProductos::class.':ValidarCampos')->add(new ConfirmarRol('socio'));
  $group->delete('[/]', \ProductoController::class.':BorrarUno')->add(new ConfirmarRol('socio'))->add(\Logger::class.':ValidarSesion');
})->add(\LogMiddleware::class.':LogTransaccion');



$app->group('/pedidos', function (RouteCollectorProxy $group) {
  $group->get('[/]', \PedidoController::class.':TraerTodos')->add(new ConfirmarRol('socio'));
  $group->get('/{id}', \PedidoController::class.':TraerUno')->add(new ConfirmarRol('socio'));
  $group->post('[/]', \PedidoController::class.':CargarUno')->add(new ConfirmarRol('socio'))->add(\ValidarMesas::class.':ValidarMesaCerradaPorCodigo');
  $group->put('[/]', \PedidoController::class.':ModificarUno')->add(\ValidarPedidos::class.':ValidarEstado')->add(\ValidarMesas::class.':ValidarMesaCerradaPorCodigo')->add(\ValidarPedidos::class.':ValidarMesaExistente')->add(new ConfirmarRol('socio', 'mozo'));
  $group->delete('[/]', \PedidoController::class.':BorrarUno')->add(new ConfirmarRol('socio', 'mozo'));
  $group->get('/listar/pendientes', \PedidoController::class . ':ListarPendientesDetallePedido')->add(new ValidarRolesListar('cocinero', 'cervecero', 'bartender', 'maestro pastelero', 'socio'));
  $group->get('/entregar/pedido/{idPedido}', \PedidoController::class.':EntregarPedidoFinalizado')->add(new ConfirmarRol('socio', 'mozo'));
  $group->post('/mostrar/tiempo', \PedidoController::class.':ListarTiempoPendiente')->add(new ConfirmarRol('socio', 'usuario'));
  $group->get('/mostrar/todos-tiempo', \PedidoController::class . ':SocioListarTiempoPendiente')->add(new ConfirmarRol('socio'));
})->add(\Logger::class.':ValidarSesion')->add(\LogMiddleware::class . ':LogTransaccion');

$app->group('/hacer', function (RouteCollectorProxy $group) {
  $group->get('/pedidos/detalle', \PedidoController::class . ':PrepararDetallePedido')->add(new ConfirmarRolEntrega('cocinero', 'cervecero', 'bartender', 'maestro pastelero', 'socio'));
})->add(\Logger::class.':ValidarSesion')->add(\LogMiddleware::class . ':LogTransaccion');


$app->group('/mesas', function (RouteCollectorProxy $group) {
  $group->get('[/]', \MesaController::class.':TraerTodos')->add(new ConfirmarRol('socio', 'mozo'));
  $group->get('/{id}', \MesaController::class.':TraerUno')->add(new ConfirmarRol('socio', 'mozo'));
  $group->post('[/]', \MesaController::class.':CargarUno')->add(new ConfirmarRol('socio', 'mozo'));
  $group->put('[/]', \MesaController::class.':ModificarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(new ConfirmarRol('socio', 'mozo'));
  $group->delete('[/]', \MesaController::class.':BorrarUno')->add(\ValidarMesas::class.':ValidarMesa')->add(new ConfirmarRol('socio', 'mozo'));
  $group->get('/mostrar/todos-estado', \MesaController::class . ':SocioTraeEstadoMesa');
})->add(\Logger::class.':ValidarSesion')->add(\LogMiddleware::class . ':LogTransaccion');

$app->group('/cobrar', function (RouteCollectorProxy $group) {
  $group->post('[/]', \MesaController::class.':cobrarMesa')->add(new ConfirmarRol('socio', 'mozo'));
  $group->post('/cerrar', \MesaController::class.':cerrarMesa')->add(new ConfirmarRol('socio'));
})->add(\Logger::class.':ValidarSesion')->add(\LogMiddleware::class . ':LogTransaccion');


$app->group('/archivos', function (RouteCollectorProxy $group) {
    $group->get('/descargarUsuarios', \UsuarioController::class.'::DescargarCSV');
    $group->post('/cargarUsuarios', \UsuarioController::class.'::CargarCSV');
    $group->get('/descargarProductos', \ProductoController::class.'::DescargarCSV');
    $group->post('/cargarProductos', \ProductoController::class.'::CargarCSV');
    $group->get('/descargarMesas', \MesaController::class.'::DescargarCSV');
    $group->get('/descargarPdfUsuarios', \UsuarioController::class.':DescargarPDF');
})->add(\Logger::class.':ValidarSesion')->add(new ConfirmarRol('socio'));



$app->group('/comentar', function (RouteCollectorProxy $group) {
  $group->post('/cargarUno', \ComentarioController::class.':CargarUno')
  ->add(\ValidarMesas::class.':ValidarMesaCerradaPorId')
  ->add(\ValidarComentarios::class.':ValidarIdMesa')
  ->add(\ValidarMesas::class.':ValidarMesaPorIdComentario')
  ->add(\ValidarComentarios::class.':ValidarCamposComentario');
  $group->put('/modificarUno', \ComentarioController::class.':ModificarUno');
  $group->delete('/borrarUno', \ComentarioController::class.':BorrarUno');
})->add(new ConfirmarRol('usuario', 'socio'));


$app->get('/mejores-comentarios', \ComentarioController::class.':TraerMejores')->add(new ConfirmarRol('socio'))->add(\LogMiddleware::class . ':LogTransaccion');


$app->group('/estadisticas', function (RouteCollectorProxy $group) {
  $group->get('/promedio', \PedidoController::class.':CalcularPromedioIngresos30Dias');
})->add(new ConfirmarRol('socio'))->add(\Logger::class.':ValidarSesion')->add(\LogMiddleware::class . ':LogTransaccion');


$app->add(\LogMiddleware::class.':LogTransaccion');

$app->run();