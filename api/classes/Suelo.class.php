<?php
/**
 * Es una especie de servicio que se va a incluir dentro de otro servicio
 * es como una asignatura metida dentro de otro dia de clase
 * es servicio es el dia de clase entero y las lineas son las asignakturasw
 * pej:
 * OC - ECRM	05/09/2022 14:45	MAD	05/09/2022 15:45	MAD
 * SR - OPC	05/09/2022 15:45	MAD	05/09/2022 21:45	MAD
 *
 * estas dos lineas corresponden al mismo servicio
 */
class Suelo {

	public $tipo;

	public $aptIni;
	public $aptFin;

	public $fechaIni;
	public $fechaFin;

	//estos atributos los obtendremos de la variable session si se han almacenado datos
	//sobre la hora programada de los vuelos
	public $fechaProgIni;
	public $fechaProgFin;

	public $tiempoBlock;

	public static $numVuelos=0;

	//cada vuelo tiene que tener asociado un piloto
	private $piloto;

	//cada vuelo tendra asociado un importe segun estemos en h1 o h2
	public $contadorHact=0;
	public $contadorMact=0;

	public $observaciones="Sin Observaciones";


	public function __construct($fila,$piloto){

		//cada vez que construyo un objecto vuelo augmento el contador
		Suelo::$numVuelos++;

		$this->tipo=$fila->tipo;

		$this->aptIni=$fila->aptIni;
		$this->aptFin=$fila->aptFin;

		$this->misc=$fila->misc;

		$this->fechaIni=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaIni,new DateTimeZone('UTC'));

		$this->fechaFin=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaFin,new DateTimeZone('UTC'));

		//alguna fecha suministrada es invalida, creamos un error y detenemos ejecucion
		if($this->fechaIni==false || $this->fechaFin==false){

			$putoFallo=new Fallo($fila,"Alguna fecha suministrada es invalida");

		}

		$this->tiempoBlock=$this->fechaFin->diff($this->fechaIni);

		$this->piloto=$piloto;

		$this->asignarHorasProgramadas();

	}

	/**
	 * funcion que busca si hay vuelos programados guardados y asigna las horas
	 * programadas al objeto. de esta manera el calculo de actividades sera exacto
	 */
	private function asignarHorasProgramadas(){

		//si no hay vuelos guardados salgo de la funcion
		if(!isset($_SESSION['vuelosProgramados'])){

			return;

		//si que hay vuelos programados guardados
		}else{

			//clono le fecha pra no alterar la propiedad del objetoi
			$fechaIniClone=clone $this->fechaIni;

			//id si el vuelo ha salido el mismo dia
			$id1=$this->aptIni . $this->aptFin . $fechaIniClone->format("Y-m-d");

			//id se el vuelo salio ya en el dia siguiente
			$fechaIniClone->sub(new DateInterval('PT24H'));
			$id2=$this->aptIni . $this->aptFin . $fechaIniClone->format("Y-m-d");

			//id si el vuelo salio adelantado al dia anterior
			$fechaIniClone->add(new DateInterval('PT48H'));
			$id3=$this->aptIni . $this->aptFin . $fechaIniClone->format("Y-m-d");

			//ahora recorrermos el array buscando alguna coincidencia:
			foreach($_SESSION['vuelosProgramados'] as $vueloProgramado){

				$idVP=$vueloProgramado->identificador;

				//hemos encontrado una coincidencia
				if($idVP==$id1 || $idVP==$id2 || $idVP==$id3){

					//la informacion es fiable, asigno las horas progs a este objeto
					if($vueloProgramado->fiable){

						$this->fechaProgIni=$vueloProgramado->fechaIniProg;
						$this->fechaProgFin=$vueloProgramado->fechaFinProg;

					}

				}

			}

		}

	}


}

?>
