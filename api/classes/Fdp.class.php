<?php

class Fdp extends Conexion{

	//periodo max sin restricciones en formato "h:m:s"
	public $fdp;
	//periodo max sin extensiones en minutos
	public $maxFdp;
	public $consulta;

/**Funcion constructora, devuelve false si el codigo de perfil no existe
 * @param $nombre son las siglas del perfil enmayuscula
 */
	public function __construct($tipo,$numSectores,$hora_firma){

		//inicia la funcion constructora de conexion
		parent::__construct();

		if($tipo=="X") $this->dameFDPdesconocido($numSectores);

		if($tipo=="B" || $tipo=="D") $this->dameFDPaclimatdado($numSectores,$hora_firma);

	}

	private function dameFDPdesconocido($numSectores){

		$consulta="
		SELECT sect_$numSectores FROM fdp_desconocido;
		";

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			$this->fdp= "UNAPOLLA-X";
		}

		if($resultado->rowCount()!=1){

			$this->fdp= "UNA POLLA-XX";

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$fdp = $resultado->fetch(PDO::FETCH_NUM);

		$cadena=$fdp[0];

		$this->fdp= $cadena;

		$arr=explode(":", $cadena);

		$hh=$arr[0];
		$mm=$arr[1];

		$this->maxFdp=$hh*60 + $mm;

		$this->consulta=$consulta;

	}

	private function dameFDPaclimatdado($numSectores,$hora_firma){

		//SELECT sect_1 FROM `fdp_aclimatado` WHERE '05:45:00' >= hora_ini AND '05:45:00' <= hora_fin;
		$arrTiempo= explode(":",$hora_firma);
		$h=$arrTiempo[0];

		$consulta="";

		if($h>=17 || $h<5){

			$consulta="
			SELECT sect_$numSectores FROM fdp_aclimatado WHERE intervalo='17:00-04:59';
			";

		}else{

			$consulta="
			SELECT sect_$numSectores FROM fdp_aclimatado WHERE '$hora_firma' >= hora_ini AND '$hora_firma' <= hora_fin;
			";

		}



		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			$this->fdp= "POLLAS";
		}

		if($resultado->rowCount()!=1){

			$this->fdp= "POLLAS2";

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$fdp = $resultado->fetch(PDO::FETCH_NUM);

		$cadena=$fdp[0];

		$this->fdp= $cadena;

		$arr=explode(":", $cadena);

		$hh=$arr[0];
		$mm=$arr[1];

		$this->maxFdp=$hh*60 + $mm;

		$this->consulta=$consulta;

	}

}

?>
