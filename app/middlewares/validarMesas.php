<?php

require_once './models/Mesa.php';

class ValidarMesas{

    public static function ValidarMesa($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['id'])){
            $mesa = Mesa::obtenerMesa($parametros['id']);
            if($mesa){
                return $handler->handle($request);
            }
        }
        throw new Exception('Mesa no existente');
    }

    public static function ValidarMesaCodigoMesa($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['codigoMesa'])){
            $mesa = Mesa::obtenerMesaPorCodigo($parametros['codigoMesa']);
            if($mesa){
                return $handler->handle($request);
            }
        }
        throw new Exception('Mesa no existente');
    }

    public static function ValidarMesaPorIdComentario($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['idMesa'])){
            var_dump($parametros['idMesa']);
            $mesa = Mesa::obtenerMesa($parametros['idMesa']);
            if($mesa){
                return $handler->handle($request);
            }
        }
        throw new Exception('Mesa no existente');
    }


    public static function ValidarCampos($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['codigo'])){
            $codigo = $parametros['codigo'];
            $mesa = Mesa::obtenerMesa($codigo);
            if(self::ValidarMesaExistente($mesa)){
                return $handler->handle($request);
            }
        }
        throw new Exception('Campos Invalidos');
    }

    public static function ValidarMesaCerradaPorId($request, $handler){
        $parametros = $request->getParsedBody();
        $mesa = Mesa::obtenerMesa($parametros['idMesa']);
        if($mesa->estado == "cerrada"){
            throw new Exception('la mesa esta cerrada');
        }
        return $handler->handle($request);
    }

    public static function ValidarMesaCerradaPorCodigo($request, $handler){
        $parametros = $request->getParsedBody();
        $mesa = Mesa::obtenerMesaPorCodigo($parametros['idMesa']);
        if($mesa->estado == "cerrada"){
            throw new Exception('la mesa esta cerrada');
        }
        return $handler->handle($request);
    }
        

    public static function ValidarCamposCobroEntreFechas($request, $handler){
        $parametros = $request->getQueryParams();
        if (isset($parametros['codigo']) && isset($parametros['fechaEntrada']) && isset($parametros['fechaSalida']))
        {
            $mesa = Mesa::obtenerMesaPorCodigo($parametros['codigo']);
            if($mesa && $mesa->estado == "cerrada")
            {
                return $handler->handle($request);
            }
            else
            {
                throw new Exception('la mesa no esta cerrada o no existe');
            }
        }
        else
        {
            throw new Exception('Campos Invalidos');
        }
    }


    public static function ValidarMesaExistente($mesa){
        if($mesa){
            return true;
        }
        return false;
    }

}