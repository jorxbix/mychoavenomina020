<?php
/**
 * La calse fallo runira informacion del fallo que se pasara por json al front end
 * interrumpira la ejecucion de codigo en el back
 */
class Fallo {

	public $aptIni="";
	public $aptFin="";

	public $fechaIni="";
	public $fechaFin="";

	public $tipo="";
	public $mensaje="";


	public function __construct($fila,$mensaje){

		$this->tipo="ERROR";
		$this->mensaje=$mensaje;

		if(isset($fila->tipo) && isset($fila->aptIni) && isset($fila->aptFin)){

			$this->aptIni=$fila->aptIni;

			$this->aptFin=$fila->aptFin;

		}else{

			$this->aptIni="";

			$this->aptFin="";

		}


		$this->misc=$fila->misc;

		$this->fechaIni=$fila->fechaIni;

		$this->fechaFin=$fila->fechaFin;



		echo json_encode($this);
		exit;

	}















}

?>
