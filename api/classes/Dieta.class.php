<?php

class Dieta extends Conexion{

	public $id;
	public $codigo;
	public $nombre;
	public $brutoP1;
	public $exentoP1;
	public $brutoP2;
	public $exentoP2;
	public $arrDatosDieta;

/**Funcion constructora, devuelve false si el codigo de dieta no existe
 * @param $codigo son las siglas enmayuscula de la dieta a crear
 */
	public function __construct($codigo){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$codigo=strtoupper($codigo);

		$this->arrDatosDieta=$this->devuelveDatosDieta($codigo);

		if(isset($this->arrDatosDieta['id']) && $this->arrDatosDieta['id']!=null && $this->arrDatosDieta['id']!=""){

			$this->id=$this->arrDatosDieta['id'];
			$this->codigo=$this->arrDatosDieta['codigo'];
			$this->nombre=$this->arrDatosDieta['nombre'];
			$this->brutoP1=$this->arrDatosDieta['brutop1'];
			$this->exentoP1=$this->arrDatosDieta['exentop1'];
			$this->brutoP2=$this->arrDatosDieta['brutop2'];
			$this->exentoP2=$this->arrDatosDieta['exentop2'];

		}else{

			return false;

		}

		return true;

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

		global $arrDatosDieta;

        header('Content-type: application/json');

 		echo json_encode($arrDatosDieta);

	}

}

?>
