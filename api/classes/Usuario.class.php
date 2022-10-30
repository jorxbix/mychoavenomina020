<?php

class Usuario extends Conexion{

	public $id;

	public $log;

	public $pass;

	public $passHash;

	public $email;

	public $rol;

	public $nombre;

/**Funcion constructora, devuelve false si el codigo de perfil no existe
 * @param $nombre son las siglas del perfil enmayuscula
 */
	public function __construct($login,$pass){

		//inicia la funcion constructora de conexion
		parent::__construct();

		$this->arrDatosUsr=$this->getUsrData($login,$pass);

		if(isset($this->arrDatosUsr['id']) && $this->arrDatosUsr['id']!=null && $this->arrDatosUsr['id']!=""){

			$this->id=$this->arrDatosUsr['id'];
			$this->log=$this->arrDatosUsr['log'];

			$this->pass=$pass;
			$this->passHash=$this->arrDatosUsr['pass'];

			$this->email=$this->arrDatosUsr['email'];

			$this->rol=$this->arrDatosUsr['rol'];

			$this->nombre=$this->arrDatosUsr['nombre'];

		}else{

			return false;

		}

		return true;

	}

	public function doLogin(){

		$_SESSION['usrId']=$this->id;
		$_SESSION['usrLog']=$this->log;
		$_SESSION['usrEmail']=$this->email;
		$_SESSION['usrNombre']=$this->nombre;

		$this->creaLog();

	}

	private function creaLog(){

		$ip=$_SERVER['REMOTE_ADDR'];

		$consulta=
		"INSERT INTO log_accesos (id_acceso, fecha, ip, nombre_usuario, server_usuario) VALUES (NULL, NOW(), '$ip', '$this->nombre', CURRENT_USER())";

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;

		}
		return true;

	}

	/**
	 * funcion que devuelve un array con todos los datos del perfil
	 * si no la encuentra en la bbdd devuelve false
	 */
	public function getUsrData($login,$pass){

		$consulta="
		SELECT * FROM usuarios WHERE log='$login' AND pass=sha2('$pass',256);
		";

		try{

			$resultado=$this->conexion->query($consulta);

		}catch(Exception $e){

			return false;

		}

		if($resultado->rowCount()!=1){

			return false;

		}

		//PDO::FETCH_ASSOC solo devuelve un array con campos asociativos y no con indices numericos
		$usuario = $resultado->fetch(PDO::FETCH_ASSOC);

		return $usuario;

	}



}

?>
