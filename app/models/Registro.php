<?php

class Registro{
    public $id;
    public $idUsuario;
    public $fechaInicio;
    public $fechaBaja;

    public function crearUsuario()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO registro (id_usuario, fecha_inicio) VALUES (:id_usuario, :fecha_inicio)");
        $consulta->bindValue(':id_usuario', $this->idUsuario, PDO::PARAM_INT);
        $consulta->bindValue(':fecha_inicio', $this->fechaInicio, PDO::PARAM_STR);

        $consulta->execute();

        return $objAccesoDatos->obtenerUltimoId();
    }
}