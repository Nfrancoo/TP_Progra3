<?php
require_once('./models/Usuario.php');
require_once './models/LoginUsuario.php';
use Slim\Psr7\Response as ResponseMW;


class Logger
{
    public static function LoguearUsuario($request, $response, $args){
        $parametros = $request->getParsedBody();
        $nombre = $parametros['nombre'];
        $clave = $parametros['clave']; //lo tenia para verificar que eran lo mismo pero no me funciona me tira false si hago el password_verify
        $usuario = Usuario::obtenerUsuarioPorNombre($nombre);
        //var_dump($usuario->clave);
        if($usuario !== null){
            $token = Autentificador::CrearToken(array('id' => $usuario->id, 'nombre' => $usuario->nombre, 'rol' => $usuario->rol, 'estado' => $usuario->estado));
            setcookie('JWT', $token, time()+60*60*24*30, '/', 'localhost', false, true);
            $payload = json_encode(array('mensaje'=>'Logueo Exitoso - Usted es: [ '.$usuario->rol.' ], tu token es: '.$token));
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

    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }
}