<?php
require_once './models/Usuario.php';
require_once './interfaces/IApiUsable.php';
require_once "./models/pdf.php";

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
        //var_dump($usuario);
        if($usuario){
          $usuario->estado = "inactivo";
          $usuario->fechaBaja = date("Y-m-d");
          Usuario::bajarUsuario($usuario);
         $payload = json_encode(array("mensaje" => "Usuario bajado con exito"));
         //var_dump($usuario);
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

    public static function DescargarCSV($request, $response, $args) {
      
      $usuarios = Usuario::obtenerTodos();
      $fecha = new DateTime(date('Y-m-d'));
      $filename = "usuarios-" . $fecha->format('Y-m-d') . ".csv";
      
      $response = $response->withHeader('Content-Type', 'text/csv')
                           ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
      
      $output = fopen('php://output', 'w');
      $encabezado = array('id','nombre', 'rol', 'clave', 'estado', 'fechaInicio', 'fechaBaja');
      fputcsv($output, $encabezado);
    
      foreach($usuarios as $usuario) {
          $linea = array($usuario->id, $usuario->nombre, $usuario->rol, $usuario->clave, $usuario->estado, $usuario->fechaInicio, $usuario->fechaBaja);
          fputcsv($output, $linea);
      }
      
      fclose($output);
      
      return $response;
    }


    public static function CargarCSV($request, $response, $args) {
      $parametros = $request->getUploadedFiles();
      $archivo = fopen($parametros['archivo']->getFilePath(), 'r');
      $encabezado = fgetcsv($archivo);
      
      while (($datos = fgetcsv($archivo)) !== false) {
          $producto = new Usuario();
          $producto->id = $datos[0];
          $producto->nombre = $datos[1];
          $producto->clave = $datos[2];
          $producto->rol = $datos[2];
          $producto->estado = $datos[4];
          $producto->fechaInicio = $datos[5];
          $producto->fechaBaja = $datos[6];
          $producto->crearUsuario();
      }
      
      fclose($archivo);
      $payload = json_encode(array("mensaje" => "Lista de usuarios cargada exitosamente"));
      $response->getBody()->write($payload);
      return $response->withHeader('Content-Type', 'application/json');
  }


    public function DescargarPDF($request, $response, $args)
    {
        Usuario::ExportarPDF();
        $payload = json_encode(array("mensaje" => "Usuarios exportados a pdf con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
}
