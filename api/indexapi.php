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
	$i=0;
	$servicioPendiente=false;
	$mismoServicio=new Servicio(null,null);

	foreach($datos->progra as $servicio){


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


		//2.NO ES UN SERVICIO DE VUELO (por lo tanto sera un servicio diferente)
		}else{
			//guardo en el contenedor solamente cuando empieza
			//un servicio nuevo y no guardo el primero
			//como coño voy a saber cunado es el ultimo servicio???
			if($servicioPendiente){
				//antes de guardar el servicio tenemos que hacer los calculos de actividad
				$mismoServicio->calculaActividad();

				array_push($contenedor,$mismoServicio);

				$servicioPendiente=false;

			}

			//VAMOS A VER SI EL SERVICIOO ES UNA IMAGINARIA
			if($servicio->tipo=="IM"){

				$miImaginaria=new Imaginaria($servicio,$miPiloto);

				array_push($contenedor,$miImaginaria);

			}else{

				$miServicio=new Servicio($servicio,$miPiloto);

				$miServicio->calculaActividad();

				array_push($contenedor,$miServicio);

			}

		}

	}

	//guardo en el contenedor solamente cuando empieza
	//un servicio nuevo y no guardo el primero
	//como coño voy a saber cunado es el ultimo servicio???
	if($servicioPendiente){

		$mismoServicio->calculaActividad();

		array_push($contenedor,$mismoServicio);

		$servicioPendiente=false;

	}

	echo json_encode($contenedor);

	exit;


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

	$contenedor=[];

	global $sonVuelos;

	$i=0;

	foreach($datos->prograMEM as $servicio){

		//1.ES UN SERVICIO DE VUELO
		if(in_array($servicio->tipo,$sonVuelos)){

			$miHoraMemorizada=new HoraMemorizada($servicio);

			array_push($contenedor,$miHoraMemorizada);

		//2.NO ES UN SERVICIO DE VUELO
		}else{

		}

		$i++;
	}

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


?>