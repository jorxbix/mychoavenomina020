<?php

class Perfil extends Conexion{

	public $id;
	public $codigo_completo;

	public $dep_apt;
	public $arr_apt;

	public $tipo; //regular o charter

	public $tiempo_perfil;

	public $id_flota;
	public $codigo_flota;
	public $nombre_flota;

	public $tiempo_firma;
	public $tiempo_desfirma;

	public $arrDatosPerfil;


/**Funcion constructora, devuelve false si el codigo de perfil no existe
 * @param $nombre son las siglas del perfil enmayuscula
 */
	public function __construct($tipo,$flota,$nombre){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$tipo=strtoupper($tipo);
		$flota=strtoupper($flota);
		$nombre=strtoupper($nombre);

		$this->arrDatosPerfil=$this->devuelveDatosPerfil($tipo,$flota,$nombre);

		if(isset($this->arrDatosPerfil['id']) && $this->arrDatosPerfil['id']!=null && $this->arrDatosPerfil['id']!=""){

			$this->id=$this->arrDatosPerfil['id'];
			$this->codigo_completo=$this->arrDatosPerfil['codigo_completo'];

			$this->dep_apt=$this->arrDatosPerfil['dep_apt'];
			$this->arr_apt=$this->arrDatosPerfil['arr_apt'];

			$this->tipo=$this->arrDatosPerfil['tipo'];

			$this->tiempo_perfil=$this->arrDatosPerfil['tiempo_perfil'];

			$this->id_flota=$this->arrDatosPerfil['id_flota'];
			$this->codigo_flota=$this->arrDatosPerfil['codigo_flota'];
			$this->nombre_flota=$this->arrDatosPerfil['nombre_flota'];

			$this->tiempo_firma=$this->arrDatosPerfil['tiempo_firma'];
			$this->tiempo_desfirma=$this->arrDatosPerfil['tiempo_desfirma'];


		}else{

			return false;

		}

		return true;

	}

	/**
	 * funcion que devuelve un array con todos los datos del perfil
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosPerfil($tipo,$flota,$nombre){

		$consulta="
		SELECT P.*,F.codigo as codigo_flota,F.nombre as nombre_flota,F.tiempo_firma,F.tiempo_desfirma
		FROM perfiles P JOIN flotas F ON P.id_flota=F.id
		WHERE P.tipo='$tipo' AND F.codigo='$flota' AND P.codigo_completo='$nombre';
		";

		// echo $consulta;
		// exit;

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;
		}

		if($resultado->rowCount()<=0){

			return false;

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$perfil = $resultado->fetch(PDO::FETCH_ASSOC);

		return $perfil;

	}

	public function imprimeJsonPerfil(){

        header('Content-type: application/json');

 		echo json_encode($this->arrDatosPerfil);

	}

}

?>
