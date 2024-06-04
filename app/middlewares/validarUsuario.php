<?php

class ValidarUsuario{

    public static function ValidarCampos($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['nombre']) || isset($parametros['clave']) || isset($parametros['rol']) || isset($parametros['estado'])){
            ValidarUsuario::ValidarRol($parametros["rol"]);
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }

    public static function ValidarCampoIdUsuario($request, $handler){
        $parametros = $request->getQueryParams();
        if(isset($parametros['idUsuario'])){
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }

    public static function ValidarRol($rol){
        $rol_usuario = ["administrador", "socio", "mozo", "cocinero", "bartender", "cervecero"];

        if(!in_array($rol, $rol_usuario)){
            throw new Exception('El rol proporcionado no existe');
        }
    }
}