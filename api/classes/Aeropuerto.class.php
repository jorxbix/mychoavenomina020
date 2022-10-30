<?php

class Aeropuerto extends Conexion{

	public $iata;

	public $nombre;

	public $pais;

	public $oaci;

	//public $tipo; //INT,NAC,LONG

	public $husos;
	public $tz;

	public $arrDatosAeropuerto;

/**Funcion constructora, devuelve false si el codigo de perfil no existe
 * @param $nombre son las siglas del perfil enmayuscula
 */
	public function __construct($iata){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$iata=strtoupper($iata);

		$this->arrDatosAeropuerto=$this->devuelveDatosAeropuerto($iata);

		if(isset($this->arrDatosAeropuerto['iata']) && $this->arrDatosAeropuerto['iata']!=null && $this->arrDatosAeropuerto['iata']!=""){

			$this->iata=$this->arrDatosAeropuerto['iata'];
			$this->nombre=$this->arrDatosAeropuerto['nombre'];

			$this->pais=$this->arrDatosAeropuerto['pais'];
			$this->oaci=$this->arrDatosAeropuerto['oaci'];

			$this->husos=$this->arrDatosAeropuerto['z_offset'];
			$this->tz=$this->arrDatosAeropuerto['tz_olson'];

			return $this;

		}else{

			return false;

		}

	}

	/**
	 * funcion que devuelve un array con todos los datos del perfil
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosAeropuerto($iata){

		$consulta="
		SELECT * FROM aeropuertos WHERE iata='$iata';
		";

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;
		}

		if($resultado->rowCount()<=0){

			return false;

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$aeropuerto = $resultado->fetch(PDO::FETCH_ASSOC);

		return $aeropuerto;

	}

	public function imprimeJsonAeropuerto(){

        header('Content-type: application/json');

 		echo json_encode($this->arrDatosAeropuerto);

	}

}

?>
