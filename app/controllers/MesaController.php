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
            $payload = json_encode(array("mensaje" => "Mesa cerrada - Total a pagar: [ ".$precioACobrar." ]"));
        }
        else{
            $payload = json_encode(array("mensaje" => "No se encontro la mesa"));
        }
        Mesa::CobrarYCerrarMesa($parametros['codigo']);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}