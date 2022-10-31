<?php

class Dieta extends Conexion{

	public $codigo;
	public $piloto;
	public $arrDatosDieta;

/**Funcion constructora, devuelve false si el codigo de dieta no existe
 * @param $codigo son las siglas enmayuscula de la dieta a crear
 */
	public function __construct($codigo, $piloto){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$codigo=strtoupper($codigo);

		$this->arrDatosDieta=$this->devuelveDatosDieta($codigo);

		if(isset($this->arrDatosDieta['id']) && $this->arrDatosDieta['id']!=null && $this->arrDatosDieta['id']!=""){

			return $this;

		}else{

			return false;

		}

	}

	/**
	 * funcion que devuelve un array con todos los datos de la dieta
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosDieta($codigo){

		$consulta="SELECT * FROM dietas WHERE codigo='$codigo';";

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
