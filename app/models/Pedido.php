<?php

class Pedido {
    public $id;
    public $codigo;
    public $nombreCliente;
    public $idMesa;
    public $estado;
    public $total;
    public $tiempoPreparacion;
    public $tiempoEntrega;
    public $productos = [];

    // Propiedades adicionales para los detalles del pedido
    public $idPedido;
    public $idProducto;
    public $cantidad;
    public $sector;

    public function crearPedido() {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("INSERT INTO pedidos (codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega) VALUES (:codigo, :idMesa, :nombreCliente, :estado, :total, :tiempoPreparacion, :tiempoEntrega)");
        $consulta->bindValue(':codigo', $this->codigo, PDO::PARAM_STR);
        $consulta->bindValue(':idMesa', $this->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':nombreCliente', $this->nombreCliente, PDO::PARAM_STR);
        $consulta->bindValue(':estado', 'pendiente', PDO::PARAM_STR);
        $consulta->bindValue(':total', $this->total, PDO::PARAM_INT);
        $consulta->bindValue(':tiempoPreparacion', $this->tiempoPreparacion, PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEntrega', $this->tiempoEntrega, PDO::PARAM_STR);
        $consulta->execute();
    
        $this->id = $objAccesoDatos->obtenerUltimoId();
    
        foreach ($this->productos as $producto) {
            $sector = self::ChequearSector($producto['tipo']);
            var_dump($sector);
            self::crearDetallePedido($this->id, $producto['idProducto'], $producto['cantidad'], $sector);
        }
    
        return $this->id;
    }
    
    
    public static function crearDetallePedido($id, $idProducto, $cantidad, $sector) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("INSERT INTO pedido_al_detalle (idPedido, idProducto, cantidad, sector, estado) VALUES (:idPedido, :idProducto, :cantidad, :sector, :estado)");
        $consulta->bindValue(':idPedido', $id, PDO::PARAM_INT);
        $consulta->bindValue(':idProducto', $idProducto, PDO::PARAM_INT);
        $consulta->bindValue(':cantidad', $cantidad, PDO::PARAM_INT);
        $consulta->bindValue(':sector', $sector, PDO::PARAM_STR);
        $consulta->bindValue(':estado', "pendiente", PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function CompletarDetallePedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedido_al_detalle SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'completado', PDO::PARAM_STR);
        $consulta->execute();
        
    }

    public static function ChequearSector($tipo){
        if($tipo === 'comida'|| $tipo === "Comida"){
            return 'cocina';
        }
        else if($tipo === 'bebida' || $tipo === 'Bebida'){
            return 'barra de bebidas';
        }
        else if($tipo === 'cerveza'|| $tipo === 'Cerveza'){
            return 'barra de choperas';
        }
        else if($tipo === 'postre' || $tipo === 'Postre'){
            return 'candybar';
        }
        
    }

    public static function obtenerPedidoPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');    
    }

    public static function obtenerDetallePedidoPorId($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idPedido, idProducto, cantidad, sector, estado FROM pedido_al_detalle WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');
    }

    public static function obtenerDetallePedidoPorIdPedido($idPedido)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idPedido, idProducto, cantidad, sector, estado FROM pedido_al_detalle WHERE idPedido = :idPedido");
        $consulta->bindValue(':idPedido', $idPedido, PDO::PARAM_INT);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function obtenerDetallePedidosPendientes()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, idPedido, idProducto, cantidad, sector, estado FROM pedido_al_detalle WHERE estado = :estado");
        $consulta->bindValue(':estado', 'pendiente', PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchAll(PDO::FETCH_OBJ);
    }

    public static function obtenerTodos()
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT * FROM pedidos");
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_ASSOC); 
    }

    
    public static function obtenerTodosFinalizados($estado)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega FROM pedidos WHERE estado = :estado");
        $consulta->bindValue(':estado', $estado, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedido($codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega FROM pedidos WHERE codigo = :codigo");
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetchAll(PDO::FETCH_CLASS, 'Pedido');
    }

    public static function obtenerPedidosPorMesaEId($idMesa, $id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega FROM pedidos WHERE id = :id AND idMesa = :idMesa");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $idMesa, PDO::PARAM_INT);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function obtenerTiempoPedido($id, $codigo)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("SELECT id, codigo, idMesa, nombreCliente, estado, total, tiempoPreparacion, tiempoEntrega FROM pedidos WHERE id = :id AND codigo = :codigo");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':codigo', $codigo, PDO::PARAM_STR);
        $consulta->execute();
        return $consulta->fetch(PDO::FETCH_ASSOC);
    }

    public static function modificarPedido($pedido)
    {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET idMesa = :idMesa, nombreCliente = :nombreCliente WHERE id = :id");
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->bindValue(':idMesa', $pedido->idMesa, PDO::PARAM_INT);
        $consulta->bindValue(':nombreCliente', $pedido->nombreCliente, PDO::PARAM_STR);
        $consulta->execute();
    }


    public static function borrarPedido($pedido) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("DELETE FROM pedidos WHERE id = :id");
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->execute();
    }


    public static function PedidoPendiente($pedido) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");        
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'en preparacion', PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function PedidoEnPreparacion($pedido) {
        $objAccesoDato = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDato->prepararConsulta("UPDATE pedidos SET estado = :estado WHERE id = :id");
        $consulta->bindValue(':id', $pedido->id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'preparado', PDO::PARAM_STR);
        $consulta->execute();
    }

    public static function LlevarPedido($id)
    {
        $objAccesoDatos = AccesoDatos::obtenerInstancia();
        $consulta = $objAccesoDatos->prepararConsulta("UPDATE pedidos SET estado = :estado, tiempoEntrega = :tiempoEntrega WHERE id = :id");
        $fecha = new DateTime(date("Y-m-d H:i:s"));
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        $consulta->bindValue(':estado', 'completado', PDO::PARAM_STR);
        $consulta->bindValue(':tiempoEntrega', date_format($fecha, "Y-m-d H:i:s") , PDO::PARAM_STR);
        $consulta->execute();

        return $consulta->fetchObject('Pedido');   
    }
}