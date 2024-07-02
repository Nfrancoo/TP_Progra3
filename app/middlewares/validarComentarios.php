<?php
    require_once './models/Comentario.php';
    class ValidarComentarios{
        
        public static function ValidarCamposComentario($request, $handler) {
            $parametros = $request->getParsedBody();
            if (isset($parametros['idMesa']) && isset($parametros['puntajeMesa']) && isset($parametros['puntajeMozo']) && isset($parametros['puntajeComida']) && isset($parametros['idPedido']) && isset($parametros['comentario'])) {
                if ($parametros['puntajeMesa'] < 1 || $parametros['puntajeMesa'] > 10 ||
                $parametros['puntajeMozo'] < 1 || $parametros['puntajeMozo'] > 10 ||
                $parametros['puntajeComida'] < 1 || $parametros['puntajeComida'] > 10) {
                    throw new Exception('Los puntajes deben estar entre 1 y 10.');
                }
                return $handler->handle($request);
                
            }
            throw new Exception('Campos Invalidos');
        }

        public static function ValidarIdMesa($request, $handler){
            $parametros = $request->getParsedBody();
            $comentario = Comentario::obtenerComentarioCodigoMesa($parametros['idMesa']);
            if($comentario){
                throw new Exception('Esta mesa ya tiene un comentario asociado.');
            }
            return $handler->handle($request);
        }

    }
