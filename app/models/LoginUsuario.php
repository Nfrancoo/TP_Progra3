<?php

class LoginUsuario{
    public $id;
    public $idUsuario;
    public $fecheDeRegristro;

    public function RegristroEnSistema(){
        $datos = AccesoDatos::obtenerInstancia();
        $consulta = $datos->prepararConsulta("INSERT TO loginUsuario (idUsuario, fechaDeRegistro) VALUES (:idUsuario, :fechaDeRegistro)");
        $fecha = date('Y-m-d H:i:s');

        $consulta->bindValue(':idUsuario', $this->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaDeRegristro', $fecha, PDO::PARAM_STR);

        $consulta->execute();
        return $datos->obtenerUltimoId();
    }

    public static function ObtenerTodos(){
        $datos = AccesoDatos::obtenerInstancia();
        $consulta = $datos->prepararConsulta("SELECT id, idUsuario, fechaDeRegistro FROM loginUsuario");

        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'LoginUsuario');
    }

    public static function ObtenerPorIdUsuario($idUsuario){
        $datos = AccesoDatos::obtenerInstancia();
        $consulta = $datos->prepararConsulta("SELECT id, idUsuario, fechaDeRegistro FROM loginUsuario WHERE idUsuario = :idUsuario");
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'LoginUsuario');
    }

    public static function ModificarRegistroLogin($id, $idUsuario, $fechaConexion){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE loginUsuario SET fechaConexion = :fechaConexion, idUsuario = :idUsuario WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':idUsuario', $idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':fechaConexion', $fechaConexion, PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function BorrarRegistroLogin($id){
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM registrologin WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();
    }
}