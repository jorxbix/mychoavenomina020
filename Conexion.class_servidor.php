<?php
class Conexion
{
private $host;
private $db;
private $user;
private $pass;
private $dsn;
protected $conexion;

public function __construct()
{
$this->host = "localhost";
$this->db = "u941083279_choave030";
$this->user = "u941083279_choaveusuario3";
$this->pass = "Ciutadella43-";
$this->dsn = "mysql:host={$this->host};dbname={$this->db};charset=utf8mb4";
$this->conexion = $this->crearConexion();
}

public function crearConexion()
{
    try {
         $conexion = new PDO($this->dsn, $this->user, $this->pass);
         $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $ex) {
        die("Error en la conexión: mensaje: " . $ex->getMessage());
    }
    return $conexion;
}

public function query($cadena_sql){

    return $this->conexion->query($cadena_sql);

}
}

?>