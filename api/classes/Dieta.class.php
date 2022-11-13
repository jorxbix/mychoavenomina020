<?php

class Dieta extends Conexion{

	public $codigo;
	public $arrDatosDieta;

/**Funcion constructora, devuelve false si el codigo de dieta no existe
 * @param $codigo son las siglas enmayuscula de la dieta a crear
 * @param $tipoPiloto puede ser PILOTO1 o PILOTO2
 */
	public function __construct($codigo,$tipoPiloto){


		//inicia la funcion constructora de conexion
		parent::__construct();

		$codigo=strtoupper($codigo);

		$this->arrDatosDieta=$this->devuelveDatosDieta($codigo,$tipoPiloto);

		if(isset($this->arrDatosDieta['codigo']) && $this->arrDatosDieta['codigo']!=null && $this->arrDatosDieta['codigo']!=""){

			$this->codigo=$this->arrDatosDieta['codigo'];

			return $this;

		}else{

			return false;

		}

	}

	/**
	 * funcion que devuelve un array con todos los datos de la dieta
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosDieta($codigo, $tipoPiloto){

		$consulta="SELECT * FROM dietas WHERE codigo='$codigo';";

		if ($tipoPiloto=="PILOTO1"){

			$consulta="SELECT codigo, nombre, brutop1 as bruto, exentop1 as exento FROM dietas WHERE codigo='$codigo';";

		}else{	//tipoPiloto es PILOTO2

			$consulta="SELECT codigo, nombre, brutop2 as bruto, exentop2 as exento FROM dietas WHERE codigo='$codigo';";

		}


		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;
		}

		if($resultado->rowCount()!=1){

			return false;

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$dieta = $resultado->fetch(PDO::FETCH_ASSOC);

		return $dieta;

	}

	public function imprimeJsonDieta(){

        header('Content-type: application/json');

 		echo json_encode($this->arrDatosDieta);

	}

}

?>
