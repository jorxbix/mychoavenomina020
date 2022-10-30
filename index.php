<?php
require_once "api/funcionalidad.php";

if(checkSession()==false){

    header('Location: login.php');
    die();

}

function checkSession(){

    if(isset($_SESSION['usrId'])){
        return true;
    }else{
        return false;
    }

}


?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="cache-control" content="max-age=0">
    <meta http-equiv="cache-control" content="no-cache">
    <meta http-equiv="expires" content="-1">
    <meta http-equiv="expires" content="Tue, 01 Jan 1980 11:00:00 GMT">
    <meta http-equiv="pragma" content="no-cache">

    <link rel="stylesheet" href="css/uikit.min.css" />
    <link rel="stylesheet" href="css/estilo_general.css">

    <script type="text/javascript" src="js/principal.js"></script>
    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>

    <title>Home MyChoaveNomina</title>
</head>
<body>

<!-- This is the modal with the default close button -->
<div id="advertencia" uk-modal>
    <div class="uk-modal-dialog uk-modal-body">
        <button class="uk-modal-close-default" type="button" uk-close></button>
        <h2 class="uk-modal-title">Cálculo Actividad Extra:</h2>
        <p>El cálculo de la actividad extra está en periodo de pruebas. Si encuentras cualquier discrepancia, porfavor mándame un email. Si pulsas el símbolo '+' que
            sale al lado del servicio se muestra más información sobre como se han hecho los cálculos. Puedes leer más sobre este tema en la sección 'About'.
        </p>
    </div>
</div>

<div id="divSecciones">
   <a href="howto.php">¿HowTo?</a>   |   <a href="about.php">About</a>
</div>

<div id="divPreparacion">


<form action="receptor.php" name="configurador" id="formConfigurador" method="post" autocomplete="on"  >

<label for="nivel">Nivel Salarial</label>
<select name="nivel" id="nivel" required>
<option value="">Selecciona Nivel</option>
    <?php
    $algo= new NivelSalarial("",$false);
    echo $algo->getSelectNiveles();
    ?>
</select>



<label for="dietas">Dietas</label>
<select name="dietas" id="dietas">
<option value="PILOTO1">PILOTO1</option>
<option selected="selected" value="PILOTO2">PILOTO2</option>
</select>



<label for="base">Base</label>
<select name="base" id="base">
<option value="PMI">PMI</option>
<option selected="selected" value="MAD">MAD</option>
<option value="BCN">BCN</option>
<option value="AGP">AGP</option>
<option value="TFN">TFN</option>
<option value="LPA">LPA</option>
</select>



<label for="flota">Flota</label>
<select name="flota" id="flota">
<option value="A330">A330</option>
<option selected="selected" value="B787">B787</option>
<option value="B737">B737</option>
<option value="OTHR">OTHR</option>
</select>


<label for="dias_cobro">Dias cobro</label>
<input type="number" step="any" min="1" max="30" name="dias_cobro" id="dias_cobro" value="30" required> </td>

<label for="porcentaje_reduccion">Porcentaje Reduccion</label>
<input type="number" step="any" min="0" max="100" name="porcentaje_reduccion" id="porcentaje_reduccion" value="100" required> </td>

<p>

<input type="checkbox" id="tablas_antiguas" name="tablas_antiguas" value="true">
<label id="lbl_tablas" for="tablas_antiguas">Calculos anteriores a 2022</label>

</p>



<label for="tiempo_firma">Tiempo Firma</label>
<input type="number" min="1" max="120" name="tiempo_firma" id="tiempo_firma" value="90" required> </td>

<label for="tiempo_desfirma">Tiempo DesFirma</label>
<input type="number" min="1" max="120" name="tiempo_desfirma" id="tiempo_desfirma" value="30" required> </td>

<label for="aclimatado">Apt Aclimatado (Se usa para el calculo de la act Extra en la primera linea de la programacion.)
</label>
<input type="text" maxlength="3" name="aclimatado" id="aclimatado" value="MAD" required> </td>

<label for="progra">Copia aqui tu Programacion</label>
<textarea rows="40" cols="100" name="progra" id="progra"></textarea>


<input type="submit" id="btnEnviar" class="boton" name="enviando" value="Generar Informe">

</form>
<button id="btnMemoProg" class="boton" name="btnMemoProg">Memorizar Horas Programadas</button>
</div>

<div id="divResultados">



</div>

</body>
</html>