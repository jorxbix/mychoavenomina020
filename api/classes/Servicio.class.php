<?php

class Servicio{

	public static $aptoIniServicio;
	public static $fechaIniServicio;

	public static $totalHorasAct=0;
	public static $totalMinutosAct=0;

	public static $totalHorasActNoc=0;
	public static $totalMinutosActNoc=0;
	public static $totalHorasActEx=0;
	public static $totalMinutosActEx=0;

	public static $totalImporteActNoc=0;
	public static $totalImporteActEx=0;


	//tipo de servicio
	public $tipo;

	//apts donde empieza y acaba el servicio
	public $aptIni;
	public $aptFin;

	//fecha y hora en la que empieza y acaba el servicio
	public $fechaIni;
	public $fechaFin;

	//timepo que ha durado el servicio !!NO ES EL TIMEPO DE AC>TIVIDAD!!
	public $tiempoServicio;

	//alguna parida que ponen en la tabla de programacion
	public $misc;

	/**
	 * Un servicio puede contener una serie de vuelos que se almacenan en
	 * la variable $arrVuelos
	 */
	public $arrVuelos;
	/**
	 * Un servicio puede contener una serie de dietas que se almacenan en
	 * la variable $arrDietas
	 */
	public $arrDietas;


	public $importeActividadEx=0;
	public $importeActividadNoc=0;

	//fechafirma sera la hora programada(si se encuentra memorizada) menos el timepo de firma en minutos.
	//si no hay hora memorizada se utilizara la hora real fuera clazos menos los minutos de firma
	public $fechaFirma;
	//fecha desfirma actuara de la misma manera, solo que siempre sera la hora real de desfirma y nunca
	//se utilizara la hora programada de llegada del vuello
	public $fechaDesfirma;

	public $tiempoActividad;
	public $tiempoActividadNocturna;
	public $tiempoActividadEx;

	private $piloto;

	//contadores de actividad que almacenaran el valor de las variables
	//estaticas para asi poderlas presentar vuelo a vuelo ... serv a serv
	public $contadorHact=0;
	public $contadorMact=0;

	public $contadorHactNoc=0;
	public $contadorMactNoc=0;
	public $contadorImpNoc=0;

	public $contadorHactEx=0;
	public $contadorMactEx=0;
	public $contadorImpEx=0;

/**Funcion constructora, devuelve false si hay algun error al montar el servicio
 * @param $fila es el array con todos los datos del servicio
 *
 */
	public function __construct($fila,$piloto){

		//truco para crear un objecto servicio vacio
		if($fila==null && $fila==null) return false;

		$this->piloto=$piloto;

		if(isset($fila->tipo) && isset($fila->aptIni) && isset($fila->aptFin)){

			$this->tipo=$fila->tipo;
			//$this->tipo="Servicio";

			$this->aptIni=$fila->aptIni;

			$this->aptFin=$fila->aptFin;

		}else{

			$putoFallo=new Fallo($fila,"Alguno de los campos no es valido.");

		}

		$this->misc=$fila->misc;

		$this->fechaIni=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaIni, new DateTimeZone('UTC'));

		$this->fechaFin=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaFin, new DateTimeZone('UTC'));

		if($this->fechaIni==false || $this->fechaFin==false){

			$putoFallo=new Fallo($fila,"Alguna fecha suministrada es invalida");

		}

		$this->tiempoServicio=$this->fechaFin->diff($this->fechaIni);

	}

	/**
	 * Funcion que asigna las dietas que corresponden a este servicio
	 * Esta funcion es llamada tanto en servicios de tierra como en servicios de vuelo
	 * y se llama una vez el servicio está todo completo y las actividades están
	 * calculadas correvtament.
	 */
	public function asignaDietas(){



		if(isset($this->arrVuelos)){

			$this->asignaDietasVuelo();

		}else{

			$this->asignaDietasTierra();

		}


	}

	protected function asignaDietasVuelo(){
		/**
		 * 2.2.9 En operaciones de larga distancia,
		 * por cada día que se inicie un servicio
		 * en la base operativa del tripulante y se
		 * realice únicamente una actividad inferior a 2 horas,
		 * devengará 3/4 dieta del tipo indicado en los puntos anteriores.
		 */

		$INFO="";
		$PERNOCTA=false;
		$DIETA_LARGA_REDUCIDA=false;

		//determinar distancia de la dieta
		$DISTANCIA= Aeropuerto::dameDistancia($this->arrVuelos);

		//obtener la bse del pilto
		$base=$this->piloto->base;


		//obtener fechas firmas en hora local
		//1. obtener el timezone de la base del fulano
		$laBase=new Aeropuerto($base);

		if($laBase){

			$time_zone=$laBase->tz;

		}else{

			$time_zone="Europe/Madrid";

		}

		$firmaLocal=clone $this->fechaFirma;
		$desFirmaLocal=clone $this->fechaDesfirma;

		//paso la hora de firma a la que corresponda en local
		$firmaLocal->setTimezone(new DateTimeZone($time_zone));
		$desFirmaLocal->setTimezone(new DateTimeZone($time_zone));
		$INFO=$INFO . "firma: " . json_encode($firmaLocal);
		$INFO=$INFO . "DesFirma: " . json_encode($desFirmaLocal);

		//determinar si es pernocta o no ...

		if($this->aptFin!=$base){
			/**
			 * En el día de que se trate, haya disponibilidad de hotel en el territorio nacional
			 * fuera del municipio de la base operativa y del municipio de residencia entre las
			 * 23:00 y las 07:00, considerando BLOCK ON más 60 minutos como entrada en el hotel
			 */

			$PERNOCTA=true;

			$aptoHotel= new Aeropuerto($this->aptFin);

			$tipoPernocta= $aptoHotel->tipo;

		}else if($this->aptIni!=$base && $this->aptFin==$base){
			/**
			 * y/o se produzca una salida del hotel a cualquier hora
			 * con llegada a base operativa igual o posterior a las 05:00 (BLOCKS ON).
			 */
			$horaDesFirma=(int) $desFirmaLocal->format("H");

			if($horaDesFirma>=5) $PERNOCTA=true;

			$aptoHotel= new Aeropuerto($this->aptIni);

			$tipoPernocta= $aptoHotel->tipo;

		}

		//veamos cuantas dietas corresponden a este servicio:

		$diaFirma =(int) $firmaLocal->format("d");
		$diaDesfirma =(int) $desFirmaLocal->format("d");

		//determinar si hay blockoff en el dia:
		$TRIBUTABLE=false;

		$blockOffLocal=clone $this->arrVuelos[0]->fechaIni;
		$blockOffLocal->setTimezone(new DateTimeZone($time_zone));
		$diaBlockOff =(int) $blockOffLocal->format("d");
		$INFO=$INFO . "BlockOff: " . json_encode($blockOffLocal);
		if($diaBlockOff>$diaFirma){
			$TRIBUTABLE=true;

			$INFO=$INFO . " Sin BlockOff en el dia";

		}

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

		if($minsEnElDia<120) $DIETA_LARGA_REDUCIDA=true;


		// echo "HORAFIRMA=" . json_encode($firmaLocal);
		// echo " MEDIANOCHE=" . json_encode($mediaNoche);
		// echo " ACT EN EL DIA=" . json_encode($actEnElDia);
		// echo " MINS ACT EN EL DIA=" . json_encode($minsEnElDia);
		// echo " dieta larga reducidad=" . $DIETA_LARGA_REDUCIDA;

		// exit;








		//**************DIETA DE SALIDA*************** */

		$dieta="D";
		if($DISTANCIA=="NACIONAL") $dieta=$dieta . "N";
		if($DISTANCIA=="INTERNACIONAL") $dieta=$dieta . "I";
		if($DISTANCIA=="LARGA"){

			if ($DIETA_LARGA_REDUCIDA){

				$dieta=$dieta . "L";
				$INFO=$INFO . "Menos de 2Hrs de Actividad en el dia. ";

			}else{

				$dieta=$dieta . "L";

			}

		}

		if($TRIBUTABLE){

			$dieta=$dieta . "T";

		}else{

			if($PERNOCTA){

				$dieta=$dieta . "P";

			}else{

				$dieta=$dieta . "C";

			}

		}


		$this->arrDietas[0]=new Dieta($dieta,$this->piloto);
		if($DIETA_LARGA_REDUCIDA){


			$this->arrDietas[0]->arrDatosDieta['bruto']=round($this->arrDietas[0]->arrDatosDieta['bruto']*0.75,2);
			$this->arrDietas[0]->arrDatosDieta['exento']=round($this->arrDietas[0]->arrDatosDieta['exento']*0.75,2);

			$this->arrDietas[0]->codigo=$this->arrDietas[0]->codigo . "_redu 3/4";
		}

		$this->arrDietas[0]->misc=$INFO;







		//**************DIETA DE LLEGADA SOLO SI SE LLEGA UN DIA DIFERENTE*************** */
		if($diaFirma!=$diaDesfirma){

		$dieta="D";
		if($DISTANCIA=="NACIONAL") $dieta=$dieta . "N";
		if($DISTANCIA=="INTERNACIONAL") $dieta=$dieta . "I";
		if($DISTANCIA=="LARGA") $dieta=$dieta . "L";

		if($PERNOCTA){

			$dieta=$dieta . "P";

		}else{

			$dieta=$dieta . "C";

		}

		$this->arrDietas[1]=new Dieta($dieta,$this->piloto);

		$this->arrDietas[1]->misc="DIETA DE LLEGADA";

		}


	}



	protected function asignaDietasTierra(){

		$this->arrDietas[0]="SERVICOO TIERRA";

	}



	public function calculaActividad(){

		global $sonVuelos;

		//si el servicio es de tierra (suma actividad ordinaria)
		if(!in_array($this->tipo,$sonVuelos)){

			//guardo rn variables static del lugar y fecha donde ha
			//salido el tripulante ACRTUALIZO VARIABLES STATIC para calculo de timezone
			Servicio::$fechaIniServicio=$this->fechaIni;
			Servicio::$aptoIniServicio=$this->aptIni;

			//si el servicio no es de vuelo no calculamos actividad
			//si el servicio es el medico sumamos cero a la actividad
			if($this->tipo=="RM"){

				$this->tiempoActividad=new DateInterval("PT0M");

			}else{

				$this->tiempoActividad=$this->fechaIni->diff($this->fechaFin);

			}

			$this->actualizaTotales();
			$this->asignaDietas();
			return;

		}

		/**
		 * para obtener miraremos lo que hay en la bbdd tabla 'firmas' o los valores
		 * introducidos por el usuario en el form. Tendra preferencia los valores
		 * que figuren en la bbdd
		 */
		$tiempoFirma=$this->dameTiempoFirma();

		$tiempoDesfirma=$this->dameTiempoDesfirma();

		//miramos si el primer vuelo contiene informacion de hora programada par el vuelo:
		if(isset($this->arrVuelos[0]->fechaProgIni)){

			$this->fechaFirma=clone $this->arrVuelos[0]->fechaProgIni;
			$this->fechaFirma->sub(new DateInterval("PT".$tiempoFirma."M"));
			$this->misc=$this->misc." *HoraProg";

		}else{

			$this->fechaFirma=clone $this->fechaIni;
			$this->fechaFirma->sub(new DateInterval("PT".$tiempoFirma."M"));
			$this->misc=$this->misc." *HoraVuelo";

		}


		//fechadesfirma siempre sera la llegada del vuelo mas el tiempo desfrima. no depende de lo programado
		$this->fechaDesfirma=clone $this->fechaFin;
		$this->fechaDesfirma->add(new DateInterval("PT".$tiempoDesfirma."M"));

		$this->tiempoActividad=$this->fechaDesfirma->diff($this->fechaFirma);
		$this->actualizaTotales();

		//CALCULO DE ACTIVIDAD NOCTURNA

		$this->calculaActividadNocturna();

		//CALCULO DE ACTIVIDAD EXTRAORDINARIA POR FDP
		//como necesito la hora local del ultimo lugar en el que estuvo,
		//dejo en variables static el apto y la fecha z de desfirma mas abajo
		$this->calculaActividadExtra();

		//guardo rn variables static del lugar y fecha donde ha
		//salido el tripulante ACRTUALIZO VARIABLES STATIC
		Servicio::$fechaIniServicio=$this->fechaFirma;
		Servicio::$aptoIniServicio=$this->aptIni;

		$this->calculaImportes();
		$this->asignaDietas();

	}

	/**
	 * funcion que calvula los importes correspondientes a la nocturnidad
	 * y a la actividad extraordinaria
	 */
	private function calculaImportes(){

		//caclculo el importe nocturno del servicio en cuestion

		$impNoc=$this->piloto->nivel['nocturna'];

		$horas=$this->tiempoActividadNocturna->h;

		$minsDecimal=$this->tiempoActividadNocturna->i/60;

		$this->importeActividadNoc=($horas + $minsDecimal) * $impNoc;

		Servicio::$totalImporteActNoc=Servicio::$totalImporteActNoc + $this->importeActividadNoc;

		$this->contadorImpNoc=Servicio::$totalImporteActNoc;

		//ahora calculo el importe de la actividad ex

		$impEx=$this->piloto->nivel['actividad'];

		$horas=$this->tiempoActividadEx->h;

		$minsDecimal=$this->tiempoActividadEx->i/60;

		$this->importeActividadEx=($horas + $minsDecimal) * $impEx;

		Servicio::$totalImporteActEx=Servicio::$totalImporteActEx + $this->importeActividadEx;

		$this->contadorImpEx=Servicio::$totalImporteActEx;



	}

	/**
	 * la funcion calcula lactividad nocturna y devuelve un numero entero de minutos
	 * correspondientes a la actividad nocturna
	 */
	private function dameNocturno(){

		$tmActividad=$this->tiempoActividad;
		$dtFirma=clone $this->fechaFirma;
		$dtDesFirma=clone $this->fechaDesfirma;

		//hay 6 casos diferentes para un servicio:
		/**SERVICIO
		 * 1.- SERVICIO EMPIEZA DE DIA Y ACABA DE DIA:
		 * 		1A.- SERVICIO CORTO; Todo diurno.
		 * 		1B.- SERVICIO PELOTAZO; (empezo de dia y volo toda la noche); Todo Nocturno
		 * 2.- SERVICIO EMPIEZA DIA Y ACABA NOCHE:
		 * 		2A.- SERVICIO QUE ENTRA MENOS DE 4H EN NOCHE (Parte Nocturno)
		 * 		2B.- SERVICIO QUE ENTRA 4h en noche (Todo nocturno)
		 * 3.- SERVICO EMPIEZA NOCHE Y ACABA DIA:
		 * 		3A.- EMPIEZA TARDE EN LA NOCHE (mADRUGON) (menos 4h noche) (parte nocturno)
		 * 		3B.- EMPIEZA PRONTO EN LA NOCHE (mas de 4 de noche) (todo nocturno);
		 * 4.- SERVICIO QUE EMPIEZA DE NOCHE Y ACABA DE NOCHE
		 * 		4A.- MAS DE 4H DE NOCTURNO
		 * 		4B.- MENOS DE 4H
		 */
		$horaFirma=strtotime($dtFirma->format("H:i"));
		$horaDesFirma=strtotime($dtDesFirma->format("H:i"));

		$minutosAct=$tmActividad->h * 60 + $tmActividad->i;

		$empiezaDia=false;
		$acabaDia=false;

		//miramos si el vuelo empieza de dia:
		if($horaFirma<strtotime("21:00") && $horaFirma>strtotime("08:00")){

			$empiezaDia=true;

		}else{

			$empiezaDia=false;

		}

		//miramos si el vuelo acaba de dia:
		if($horaDesFirma<strtotime("21:00") && $horaDesFirma>strtotime("08:00")){

			$acabaDia=true;

		}else{

			$acabaDia=false;

		}

		//ahora ya podemos ir con los cassos expuestos arriba:
		//1. empieza dia y acaba dia
		if($empiezaDia && $acabaDia){

			//la noche dura 11h = 660mins
			if($minutosAct>=660){
				//1b pelotazo
				return $minutosAct;
			}else{
				//1a servicio diurno
				return 0;

			}

		//2. empieza dia y acaba noche
		}else if($empiezaDia && !$acabaDia){

			$las2100=clone $dtDesFirma;

			date_time_set($las2100,21,0);

			$timepoNoche=$dtDesFirma->diff($las2100);

			$minsNoche=$timepoNoche->h*60+$timepoNoche->i;

			//miramos si ha pasado la media noche (+ 1 dia)
			//180m = 3h desde las 2100 a medianoche
			if($minsNoche>180){

				$minsNoche=$minsNoche-24*60;

				$minsNoche=abs($minsNoche);

			}

			//finalmente devolvemos la actividad nocturna dependiendo de
			//si han pasado mas de 4h nocturnas o no:
			if($minsNoche>=240){

				//cxaso 2b
				return $minutosAct;

			}else{

				//caso 2a
				return $minsNoche;

			}

		//3.- empieza noche y acaba dia
		}else if(!$empiezaDia && $acabaDia){

			$las0800=clone $dtFirma;

			date_time_set($las0800,8,0);

			$timepoNoche=$las0800->diff($dtFirma);

			$minsNoche=$timepoNoche->h*60+$timepoNoche->i;

			//miramos si ha pasado la media noche (+ 1 dia)
			//480mins =8h de media noche al inicio del 'dia'
			if($minsNoche>480){

				$minsNoche=$minsNoche-24*60;

				$minsNoche=abs($minsNoche);

			}
			//finalmente devolvemos la actividad nocturna dependiendo de
			//si han pasado mas de 4h nocturnas o no:
			if($minsNoche>=240){

				//cxaso 2b
				return $minutosAct;

			}else{

				//caso 2a
				return $minsNoche;

			}

		//4.- con el 787 y reforzados puede darse el caso de empezar un servicio de noche
		// y acabar de noche ... se firma antes de las 0800 y se desfirma
		//pasadas las 2100.
		}else if(!$empiezaDia && !$acabaDia){
			//en este caso hay que mirar la nocturnidad al principio y al final
			//echo "empieza noche y acaba noche";

			//***** empiezo mirando la nocturnindad al inicio del servicio
			$las0800=clone $dtFirma;

			date_time_set($las0800,8,0);

			$timepoNocheIni=$las0800->diff($dtFirma);

			$minsNocheIni=$timepoNocheIni->h*60+$timepoNocheIni->i;

			//miramos si ha pasado la media noche (+ 1 dia)
			//480mins =8h de media noche al inicio del 'dia'
			if($minsNocheIni>480){

				$minsNocheIni=$minsNocheIni-24*60;

				$minsNocheIni=abs($minsNocheIni);

			}

			//***** termino mirando la nocturnindad al final del servicio
			$las2100=clone $dtDesFirma;

			date_time_set($las2100,21,0);

			$timepoNocheFin=$dtDesFirma->diff($las2100);

			$minsNocheFin=$timepoNocheFin->h*60+$timepoNocheFin->i;

			//miramos si ha pasado la media noche (+ 1 dia)
			//180m = 3h desde las 2100 a medianoche
			if($minsNocheFin>180){

				$minsNocheFin=$minsNocheFin-24*60;

				$minsNocheFin=abs($minsNocheFin);

			}

			//miramos la nocturnidad total (del principio y del final del servicio)
			$minsNoche=$minsNocheIni + $minsNocheFin;

			//finalmente devolvemos la actividad nocturna dependiendo de
			//si han pasado mas de 4h nocturnas o no:
			if($minsNoche>=240){

				//cxaso 4b
				return $minutosAct;

			}else{

				//caso 4a
				return $minsNoche;

			}

		}

	//me va a reventar la cabeza, el codigo parece funcionarr, pero tiene
	//que haber una manera mas bonita de hacerlo. lo dejo asi y ya lo repasare

	}

	/**
	 * funcion que comprueba si hay info sobre la firma en ese aeropuerto
	 * y la devuelve. si no encuentra nada devuelve la firma introducida
	 * en el form por el ususario
	 */
	private function dameTiempoFirma(){

		$unaFirma=new Firma($this->aptIni,$this->piloto->flota);

		if(isset($unaFirma->firma) && $unaFirma->firma!=0){

			$tiempoFirma= $unaFirma->firma;

		}else{

			$tiempoFirma=$this->piloto->tiempoFirma;

		}

		$this->misc=$this->misc . "<br>firma_" . $tiempoFirma;

		return $tiempoFirma;

	}

	private function dameTiempoDesfirma(){

		$unaDesFirma=new Firma($this->aptFin,$this->piloto->flota);

		if(isset($unaDesFirma->desfirma) && $unaDesFirma->desfirma!=0){

			$tiempoDesFirma= $unaDesFirma->desfirma;

		}else{

			$tiempoDesFirma=$this->piloto->tiempoDesfirma;

		}

		$this->misc=$this->misc . " desf_" . $tiempoDesFirma;

		return $tiempoDesFirma;


	}

	private function actualizaTotales(){

		//********************************** */

		$horas=$this->tiempoActividad->h;
		$minutos=$this->tiempoActividad->i;

		Servicio::$totalHorasAct=Servicio::$totalHorasAct+$horas;
		Servicio::$totalMinutosAct=Servicio::$totalMinutosAct+$minutos;

		//miramos si los minutos son mayores o iguales de 60
		if(Servicio::$totalMinutosAct>=60){

			$sumHoras=intdiv(Servicio::$totalMinutosAct,60);

			Servicio::$totalHorasAct=Servicio::$totalHorasAct+$sumHoras;

			$nuevosMinutos=Servicio::$totalMinutosAct % 60;

			Servicio::$totalMinutosAct=$nuevosMinutos;

		}

		//almacenamos los valores estatic en propiedades del obj
		$this->contadorHact=Servicio::$totalHorasAct;
		$this->contadorMact=Servicio::$totalMinutosAct;


	}

	/**
	 * la funcion calcula lactividad extra y devuelve un numero entero de minutos
	 * correspondientes a la actividad extraordinaria
	 */
	private function dameMaxFdp(){

		/**
		 * https://stackoverflow.com/questions/46276333/php-calculating-time-difference-in-hours-between-two-timezones
		 */

		/**
		 *  the period between 02:00 hours and 05:59 hours.
		 *  Within a band of three time zones the WOCL refers to home base time.
		 *  Beyond these three time zones the WOCL refers to home base time for
		 *  the first 48 hours after departure from home base time zone and to local
		 *  time thereafter.
		 */
		//las horas de firma y desfirma estan en utc
		//hay q cambiarlas a local de madrid para ve si estamos en el wocl

		$tmActividad=$this->tiempoActividad;
		$dtFirma=clone $this->fechaFirma;
		$dtDesFirma=clone $this->fechaDesfirma;

		$time_zone=$this->calculaTz();

		//paso la hora de firma a la que corresponda en local
		$dtFirma->setTimezone(new DateTimeZone($time_zone));
		$dtDesFirma->setTimezone(new DateTimeZone($time_zone));


	/**
	 * 7.4 The maximum basic daily FDP
	 * 7.4.1 The maximum basic daily FDP is 13 hours.
	 * 7.4.2 These 13 hours shall be further reduced by 30 minutes for each
	 * sector from the 3rd sector onwards. Maximum FDP reduction for the number of
	 * sectors is 2 hours.
	 *
	 * 7.4.3 When the FDP starts in the Window of Circadian Low (WOCL),
	 * the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2 shall be further
	 * reduced by 100% of its encroachment on the WOCL. This reduction shall be a
	 * maximum of 2 hours.
	 * When the FDP ends in or fully encompasses the WOCL,
	 * the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2 shall
	 * be further reduced by 50% of its encroachment.
	 *
	 *
	 * 2.30 Window of Circadian Low – WOCL
	 * The time period between 02:00 hours and 05:59 hours.
	 * Within a band of 3 time zones the WOCL refers to local time of a crew member’s
	 * home base. Beyond these 3 time zones the WOCL refers to the local time
	 * of the aerodrome of departure within the first 48 hours, and thereafter,
	 * to the local time of the destination aerodrome.
	 *
	*/

	//780minutos son 13horas
	$maxFDP=780;

	$numeroSectores=count($this->arrVuelos);

	//maxima reduccion del fdp por sectores son 2h
	if($numeroSectores>=6) {

		$maxFDP=$maxFDP-120;

		$this->misc= $this->misc . "<br>Max Reduccion por sectores (120min)";

	}

	if($numeroSectores>2 && $numeroSectores<6) {

		$reduccion=($numeroSectores - 2) * 30;

		$maxFDP=$maxFDP-$reduccion;

		$this->misc= $this->misc . "<br>Reduccion por numero de sectores: $reduccion min ";

	}

	$horaFirma=strtotime($dtFirma->format("H:i"));
	$horaDesFirma=strtotime($dtDesFirma->format("H:i"));

	//miro si la firma se produce en el día anterior:////////
	$diaDesfirma=$dtDesFirma->format("j");
	$diaFirma=$dtFirma->format("j");
	$diaDiferente=false;
	$diferencia=abs($diaDesfirma-$diaFirma);
	if($diferencia!=0) $diaDiferente=true;
	/////////////////////////////////////////////////////////

	$empiezaWocl=false;
	$acabaWocl=false;
	$nocturnazo=false;

	//miramos si el vuelo empieza en wocl:
	if($horaFirma<=strtotime("05:59") && $horaFirma>=strtotime("02:00")){

		$empiezaWocl=true;

	}else{

		$empiezaWocl=false;

	}

	//miramos si el vuelo acaba en wocl:
	if($horaDesFirma<=strtotime("05:59") && $horaDesFirma>=strtotime("02:00")){

		$acabaWocl=true;

	}else{

		$acabaWocl=false;

	}

	//miramos si el vuelo es un nocturnazo
	//se desfirma de 'tarde' y se firma 'pronto' en el mismo dia
	if($horaDesFirma>strtotime("05:59") && $horaFirma<strtotime("02:00")){

		$nocturnazo=true;

	//se desfirma de 'tarde' y se firma 'pronto' en el dia anterior
	}else if($horaDesFirma>strtotime("05:59") && $horaFirma<strtotime("23:59") && $diaDiferente){

		$nocturnazo=true;

	}



	//caso que empiece en wocl y no acabe en wocl
	if($empiezaWocl && !$acabaWocl){
		/**MADRUGON EN ESPAÑOL
		 * When the FDP starts in the Window of Circadian Low (WOCL),
		 * the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2
		 * shall be further reduced by 100% of its encroachment on the WOCL.
		 * This reduction shall be a maximum of 2 hours.
		 */

		$las0559=clone $dtFirma;

		date_time_set($las0559,5,59);

		$timepoWocl=$las0559->diff($dtFirma);

		$minsWocl=$timepoWocl->h*60+$timepoWocl->i;

		//This reduction shall be a maximum of 2 hours.
		if($minsWocl>=120) $minsWocl=120;

		$this->misc= $this->misc . "<br>Empieza en WOCL $minsWocl minutos.";

		$maxFDP=$maxFDP-$minsWocl;


	}else if($acabaWocl && !$empiezaWocl){

		/** VUELO QUE LLEGA DE NOCHE EN ESPAÑOL
		 * When the FDP ends in or fully encompasses the WOCL,
	 	 * the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2 shall
		 * be further reduced by 50% of its encroachment.
		 */

		$las0200=clone $dtDesFirma;

		date_time_set($las0200,2,0);

		$timepoWocl=$dtDesFirma->diff($las0200);

		$minsWocl=$timepoWocl->h*60+$timepoWocl->i;

		$this->misc= $this->misc . "<br>Acaba en WOCL $minsWocl minutos. (50% redu)";

		//further reduced by 50% of its encroachment.

		//echo " max fdp: " . $maxFDP;
		$maxFDP=$maxFDP-round($minsWocl/2);

		/**fully encompasses the WOCL,
		* the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2 shall
		* be further reduced by 50% of its encroachment.
		*/
	}else if($nocturnazo){

		$minsWocl=240;

		$this->misc= $this->misc . "<br>Comprende WOCL $minsWocl minutos.(50% redu)";

		//further reduced by 50% of its encroachment.

		$maxFDP=$maxFDP-round($minsWocl/2);

		/**fully encompasses the WOCL,
		* the maximum FDP limit stated in paragraphs 7.4.1 and 7.4.2 shall
		* be further reduced by 50% of its encroachment.
		*/

	}



	// echo json_encode($dtFirma);
	// echo json_encode($dtDesFirma);

	$cadenaMaxFdp=intval($maxFDP/60) . " horas " . ($maxFDP % 60) . " minutos";
	$this->misc=$this->misc . "<br>MaxFDP " . $cadenaMaxFdp;
	$this->misc=$this->misc . "<br>TimeZone utilizada: " . $time_zone;
	//ahora con el MAXFDP calculado podemos ver si entramos en el o no:

	return $maxFDP;


	}

	private function calculaActividadNocturna(){

		$minsActNocturna=$this->dameNocturno();
		$horasActNocturna=0;

		if($minsActNocturna>=60){

			$horasActNocturna=intdiv($minsActNocturna,60);

			$minsActNocturna=$minsActNocturna % 60;

		}

		Servicio::$totalMinutosActNoc=Servicio::$totalMinutosActNoc+$minsActNocturna;

		Servicio::$totalHorasActNoc=Servicio::$totalHorasActNoc+$horasActNocturna;

		if(Servicio::$totalMinutosActNoc>=60){

			$sumHorasNoc=intdiv(Servicio::$totalMinutosActNoc,60);

			Servicio::$totalHorasActNoc=Servicio::$totalHorasActNoc+$sumHorasNoc;

			$nuevosMinutosNoc=Servicio::$totalMinutosActNoc % 60;

			Servicio::$totalMinutosActNoc=$nuevosMinutosNoc;

		}

		$this->contadorHactNoc=Servicio::$totalHorasActNoc;
		$this->contadorMactNoc=Servicio::$totalMinutosActNoc;

		$this->tiempoActividadNocturna=new DateInterval("PT".$horasActNocturna."H".$minsActNocturna."M");

	}

	private function calculaActividadExtra(){

		//con esta variable nos aseguramos mas abajo que no se vuelve a calcular
		//si se pasan de 160h la act ordinaria
		$actExtraordinariaYaCalculada=false;

		$maxFDP=$this->dameMaxFdp();

		global $limiteActExtra;

		$horasActEx=0;

		$minsActEx=0;

		$minutosActividad= ($this->tiempoActividad->h) * 60 + ($this->tiempoActividad->i);

		//ESTAMOS EN UNA ACTIVIDAD MAYOR Q LA ORDINARIA
		if($minutosActividad>$maxFDP){

			$actExtraordinariaYaCalculada=true;

			if($minutosActividad>=60){

				$horasActEx=intdiv($minutosActividad,60);

				$minsActEx=$minutosActividad % 60;

			}
		}

		//SI YA ESTAMOS POR ENCIMA DE 160H
		else if(Servicio::$totalHorasAct >= $limiteActExtra  &&
		$actExtraordinariaYaCalculada==false){

			$actDecimal=Servicio::$totalHorasAct + (Servicio::$totalMinutosAct /60);

			$diferencia= $actDecimal - $limiteActExtra;

			//paso la diferencia a minutos (era en hora decimal)
			$diferencia=abs($diferencia * 60);
			//entramos en 160h en este servicio, solo cuento una parte
			if($minutosActividad>$diferencia){

				$diferencia=$diferencia / 60;

				$horasActEx=(int) $diferencia;

				$minsActEx=round(($diferencia - $horasActEx) * 60);

			//ya estamos en bonus, toda la act es act extra
			}else{

				if($minutosActividad>=60){

					$horasActEx=intdiv($minutosActividad,60);

					$minsActEx=$minutosActividad % 60;

				}

			}

		}

		//augmentamos los contadores
		Servicio::$totalMinutosActEx=Servicio::$totalMinutosActEx+$minsActEx;

		Servicio::$totalHorasActEx=Servicio::$totalHorasActEx+$horasActEx;

		if(Servicio::$totalMinutosActEx>=60){

			$sumHorasEx=intdiv(Servicio::$totalMinutosActEx,60);

			Servicio::$totalHorasActEx=Servicio::$totalHorasActEx+$sumHorasEx;

			$nuevosMinutosEx=Servicio::$totalMinutosActEx % 60;

			Servicio::$totalMinutosActEx=$nuevosMinutosEx;

		}

		$this->contadorHactEx=Servicio::$totalHorasActEx;
		$this->contadorMactEx=Servicio::$totalMinutosActEx;

		$this->tiempoActividadEx=new DateInterval("PT".$horasActEx."H".$minsActEx."M");


	}

	/**
	 * funcion que devuelve el timezone para el calculo de la hora
	 * local en el calculo de act extraordinaria
	 * es llamadaa desde dame max FDP
	 */
	public function calculaTz(){

		$aptoSalida=Servicio::$aptoIniServicio;
		$fechaSalida=Servicio::$fechaIniServicio;

		//caso de que sea el primer vuelo par el calculo
		if($aptoSalida==null)$aptoSalida=$this->piloto->aclimatadoInicial;
		if($fechaSalida==null)$fechaSalida=$this->fechaFirma;

		//ahora miro en cuantos husos estoy
		$previousDepAp=new Aeropuerto($aptoSalida);

		if($previousDepAp->arrDatosAeropuerto==false){

			$previousDepAp=new Aeropuerto("MAD");
			$this->misc=$this->misc . "<br>Aeropuerto $aptoSalida no encontrado. Act Extra no fiable.";

		}

		$z_previousDepAp=$previousDepAp->husos;

		$depAp=new Aeropuerto($this->aptIni);

		if($depAp->arrDatosAeropuerto==false){

			$depAp=new Aeropuerto("MAD");
			$this->misc=$this->misc . "<br>Aeropuerto $depAp no encontrado. Act Extra no fiable.";

		}
		$z_depAp=$depAp->husos;

		//diferencia entre la ultima salida y esta salida
		$diferenciaHoraria=abs($z_previousDepAp-$z_depAp);

		$this->misc=$this->misc . "<br>DiferenciaHoraria $diferenciaHoraria";
		//si hay 3 o menos husos, se considera la misma hora local
		if($diferenciaHoraria<=3){

			return $previousDepAp->tz;

		}else{

			//si hay mas de 3 husos de diferencia miro si han pasado mas de 48h
			$tiempoTranscurrido=$this->fechaFirma->diff($fechaSalida);

			//$this->misc=$this->misc . "<br>" . json_encode($tiempoTranscurrido);
			//miro si han pasado mas de 48h (tomamos tz del apto de salida)
			//o menos, tomamos el tz del apto previo.
			if($tiempoTranscurrido->d>2){

				return $depAp->tz;

			}else{

				return $previousDepAp->tz;

			}

		}



	}



}

?>
