<?php
require_once './models/Productos.php';

class ValidarProductos{
    public static function ValidarCampos($request, $handler){
        $parametros = $request->getParsedBody();
        if(isset($parametros['producto']) ||isset($parametros['tipo']) || isset($parametros['precio']) || isset($parametros['tiempoPreparacion'])){
            return $handler->handle($request);
        }
        throw new Exception('Campos Invalidos');
    }
}