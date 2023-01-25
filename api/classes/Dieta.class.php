<?php

class Dieta extends Conexion{

	public $codigo;
	public $arrDatosDieta;
	public $diaDieta;

/**Funcion constructora, devuelve false si el codigo de dieta no existe
 * @param $codigo son las siglas enmayuscula de la dieta a crear
 * @param $tipoPiloto puede ser PILOTO1 o PILOTO2
 */
	public function __construct($codigo,$tipoPiloto){


		//inicia la funcion constructora de conexion
		parent::__construct();

		$codigo=strtoupper($codigo);

		$this->arrDatosDieta=$this->devuelveDatosDieta($codigo,$tipoPiloto);

		if(isset($this->arrDatosDieta['codigo']) && $this->arrDatosDieta['codigo']!=null && $this->arrDatosDieta['codigo']!=""){

			$this->codigo=$this->arrDatosDieta['codigo'];

			return $this;

		}else{

			return false;

		}

	}

	/**
	 * funcion que devuelve un array con todos los datos de la dieta
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosDieta($codigo, $tipoPiloto){

		$consulta="SELECT * FROM dietas WHERE codigo='$codigo';";

		if ($tipoPiloto=="PILOTO1"){

			$consulta="SELECT codigo, nombre, brutop1 as bruto, exentop1 as exento FROM dietas WHERE codigo='$codigo';";

		}else{	//tipoPiloto es PILOTO2

			$consulta="SELECT codigo, nombre, brutop2 as bruto, exentop2 as exento FROM dietas WHERE codigo='$codigo';";

		}


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

	public static function esPernocta($servicio,$dia,$DISTANCIA){

		//obtener la bse del pilto
		$BASE=$servicio->piloto->base;

		$time_zone="Europe/Madrid";

		$laBase=new Aeropuerto($BASE);

		//si han metido un aeropuerto que no existe, le asignamos MAD
		if($laBase) $time_zone=$laBase->tz;

		$arrVuelos=$servicio->arrVuelos;

		$ultimoVuelo=end($arrVuelos);

		//obtengo la hora blockOn del ultimo vuelo
		$horaEntradaHotel=clone $ultimoVuelo->fechaFin;

		//el convenio habla de BLOCKON per la empresa esta usando la hora de desfirma
		// $horaLLegada=clone $ultimoVuelo->fechaFin;
		$horaLLegada=clone $servicio->fechaDesfirma;

		//le añado 60min como hora de netrada al hotel
		$horaEntradaHotel->add(new DateInterval('PT1H'));

		//pongo la hora de ntrada al hotel en local de la base
		$horaEntradaHotel->setTimezone(new DateTimeZone($time_zone));

		$hora_EntradaHotel = (int) $horaEntradaHotel->format("H");
		$dia_EntradaHotel = (int) $horaEntradaHotel->format("d");

		$horaLLegada->setTimezone(new DateTimeZone($time_zone));

		$hora_LLegada=(int) $horaLLegada->format("H");
		$dia_LLegada=(int) $horaLLegada->format("d");

		$aptoLLegada=new Aeropuerto($servicio->aptFin);

		//EL SERVICIO TERMINA EN UN SITIO QUE NO ES LA BASE ...
		if($servicio->aptFin!=$BASE){
			/**
			 * En el día de que se trate, haya disponibilidad de hotel en el territorio nacional
			 * fuera del municipio de la base operativa y del municipio de residencia entre las
			 * 23:00 y las 07:00, SON HORAS LOCALES considerando BLOCK ON más 60 minutos como entrada en el hotel
			 */

			//if($dia_EntradaHotel>$dia) return "EntradaHotel: + 1dia";
			//si el vuelo llega pasado el dia de la dieta no va a ser de pernocta

			// echo "dia_EntradaHotel=$dia_EntradaHotel , dia de la dieta= $dia";
			// exit;

			if($dia_EntradaHotel>$dia) return false;
			if($aptoLLegada->pais!="Spain") return "Disponibilidad hotel fuera ESP";
			if($hora_EntradaHotel>=23 || $hora_EntradaHotel<=7) return "EntradaHotel: " . json_encode($horaEntradaHotel);



		//EL SERVICIO EMPIEZA FUERA DE BASE Y ACABA EN LA BASE (vueltas)
		}else if($servicio->aptIni!=$BASE && $servicio->aptFin==$BASE){
			/**
			 * y/o se produzca una salida del hotel a cualquier hora
			 * con llegada a base operativa igual o posterior a las 05:00 (BLOCKS ON).
			 */

			if($hora_LLegada>=5) return "HoraLlegada a Base: > 05:00";
			//if($dia_LLegada>$dia) return "HoraLlegada a Base: + 1dia";

		}

		//si he llegado hasta aqui sin q se cumpla ninguna condicion es que NO es ernocat
		return false;


	}

	/**
	 * funcion que determina si se produce algun blockoff en el dia especificado
	 */
	public static function hayBlockOffenElDia($servicio,$dia){

		//obtener la bse del pilto
		$BASE=$servicio->piloto->base;

		//si han metido un aeropuerto que no existe, le asignamos MAD
		$time_zone="Europe/Madrid";

		//obtener fechas firmas en hora local
		//1. obtener el timezone de la base del fulano
		$laBase=new Aeropuerto($BASE);


		if($laBase){

			$time_zone=$laBase->tz;

		}

		$arrDiasConOffBlocks=[];

		//obtengo un array con todos los dias en local en los que ha habido offblock
		foreach($servicio->arrVuelos as $flight){

			$blockOffLocal=clone $flight->fechaIni;
			$blockOffLocal->setTimezone(new DateTimeZone($time_zone));
			$diaBlockOff =(int) $blockOffLocal->format("d");

			array_push($arrDiasConOffBlocks, $diaBlockOff);

		}

		//miro si el dia por el q preguntan esta en el array
		return in_array($dia, $arrDiasConOffBlocks);

	}

	public static function dameDistancia2($arrVuelos,$dia,$base){

		$distancia="NACIONAL";

		//por defecto, le asignamos MAD
		$time_zone="Europe/Madrid";

		$laBase=new Aeropuerto($base);

		if($laBase)	$time_zone=$laBase->tz;

		foreach($arrVuelos as $vuelo){

			if(!isset($vuelo->fechaIni)) $vuelo->fechaIni=new DateTime();
			$offblockLocal=clone $vuelo->fechaIni;
			//paso la hora de firma a la que corresponda en local
			$offblockLocal->setTimezone(new DateTimeZone($time_zone));

			if($offblockLocal->format("d")==$dia){

				$unApto= new Aeropuerto($vuelo->aptIni);

				if($unApto->tipo=="LARGA") $distancia="LARGA";
				if($unApto->tipo=="INTERNACIONAL" && $distancia!="LARGA") $distancia="INTERNACIONAL";

				$unApto= new Aeropuerto($vuelo->aptFin);

				if($unApto->tipo=="LARGA") $distancia="LARGA";
				if($unApto->tipo=="INTERNACIONAL" && $distancia!="LARGA") $distancia="INTERNACIONAL";

			}

		}

		return $distancia;

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

	public static function dameDistanciaApto($codigo){

		$distancia="NACIONAL";

		$unApto= new Aeropuerto($codigo);

			if($unApto->tipo=="LARGA") $distancia="LARGA";
			if($unApto->tipo=="INTERNACIONAL" && $distancia!="LARGA") $distancia="INTERNACIONAL";

		return $distancia;

	}



	public static function dameDiasDieta($servicio){

		//obtener la bse del pilto
		$BASE=$servicio->piloto->base;

		$time_zone="Europe/Madrid";

		//obtener fechas firmas en hora local
		//1. obtener el timezone de la base del fulano
		$laBase=new Aeropuerto($BASE);

		//si han metido un aeropuerto que no existe, le asignamos MAD
		if($laBase) $time_zone=$laBase->tz;


		$firmaLocal=clone $servicio->fechaFirma;
		$desFirmaLocal=clone $servicio->fechaDesfirma;

		//paso la hora de firma a la que corresponda en local
		$firmaLocal->setTimezone(new DateTimeZone($time_zone));
		$desFirmaLocal->setTimezone(new DateTimeZone($time_zone));

		$diaFirma =(int) $firmaLocal->format("d");
		$diaDesfirma =(int) $desFirmaLocal->format("d");

		if($diaFirma==$diaDesfirma){

			return [$diaFirma];

		}else{

			return [$diaFirma,$diaDesfirma];

		}

	}

	public static function esDietaReducida($servicio){

		$time_zone="Europe/Madrid";
		//obtener la bse del pilto
		$BASE=$servicio->piloto->base;

		/**
		 * 2.2.9 En operaciones de larga distancia, por cada día que se inicie un servicio
		 * en la base operativa del tripulante y se realice únicamente una actividad inferior a 2 horas,
		 * devengará 3/4 dieta del tipo indicado en los puntos anteriores.
		 */
		//solo puede ser dieta reducida si se sale de la base y es operacion de larga
		if($servicio->aptIni!=$BASE) return false;

		//obtener fechas firmas en hora local
		//1. obtener el timezone de la base del fulano
		$laBase=new Aeropuerto($BASE);

		//si han metido un aeropuerto que no existe, le asignamos MAD
		if($laBase)	$time_zone=$laBase->tz;

		if(!isset($servicio->fechaFirma)) $servicio->fechaFirma=new DateTime();
		if(!isset($servicio->fechaDesfirma)) $servicio->fechaDesfirma=new DateTime();
		$firmaLocal=clone $servicio->fechaFirma;
		$desFirmaLocal=clone $servicio->fechaDesfirma;

		//paso la hora de firma a la que corresponda en local
		$firmaLocal->setTimezone(new DateTimeZone($time_zone));
		$desFirmaLocal->setTimezone(new DateTimeZone($time_zone));

		// $diaFirma =(int) $firmaLocal->format("d");
		// $diaDesfirma =(int) $desFirmaLocal->format("d");


		/**
		 * 2.2.9 En operaciones de larga distancia,
		 * por cada día que se inicie un servicio en la base operativa del tripulante y
		 * se realice únicamente una actividad inferior a 2 horas,
		 * devengará 3/4 dieta del tipo indicado en los puntos anteriores.
		 */
		//calcular actividad en el día:
		$mediaNoche=clone $firmaLocal;

		date_time_set($mediaNoche,23,59);

		$actEnElDia=$firmaLocal->diff($mediaNoche);

		$minsEnElDia=$actEnElDia->h*60+$actEnElDia->i +1;

		if($minsEnElDia<120){

			return true;

		}else{

			return false;

		}
	}

}

?>
