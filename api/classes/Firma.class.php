<?php

class Firma extends Conexion{

	public $iata;

	public $flota;

	public $firma;

	public $desfirma;

	public $arrDatosFirma;


	public function __construct($iata,$flota){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$iata=strtoupper($iata);

		$flota=strtoupper($flota);

		$this->arrDatosFirma=$this->devuelveDatosFirma($iata,$flota);

		if(isset($this->arrDatosFirma['iata']) && $this->arrDatosFirma['iata']!=null && $this->arrDatosFirma['iata']!=""){

			$this->iata=$this->arrDatosFirma['iata'];
			$this->flota=$this->arrDatosFirma['codigo'];

			$this->firma=$this->arrDatosFirma['firma'];
			$this->desfirma=$this->arrDatosFirma['desfirma'];

		}else{

			return false;

		}

		return true;

	}

	/**
	 * funcion que devuelve un array con todos los datos dela firma
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosFirma($iata,$flota){

		$consulta="
		SELECT fi.iata,fi.firma,fi.desfirma,flo.codigo
		FROM
        firmas fi JOIN flotas flo ON fi.id_flota=flo.id
        WHERE
        fi.iata='$iata' AND flo.codigo='$flota'
		;
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
		$firma = $resultado->fetch(PDO::FETCH_ASSOC);

		return $firma;

	}

	public function imprimeJsonFirma(){

        header('Content-type: application/json');

 		echo json_encode($this->arrDatosFirma);

	}

}

?>
