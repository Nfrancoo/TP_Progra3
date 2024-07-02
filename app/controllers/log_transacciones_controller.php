<?php

require_once("./models/class_log_transaccion.php");
require_once("./models/Usuario.php");

class LogTransaccionesController {


    public static function InsertarLogTransaccion($idUsuario, $accion, $code)
    {
        $logTransaccion = new LogTransaccion();
        $logTransaccion->idUsuario = $idUsuario;
        $logTransaccion->code = $code;
        $logTransaccion->accion = $accion;

        $logTransaccion->Insertar();
    }
}
