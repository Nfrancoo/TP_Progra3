<?php
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once './models/Mesa.php';

class ValidarMesas{

    public static function ValidarMesa(Request $request,  RequestHandler $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['id'])){
            $mesa = Mesa::obtenerMesa($parametros['id']);
            if($mesa){
                return $handler->handle($request);
            }
        }
        throw new Exception('Mesa no existente');
    }


    public static function ValidarMesaExistente($mesa){
        if($mesa){
            return true;
        }
        return false;
    }
}