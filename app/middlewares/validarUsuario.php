<?php
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Response as ResponseClass;


class ValidarUsuario{

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
        $rol_usuario = ["socio", "mozo", "cocinero", "bartender", "cervecero", "maestro pastelero", "usuario"];

        if(!in_array($rol, $rol_usuario)){
            throw new Exception('El rol proporcionado no existe');
        }
    }

    public static function validarRoles($request, $handler) {
        $header = $request->getHeaderLine('Authorization');
        $parametros = $request->getQueryParams();
    
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::verificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                $detallePedido = Pedido::obtenerDetallePedidoPorId($parametros["id"]);
                switch ($detallePedido->sector) {
                    case 'cocina':
                        if ($datos->rol == 'cocinero' || $datos->rol == 'socio') {
                            return $handler->handle($request);
                        }else{
                            throw new Exception('Solo el cocinero y el socio pueden preparar productos de cocina');
                        }
                        break;
                    case 'barra de choperas':
                        if ($datos->rol == 'cervecero' || $datos->rol == 'socio'){
                            return $handler->handle($request);
                        }else{
                            throw new Exception('Solo el cervecero y el socio pueden preparar productos de la barra de choperas');
                        }
                        break;
                    case 'barra de bebidas':
                        var_dump($datos->rol);
                        if ($datos->rol == 'bartender' || $datos->rol == 'socio') {   
                            return $handler->handle($request);
                        }else{
                            throw new Exception('Solo el bartender y el socio pueden preparar productos de la barra de bebidas');
                        }
                        break;
                    case 'candybar':
                        if ($datos->rol == 'maestro pastelero' || $datos->rol == 'socio') {
                            return $handler->handle($request);
                        }else{
                            throw new Exception('Solo el maestro pastelero y el socio pueden preparar productos de candybar');
                        }
                        break;
                    case 'usuario':
                        throw new Exception('Un usuario comun no puede ver nada de esto');
                        break;
                    default:
                        throw new Exception('Sector no valido');
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


    public static function validarRolesListar($request, $handler) {
        $header = $request->getHeaderLine('Authorization');
        
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
            try {
                Autentificador::verificarToken($token);
                $datos = Autentificador::ObtenerData($token);
                $detallesPendientes = Pedido::obtenerDetallePedidosPendientes();
                
                $mensaje = [];

                
                foreach ($detallesPendientes as $detalle) {
                    //var_dump($datos->rol);
                    switch ($detalle->sector) {
                        case 'cocina':
                            if ($datos->rol == "cocinero" || $datos->rol == 'socio') {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'barra de choperas':
                            if ($datos->rol == 'cervecero' || $datos->rol == 'socio') {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'barra de bebidas':
                            if ($datos->rol == 'bartender' || $datos->rol == 'socio') {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'candybar':
                            if ($datos->rol == 'maestro pastelero' || $datos->rol == 'socio') {
                                $mensaje[] = $detalle;
                            }
                            break;
                        case 'usuario':
                            throw new Exception('Un usuario comun no puede ver nada de esto');
                        default:
                            throw new Exception('Sector no válido');
                    }
                }
                $response = new \Slim\Psr7\Response();
                if (empty($mensaje)) {
                    throw new Exception('No tienes acceso a ningún sector');
                }
                $response->getBody()->write(json_encode(array("mensaje" => $mensaje)));
                return $response->withHeader('Content-Type', 'application/json');
    
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
    
    

}