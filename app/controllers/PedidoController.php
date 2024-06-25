<?php

require_once './models/Pedido.php';
require_once './models/Productos.php';
require_once './interfaces/IApiUsable.php';
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Exception\HttpNotFoundException;

class PedidoController extends Pedido implements IApiUsable{
 

    public function CargarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();

        $productos = json_decode($parametros['productos'], true);
        
        if (!is_array($productos)) {
            $payload = json_encode(array("error" => "Formato de productos inválido"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
        }

        // Inicializar un nuevo pedido
        $pedido = new Pedido();
        $pedido->codigo = self::generarCodigoPedido();
        $pedido->idMesa = $parametros["idMesa"];
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->productos = $productos;
        
        // Calcular el total y determinar el sector
        $total = 0;
        foreach ($pedido->productos as &$producto) {
            $productoData = Productos::obtenerProducto($producto['idProducto']);
            if (!$productoData) {
                $payload = json_encode(array("error" => "Producto con id {$producto['idProducto']} no encontrado"));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(404);
            }
            $producto['precio'] = $productoData->precio;
            $producto['tipo'] = $productoData->tipo;
            $total += $productoData->precio * $producto['cantidad'];
        }
        $pedido->total = $total;
        $pedido->tiempoPreparacion = $parametros["tiempoPreparacion"];

        // Crear el pedido y los detalles
        $pedido->crearPedido();

        // Manejar la subida de archivos
        $carpeta_archivo = 'C:\xampp\htdocs\TP_Progra3\app\imagen-mesa';
        $uploadedFiles = $request->getUploadedFiles();
        if (isset($uploadedFiles['archivo'])) {
            $uploadedFile = $uploadedFiles['archivo'];

            if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
                $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
                $filename = "{$parametros['nombreCliente']}-{$pedido->codigo}-{$parametros['idMesa']}.{$extension}";
                $uploadedFile->moveTo($carpeta_archivo . DIRECTORY_SEPARATOR . $filename);
            }
        }

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args){
        $id = $args['id'];
        
        $pedido = Pedido::obtenerPedidoPorId($id);
        //var_dump($pedido);
        $payload = json_encode($pedido);
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args){
        $lista = Pedido::obtenerTodos();
        $payload = json_encode(array("listaPedidos" => $lista));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ChequearSector($tipo){
        if($tipo === 'comida'|| $tipo === "Comida"){
            return 'cocina';
        }
        else if($tipo === 'bebidas' || $tipo === 'Bebidas'){
            return 'barra de bebidas';
        }
        else if($tipo === 'cerveza'|| $tipo === 'Cerveza'){
            return 'barra de choperas';
        }
        else if($tipo === 'postre' || $tipo === 'Postre'){
            return 'candybar';
        }
        
    }

    // public static function TraerTodosPorSector($request, $response, $args) {
    //     $cookie = $request->getCookieParams();
    //     if(isset($cookie['JWT'])){
    //         $token = $cookie['JWT'];
    //         $datos = Autentificador::ObtenerData($token);
    //         if($datos->rol == 'cocinero'){
    //             $lista = Pedido::obtenerTodosPorSector('cocina');
    //         }
    //         if($datos->rol == 'bartender'){
    //             $lista = Pedido::obtenerTodosPorSector('barra de bebidas');
    //         }
    //         if($datos->rol == 'cervecero'){
    //             $lista = Pedido::obtenerTodosPorSector('barra de choperas');
    //         }
    //         if($datos->rol == 'maestro pastelero'){
    //             $lista = Pedido::obtenerTodosPorSector('candybar');
    //         }
    //         $payload = json_encode(array("listaPedidos" => $lista));
    //     }
    //     else{
    //         $payload = json_encode(array("listaPedidos" => 'No hay pedidos para tu sector'));
    //     }
    //     $response->getBody()->write($payload);
    //     return $response->withHeader('Content-Type', 'application/json');
    // }

    public static function generarCodigoPedido(){
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longitud = 5;
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

    public function BorrarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        if(isset($parametros['id'])){
            //var_dump($parametros["id"]);
            $pedido = Pedido::obtenerPedidoPorId($parametros['id']);
            //var_dump($pedido);
            Pedido::borrarPedido($pedido);
            $payload = json_encode(array("mensaje" => "Pedido borrado con exito"));
        }
        else{
            $payload = json_encode(array("mensaje" => "Debe ingresar un id de pedido valido"));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $pedido = Pedido::obtenerPedidoPorId($parametros['id']);
        $pedido->nombreCliente = $parametros["nombreCliente"];
        $pedido->idMesa = $parametros['idMesa'];
        Pedido::modificarPedido($pedido);
        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function EntregarPedidoFinalizado($request, $response, $args) {
        $idPedido = $args['idPedido'];
        
        try {
            $detallesPedido = Pedido::obtenerDetallePedidoPorIdPedido($idPedido);
            foreach ($detallesPedido as $detalle) {

                if ($detalle->estado != 'completado') {
                    throw new Exception('No se puede entregar el pedido porque no todos los productos estan completados');
                }
            }                
            Pedido::LlevarPedido($idPedido);
            $payload = json_encode(array("mensaje" => "¡Que lo disfrutes!"));
            $response->getBody()->write($payload);
            return $response->withHeader('Content-Type', 'application/json');
            
        } catch (Exception $e) {
            $payload = json_encode(array("error" => $e->getMessage()));
            $response->getBody()->write($payload);
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }

    public static function PrepararDetallePedido(Request $request, Response $response, array $args) {
        $parametros = $request->getQueryParams();
        Pedido::CompletarDetallePedido($parametros["id"]);
        $payload = json_encode(array("mensaje" => 'Listo para ser entregado'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ListarPendientesDetallePedido(Request $request, Response $response, array $args) {
        $mensaje = Pedido::obtenerDetallePedidosPendientes();
        $payload = json_encode(array("mensaje" => $mensaje));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public static function ListarTiempoPendiente(Request $request, Response $response, array $args) {
        $parametros = $request->getParsedBody();
        $id = $parametros["id"];
        $codigo = $parametros["codigo"];
        $pedido = Pedido::obtenerTiempoPedido($id, $codigo);
        $payload = json_encode(array("mensaje" => $pedido['tiempoPreparacion']));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function SocioListarTiempoPendiente(Request $request, Response $response, array $args) {
        $pedidos = Pedido::obtenerTodos();
        
        if ($pedidos) {
            foreach ($pedidos as $pedido) {
                $resultado[] = array(
                    "id" => $pedido['id'],
                    "tiempoPreparacion" => $pedido['tiempoPreparacion']
                );
            }
            $payload = json_encode($resultado);
        } else {
            $payload = json_encode(array("mensaje" => "No hay pedidos disponibles"));
        }
    
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}
