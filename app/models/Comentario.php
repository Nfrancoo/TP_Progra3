<?php

class Comentario{
    public $id;
    public $idMesa;
    public $idCliente;
    public $fechaComentario;
    public $puntajeMesa;
    public $puntajeMozo;
    public $puntajeComida;
    public $puntajeGeneral;
    public $idPedido;
    public $comentario;
    
    public function crearComentario() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO comentarios (idMesa, idCliente, fechaComentario, puntajeMesa, puntajeMozo, puntajeComida, puntajeGeneral, idPedido, comentario) VALUES (:idMesa, :idCliente, :fechaComentario, :puntajeMesa, :puntajeMozo, :puntajeComida, :puntajeGeneral, :idPedido, :comentario)");
    
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':idCliente', $this->idCliente, PDO::PARAM_INT);
        $consulta->bindValue(':fechaComentario', $this->fechaComentario, PDO::PARAM_STR);
        $consulta->bindValue(':puntajeMesa', $this->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMozo', $this->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeComida', $this->puntajeComida, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeGeneral', $this->puntajeGeneral, PDO::PARAM_INT);
        $consulta->bindValue(':idPedido', $this->idPedido, PDO::PARAM_INT);
        $consulta->bindValue(':comentario', $this->comentario, PDO::PARAM_STR);
    
        $consulta->execute();
    
        return $objAccesoDatos->obtenerUltimoId();
    }
    

    public static function obtenerTodos(){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idMesa, idCliente, fechaComentario, puntajeMesa, puntajeMozo, puntajeComida, puntajeGeneral, idPedido, comentario FROM comentarios");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Comentario');
    }

    public static function obtenerComentario($id){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idMesa, idCliente, fechaComentario, puntajeMesa, puntajeMozo, puntajeComida, puntajeGeneral, idPedido, comentario FROM comentarios WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Comentario');
    }

    public static function obtenerComentarioCodigoMesa($idMesa){
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idMesa, idCliente, fechaComentario, puntajeMesa, puntajeMozo, puntajeComida, puntajeGeneral, idPedido, comentario FROM comentarios WHERE idMesa = :idMesa");
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Comentario');
    }
    public static function modificarComentario($comentario) {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE comentarios SET comentario = :comentario, puntajeMesa = :puntajeMesa, puntajeMozo = :puntajeMozo, puntajeComida = :puntajeComida, puntajeGeneral = :puntajeGeneral WHERE id = :id");
        
        $consulta->bindValue(':id', $comentario->id, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMesa', $comentario->puntajeMesa, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeMozo', $comentario->puntajeMozo, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeComida', $comentario->puntajeComida, PDO::PARAM_INT);
        $consulta->bindValue(':puntajeGeneral', $comentario->puntajeGeneral, PDO::PARAM_INT);
        $consulta->bindValue(':comentario', $comentario->comentario, PDO::PARAM_STR);
        
        $consulta->execute();
    }

    public static function borrarComentario($comentario){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM comentarios WHERE id = :id");
        $consulta->bindValue(':id', $comentario->id, PDO::PARAM_INT);
        $consulta->execute();
    }
    
}