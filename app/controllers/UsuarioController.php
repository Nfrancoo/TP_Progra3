<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';

class UsuarioController extends Usuario implements IApiUsable
{
    public function CargarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();

        $nombre = $parametros['nombre'];
        $rol = $parametros["rol"];
        $clave = $parametros['clave'];
        $usr = new Usuario();
        $usr->nombre = $nombre;
        $usr->rol = $rol;
        $usr->clave = $clave;
        $usr->fechaInicio = date("Y-m-d");
        $usr->crearUsuario();

        $payload = json_encode(array("mensaje" => "Usuario creado con exito"));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function TraerUno($request, $response, $args)
    {
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuario($id);
        $payload = json_encode($usuario);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Usuario::obtenerTodos();
        $payload = json_encode(array("listaUsuario" => $lista));

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }

    public function BajarUno($request, $response, $args)
    {
        $id = $args['id'];
        $usuario = Usuario::obtenerUsuario($id);
        var_dump($usuario);
        if($usuario){
          $usuario->estado = "inactivo";
          $usuario->fechaBaja = date("Y-m-d");
          Usuario::bajarUsuario($usuario);
         $payload = json_encode(array("mensaje" => "Usuario bajado con exito"));
        }else {
          
          $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
      }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    
    public function ModificarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        $usuario = Usuario::obtenerUsuario($parametros["id"]);
        if($usuario){
          $usuario->nombre = $parametros["nombre"];
          $usuario->rol = $parametros["rol"];
          $usuario->clave = $parametros["clave"];
          $usuario->estado = $parametros["estado"];

          // $nombre = $parametros['nombre'];
        Usuario::modificarUsuario($usuario);
        $payload = json_encode(array("mensaje" => "Usuario modificado con exito"));
        }else {
          
          $payload = json_encode(array("mensaje" => "Usuario no encontrado"));
      }

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args)
    {
        $parametros = $request->getParsedBody();
        Usuario::borrarUsuario($parametros["id"]);

        if($parametros["id"]){
          $payload = json_encode(array("mensaje" => "Usuario borrado con exito"));
        } else{
          $payload = json_encode(array("mensaje" => "No puedo borrar el usuario, no me pasas un id correcto"));
        }
        

        $response->getBody()->write($payload);
        return $response
          ->withHeader('Content-Type', 'application/json');
    }
}
