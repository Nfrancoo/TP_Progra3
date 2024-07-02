<?php
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseClass;


class ValidarUsuario{

    public static function ValidarCampos(Request $request,  RequestHandler $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['nombre']) || isset($parametros['clave']) || isset($parametros['rol']) || isset($parametros['estado'])){
            ValidarUsuario::ValidarRol($parametros["rol"]);
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }

    public static function ValidarCampoIdUsuario(Request $request,  RequestHandler $handler){
        $parametros = $request->getQueryParams();
        if(isset($parametros['idUsuario'])){
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }



    public static function ValidarRol($rol){
        $rol_usuario = ["socio", "mozo", "cocinero", "bartender", "cervecero", "maestro pastelero", "usuario"];

        if(!in_array($rol, $rol_usuario)){
            throw new Exception('El rol proporcionado no existe');
        }
    }
}