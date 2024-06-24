<?php
require_once('./models/Usuario.php');
require_once './models/LoginUsuario.php';
require_once './models/Registro.php';
use Slim\Psr7\Response as ResponseMW;
use Slim\Psr7\Response as ResponseClass;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;


class Logger
{

    
    public static function LoguearUsuario($request, $response, $args){
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $clave = $parametros['clave'];
        $usuario = Usuario::obtenerUsuarioPorNombre($nombre);
        //var_dump($usuario->clave);
        if($usuario !== null && password_verify($clave, $usuario->clave)){
            $token = Autentificador::CrearToken(array('id' => $usuario->id, 'nombre' => $usuario->nombre, 'rol' => $usuario->rol, 'estado' => $usuario->estado));
            $payload = json_encode(array('mensaje'=>'Logueo Exitoso - Usted es: [ '.$usuario->rol.' ], tu token es: '.$token));

            $registro = new Registro();
            $registro->idUsuario = $usuario->id;
            $registro->fechaInicio = date('Y-m-d H:i:s');

            $registro->crearUsuario();  
            $payload = json_encode(array('jwt' => $token));

        }
        else{
            $payload = json_encode(array('mensaje'=>'Datos Invalidos'));
        }
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function Salir($request, $response, $args){
        $payload = json_encode(array('mensaje'=>'Sesion Cerrada'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function ValidarSesion($request, $handler)
    {
        $header = $request->getHeaderLine('Authorization');
        $response = new ResponseClass();

        if($header)
        {
            $token = trim(explode("Bearer", $header)[1]);
        }
        else{$token = '';}

        try
        {
            $datos = Autentificador::ObtenerData($token);
            if($datos->estado == "activo")
            {
                $response = $handler->handle($request);
            }else
            {
                $response->getBody()->write(json_encode(array("error" => "no es un usuario activo"))); 
            }
        }catch(Exception $e)
        {
            $response->getBody()->write(json_encode(array("error" => $e->getMessage()))); 
        }

        return $response;
        
    }

    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }

}