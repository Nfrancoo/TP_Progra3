<?php

require_once './models/Pedido.php';
require_once './models/Productos.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable{

    public function TraerUno($request, $response, $args){
        $codigo = $args['codigo'];
        $pedido = Pedido::obtenerPedido($codigo);
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

    

    public function CargarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $producto = Productos::obtenerProducto($parametros['idProducto']);
        $pedido = new Pedido();
        $pedido->codigo = self::generarCodigoPedido();
        // $pedido->idMesa = 
        $pedido->idProducto = $parametros['idProducto'];
        $pedido->sector = self::ChequearSector($producto->tipo);
        var_dump($producto->tipo);
        $pedido->cantidad = $parametros['cantidad'];
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->precio = $producto->precio * $parametros['cantidad'];
        $pedido->crearPedido();
        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ChequearSector($tipo){
        if($tipo === 'comida'|| $tipo === "comida"){
            return 'cocina';
        }
        else if($tipo === 'bebidas' || $tipo === 'cerveza'){
            return 'barra de bebidas';
        }
        else if($tipo === 'cerveza'|| $tipo === 'Cerveza'){
            return 'barra de choperas';
        }
        else if($tipo === 'postre' || $tipo === 'Postre'){
            return 'candybar';
        }
    }

    public static function generarCodigoPedido(){
        $caracteres = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $longitud = 16;
        $codigo = '';
        for ($i = 0; $i < $longitud; $i++) {
            $codigo .= $caracteres[rand(0, strlen($caracteres) - 1)];
        }
        return $codigo;
    }

    public function BorrarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        if(isset($parametros['id'])){
            $pedido = Pedido::obtenerPedidoPorId($parametros['id']);
            var_dump($pedido);
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
        $pedido->cantidad = $parametros['cantidad'];
        $producto = Productos::obtenerProducto($parametros['idProducto']);
        $pedido->precio = $producto->precio * $parametros['cantidad'];
        if(isset($parametros['idProducto'])){
            Pedido::modificarPedido($pedido, $parametros['idProducto']);
        }
        else{
            Pedido::modificarPedido($pedido, false);
        }
        $payload = json_encode(array("mensaje" => "Pedido modificado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public static function RecibirPedidos($request, $response, $args) {
        $idPedido = $args['idPedido'];
        $pedido = Pedido::obtenerPedidoPorId($idPedido);
        Pedido::PedidoPendiente($pedido);
        $payload = json_encode(array("mensaje" => 'Comenzo la preparacion del pedido'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function PrepararPedido($request, $response, $args) {
        $idPedido = $args['idPedido'];
        $pedido = Pedido::obtenerPedidoPorId($idPedido);
        Pedido::PedidoEnPreparacion($pedido);
        $payload = json_encode(array("mensaje" => 'Finalizo la preparacion del pedido'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function EntregarPedidoFinalizado($request, $response, $args) {
        $idPedido = $args['idPedido'];
        Pedido::LlevarPedido($idPedido);
        $payload = json_encode(array("mensaje" => 'Que lo disfrutes'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


}