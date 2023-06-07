<?php
/**
 * La calse hora memorizada crea objetos vuelo con horas programadas para los calculos de actividad
 * la propiedad identificador se compone de MAD.BCN.9.12 para poder buscarlo rapido en un array de objetos
 * donde 99 es el mes y 12 el día. la propiedad fiable me indicará si esta hora programada contiene algun error
 */
class HoraMemorizada {

	public $aptIni="";
	public $aptFin="";

	public $fechaIniProg;
	public $fechaFinProg;

	public $fiable=true;

	public $identificador="";

	public function __construct($fila){

		if(isset($fila->aptIni) && isset($fila->aptFin)){

			$this->fiable=true;

			$this->aptIni=$fila->aptIni;

			$this->aptFin=$fila->aptFin;

		}else{

			$this->fiable=false;

			$this->aptIni="";

			$this->aptFin="";

		}

		$this->fechaIniProg=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaIni, new DateTimeZone('UTC'));

		$this->fechaFinProg=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaFin, new DateTimeZone('UTC'));

		if($this->fechaIniProg==false || $this->fechaFinProg==false){

			$this->fiable=false;

		}else{

			$this->fiable=true;

			$this->identificador=$this->aptIni . $this->aptFin . $this->fechaIniProg->format("Y-m-d");

		}




	}





}

?>
