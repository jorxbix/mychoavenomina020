<?php

$base = __DIR__;
require_once("funcionalidad.php");

//preparamos los datos para devolver en formato JSON
$datosacceso['nombre']="MyChoaveNomina API";
$datosacceso['tipoAcceso']=$_SERVER['REQUEST_METHOD'];
$datosacceso['URI']=$_SERVER['REQUEST_URI'];


//ens quedam la part de la URL que ens interessa
//array_shift() Quita el primer valor del array y lo devuelve, acortando el array un elemento y
//corriendo el array hacia abajo. Todas la claves del array numéricas serán modificadas para que
//empiece contando desde cero mientras que las claves literales no se verán afectadas.
$paths=explode("/",$_SERVER['REQUEST_URI']);
$resource = array_shift($paths);

while($resource!="api") {
	$resource=array_shift($paths);
}

//en este punto ya tenemos el tipo de recurso solicitado, dietas, perfiles, programacion etc
//para evitar el cache en el objeto XMLHttpRequest paso una variable random al final conseguir re
//resultados frescos en cada consulta. no he tenido mas cojones que hacer esta chapuza

$cadenaRecurso=array_shift($paths);
$arrRecurso=explode("?", $cadenaRecurso);
$datosacceso['tipoObjeto']=$arrRecurso[0];


// $datosacceso['tipoObjeto']=array_shift($paths);


if($datosacceso['tipoObjeto']=="dietas"){

	if($datosacceso['tipoAcceso']=="GET"){

		$codigoDieta=array_shift($paths);

		restGetDieta($codigoDieta, null);

	}else{ //solo se permite el metodo GET para dietas

		header('HTTP/1.1 405 Method Not allowed');
	}

}elseif($datosacceso['tipoObjeto']=="perfiles"){

	if($datosacceso['tipoAcceso']=="GET"){

		$tipo=array_shift($paths);

		$flota=array_shift($paths);

		$perfil=array_shift($paths);

		restGetPerfil($tipo,$flota,$perfil);

	}else{ //solo se permite el metodo get para perfiles

		header('HTTP/1.1 405 Method Not allowed');

	}

}elseif($datosacceso['tipoObjeto']=="aeropuertos"){

	if($datosacceso['tipoAcceso']=="GET"){

		$iata=array_shift($paths);

		restGetAeropuerto($iata);

	}else{ //no se permite otro metodo

		header('HTTP/1.1 405 Method Not allowed');

	}

}elseif($datosacceso['tipoObjeto']=="niveles"){

	if($datosacceso['tipoAcceso']=="GET"){

		$codigo=array_shift($paths);

		restGetNivel($codigo);

	}else{ //no se permite otro metodo

		header('HTTP/1.1 405 Method Not allowed');

	}

}elseif($datosacceso['tipoObjeto']=="programacion"){

	if($datosacceso['tipoAcceso']=="POST"){

		$txt= file_get_contents('php://input');

		$json_prog=json_decode($txt);

		restProcesaProg($json_prog);

	}else{ //no se permite otro metodo a parte del post para procesar programaciones

		header('HTTP/1.1 405 Method Not allowed');

	}


}else{ //si no pedimos una dieta, un nivel, un perfil o un aeropuerto ....

header('HTTP/1.1 404 Not Found');

}


/**
 * funciones que acceden a las clases y a la case de datos
 */

function restGetDieta($codigoDieta, $piloto){

	global $datosacceso;

	$res['datosacceso']=$datosacceso;
	//cream un objecte usuari a partir del seu id
	$unaDieta=new Dieta($codigoDieta, null);

	if($unaDieta){
		//la dieta existe
		$res['datosdieta']=$unaDieta->arrDatosDieta;
		$res['respuesta']['msg']="DIETA ENCONTRADA";
		header('HTTP/1.1 200 OK');

	}else{

		$res['datosdieta']="";
		$res['respuesta']="DIETA NO ENCONTRADA";
		header('HTTP/1.1 404 NOT FOUND');

	}

	header('Content-type: application/json');
	echo json_encode($res);

}

function restGetNivel($codigo){

	global $datosacceso;

	$res['datosacceso']=$datosacceso;

	$unNivel=new NivelSalarial($codigo,false);

	if($unNivel){
		//el nivel existe
		$res['datosnivel']=$unNivel->arrDatosNivel;
		$res['respuesta']['msg']="NIVEL ENCONTRADO";
		header('HTTP/1.1 200 OK');

	}else{

		$res['datosnivel']="";
		$res['respuesta']="NIVEL NO ENCONTRADO";
		header('HTTP/1.1 404 NOT FOUND');

	}

	header('Content-type: application/json');
	echo json_encode($res);

}

function restGetPerfil($tipo,$flota,$perfil){

	global $datosacceso;

	$res['datosacceso']=$datosacceso;

	$unPerfil=new Perfil($tipo,$flota,$perfil);

	// print_r($unPerfil);
	// exit;

	if($unPerfil->arrDatosPerfil){
		//el perfil existe
		$res['datosperfil']=$unPerfil->arrDatosPerfil;
		$res['respuesta']['msg']="PERFIL ENCONTRADO";
		header('HTTP/1.1 200 OK');

	}else{

		$res['datosperfil']="";
		$res['respuesta']="PERFIL NO ENONTRADO, ejemplo: /api/perfiles/R/B787/MADEZE";
		header('HTTP/1.1 404 NOT FOUND');

	}

	header('Content-type: application/json');
	echo json_encode($res);

}

function restGetAeropuerto($iata){

	global $datosacceso;

	$res['datosacceso']=$datosacceso;

	$unApt=new Aeropuerto($iata);

	if($unApt->arrDatosAeropuerto){
		//el aeropuerto exixte
		$res['datosaeropuerto']=$unApt->arrDatosAeropuerto;
		$res['respuesta']['msg']="AEROPUERTO ENCONTRADO";
		header('HTTP/1.1 200 OK');

	}else{

		$res['datosaeropuerto']="";
		$res['respuesta']="AEROPUERTO NO ENONTRADO";
		header('HTTP/1.1 404 NOT FOUND');

	}

	header('Content-type: application/json');
	echo json_encode($res);

}

function restProcesaProg($datos){

	//si no exite el objeto piloto es que solo recibo una progra para memorizar las horas
	if(isset($datos->prograMEM)){

		memorizaHoras($datos);

	}

	$contenedor=[];

	global $sonVuelos;

	$miPiloto=new Piloto($datos->piloto);

	$ultimoVueloProcesado=null;
	$ultimoSueloProcesado=null;

	$i=0;
	$servicioPendiente=false;

	$mismoServicio=new Servicio(null,null);

	foreach($datos->progra as $indice=>$servicio){

		//1.ES UN SERVICIO DE VUELO
		if(in_array($servicio->tipo,$sonVuelos)){

			$miVuelo=new Vuelo($servicio,$miPiloto);

			//1.A.el vuelo pertenece a un servicio diferente
			if(esServicioDiferente($ultimoVueloProcesado,$miVuelo)){
				//guardo en el contenedor solamente cuando empieza
				//un servicio nuevo y no guardo el primero
				//como coño voy a saber cunado es el ultimo servicio???
				if($servicioPendiente){
					//antes de guardar el servicio tenemos que hacer los calculos de actividad
					$mismoServicio->calculaActividad();

					array_push($contenedor,$mismoServicio);

					if(Servicio::$dietaPendiente){

						array_push($contenedor,dame_SA_dieta());

					}

					$servicioPendiente=false;

				}

				//pongo el contador de vuelos a cero
				$i=0;
				//creo el servicio que contendra el/los vuelos
				$miServicio=new Servicio($servicio,$miPiloto);
				//meto el vuelo creado dentro del servicio
				$miServicio->arrVuelos[$i]=$miVuelo;
				//guardo el servicio por si hay q meter mas vuelos
				$mismoServicio=$miServicio;
				//augmento el contador de vuelos
				$i++;

				// substituyo la propiedad misc para qu no coincida con ningun vuelo
				$mismoServicio->misc="Vuelo Unico,";

				$servicioPendiente=true;

			//1.B.el vuelo pertenece al servicio ya creado
			}else{

				//meto el vuelo creado dentro del servicio
				$mismoServicio->arrVuelos[$i]=$miVuelo;

				//augmento el contador de vuelos
				$i++;

				// substituyo la propiedad misc para qu no coincida con ningun vuelo
				$mismoServicio->misc="Multiples Vuelos,";
				// actualizo el apto fin y la hora que termina el servicio con las del ultimo vuelo
				$mismoServicio->aptFin=$miVuelo->aptFin;
				$mismoServicio->fechaFin=$miVuelo->fechaFin;

				$servicioPendiente=true;

			}
			//guardo los datos del ultimo vuelo procesado para compararlo
			//en el siguinete loop
			$ultimoVueloProcesado=$miVuelo;


		//2.NO ES UN SERVICIO DE VUELO (por lo tanto sera un servicio diferente, pues NOOOO)
		}else{

			//2.1 VAMOS A VER SI EL SERVICIOO ES UNA IMAGINARIA o una RESERVA (la im es un servicio en si)
			if($servicio->tipo=="IM"){

				//2.1.1 La imaginaria comprende dos dias (imaginarias de noche)...

				if(array_key_exists($indice+1, $datos->progra)){
					$siguienteServicio=$datos->progra[$indice+1]; //obtengo el siguinete servicio
				}else{
					$siguienteServicio=null;
				}


				if(esImaginariaDoble($servicio,$siguienteServicio)){

					$arrImaginarias=divideImaginarias($servicio);

					$miImaginaria=new Imaginaria($arrImaginarias[0],$miPiloto);

					array_push($contenedor,$miImaginaria);

					$miImaginaria=new Imaginaria($arrImaginarias[1],$miPiloto);

					array_push($contenedor,$miImaginaria);

					if(Servicio::$dietaPendiente) array_push($contenedor,dame_SA_dieta());

				//2.1.2 La imaginaria solo comprende un dia
				}else{

					$miImaginaria=new Imaginaria($servicio,$miPiloto);

					array_push($contenedor,$miImaginaria);

					if(Servicio::$dietaPendiente) array_push($contenedor,dame_SA_dieta());

				}

			}else if($servicio->tipo=="RV"){

				$miReserva=new Reserva($servicio,$miPiloto);

				array_push($contenedor,$miReserva);

				if(Servicio::$dietaPendiente) array_push($contenedor,dame_SA_dieta());

			//2.2 el servicio no es una imaginaria y puede contener varias lineas
			}else{

				$miSuelo=new Suelo($servicio,$miPiloto);

				//2.2.1 el servicio es diferente
				if(esServicioDiferente($ultimoSueloProcesado,$miSuelo)){

					//el servicio es diferente y debemos cerrar el anterior
					if($servicioPendiente){
						//antes de guardar el servicio tenemos que hacer los calculos de actividad
						$mismoServicio->calculaActividad();

						array_push($contenedor,$mismoServicio);

						if(Servicio::$dietaPendiente){

							array_push($contenedor,dame_SA_dieta());

						}

						$servicioPendiente=false;

					}

					//una vez guardaddo el servicio pendiente abro uno nuevo
					//pongo el contador de suelos a cero
					$i=0;
					//creo el servicio que contendra el/los vuelos
					$miServicio=new Servicio($servicio,$miPiloto);
					//meto el suelo creado dentro del servicio
					$miServicio->arrSuelos[$i]=$miSuelo;
					//guardo el servicio por si hay q meter mas vuelos
					$mismoServicio=$miServicio;
					//augmento el contador de suelos
					$i++;

					// substituyo la propiedad misc para qu no coincida con ningun vuelo
					$mismoServicio->misc="Suelo Unico,";

					$servicioPendiente=true;

				//2.2.2 suelo pertenece a un servicio ya creado
				}else{

					//meto el suelo creado dentro del servicio
					$mismoServicio->arrSuelos[$i]=$miSuelo;

					//augmento el contador de suelos
					$i++;

					// substituyo la propiedad misc para qu no coincida con ningun vuelo
					$mismoServicio->misc="Multiples Vuelos,";
					// actualizo el apto fin y la hora que termina el servicio con las del ultimo vuelo
					$mismoServicio->aptFin=$miSuelo->aptFin;
					$mismoServicio->fechaFin=$miSuelo->fechaFin;

					$servicioPendiente=true;

				}
				//guardo los datos del ultimo vuelo procesado para compararlo
				//en el siguinete loop
				$ultimoSueloProcesado=$miSuelo;

			}

		}

	}

	//guardo en el contenedor solamente cuando empieza
	//un servicio nuevo y no guardo el primero
	//como coño voy a saber cunado es el ultimo servicio???
	if($servicioPendiente){

		$mismoServicio->calculaActividad();

		array_push($contenedor,$mismoServicio);

		if(Servicio::$dietaPendiente){

			array_push($contenedor,dame_SA_dieta());

		}

		$servicioPendiente=false;

	}


	//EN ESTA PARTE VAMOS A BORRAR TODOS LOS SERVICIOS ESTUPIDOS QUE
	//EL SGT GENERA Y QUE NO SIRVEN PARA NADA
	$numServicios=count($contenedor);

	for($i=0 ; $i<$numServicios ; $i++){

		if(isset($contenedor[$i]->destruir) && $contenedor[$i]->destruir==true){

			array_splice($contenedor, $i, 1);
			//cada vez que quito un elemento tengo que disminuir el numero total de elementos
			$numServicios--;

		}
	}

	echo json_encode (ordenaResultados($contenedor));

	//echo json_encode ($contenedor);

}

function ordenaResultados($contenedor){

	do{

		$servicioAnterior=null;
		$operacionesRealizadas=0;

		foreach($contenedor as $i=>$servicio){

			if (!is_null($servicioAnterior)){

				if($servicio->fechaFin < $servicioAnterior->fechaIni){

					$contenedor[$i-1]=$servicio;
					$contenedor[$i]=$servicioAnterior;
					$operacionesRealizadas++;
					continue;

				}else{


				}

				if($servicio->eliminarSAdietaAnterior==true){

					array_splice($contenedor, $i-1, 1);

				}

			}

		$servicioAnterior=$servicio;

		}

	} while ($operacionesRealizadas>0);


	return $contenedor;

}

function dame_SA_dieta(){

	if(Servicio::$dietaPendiente){

		$servicio_SA_dieta=Servicio::$dietaPendiente;

		Servicio::$dietaPendiente=null;

		$servicio_SA_dieta->tipo="SA";

		//quito la primera dieta que ya estaba contada
		array_shift($servicio_SA_dieta->arrDietas);

		$servicio_SA_dieta->arrVuelos=null;

		$servicio_SA_dieta->misc="SA_Dieta";

		//ahora miramos que no corresponda a un dia que no es el mes del informe:
		
			global $mesInforme;

			//obtener la bse del pilto
			$BASE=$servicio_SA_dieta->piloto->base;
	
			$time_zone="Europe/Madrid";
	
			$laBase=new Aeropuerto($BASE);
	
			//si han metido un aeropuerto que no existe, le asignamos MAD
			if($laBase) $time_zone=$laBase->tz;

			$horaLLegada=clone $servicio_SA_dieta->fechaDesfirma;	
	
			$horaLLegada->setTimezone(new DateTimeZone($time_zone));
	
			$hora_LLegada=(int) $horaLLegada->format("H");
			$dia_LLegada=(int) $horaLLegada->format("d");
			$mes_LLegada= (int) $horaLLegada->format("m");

			if($mes_LLegada!=$mesInforme){

				$servicio_SA_dieta->fantasma=true;

				$servicio_SA_dieta->arrDietas[0]->diaDieta=$horaLLegada;

				$servicio_SA_dieta->misc=$servicio_SA_dieta->misc . " DIETA MES SIG ";


			} 


		////////////////////////////////////////////////////////////////////////////

		return $servicio_SA_dieta;

	}
}

/**
 * funcion que compara dos vuelos y determina si pertenecen a servicios
 * diferentes. Devuelve true si son servicios diferentes (han pasado mas
 * de las horas especificadas en funcionalidad.php)
 */
function esServicioDiferente($vueloA,$vueloB){

	//si es el primer vuelo que se procesa
	//se va a crear un servicio nuevo

	if($vueloA==null)return true;

	global $sonLibres;
	global $sonVuelos;
	global $sonServTierra;

	if(in_array($vueloA->tipo,$sonLibres)) return true;
	if(in_array($vueloB->tipo,$sonLibres)) return true;
	if(in_array($vueloA->tipo,$sonVuelos) && in_array($vueloB->tipo,$sonServTierra)) return true;
	if(in_array($vueloB->tipo,$sonVuelos) && in_array($vueloA->tipo,$sonServTierra)) return true;
	//el caso del SA es extraño pq es libre pero puedes cobrar dieta
	if($vueloA->tipo=="SA" || $vueloB->tipo=="SA") return true;

	global $tiempoEntreServicios;

	$difEntreVuelos=$vueloA->fechaFin->diff($vueloB->fechaIni);

	$horasEntreVuelos=$difEntreVuelos->d*24+$difEntreVuelos->h;

	if($horasEntreVuelos>$tiempoEntreServicios->h){

		return true;

	}else{

		return false;

	}

}


function memorizaHoras($datos){

	global $sonVuelos;

	global $mesInforme;

	global $anoInforme;

	$arrMeses=[];

	$arrAnos=[];

	$contenedor=[];

	$i=0;

	foreach($datos->prograMEM as $servicio){

		//meto en el array todos los meses que estan en la prog para determinar de que mes
		//se van a sacar los totales
		$arrFecha=explode("/", $servicio->fechaIni);
		array_push($arrMeses,$arrFecha[1]);
		//echo substr($arrFecha[2],0,4) . " ";
		array_push($arrAnos,substr($arrFecha[2],0,4));

		//1.ES UN SERVICIO DE VUELO
		if(in_array($servicio->tipo,$sonVuelos)){

			$miHoraMemorizada=new HoraMemorizada($servicio);

			array_push($contenedor,$miHoraMemorizada);

		//2.NO ES UN SERVICIO DE VUELO
		}else{

		}

		$i++;
	}

	//BUSCO EL MES MAS COMUN PARA DETERMINAR EL MES DEL INFORME
	$values = array_count_values($arrMeses);
	arsort($values);
	$popular = array_slice(array_keys($values), 0, 5, true);
	$_SESSION['mesInforme']=$popular[0];

	//BUSCO EL ANO MAS COMUN PARA DETERMINAR EL ANO DEL INFORME
	$values = array_count_values($arrAnos);
	arsort($values);
	$popular = array_slice(array_keys($values), 0, 5, true);
	$_SESSION['anoInforme']=$popular[0];

	//guardamos los vuelos programados en una variable session para poder acceder a
	//ellos en cualquine momento de la sesion del ususario. Se sobreescribiran cada
	//vez que pulsemos memorizar vuelos
	$objVuelosMemorizados=new stdClass();

	$_SESSION['vuelosProgramados']=$contenedor;

	$objVuelosMemorizados->vuelos=$contenedor;
	$objVuelosMemorizados->tipo="MEM";
	$objVuelosMemorizados->numVuelosMemorizados=count($contenedor);

	echo json_encode($objVuelosMemorizados);
	exit;

}

function esImaginariaDoble($fila,$siguienteFila){

	//echo json_encode($fila) . '-------------' . json_encode($siguienteFila);

	$fechaIni=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaIni);

	$fechaFin=DateTime::createFromFormat("d/m/Y G:i",$fila->fechaFin);

	if($siguienteFila==null){

		if($fechaIni==false || $fechaFin==false){

			$putoFallo=new Fallo($fila,"Alguna fecha suministrada en la Imaginaria es invalida");

		}

		$diaInicio=$fechaIni->format("d");
		$diaFin=$fechaFin->format("d");

		if($diaInicio!=$diaFin){

			return true;

		}else{

			return false;

		}

	}

	$fechaIniSiguiente=DateTime::createFromFormat("d/m/Y G:i",$siguienteFila->fechaIni);

	if($fechaIni==false || $fechaFin==false || $fechaIniSiguiente==false){

		$putoFallo=new Fallo($fila,"Alguna fecha suministrada en la Imaginaria es invalida");

	}

	$diaInicio=$fechaIni->format("d");
	$diaFin=$fechaFin->format("d");
	$diaInicioSiguiente=$fechaIniSiguiente->format("d");

	if($diaInicio!=$diaFin && $diaFin!=$diaInicioSiguiente){

		return true;

	}else{

		return false;

	}

}

function divideImaginarias($servicio){

	$arrImaginarias=[];

	$imaginaria1=clone($servicio);
	$imaginaria2=clone($servicio);

	$arrFecha= explode(" ", $imaginaria1->fechaIni);
	$arrFecha[1]="23:59";

	$imaginaria1->fechaFin=$arrFecha[0] . " " . $arrFecha[1] ;

	$arrFecha= explode(" ", $imaginaria2->fechaFin);
	$arrFecha[1]="00:00";

	$imaginaria2->fechaIni=$arrFecha[0] . " " . $arrFecha[1] ;

	$arrImaginarias[0]=$imaginaria1;

	$arrImaginarias[1]=$imaginaria2;

	return $arrImaginarias;

}


?>