<?php
require_once './models/Usuario.php';
require_once './models/LoginUsuario.php';
class Logger
{
    public static function LogOperacion($request, $response, $next)
    {
        $retorno = $next($request, $response);
        return $retorno;
    }

    private static function RegistrarLogin($idUsuario)
    {
        $registroLogin = new LoginUsuario();
        $registroLogin->idUsuario = $idUsuario;
        $registroLogin->RegristroEnSistema();
    }

    public static function Logeuar($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $id = $parametros['id'];
        $clave = $parametros['clave'];
        $usuario = Usuario::obtenerUsuario($id);
        if($usuario !== null && password_verify($clave, $usuario->clave)){
            $payload = json_encode(array('mensaje'=>'Logueo Exitoso - Usted es: [ '.$usuario->rol.' ]'));
            Logger::RegistrarLogin($usuario->id);
        }
    }

    public static function Salir($request, $response, $args){
        $payload = json_encode(array('mensaje'=>'Sesion Cerrada'));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}