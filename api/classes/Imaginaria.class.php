<?php
/**
 * Finalmente la clase imaginaria es considerada una clase a parte
 * y no hereda de servicio ni de vuelo. Usa datos del objeto piloto
 * para el calculo de importes y tiene unas variables estaticas
 * para almecenar sumatorios
 */
class Imaginaria {

	public $aptIni;
	public $aptFin;

	public $fechaIni;
	public $fechaFin;

	public $tipo;

	public $tiempoImaginaria;
	public $importeImaginaria=0;

	public static $numImaginarias=0;
	public static $importeImaginarias=0;

	public $contadorNumImaginarias;
	public $contadorImporteImaginarias;

	//cada imaginaria tiene que tener asociado un piloto
	public $piloto;

	public $misc="";


	public function __construct($fila,$piloto){

		$this->tipo=$fila->tipo;

		$this->aptIni=$fila->aptIni;
		$this->aptFin=$fila->aptFin;

		$this->misc=$fila->misc;

		$this->fechaIni=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaIni);

		$this->fechaFin=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaFin);

		if($this->fechaIni==false || $this->fechaFin==false){

			$putoFallo=new Fallo($fila,"Alguna fecha suministrada es invalida");

		}

		$this->tiempoImaginaria=$this->fechaFin->diff($this->fechaIni);
		//
		$this->piloto=$piloto;

		//augmento el numero de imaginarias del periodo
		Imaginaria::$numImaginarias++;

		//paso el total a una propiedad del objeto
		$this->contadorNumImaginarias=Imaginaria::$numImaginarias;

		//presento el importe de cada imaginaria
		$this->importeImaginaria=$this->piloto->nivel['imaginaria'];

		//augento el total liquido para compara con la nomina
		Imaginaria::$importeImaginarias=Imaginaria::$importeImaginarias + $this->importeImaginaria;

		//paso el total a una propiedad del objeto
		$this->contadorImporteImaginarias=Imaginaria::$importeImaginarias;

		//finalment augmento el contador de actividad en 12h
		//dentro de minimos:

		Servicio::$totalHorasAct=Servicio::$totalHorasAct+12;



	}















}

?>
