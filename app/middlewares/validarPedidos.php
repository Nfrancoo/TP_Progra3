<?php
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Pedido.php';
//no use actualmente
class ValidarPedidos{
    public static function ValidarMesaExistente(Request $request,  RequestHandler $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['idMesa'])){
            $codigo = $parametros['idMesa'];
            $mesa = Mesa::obtenerMesaPorCodigo($codigo);
            if($mesa){
                return $handler->handle($request);
            } else {
                throw new Exception('Mesa no existente');
            }
        } else {
            throw new Exception('Falta el parámetro idMesa');
        }
    }

    public static function ValidarCampos(Request $request,  RequestHandler $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['idMesa'], $parametros['idProducto'], $parametros['nombreCliente'], $parametros['cantidad'])){
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }
    
    public static function ValidarEstado(Request $request,  RequestHandler $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['id'])){
            $pedido = Pedido::obtenerPedidoPorId($parametros['id']);
            if($pedido->estado == 'pendiente'){
                return $handler->handle($request);
            }
            else{
                throw new Exception('El pedido no se puede modificar porque se finalizo la preparacion o fue cancelado');
            }
        }
        throw new Exception('Campos Invalidos');
    }
}