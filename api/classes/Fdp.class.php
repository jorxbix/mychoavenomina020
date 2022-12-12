<?php

class Fdp extends Conexion{

	public $iata;

	public $nombre;

	public $pais;

	public $oaci;

	public $tipo; //INTERNACIONAL,NACIONAL,LARGA

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

			$this->tipo = $this->calculaTipoAeropuerto();

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

	private function calculaTipoAeropuerto(){

		if($this->pais=="Spain") return "NACIONAL";

		$arrTz=explode("/", $this->tz);

		//Estos son loas paises que devolveran dieta internacional, solo los que pertenecen a aeuropa
		//me falta comprobar el tema de colonias como azores u otras islas lejanas que devengan
		//dieta de larga aunque sean de un pais europeo ...
		// Belgium
		// Germany
		// Estonia
		// Finland
		// United Kingdom
		// Guernsey
		// Jersey
		// Isle of Man
		// Netherlands
		// Ireland
		// Denmark
		// Luxembourg
		// Norway
		// Poland
		// Sweden
		// Spain
		// Albania
		// Bulgaria
		// Cyprus
		// Croatia
		// France
		// Greece
		// Hungary
		// Italy
		// Slovenia

		if($arrTz[0]=="Europe"){

			 return "INTERNACIONAL";

		}else{

			return "LARGA";
		}

	}


	public static function dameDistancia($arrVuelos){

		$distancia="NACIONAL";

		foreach($arrVuelos as $vuelo){

			$unApto= new Aeropuerto($vuelo->aptIni);

			if($unApto->tipo=="LARGA") $distancia="LARGA";
			if($unApto->tipo=="INTERNACIONAL" && $distancia!="LARGA") $distancia="INTERNACIONAL";

			$unApto= new Aeropuerto($vuelo->aptFin);

			if($unApto->tipo=="LARGA") $distancia="LARGA";
			if($unApto->tipo=="INTERNACIONAL" && $distancia!="LARGA") $distancia="INTERNACIONAL";


		}

		return $distancia;

	}

	public function imprimeJsonAeropuerto(){

        header('Content-type: application/json');

 		echo json_encode($this->arrDatosAeropuerto);

	}

}

?>
