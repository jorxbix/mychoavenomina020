<?php

class Piloto{

	/**
	 * this->nivel Almacena todos los datos sobre el nivel del piloto en forma de objeto-array,
	 * por ejemplo para acceder al valor de h1 haremos lo siguiente: $this->nivel["h1"]
	 */

	public $nivel;

	public $base;

	public $flota;

	public $tipoDieta;

	public $tiempoFirma;

	public $tiempoDesfirma;

	public $porcentajeReduccion;

	public $diasCobro;

	public $aclimatadoInicial;

	public $sentenciaPerfiles=false;


/**
 *
 */
	public function __construct($piloto){

		$nivel=strtoupper(trim($piloto->nivel));

		if($piloto->tablas_sentencia==true) $this->sentenciaPerfiles=true;

		//miramos la fecha del informe para elegir tabla de niveles salariales
		$nombreTabla="anteriores";
		$mesInforme=$_SESSION['mesInforme'];
		$anoInforme=$_SESSION['anoInforme'];

		if($anoInforme==2022) $nombreTabla="2022"; 
		if ($anoInforme==2023 && $mesInforme<=4)  $nombreTabla="2022";

		if($anoInforme==2023 &&
			($mesInforme>4 && $mesInforme<7))  $nombreTabla="2023";

		if($anoInforme=2023 && $mesInforme>=7)  $nombreTabla="2023_4percent";
		if($anoInforme>=2024)  $nombreTabla="2023_4percent";

		$miNivelSalarial=new NivelSalarial($nivel,$nombreTabla);

		$this->nivel=$miNivelSalarial->arrDatosNivel;

		$this->base=$piloto->base;

		$this->flota=$piloto->flota;

		$this->tipoDieta=$piloto->dietas;

		$this->tiempoFirma=$piloto->tiempo_firma;

		$this->tiempoDesfirma=$piloto->tiempo_desfirma;

		$this->diasCobro=$piloto->dias_cobro;

		$this->porcentajeReduccion=$piloto->porcentaje_reduccion;

		//echo json_encode($piloto);
		//exit;
		$this->aclimatadoInicial=strtoupper($piloto->aclimatado);

		global $limiteH1;
		global $limiteH2;

		//al recibir la info sobre el numero de dias la uso para
		//prorratear las variables de vuelo

		if($this->diasCobro<30 && $this->diasCobro>0){

			$limiteH1=$this->diasCobro*55/30;

			$limiteH2=$this->diasCobro*75/30;

			$limiteActExtra=$this->diasCobro*160/30;

			return;

		}

		if($this->porcentajeReduccion<100 && $this->porcentajeReduccion>0){

			$limiteH1=$this->porcentajeReduccion/100*55;

			$limiteH2=$this->porcentajeReduccion/100*75;

			$limiteActExtra=$this->porcentajeReduccion/100*160;

		}


	}

}

?>
