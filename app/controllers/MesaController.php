<?php
require_once './models/Mesa.php';
require_once './models/Pedido.php';
require_once './interfaces/IApiUsable.php';
class MesaController extends Mesa implements IApiUsable{
    public function TraerUno($request, $response, $args){
        $id = $args['id'];
        $mesa = Mesa::obtenerMesa($id);
        $payload = json_encode($mesa);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args){
        $lista = Mesa::obtenerTodos();
        $payload = json_encode(array("listaMesas" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        
        $mesa = new Mesa();
        $mesa->codigo = Mesa::generarCodigoMesa();
        $mesa->cobro = $parametros["cobro"];
        $mesa->crearMesa();
        $payload = json_encode(array("mensaje" => "Mesa creada con exito - puede empezar a regitrar pedidos con el codigo [ $mesa->codigo ]"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public function BorrarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $mesa = Mesa::obtenerMesa($parametros['id']);
        Mesa::borrarMesa($mesa);
        $payload = json_encode(array("mensaje" => "Mesa borrada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $mesa = Mesa::obtenerMesa($parametros['id']);
        $mesa->cobro = $parametros["cobro"];
        $mesa->estado = $parametros["estado"];
        Mesa::modificarMesa($mesa);
        $payload = json_encode(array("mensaje" => "Mesa modificada con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public static function CerrarMesa($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $codigoMesa = $parametros['codigo'];
        if(isset($codigoMesa)){
            $listaPedidos = Pedido::obtenerPedidosPorMesa($codigoMesa);
            $mesa = Mesa::obtenerMesaPorCodigo($codigoMesa);
            $precioACobrar = 0;
            foreach($listaPedidos as $pedido){
                if($pedido->estado == 'completado'){
                    $precioACobrar += $pedido->importe;
                }
            }
            $mesa->cobro = $precioACobrar;
            Mesa::modificarMesa($mesa);
            $payload = json_encode(array("mensaje" => "Mesa cerrada - Total a pagar: [ ".$mesa->cobro." ]"));
        }
        else{
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        Mesa::CobrarYCerrarMesa($parametros['codigo']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function DescargarCSV($request, $response, $args) {
        $carpeta_archivo = 'C:\xampp\htdocs\TP_Progra3\app\descargas-csv\Mesas/';
        $mesas = Mesa::obtenerMesasCerradas("cerrada");
        $fecha = new DateTime(date('Y-m-d'));
        $path = $carpeta_archivo . date_format($fecha, 'Y-m-d') . 'mesas.csv';
        $archivo = fopen($path, 'w');
        $encabezado = array('id','codigo', 'estado', 'cobro');
        fputcsv($archivo, $encabezado);
        foreach($mesas as $mesa){
            $linea = array($mesa->id, $mesa->codigo, $mesa->estado, $mesa->cobro);
            fputcsv($archivo, $linea);
        }
        $payload = json_encode(array("mensaje" => 'Archivo creado exitosamente'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}