<?php
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseClass;

class ValidarUsuario{

    public static function VerificarUsuario($request, $handler){
        $cookies = $request->getCookieParams();
        $token = $cookies['JWT'];
        Autentificador::VerificarToken($token);
        $datos = Autentificador::ObtenerData($token);
        if(self::ValidarRol($datos->rol)){
            return $handler->handle($request);
        }
        else{
            throw new Exception('No autorizado');
        }
    }

    public static function ValidarPermisosDeRolSocio($request, $handler, $rol = false) {
        $header = $request->getHeaderLine('Authorization');
        $response = new ResponseClass();
    
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
    
            try {
                Autentificador::VerificarToken($token);
                $datos = Autentificador::ObtenerData($token);
    
                if ((!$rol && $datos->rol == 'socio') ||
                    ($rol && $datos->rol == $rol) ||
                    $datos->rol == 'socio') {
                    return $handler->handle($request);
                } else {
                    throw new Exception('Acceso denegado, no tienes permisos  ');
                }
            } catch (Exception $e) {
                $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
            }
        } else {
            $response->getBody()->write(json_encode(array("error" => "Token no encontrado")));
        }
    
        return $response->withHeader('Content-Type', 'application/json');
    }
    

    public static function ValidarPermisosDeRolSocioOMozo($request, $handler){
        $header = $request->getHeaderLine('Authorization');
    
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::VerificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                if ($datos->rol == 'socio' || $datos->rol == 'mozo') {
                    return $handler->handle($request);
                } else {
                    throw new Exception('Acceso denegado');
                }
            } catch (Exception $e) {
                $response = new \Slim\Psr7\Response();
                $response->getBody()->write(json_encode(array("error" => $e->getMessage())));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } else {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(array("error" => "Token no encontrado")));
            return $response->withHeader('Content-Type', 'application/json');
        }
    }


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
        $rol_usuario = ["socio", "mozo", "cocinero", "bartender", "cervecero", "maestro pastelero"];

        if(!in_array($rol, $rol_usuario)){
            throw new Exception('El rol proporcionado no existe');
        }
    }
}