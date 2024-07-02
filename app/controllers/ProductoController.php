<?php
require_once './models/Productos.php';
require_once './interfaces/IApiUsable.php';
class ProductoController extends Productos implements IApiUsable{

    public function TraerUno($request, $response, $args){

        $id = $args['id'];
        $prd = Productos::obtenerProducto($id);
        $payload = json_encode($prd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args)
    {
        $lista = Productos::obtenerTodos();
        $payload = json_encode(array("listaProducto" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
        
    }

    public function CargarUno($request, $response, $args){
        $parametros = $request->getParsedBody();

        $producto = $parametros['producto'];
        $tipo = $parametros['tipo'];
        $precio = $parametros['precio'];
        $tiempo = $parametros['tiempoPreparacion'];
        $prd = new Productos();
        $prd->producto = $producto;
        $prd->tipo = $tipo;
        $prd->precio = $precio;
        $prd->tiempoPreparacion = $tiempo;
        $prd->crearProducto();

        $payload = json_encode(array("mensaje" => "Producto creado con exito"));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function BorrarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        Productos::borrarProducto($parametros['id']);
        $payload = json_encode(array("mensaje" => "Producto borrado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    public function ModificarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $prd = Productos::obtenerProducto($parametros['id']);
        $prd->producto = $parametros["producto"];
        $prd->tipo = $parametros['tipo'];
        $prd->precio = $parametros['precio'];
        $prd->tiempoPreparacion = $parametros['tiempoPreparacion'];
        Productos::modificarProducto($prd);
        $payload = json_encode(array("mensaje" => "Producto modificado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function DescargarCSV($request, $response, $args) {
        $productos = Productos::obtenerTodos();
        $fecha = new DateTime(date('Y-m-d'));
        $filename = "productos-" . $fecha->format('Y-m-d') . ".csv";
        
        $response = $response->withHeader('Content-Type', 'text/csv')
                             ->withHeader('Content-Disposition', 'attachment; filename="' . $filename . '"');
        
        $output = fopen('php://output', 'w');
        $encabezado = array('id','producto', 'tipo', 'precio', 'tiempoPreparacion');
        fputcsv($output, $encabezado);
        foreach($productos as $producto){
            $linea = array($producto->id, $producto->producto, $producto->tipo, $producto->precio, $producto->tiempoPreparacion);
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
            $producto = new Productos();
            $producto->id = $datos[0];
            $producto->producto = $datos[1];
            $producto->tipo = $datos[2];
            $producto->precio = $datos[3];
            $producto->tiempoPreparacion = $datos[4];
            $producto->crearProducto();
        }
        
        fclose($archivo);
        $payload = json_encode(array("mensaje" => "Lista de productos cargada exitosamente"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }
    
}