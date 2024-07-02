<?php
class LogTransaccion
{
    public $nroTransaccion;
    public $fecha;
    public $idUsuario;
    public $code;
    public $accion;

    public function Insertar()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();

        $fecha = new DateTime();
        $fechaStr = $fecha->format('d/m/Y H:i:s');
        $this->fecha = $fechaStr;

        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO logTransacciones (fecha, idUsuario, accion, code) VALUES (:fecha, :idUsuario, :accion, :code)");


        $consulta->bindValue(':fecha', $this->fecha, PDO::PARAM_STR);
        $consulta->bindValue(':idUsuario', $this->idUsuario, PDO::PARAM_STR);
        $consulta->bindValue(':code', $this->code, PDO::PARAM_INT);
        $consulta->bindValue(':accion', $this->accion, PDO::PARAM_INT);
        $consulta->execute();

        $resultado =  $objAccesoDatos->obtenerUltimoId();

        return $resultado;
    }
    

    public static function TraerTodo()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM logTransacciones");
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_CLASS, 'logTransaccion');
    }

}