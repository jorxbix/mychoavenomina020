<?php
/**
 * Finalmente la clase imaginaria es considerada una clase a parte
 * y no hereda de servicio ni de vuelo. Usa datos del objeto piloto
 * para el calculo de importes y tiene unas variables estaticas
 * para almecenar sumatorios
 */
class Reserva {

	public $aptIni;
	public $aptFin;

	public $fechaIni;
	public $fechaFin;

	public $tipo;

	public $tiempoReserva;
	public $importeReserva=0;

	public static $numReservas=0;
	public static $importeReservas=0;

	public $contadorNumReservas;
	public $contadorImporteReservas;

	//cada imaginaria tiene que tener asociado un piloto
	public $piloto;

	public $misc="";

	public $imaginariaDoble=false;


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

		$this->tiempoReserva=$this->fechaFin->diff($this->fechaIni);
		//
		$this->piloto=$piloto;

		//augmento el numero de imaginarias del periodo
		Reserva::$numReservas++;

		//paso el total a una propiedad del objeto
		$this->contadorNumReservas=Reserva::$numReservas;

		//presento el importe de cada reserva
		//se cobra la mitad que en una imaginaria: 1,5hrs
		$this->importeReserva=0.5 * ($this->piloto->nivel['imaginaria']);

		//augento el total liquido para compara con la nomina
		Reserva::$importeReservas=Reserva::$importeReservas + $this->importeReserva;

		//paso el total a una propiedad del objeto
		$this->contadorImporteReservas=Reserva::$importeReservas;

		//finalment augmento el contador de actividad en 12h
		//dentro de minimos:

		Servicio::$totalHorasAct=Servicio::$totalHorasAct+6;



	}















}

?>
