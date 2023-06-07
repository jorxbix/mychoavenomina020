<?php
require_once "classes/Conexion.class.php";
require_once "classes/Aeropuerto.class.php";
require_once "classes/Usuario.class.php";
require_once "classes/Dieta.class.php";
require_once "classes/Perfil.class.php";
require_once "classes/NivelSalarial.class.php";
require_once "classes/Servicio.class.php";
require_once "classes/Piloto.class.php";
require_once "classes/Vuelo.class.php";
require_once "classes/Imaginaria.class.php";
require_once "classes/Fallo.class.php";
require_once "classes/HoraMemorizada.class.php";
require_once "classes/Firma.class.php";
require_once "classes/Suelo.class.php";
require_once "classes/Reserva.class.php";
session_start();
//he incluido las clases antes del sesision start pq estaba recibiendo un error
//_PHP_Incomplete_Class_Name con la clasee HoraMemorizada almacenada en session

$sonVuelos=["CO","PM","PR","PS","VS"];
$sonServTierra=["SR","OC","EN","XX","SA","FR","CR"];
$sonLibres=["LI","LN","LD","VA","LA","RT","LB","BA"];

//09h para considerar dos actividades diferrentes
$tiempoEntreServicios=new DateInterval("PT09H");
//valores por defecto de limites h1 y h2
$limiteH1=55;
$limiteH2=75;
$limiteActExtra=160;

?>
