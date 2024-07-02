<?php
use Slim\Psr7\Response as ResponseClass;

require_once './models/Comentario.php';
require_once './interfaces/IApiUsable.php';
class ComentarioController extends Comentario implements IApiUsable{
    
    public function TraerUno($request, $response, $args){
        $parametros = $request->getQueryParams();
        $id = $parametros['id'];
        $prd = Comentario::obtenerComentario($id);
        $payload = json_encode($prd);

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos($request, $response, $args){
        $lista = Comentario::obtenerTodos();
        $payload = json_encode(array("listaComentario" => $lista));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function TraerMejores($request, $response, $args){
        $lista = Comentario::obtenerTodos();
        $lista_mejores = [];
        $max_puntaje = 6;
        foreach ($lista as $comentario)
        {
            if ($comentario->puntajeGeneral > $max_puntaje)
                $lista_mejores[] = $comentario;    
        }

        $payload = json_encode(array("listaComentario" => $lista_mejores));

        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function CargarUno($request, $response, $args) {
        $header = $request->getHeaderLine('Authorization');
    
        if ($header && preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            $token = $matches[1];
    
            try {
                Autentificador::VerificarToken($token);
                $datos = Autentificador::ObtenerData($token);
    
                $parametros = $request->getParsedBody();
    
                $idMesa = $parametros['idMesa'];
                $idCliente = $datos->id;
                $puntajeMesa = $parametros['puntajeMesa'];
                $puntajeMozo = $parametros['puntajeMozo'];
                $puntajeComida = $parametros['puntajeComida'];
                $puntajeGeneral = ($puntajeMesa + $puntajeMozo + $puntajeComida) / 3;
                $idPedido = $parametros['idPedido'];
                $comentario = $parametros['comentario'];
    
                // Validar el puntaje antes de crear el comentario
                if ($puntajeGeneral < 1 || $puntajeGeneral > 10) {
                    $payload = json_encode(array("mensaje" => "Puntaje invalido. Debe estar entre 1 y 10."));
                    $response->getBody()->write($payload);
                    return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
                }
    
                $prd = new Comentario();
                $prd->idMesa = $idMesa;
                $prd->idCliente = $idCliente;
                $prd->puntajeMesa = $puntajeMesa;
                $prd->puntajeMozo = $puntajeMozo;
                $prd->puntajeComida = $puntajeComida;
                $prd->puntajeGeneral = $puntajeGeneral;
                $prd->idPedido = $idPedido;
                $prd->comentario = $comentario;
                $prd->fechaComentario = date('Y-m-d H:i:s'); // Asignar la fecha de comentario aquí
    
                $prd->crearComentario();
    
                $payload = json_encode(array("mensaje" => "Comentario creado con exito"));
    
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(201);
            } catch (Exception $e) {
                $payload = json_encode(array("error" => $e->getMessage()));
                $response->getBody()->write($payload);
                return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
            }
        }
    
        $payload = json_encode(array("error" => "Token no proporcionado o inválido"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus(401);
    }
    

    public function BorrarUno($request, $response, $args){
        $parametros = $request->getParsedBody();
        $comentario = Comentario::obtenerComentario($parametros['id']);
        Comentario::borrarComentario($comentario);
        $payload = json_encode(array("mensaje" => "Comentario borrado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno($request, $response, $args) {
        $parametros = $request->getParsedBody();
    
        $comentario = Comentario::obtenerComentario($parametros['id']);
    
        if (isset($parametros['puntajeMesa'])) {
            $comentario->puntajeMesa = $parametros['puntajeMesa'];
        }
        if (isset($parametros['puntajeMozo'])) {
            $comentario->puntajeMozo = $parametros['puntajeMozo'];
        }
        if (isset($parametros['puntajeComida'])) {
            $comentario->puntajeComida = $parametros['puntajeComida'];
        }
        if (isset($parametros['puntajeMesa']) || isset($parametros['puntajeMozo']) || isset($parametros['puntajeComida'])) {
            $comentario->puntajeGeneral = ($comentario->puntajeMesa + $comentario->puntajeMozo + $comentario->puntajeComida) / 3;
        }
        if (isset($parametros['comentario'])) {
            $comentario->comentario = $parametros['comentario'];
        }
    
        Comentario::modificarComentario($comentario);
    
        $payload = json_encode(array("mensaje" => "Comentario modificado con exito"));
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json');
    }

}