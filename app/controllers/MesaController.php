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


    public static function cobrarMesa($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $codigoMesa = $parametros['codigo'];
        $id = $parametros['id'];
        if($codigoMesa){
            $pedido = Pedido::obtenerPedidosPorMesaEId($codigoMesa, $id);
            var_dump($pedido["estado"]);
            $mesa = Mesa::obtenerMesaPorCodigo($codigoMesa);
            $precioACobrar = 0;
            if($pedido["estado"] == 'completado'){
                var_dump($pedido["total"]);
                $precioACobrar += $pedido["total"];
            }
            $payload = json_encode(array("mensaje" => "Total a pagar: [ ".$precioACobrar." ]"));
            Mesa::Cobrar($mesa->codigo, $precioACobrar);
        }
        else{
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function cerrarMesa($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $codigoMesa = $parametros['codigo'];
        $id = $parametros['id'];
        if($codigoMesa){
            $pedido = Pedido::obtenerPedidosPorMesaEId($codigoMesa, $id);
            var_dump($pedido["estado"]);
            $mesa = Mesa::obtenerMesaPorCodigo($codigoMesa);
            $payload = json_encode(array("mensaje" => "Mesa cerrada"));
            Mesa::CerrarMesaSocio($mesa->codigo);
        }
        else{
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function abrirMesa($request, $response, $args) {
        $parametros = $request->getParsedBody();
        $codigoMesa = $parametros['codigo'];
        $id = $parametros['id'];
        if($codigoMesa){
            $pedido = Pedido::obtenerPedidosPorMesaEId($codigoMesa, $id);
            $mesa = Mesa::obtenerMesaPorCodigo($codigoMesa);
            $payload = json_encode(array("mensaje" => "Mesa abierta"));
            Mesa::AbrirMesaSocio($mesa->codigo);
        }
        else{
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }


    public static function DescargarCSV($request, $response, $args) {
        $mesas = Mesa::obtenerTodos();
        $fecha = new DateTime(date('Y-m-d'));
        $filename = "mesas-" . $fecha->format('Y-m-d') . ".csv";
        
        $response = $response->withHeader('Content-Type', 'text/csv')
                             ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        $encabezado = array('id','codigo', 'estado', 'cobro');
        fputcsv($output, $encabezado);
        foreach($mesas as $mesa){
            $linea = array($mesa->id, $mesa->codigo, $mesa->estado, $mesa->cobro);
            fputcsv($output, $linea);
        }

        fclose($output);
        
        return $response;
    }


    public function SocioTraeEstadoMesa($request, $response, $args){
        $lista = Mesa::obtenerTodos();
        if ($lista) {
            foreach ($lista as $mesa) {
                $resultado[] = array(
                    "id" => $mesa->id,
                    "tiempoPreparacion" => $mesa->estado
                );
            }
            $payload = json_encode($resultado);
        }else {
            $payload = json_encode(array("mensaje" => "No hay mesas disponibles"));
        }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}