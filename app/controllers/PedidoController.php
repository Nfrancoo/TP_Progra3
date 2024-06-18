<?php

require_once './models/Pedido.php';
require_once './models/Productos.php';
require_once './interfaces/IApiUsable.php';

class PedidoController extends Pedido implements IApiUsable{
 

    public function CargarUno($request, $response, $args){
        
        $parametros = $request->getParsedBody();
        $producto = Productos::obtenerProducto($parametros['idProducto']);
        $pedido = new Pedido();
        $pedido->codigo = self::generarCodigoPedido();
        $pedido->idMesa = $parametros["idMesa"];
        $pedido->idProducto = $parametros['idProducto'];
        $pedido->sector = self::ChequearSector($producto->tipo);
        //var_dump($producto->tipo);
        $pedido->cantidad = $parametros['cantidad'];
        $pedido->nombreCliente = $parametros['nombreCliente'];
        $pedido->total = $producto->precio * $parametros['cantidad'];
        $pedido->crearPedido();

        //cargar imagen
        $carpeta_archivo = 'C:\xampp\htdocs\TP_Progra3\app\imagen-mesa';
        $uploadedFiles = $request->getUploadedFiles();
        $uploadedFile = $uploadedFiles['archivo'];

        if ($uploadedFile->getError() === UPLOAD_ERR_OK) {
            $extension = pathinfo($uploadedFile->getClientFilename(), PATHINFO_EXTENSION);
            $filename = "{$parametros['nombreCliente']}-{$pedido->codigo}-{$parametros['idMesa']}.{$extension}";

            $uploadedFile->moveTo($carpeta_archivo . DIRECTORY_SEPARATOR . $filename);
        }

        $payload = json_encode(array("mensaje" => "Pedido creado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args){
        $id = $args['id'];
        
        $pedido = Pedido::obtenerPedidoPorId($id);
        var_dump($pedido);
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
        $pedido->idMesa = $parametros['idMesa'];
        $producto = Productos::obtenerProducto($parametros['idProducto']); 
        $pedido->total = $producto->precio * $pedido->cantidad;
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

    public static function DescargarCSV($request, $response, $args) {
        $carpeta_archivo = 'C:\xampp\htdocs\TP_Progra3\app\descargas-csv\Pedidos/';
        $pedidos = Pedido::obtenerTodosFinalizados('completado');
        $fecha = new DateTime(date('Y-m-d'));
        $path = $carpeta_archivo. date_format($fecha, 'Y-m-d').'pedidos_completados.csv';
        $archivo = fopen($path, 'w');
        $encabezado = array('id','codigo','idMesa','idProducto','nombreCliente','sector','estado','cantidad', 'total');
        fputcsv($archivo, $encabezado);
        foreach($pedidos as $pedido){
            $linea = array($pedido->id, $pedido->codigo, $pedido->idMesa, $pedido->idProducto, $pedido->nombreCliente, $pedido->sector, $pedido->estado,  $pedido->cantidad, $pedido->total,);
            fputcsv($archivo, $linea);
        }
        $payload = json_encode(array("mensaje" => 'Archivo creado exitosamente'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function CargarCSV($request, $response, $args) {
        $parametros = $request->getUploadedFiles();
        $archivo = fopen($parametros['archivo']->getFilePath(), 'r');
        $encabezado = fgetcsv($archivo);
        
        while (($datos = fgetcsv($archivo)) !== false) {
            $producto = new Pedido();
            $producto->id = $datos[0];
            $producto->codigo = $datos[1];
            $producto->idMesa = $datos[2];
            $producto->idProducto = $datos[3];
            $producto->nombreCliente = $datos[4];
            $producto->sector = $datos[5];
            $producto->estado = $datos[6];
            $producto->cantidad = $datos[7];
            $producto->total = $datos[8];
            $producto->crearPedido();
        }
        
        fclose($archivo);
        $payload = json_encode(array("mensaje" => "Lista de pedidos cargada exitosamente"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


}