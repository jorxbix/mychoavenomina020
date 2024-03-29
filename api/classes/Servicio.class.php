<?php
require_once "Fdp.class.php";
class Servicio{

	public static $aptoIniServicio;
	public static $fechaIniServicio;

	public static $totalHorasAct=0;
	public static $totalMinutosAct=0;

	public static $totalHorasActNoc=0;
	public static $totalMinutosActNoc=0;

	public static $totalHorasActEx=0;
	public static $totalMinutosActEx=0;

	public static $totalHorasActFueraMins=0;
	public static $totalMinutosActFueraMins=0;

	public static $totalImporteActNoc=0;
	public static $totalImporteActEx=0;
	public static $totalImporteActFueraMins=0;

	public static $diaUltimaDieta=0;
	public static $importeUltimaDieta=0;


	//tipo de servicio
	public $tipo;

	//este valor indica si se tiene que borra el SA con dieta que se genera:
	public $eliminarSAdietaAnterior = false;

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
	 * Un servicio puede contener una serie de vuelos que se almacenan en
	 * la variable $arrVuelos
	 */
	public $arrSuelos;
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

	public $piloto;

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

	public static $dietaPendiente;

	//esta propiedad se va a usar para determinar los totales del calculo
	public $mesDelInforme=0;
	public $fantasma=false;
	public $destruir=false;

/**Funcion constructora, devuelve false si hay algun error al montar el servicio
 * @param $fila es el array con todos los datos del servicio
 *
 */
	public function __construct($fila,$piloto){

		if(!isset($_SESSION['mesInforme'])) $_SESSION['mesInforme']=0;
		$this->mesDelInforme=$_SESSION['mesInforme'];

		//truco para crear un objecto servicio vacio
		if($fila==null && $piloto==null) return false;

		$this->piloto=$piloto;

		if(isset($fila->tipo) && isset($fila->aptIni) && isset($fila->aptFin)){

			$this->tipo=$fila->tipo;

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

		if(!is_null($this->arrVuelos)){

			$this->asignaDietasVuelo();

		}else{

			$this->asignaDietasTierra();

		}


	}



	protected function asignaDietasVuelo(){

		$INFO="";

		$BASE=$this->piloto->base;

		$PERNOCTA=false;

		$DIETA_LARGA_REDUCIDA=false;

		//veamos cuantas dietas corresponden a este servicio:

		$arrDIAS_DIETA=Dieta::dameDiasDieta($this);
		$arrMESES_DIETA=Dieta::dameMesesDieta($this);

		$INFO=$INFO . " arrDias: " . json_encode($arrDIAS_DIETA);

		$INFO=$INFO . " DIETAS DIA: $arrDIAS_DIETA[0] // ";

		//**************DIETA DE SALIDA DIETA 1*************** */

		//si no hay dia para dieta aborto
		if (!isset($arrDIAS_DIETA[0])) return;

		$dieta="D";

		//determinar distancia de la dieta
		$DISTANCIA= Dieta::dameDistancia($this->arrVuelos,$arrDIAS_DIETA[0],$BASE);

		//$INFO=$INFO  . $DISTANCIA . " // ";

		if($DISTANCIA=="NACIONAL") $dieta=$dieta . "N";
		if($DISTANCIA=="INTERNACIONAL") $dieta=$dieta . "I";
		if($DISTANCIA=="LARGA"){

			$dieta=$dieta . "L";

			$DIETA_LARGA_REDUCIDA=Dieta::esDietaReducida($this);

			if ($DIETA_LARGA_REDUCIDA)	$INFO=$INFO . "Es Dieta Larga Reducida. ";

		}

		$BLOCK_OFF_ENELDIA=Dieta::hayBlockOffenElDia($this,$arrDIAS_DIETA[0]);

		if(!$BLOCK_OFF_ENELDIA){

			$dieta=$dieta . "T";

			$INFO=$INFO . "Sin BlockOff en el dia. " ;

		}else{

			$PERNOCTA=Dieta::esPernocta($this,$arrDIAS_DIETA[0],$arrMESES_DIETA[0]);

			$INFO=$INFO . "Dia Calculo Pernocta: " . $arrDIAS_DIETA[0] . " // ";

			$INFO=$INFO . $PERNOCTA . " /****/ ";

			if($PERNOCTA){

				$dieta=$dieta . "P";

			}else{

				$dieta=$dieta . "C";

				if($this->aptFin==$BASE) $INFO=$INFO . "No Pernocta (puede que ya este dada)  // ";

			}

		}

		$miDieta=new Dieta($dieta,$this->piloto);

		//si la dieta ya esta dada aborto .... O NO PQ TALVEZ CORRESPONDE UNA DIETA MAS ALTA
		if ($arrDIAS_DIETA[0]==Servicio::$diaUltimaDieta && 
		Servicio::$importeUltimaDieta>$miDieta->arrDatosDieta['bruto']){

			//la dieta ya estaba dada y el importe era mayor, por eso no asigno otra mas baja
			$INFO=$INFO . Servicio::$importeUltimaDieta . "//" . $miDieta->arrDatosDieta['bruto'] ;
			$this->arrDietas[0]->misc=$INFO;
			return;

		}else{
			if ($arrDIAS_DIETA[0]==Servicio::$diaUltimaDieta && 
			Servicio::$importeUltimaDieta<=$miDieta->arrDatosDieta['bruto']) {

				$this->eliminarSAdietaAnterior=true;
				$this->misc=$this->misc . "ELIMINAR SERV ANTERIORRRR****";

				$INFO=$INFO . "/eliminar SA anterior y dieta/";

			}

			$INFO=$INFO . Servicio::$importeUltimaDieta . "//" . $miDieta->arrDatosDieta['bruto'] ;
			//la dieta no estaba dada o el importe de esta es mayor, asigno la nueva
			Servicio::$diaUltimaDieta=$arrDIAS_DIETA[0];

			$this->arrDietas[0]=$miDieta;
			//$this->arrDietas[0]=new Dieta($dieta,$this->piloto);

		} 

		


		

		if($DIETA_LARGA_REDUCIDA){

			$this->arrDietas[0]->arrDatosDieta['bruto']=round($this->arrDietas[0]->arrDatosDieta['bruto']*0.75,2);

			//en la dieta larga reducida no se reduce el importe exento
			//$this->arrDietas[0]->arrDatosDieta['exento']=round($this->arrDietas[0]->arrDatosDieta['exento']*0.75,2);

			$this->arrDietas[0]->codigo=$this->arrDietas[0]->codigo . "_2 (redu 3/4)";

		}

		Servicio::$importeUltimaDieta = $this->arrDietas[0]->arrDatosDieta['bruto'];

		$this->arrDietas[0]->misc=$INFO;

		$this->arrDietas[0]->diaDieta=$this->fechaFirma;

		$this->arrDietas[0]->misc=$INFO;


		//**************DIETA DE LLEGADA SOLO SI SE LLEGA UN DIA DIFERENTE*************** */
		//dieta2:

		if (!isset($arrDIAS_DIETA[1])) return;

		//si la dieta ya esta dada aborto
		if ($arrDIAS_DIETA[1]==Servicio::$diaUltimaDieta) return;

		Servicio::$diaUltimaDieta=$arrDIAS_DIETA[1];

		//reseteo de variables para el segundo calculo

		$INFO="";

		$INFO=$INFO . "DIETAS DIA: $arrDIAS_DIETA[1] /dieta de llegada/ ";

		$PERNOCTA=false;

		$DIETA_LARGA_REDUCIDA=false;

		$dieta="D";

		//determinar distancia de la dieta
		$DISTANCIA= Dieta::dameDistancia($this->arrVuelos,$arrDIAS_DIETA[1],$BASE);

		$INFO=$INFO  . $DISTANCIA . " // ";

		if($DISTANCIA=="NACIONAL") $dieta=$dieta . "N";
		if($DISTANCIA=="INTERNACIONAL") $dieta=$dieta . "I";
		if($DISTANCIA=="LARGA"){

			$dieta=$dieta . "L";

		}

		if($this->aptFin!=$BASE){

			$dieta=$dieta . "P";

			$INFO=$INFO . "Sin BlockOff en el dia, pero con Hotel. // ";

		}else{

			$dieta=$dieta . "T";

			$INFO=$INFO . "Sin BlockOff en el dia. " . " // ";

		}

		$this->arrDietas[1]=new Dieta($dieta,$this->piloto);

		if($DIETA_LARGA_REDUCIDA){

			$this->arrDietas[1]->arrDatosDieta['bruto']=round($this->arrDietas[1]->arrDatosDieta['bruto']*0.75,2);

			$this->arrDietas[1]->arrDatosDieta['exento']=round($this->arrDietas[1]->arrDatosDieta['exento']*0.75,2);

			$this->arrDietas[1]->codigo=$this->arrDietas[1]->codigo . "_2 (redu 3/4)";

		}

		$this->arrDietas[1]->misc=$INFO;		

		$this->arrDietas[1]->diaDieta=$this->fechaDesfirma;

		//******************linea para evitar cobrar una dieta mas baja */
		Servicio::$importeUltimaDieta = $this->arrDietas[1]->arrDatosDieta['bruto'];

		Servicio::$dietaPendiente=clone $this;

		//ahora me tengo que cargar la segunda dieta de este servicio puesto que se va
		//a presentar en un dia diferente.

		array_pop($this->arrDietas);


	}



	protected function asignaDietasTierra(){

		//para generar dieta en tierra tiene q ser codigo de serv en tierra
		global $sonServTierra;

		if(!in_array($this->tipo,$sonServTierra)) return;

		if(Servicio::$diaUltimaDieta==$this->fechaFin->format("d")){

			$this->misc="Dieta ya asignada";
			$this->fantasma=true;
			if($this->tipo=="SA") $this->destruir=true;
			return;

		}

		//los SA y los FR en base no se pagan
		$BASE=$this->piloto->base;

		if($this->tipo=="SA" || $this->tipo=="FR"){

			if($this->aptIni == $this->aptFin && $this->aptFin == $BASE) return;

		}


		$INFO="";

		$PERNOCTA=false;

		if($BASE!=$this->aptFin) $PERNOCTA=true;

		$INFO=$INFO . "DIETA TIERRA ";

		$dieta="D";

		//determinar distancia de la dieta
		$DISTANCIA= Dieta::dameDistanciaApto($this->aptFin);

		$INFO=$INFO  . $DISTANCIA . " // dia ultima dieta: " . Servicio::$diaUltimaDieta . " // ";

		if($DISTANCIA=="NACIONAL") $dieta=$dieta . "N";
		if($DISTANCIA=="INTERNACIONAL") $dieta=$dieta . "I";
		if($DISTANCIA=="LARGA") $dieta=$dieta . "L";

		if($PERNOCTA){

			$dieta=$dieta . "P";

		}else{

			$dieta=$dieta . "C";

		}

		$this->arrDietas[0]=new Dieta($dieta,$this->piloto);

		$this->arrDietas[0]->misc=$INFO;

		$this->arrDietas[0]->diaDieta=$this->fechaIni;

		Servicio::$diaUltimaDieta=$this->fechaIni->format("d");

		$INFO=$INFO  . " dia esta dieta: " . $this->fechaIni->format("d") . " // ";

	}



	public function calculaActividad(){

		global $sonVuelos;
		global $sonLibres;

		//si el servicio es de tierra (suma actividad ordinaria)
		if(!in_array($this->tipo,$sonVuelos)){

			//guardo rn variables static del lugar y fecha donde ha
			//salido el tripulante ACRTUALIZO VARIABLES STATIC para calculo de timezone
			//**********si es un servicio de TIERRA NO actualizo las variables static *********/
			// Servicio::$fechaIniServicio=$this->fechaIni;
			// Servicio::$aptoIniServicio=$this->aptIni;
			//********************************************************************************/

			//si el servicio no es de vuelo no calculamos actividad
			//si el servicio es el medico sumamos cero a la actividad
			if($this->tipo=="RM" || in_array($this->tipo,$sonLibres)){

				$this->tiempoActividad=new DateInterval("PT0M");

			}else{

				$this->tiempoActividad=$this->fechaIni->diff($this->fechaFin);

			}

			$this->actualizaTotales();
			//asigno las fechas de firma y desfirma al mismo que fecha ini y fecha fin
			$this->fechaFirma=$this->fechaIni;
			$this->fechaDesfirma=$this->fechaFin;
			$this->calculaActividadNocturna();
			$this->calculaActividadExtra2();

			//guardo rn variables static del lugar y fecha donde ha
			//salido el tripulante ACRTUALIZO VARIABLES STATIC para el calculo de timezone
			Servicio::$fechaIniServicio=$this->fechaFirma;
			Servicio::$aptoIniServicio=$this->aptIni;

			$this->calculaImportes();
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
		$this->calculaActividadExtra2();

		//guardo rn variables static del lugar y fecha donde ha
		//salido el tripulante ACRTUALIZO VARIABLES STATIC para el calculo de timezone
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

		//primero miramos si el vuelo forma parte del calculo de este mes para actualizar totales;
		if ($this->fechaFirma->format('n')==$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			Servicio::$totalImporteActNoc=Servicio::$totalImporteActNoc + $this->importeActividadNoc;

			$this->contadorImpNoc=Servicio::$totalImporteActNoc;

		}

		//ahora calculo el importe de la actividad ex

		$impEx=$this->piloto->nivel['actividad'];

		$horas=$this->tiempoActividadEx->h;

		$minsDecimal=$this->tiempoActividadEx->i/60;

		$this->importeActividadEx=($horas + $minsDecimal) * $impEx;

		//primero miramos si el vuelo forma parte del calculo de este mes para actualizar totales;
		if ($this->fechaFirma->format('n')==$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			Servicio::$totalImporteActEx=Servicio::$totalImporteActEx + $this->importeActividadEx;

			$this->contadorImpEx=Servicio::$totalImporteActEx;

		}

	}

	/**
	 * la funcion calcula lactividad nocturna y devuelve un numero entero de minutos
	 * correspondientes a la actividad nocturna
	 */
	private function dameNocturno(){

		global $sonLibres;
		if(in_array($this->tipo, $sonLibres)) return 0;

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

		//primero miramos si el vuelo forma parte del calculo de este mes;
		if ($this->fechaIni->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;

			return;

		}

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

		//primero miramos si el vuelo forma parte del calculo de este mes;
		if ($this->fechaFirma->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;
			$this->tiempoActividadNocturna=new DateInterval("PT".$horasActNocturna."H".$minsActNocturna."M");
			return;

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

		/////////////////////////EVITA Q DE FALLO EN EL PRIMER SERVICIO CON VARIABLES STSTAIC EN NULL
		if(Servicio::$fechaIniServicio==null) Servicio::$fechaIniServicio=$this->fechaIni;

		$maxFDP=$this->dameMaxFdp2();

		global $limiteActExtra;

		$horasActEx=0;

		$minsActEx=0;

		$minutosActividad= ($this->tiempoActividad->h) * 60 + ($this->tiempoActividad->i);

		// $this->misc=$this->misc." -MinsAct=$minutosActividad MaxFDP=$maxFDP";
		// if($minutosActividad>$maxFDP) $this->misc=$this->misc."MisAct MAYOR que MaxFDP";
		//ESTAMOS EN UNA ACTIVIDAD MAYOR Q LA ORDINARIA
		if($minutosActividad>$maxFDP){

			$actExtraordinariaYaCalculada=true;

			if($minutosActividad>=60){

				$horasActEx=intdiv($minutosActividad,60);

				$minsActEx=$minutosActividad % 60;

			}
		}

		//SI YA ESTAMOS POR ENCIMA DE 160H *****ESTO ESTA TODO MAL, LOS FUERA MINIMOS SE CUENTAN A APRTE
		else if(Servicio::$totalHorasAct >= $limiteActExtra  &&
		$actExtraordinariaYaCalculada==false){

			$this->misc=$this->misc." -Entramos en fuera minimos.";

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

		//primero miramos si el vuelo forma parte del calculo de este mes;
		if ($this->fechaFirma->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;
			$this->tiempoActividadEx=new DateInterval("PT".$horasActEx."H".$minsActEx."M");
			return;

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
	private function calculaActividadExtra2(){

		/////////////////////////EVITA Q DE FALLO EN EL PRIMER SERVICIO CON VARIABLES STSTAIC EN NULL
		if(Servicio::$fechaIniServicio==null) Servicio::$fechaIniServicio=$this->fechaIni;

		$maxFDP=$this->dameMaxFdp2();

		global $limiteActExtra;

		$horasActEx=0;

		$minsActEx=0;

		$minutosActividad= ($this->tiempoActividad->h) * 60 + ($this->tiempoActividad->i);

		// $this->misc=$this->misc." -MinsAct=$minutosActividad MaxFDP=$maxFDP";
		// if($minutosActividad>$maxFDP) $this->misc=$this->misc."MisAct MAYOR que MaxFDP";
		//ESTAMOS EN UNA ACTIVIDAD MAYOR Q LA ORDINARIA
		if($minutosActividad>$maxFDP){

			//$actExtraordinariaYaCalculada=true;

			if($minutosActividad>=60){

				$horasActEx=intdiv($minutosActividad,60);

				$minsActEx=$minutosActividad % 60;

			}
		}


		//primero miramos si el vuelo forma parte del calculo de este mes;
		if ($this->fechaFirma->format('n')!=$_SESSION['mesInforme'] && $_SESSION['mesInforme']!=0){

			$this->fantasma=true;
			$this->tiempoActividadEx=new DateInterval("PT".$horasActEx."H".$minsActEx."M");
			return;

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
	 * local en el calculo de act extraordinaria asi como la definicion del periodo wocl
	 * es llamadaa desde dame max FDP
	 */
	public function calculaTz(){

		$aptoSalida=Servicio::$aptoIniServicio;
		$fechaSalida=Servicio::$fechaIniServicio;

		//caso de que sea el primer vuelo para el calculo
		if($aptoSalida==null) $aptoSalida=$this->piloto->aclimatadoInicial;
		if($fechaSalida==null) $fechaSalida=$this->fechaFirma;

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

		//debug
		$this->misc=$this->misc . "<br>DiferenciaHoraria=$diferenciaHoraria ; $previousDepAp->iata ($z_previousDepAp) - $depAp->iata ($z_depAp)";

		/**
		 *
		 *
		 * 2.30 Window of Circadian Low – WOCL
		 * The time period between 02:00 hours and 05:59 hours.
		 * Within a band of 3 time zones the WOCL refers to local time of a crew member’s home base.
		 * Beyond these 3 time zones the WOCL refers to the local time of the aerodrome of departure
		 * within the first 48 hours, and thereafter, to the local time of the destination aerodrome.
		 */

		//si hay 3 o menos husos, se considera la misma hora local de la base del piloto
		if($diferenciaHoraria<=3){

			return $previousDepAp->tz;

		}else{

			//si hay mas de 3 husos de diferencia miro si han pasado mas de 48h
			$tiempoTranscurrido=$this->fechaFirma->diff($fechaSalida);

			//debug
			$this->misc=$this->misc . "<br>TiempoTranscurrido: $tiempoTranscurrido->d d $tiempoTranscurrido->h h $tiempoTranscurrido->i m";

			//miro si han pasado mas de 48h (tomamos tz del apto de llegada)
			//o menos, tomamos el tz del apto de salida.
			if($tiempoTranscurrido->d >= 2){

				$arrAp=new Aeropuerto($this->aptFin);

				if($arrAp->arrDatosAeropuerto==false){
					$arrAp=new Aeropuerto("MAD");
					$this->misc=$this->misc . "<br>Aeropuerto $arrAp no encontrado. Act Extra no fiable.";
				}

				return $arrAp->tz;


			}else{

				return $depAp->tz;

			}

		}

	}

	/**
	 * funcion que devuelve el timezone para el calculo de la hora
	 * local en el calculo de act extraordinaria asi como la definicion del periodo wocl
	 * es llamadaa desde dame max FDP
	 */
	public function dameDiferenciaHoraria(){

		$aptoSalida=Servicio::$aptoIniServicio;
		$fechaSalida=Servicio::$fechaIniServicio;

		//caso de que sea el primer vuelo para el calculo
		if($aptoSalida==null) $aptoSalida=$this->piloto->aclimatadoInicial;
		if($fechaSalida==null) $fechaSalida=$this->fechaFirma;

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

		//debug
		$this->misc=$this->misc . "<br>DiferenciaHoraria=$diferenciaHoraria ; $previousDepAp->iata ($z_previousDepAp) - $depAp->iata ($z_depAp)";

		return $diferenciaHoraria;

	}

	/**
	 * funcion que devuelve el timezone para el calculo de la hora
	 * local en el calculo de act extraordinaria asi como la definicion del periodo wocl
	 * es llamadaa desde dame max FDP **** NUEVA VERSION
	 * ****RESTA LAS HORAS LOCALES A LA LLEGADA PARA SABER EL Z_OFFSET
	 */
	public function dameDiferenciaHoraria2(){

		$aptoSalida=Servicio::$aptoIniServicio;
		$fechaSalida=Servicio::$fechaIniServicio;

		//caso de que sea el primer vuelo para el calculo
		if($aptoSalida==null) $aptoSalida=$this->piloto->aclimatadoInicial;
		if($fechaSalida==null) $fechaSalida=$this->fechaFirma;

		//ahora miro en cuantos husos estoy
		$previousDepAp=new Aeropuerto($aptoSalida);

		if($previousDepAp->arrDatosAeropuerto==false){

			$previousDepAp=new Aeropuerto("MAD");

			$this->misc=$this->misc . "<br>Aeropuerto $aptoSalida no encontrado. Act Extra no fiable.";

		}

		$z_previousDepAp=$previousDepAp->tz; /////////////////////////////

		// $depAp=new Aeropuerto($this->aptIni);

		// if($depAp->arrDatosAeropuerto==false){

		// 	$depAp=new Aeropuerto("MAD");

		// 	$this->misc=$this->misc . "<br>Aeropuerto $depAp no encontrado. Act Extra no fiable.";

		// }

		// $z_depAp=$depAp->tz; /////////////////////////////////////////

		$arrAp=new Aeropuerto($this->aptFin);

		if($arrAp->arrDatosAeropuerto==false){

			$arrAp=new Aeropuerto("MAD");

			$this->misc=$this->misc . "<br>Aeropuerto $arrAp no encontrado. Act Extra no fiable.";

		}

		$z_arrAp=$arrAp->tz; /////////////////////////////////////////

		//ahora que tenemos los aeropuertos localizados vamos a calcular la hora de firma en cada apto */

		//paso la hora de firma a la que corresponda en local
		$dtPreviousFirma=clone $this->fechaFirma;
		$dtDepFirma=clone $this->fechaFirma;


		$dtPreviousFirma->setTimezone(new DateTimeZone($z_previousDepAp));
		$dtDepFirma->setTimezone(new DateTimeZone($z_arrAp));

		$previous_offset = $dtPreviousFirma->getOffset() / 3600;
		$dep_offset = $dtDepFirma->getOffset() / 3600;

		$diff = abs($previous_offset - $dep_offset);

		$this->misc=$this->misc . "<br> diferencia horaria " . $diff;

	
	return $diff;

	}

	private function dameEstadoAclimatacion(){
// (VERTICALES):Time difference (h) between reference time and local
// time where the crew member starts the next duty
// (HORIZONTALES):Time elapsed since reporting at reference time

// 		< 48 	48-71:59 	72-95:59 	96-119:59 	≥ 120
// <4 		B 		D 		D 		D 			D
// ≤6 		B 		X 		D 		D 			D
// ≤9 		B 		X 		X 		D 			D
// ≤12 		B 		X 		X 		X 			D

// "B" significa aclimatado a la hora local de la zona horaria de partida.
// “B” means acclimatised to the local time of the departure time zone.
// “D” significa aclimatado a la hora local en que el tripulante comienza su actividad siguiente.
// “D” means acclimatised to the local time where the crew member starts his/her next duty.
// “X” significa que un tripulante se encuentra en un estado de aclimatación desconocido.
// “X” means that a crew member is in an unknown state of acclimatisation.

		$fechaSalida=Servicio::$fechaIniServicio;

		//si hay mas de 3 husos de diferencia miro si han pasado mas de 48h
		$tiempoTranscurrido=$this->fechaFirma->diff($fechaSalida);

		//debug

		$this->misc=$this->misc . "<br>TiempoTranscurrido: $tiempoTranscurrido->d d $tiempoTranscurrido->h h $tiempoTranscurrido->i m ";

		$diferenciaHoraria=$this->dameDiferenciaHoraria2();

		//NOS ENCONTRAMOS DENTRO DE LOS 2 HUSOS, ACLIMATADOS AL LUGAR DE REFERENCIA
		if($diferenciaHoraria<=2)return "B";

		//primera columna
		if($tiempoTranscurrido->d < 2) return "B";

		//segunda columna
		if($tiempoTranscurrido->d >= 2 and $tiempoTranscurrido->d < 3){
			if($diferenciaHoraria<4){
				return "D";
			}else{
				return "X";
			}
		}

		//tercera columna
		if($tiempoTranscurrido->d >= 3 and $tiempoTranscurrido->d < 4){
			if($diferenciaHoraria<=6){
				return "D";
			}else{
				return "X";
			}
		}

		//cuarta columna
		if($tiempoTranscurrido->d >= 4 and $tiempoTranscurrido->d < 5){
			if($diferenciaHoraria>=12){
				return "X";
			}else{
				return "D";
			}
		}

		//quinta y ultima columna
		if($tiempoTranscurrido->d >= 5){
			return "D";
		}

	}

	/**
	 * FUNCION QUE DEVUELVA EL MAX FDP (SIN EXTENSIONES) CONFORME AL MO PARTE CAP 7
	 * DEVUELVE EL MAX FDP SIN EXTENSIONES DE NINGUN TIPO. Lo devuelve en minutos
	 */
	private function dameMaxFdp2(){

		//las horas de firma y desfirma estan en utc
		//shortcuts:

		$dtFirma=clone $this->fechaFirma;

		$estado_aclim=$this->dameEstadoAclimatacion();

		$time_zone=$this->dameTz($estado_aclim);

		if($this->arrVuelos!=null){

			$numero_sectores=count($this->arrVuelos);

		}else{

			$numero_sectores=1;
		}


		if($estado_aclim=="X"){

			$fdp=new Fdp("X", $numero_sectores, "00:01:00");

			$FDP= $fdp->fdp;

			$this->misc=$this->misc . "<br>EstadoAclim $estado_aclim (Desconocido) Sectores: $numero_sectores, HoraFirma No Relevante<br>MaxFPD: $FDP";

			return $fdp->maxFdp;

		}else{

			//paso la hora de firma a la que corresponda en local
			$dtFirma->setTimezone(new DateTimeZone($time_zone));

			$horaFirma=$dtFirma->format("H:i:s");
			$horaFirmaString=$dtFirma->format("H:i");

			$fdp=new Fdp($estado_aclim, $numero_sectores, $horaFirma);

			$FDP= $fdp->fdp;

			$this->misc=$this->misc . "<br>EstadoAclim $estado_aclim ($time_zone) Sectores: $numero_sectores, HoraFirma $horaFirmaString LT<br>MaxFPD: $FDP";

			return $fdp->maxFdp;

		}

	}

	/**
	 * funcion que devuelve el timezone para el calculo de la hora
	 * local en el calculo de act extraordinaria asi como la definicion del periodo wocl
	 * es llamadaa desde dame max FDP
	 */
	private function dameTz($estadoAclimatacion){

		$aptoSalida=Servicio::$aptoIniServicio;

		//caso de que sea el primer vuelo para el calculo
		if($aptoSalida==null) $aptoSalida=$this->piloto->aclimatadoInicial;

		//ahora miro en cuantos husos estoy
		$previousDepAp=new Aeropuerto($aptoSalida);

		if($previousDepAp->arrDatosAeropuerto==false){

			$previousDepAp=new Aeropuerto("MAD");
			$this->misc=$this->misc . "<br>Aeropuerto $aptoSalida no encontrado. Act Extra no fiable.";

		}

		$depAp=new Aeropuerto($this->aptIni);

		if($depAp->arrDatosAeropuerto==false){

			$depAp=new Aeropuerto("MAD");
			$this->misc=$this->misc . "<br>Aeropuerto $depAp no encontrado. Act Extra no fiable.";

		}

		if($estadoAclimatacion=="B"){

			return $previousDepAp->tz;

		}

		if($estadoAclimatacion=="D"){

			return $depAp->tz;

		}

	}


}

?>
