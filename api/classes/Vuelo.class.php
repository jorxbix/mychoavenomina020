<?php
/**
 * La clase vuelo hereda de la clase servicio con lo que tenemos el calculo de
 * las horas block y resto de datos de un servicio ya calculados
 */
class Vuelo {

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

	public static $totalHorasPerfil=0;
	public static $totalMinutosPerfil=0;
	public static $totalImportePerfil=0;

	public static $totalHorasPerfil_sentencia=0;
	public static $totalMinutosPerfil_sentencia=0;
	public static $totalImportePerfil_sentencia=0;

	public static $totalHorasBlock=0;
	public static $totalMinutosBlock=0;

	public static $numVuelos=0;

	//cada vuelo tiene que tener asociado un piloto
	private $piloto;
	//cada vuelo tendra asociado un objeto perfil
	public $perfil;
	public $perfilSentencia;
	//cada vuelo tendra asociado un importe segun estemos en h1 o h2
	public $contadorHperfil=0;
	public $contadorMperfil=0;

	public $contadorHperfil_sentencia=0;
	public $contadorMperfil_sentencia=0;

	public $contadorHact=0;
	public $contadorMact=0;
	public $contadorHblock=0;
	public $contadorMblock=0;


	public $importePerfil=0;
	public $importePorEsteVuelo=0;

	public $importePerfil_Sentencia=0;
	public $importePorEsteVuelo_Sentencia=0;

	public $observaciones="Sin Observaciones";
	public $observaciones_sentencia="Sin Observaciones (sentencia)";
	public $misc="";
	public $fantasma=false;


	public function __construct($fila,$piloto){

		//cada vez que construyo un objecto vuelo augmento el contador
		Vuelo::$numVuelos++;

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

		$this->calculaTiempoBlock();

		if($this->tipo=="VS"){

			$this->asignaPerfilVS();

		}else{

			$this->asignaPerfil();
			if($this->piloto->sentenciaPerfiles==true) $this->asignaPerfilSentencia();

		}


		$this->asignarHorasProgramadas();

	}

	private function calculaTiempoBlock(){

		//primero miramos si el vuelo forma parte del calculo de este mes;

		if ($this->fechaIni->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;

			//$this->misc=$this->misc . " *fechaIni* " . $this->fechaIni->format('n') . " *mesInforme* " .$_SESSION['mesInforme'];

			return;

		}

		Vuelo::$totalHorasBlock=Vuelo::$totalHorasBlock+$this->tiempoBlock->h;
		Vuelo::$totalMinutosBlock=Vuelo::$totalMinutosBlock+$this->tiempoBlock->i;

		//pasamos los minutos a horas ....
		if(Vuelo::$totalMinutosBlock>=60){

			$sumHoras=intdiv(Vuelo::$totalMinutosBlock,60);

			Vuelo::$totalHorasBlock=Vuelo::$totalHorasBlock+$sumHoras;

			$nuevosMinutos=Vuelo::$totalMinutosBlock % 60;

			Vuelo::$totalMinutosBlock=$nuevosMinutos;

		}

		$this->contadorHblock=Vuelo::$totalHorasBlock;
		$this->contadorMblock=Vuelo::$totalMinutosBlock;

	}

	private function asignaPerfil(){

		global $limiteH1;
		global $limiteH2;

		$unPerfil=new Perfil("R",$this->piloto->flota,$this->aptIni.$this->aptFin);

		$this->perfil=$unPerfil->devuelveDatosPerfil("R",$this->piloto->flota,$this->aptIni.$this->aptFin);

		//si el perfil no se encuentra
		if ($this->perfil==false){

			$this->observaciones="ERROR FATAL, Perfil no encontrado!";
			return;

		}

		$tiempoPerfil=$this->perfil["tiempo_perfil"];

		$arrTimepoPerfil=explode(":",$tiempoPerfil);

		//si el vuelo no corresponde al mes del informe no actualizo totales
		//ni hago mas calculos
		if ($this->fechaIni->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;
			return;

		}

		Vuelo::$totalHorasPerfil=Vuelo::$totalHorasPerfil+$arrTimepoPerfil[0];

		Vuelo::$totalMinutosPerfil=Vuelo::$totalMinutosPerfil+$arrTimepoPerfil[1];

		//pasamos los minutos a horas ....
		if(Vuelo::$totalMinutosPerfil>=60){

			$sumHoras=intdiv(Vuelo::$totalMinutosPerfil,60);

			Vuelo::$totalHorasPerfil=Vuelo::$totalHorasPerfil+$sumHoras;

			$nuevosMinutos=Vuelo::$totalMinutosPerfil % 60;

			Vuelo::$totalMinutosPerfil=$nuevosMinutos;

		}

		$this->contadorHperfil=Vuelo::$totalHorasPerfil;
		$this->contadorMperfil=Vuelo::$totalMinutosPerfil;

		$this->observaciones="No Bonus (h1=" . round($limiteH1,2) . ", h2=" . round($limiteH2,2) . ")" ;

		//1.-NOS PASAMOS DE H2 */
		if($this->contadorHperfil>=$limiteH2){

			$importeH2=$this->piloto->nivel['h2'];
			$importeH1=$this->piloto->nivel['h1'];

			$horas=$this->contadorHperfil-$limiteH2;

			$minutos=$this->contadorMperfil;

			//al importe total por perfil hay que añadir el importe por por h1
			$this->importePerfil=($limiteH2-$limiteH1)*$importeH1;

			$unidadesH1=$limiteH2-$limiteH1;
			$unidadesH2=$horas + round(($minutos/60),2);
			$totH1=round(($limiteH2-$limiteH1)*$importeH1,2);
			$totH2=round(($horas + round(($minutos/60),2))*$importeH2,2);

			$this->observaciones="H1=$unidadesH1 h * $importeH1 = $totH1 € & H2=$unidadesH2 h * $importeH2 = $totH2 €"; 

			// $this->observaciones="H1=" . 
			// ($limiteH2-$limiteH1) . " * " . $importeH1 . " = " .
			// $this->importePerfil . "€ H2=" .
			// $horas + round(($minutos/60),2) . " * " . $importeH2 . " = " .
			// round($horas*$importeH2 + ($minutos/60*$importeH2),2) . "€.";

			

			$this->importePerfil=$this->importePerfil + round($horas*$importeH2 + ($minutos/60*$importeH2),2);

			$this->importePorEsteVuelo=$this->importePerfil - Vuelo::$totalImportePerfil;

			Vuelo::$totalImportePerfil= $this->importePerfil;

		//2.- //NOS PASAMOS DE H1 */		
		}elseif($this->contadorHperfil>=$limiteH1 && $this->contadorHperfil<$limiteH2){

			$importeH1=$this->piloto->nivel['h1'];

			$horas=$this->contadorHperfil-$limiteH1;

			$minutos=$this->contadorMperfil;

			$this->importePerfil=round($horas*$importeH1 + ($minutos/60*$importeH1),2);

			$unidadesH1=$horas + round($minutos/60,2);			

			$this->observaciones="H1= $unidadesH1 h * $importeH1 = " . $this->importePerfil . "€";

			$this->importePorEsteVuelo=$this->importePerfil - Vuelo::$totalImportePerfil;

			Vuelo::$totalImportePerfil= $this->importePerfil;

		}

	}

	private function asignaPerfilVS(){

		$unPerfil=new Perfil("R",$this->piloto->flota,$this->aptIni.$this->aptFin);

		$this->perfil=$unPerfil->devuelveDatosPerfil("R",$this->piloto->flota,$this->aptIni.$this->aptFin);

		//si el perfil no se encuentra
		if ($this->perfil==false){

			$this->observaciones="ERROR FATAL, PERFIL NO ENCONTRADO!";

			return;

		}

		$tiempoPerfil=$this->perfil["tiempo_perfil"];

		$arrTimepoPerfil=explode(":",$tiempoPerfil);

		//EN LOS VS NO AUGMENTAMOS EL CONTADOR DE HORAS DE PERFIL
		//SE PAGA EN H1 LO QUE SE VUELA Y PUNTO

		//LOS VALORES CONTINUAN SIENDO LOS MISMOS
		$this->contadorHperfil=Vuelo::$totalHorasPerfil;
		$this->contadorMperfil=Vuelo::$totalMinutosPerfil;

		$importeH1=$this->piloto->nivel['h1'];

		$horas=$arrTimepoPerfil[0];

		$minutos=$arrTimepoPerfil[1];

		//condiciones para cobrar el 100%
		//a.- vuelo de mas de 05:37 de perfil
		//b.- vuelo de mas de 07:00 de actividad
		//c.- 3 patas o mas en vs
		$this->importePerfil=round($horas*$importeH1 + ($minutos/60*$importeH1),2);

		if($horas>=5 && $minutos>=38){

			$this->importePerfil=$this->importePerfil;
			$this->observaciones="VS(100%) H1=" . $this->importePerfil . "€";

		}else{

			$minutosActividadVS=($this->tiempoBlock->h*60) + ($this->tiempoBlock->m) + 90;

			if($minutosActividadVS>420){

				$this->importePerfil=$this->importePerfil;
				$this->observaciones="VS(100%) H1=" . $this->importePerfil . "€";

			}else{

				$this->importePerfil=round($this->importePerfil*0.75,2);
				$this->observaciones="VS(75%) H1=" . $this->importePerfil . "€";

			}

		}

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

	private function asignaPerfilSentencia(){

		global $limiteH1;
		global $limiteH2;

		//la sentencia no tiene sentido para pilotos del b737
		if($this->piloto->flota=="B737") return;

		$unPerfil=new Perfil("R","A330",$this->aptIni.$this->aptFin);

		$this->perfilSentencia=$unPerfil->devuelveDatosPerfil("R","A330",$this->aptIni.$this->aptFin);

		//si el perfil no se encuentra utilizamos el del B787 (la flota que toca)
		if ($this->perfilSentencia==false){

			$this->perfilSentencia=$unPerfil->devuelveDatosPerfil("R",$this->piloto->flota,$this->aptIni.$this->aptFin);

		}

		//si seguimossin encontrar el perfil lanzamos el error
		if ($this->perfilSentencia==false){

			$this->observaciones_sentencia="Perfil Sentencia no encontrado.";
			return;

		}

		$tiempoPerfil=$this->perfilSentencia["tiempo_perfil"];

		$arrTimepoPerfil=explode(":",$tiempoPerfil);

		//si el vuelo no corresponde al mes del informe no actualizo totales
		//ni hago mas calculos
		if ($this->fechaIni->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			//$this->fantasma=true;
			return;

		}

		Vuelo::$totalHorasPerfil_sentencia=Vuelo::$totalHorasPerfil_sentencia+$arrTimepoPerfil[0];

		Vuelo::$totalMinutosPerfil_sentencia=Vuelo::$totalMinutosPerfil_sentencia+$arrTimepoPerfil[1];

		//pasamos los minutos a horas ....
		if(Vuelo::$totalMinutosPerfil_sentencia>=60){

			$sumHoras=intdiv(Vuelo::$totalMinutosPerfil_sentencia,60);

			Vuelo::$totalHorasPerfil_sentencia=Vuelo::$totalHorasPerfil_sentencia+$sumHoras;

			$nuevosMinutos=Vuelo::$totalMinutosPerfil_sentencia % 60;

			Vuelo::$totalMinutosPerfil_sentencia=$nuevosMinutos;

		}

		$this->contadorHperfil_sentencia=Vuelo::$totalHorasPerfil_sentencia;
		$this->contadorMperfil_sentencia=Vuelo::$totalMinutosPerfil_sentencia;

		$this->observaciones_sentencia= "No Bonus Sentencia (h1=" . round($limiteH1,2) . ", h2=" . round($limiteH2,2) . ")" ;

		if($this->contadorHperfil_sentencia>=$limiteH2){

			$importeH2=$this->piloto->nivel['h2'];
			$importeH1=$this->piloto->nivel['h1'];

			$horas=$this->contadorHperfil_sentencia-$limiteH2;

			$minutos=$this->contadorMperfil_sentencia;

			//al importe total por perfil hay que añadir el importe por por h1
			$this->importePerfil_Sentencia=($limiteH2-$limiteH1)*$importeH1;

			$this->observaciones_sentencia="H1(sen)=" . $this->importePerfil_Sentencia . "€ H2(sen)=" .
			round($horas*$importeH2 + ($minutos/60*$importeH2),2) . "€.";

			$this->importePerfil_Sentencia=$this->importePerfil_Sentencia + round($horas*$importeH2 + ($minutos/60*$importeH2),2);

			$this->importePorEsteVuelo_Sentencia=$this->importePerfil_Sentencia - Vuelo::$totalImportePerfil_sentencia;

			Vuelo::$totalImportePerfil_sentencia= $this->importePerfil_Sentencia;

		}elseif($this->contadorHperfil_sentencia>=$limiteH1 && $this->contadorHperfil_sentencia<$limiteH2){

			$importeH1=$this->piloto->nivel['h1'];

			$horas=$this->contadorHperfil_sentencia-$limiteH1;

			$minutos=$this->contadorMperfil_sentencia;

			$this->importePerfil_Sentencia=round($horas*$importeH1 + ($minutos/60*$importeH1),2);

			$this->observaciones_sentencia= "H1(sen)=" . $this->importePerfil_Sentencia . "€";

			$this->importePorEsteVuelo_Sentencia=$this->importePerfil_Sentencia - Vuelo::$totalImportePerfil_sentencia;

			Vuelo::$totalImportePerfil_sentencia= $this->importePerfil_Sentencia;

		}

	}







}

?>
