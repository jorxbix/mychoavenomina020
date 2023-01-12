<?php

class NivelSalarial extends Conexion{

	public $codigo;
	public $arrDatosNivel;
	public $arrDatosDieta;



/**Funcion constructora, devuelve false si el codigo de dieta no existe
 * @param $codigo son las siglas enmayuscula de la dieta a crear
 */
	public function __construct($codigo, $tablasAntiguas){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$codigo=strtoupper($codigo);

		$this->arrDatosNivel=$this->devuelveDatosNivel($codigo, $tablasAntiguas);

		if(isset($this->arrDatosDieta['nivel']) && $this->arrDatosDieta['nivel']!=null && $this->arrDatosDieta['nivel']!=""){

			return true;

		}else{

			return false;

		}

	}

	/**
	 * funcion que devuelve un array con todos los datos del nivel
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function devuelveDatosNivel($codigo, $tablasAntiguas){

		if($tablasAntiguas==false){

			$consulta="SELECT * FROM niveles WHERE nivel='$codigo';";

		}else{

			$consulta="SELECT * FROM niveles_old WHERE nivel='$codigo';";

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
		$nivel = $resultado->fetch(PDO::FETCH_ASSOC);

		return $nivel;

	}

	public function imprimeJsonNivel(){

		global $arrDatosNivel;

        header('Content-type: application/json');

 		echo json_encode($arrDatosNivel);

	}

	public function getSelectNiveles(){

		$consulta="SELECT * FROM niveles;";

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		//$niveles = $resultado->fetchAll(PDO::FETCH_ASSOC);

		$cadena="";

		while($nivel=$resultado->fetch(PDO::FETCH_ASSOC)){

			$cadena=$cadena.
			'<option value="'.$nivel['nivel'].'">'.$nivel['nivel'].'</option>'
			;

		}

		return $cadena;

	}

}

?>
