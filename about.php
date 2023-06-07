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


    <link rel="stylesheet" href="css/uikit.min.css" />
    <link rel="stylesheet" href="css/estilo_sourcecode.css">


    <script src="js/uikit.min.js"></script>
    <script src="js/uikit-icons.min.js"></script>


    <title>myChoaveNomina About</title>
</head>
<body>

    <div>

        <a href="https://github.com/jorxbix/myChoaveNomina">

            Aqui el repositorio con el codigo en desarrollo ....

        </a>

    </div>

    <div>

        <a href="media/mychoavenomina010b.zip">

            Aqui un archivo ZIP con el codigo anterior .... ver 0.10b (01/OCT/2022)

        </a>


    </div>

    <div>

        <a href="media/mychoavenomina020b.zip">

            Aqui un archivo ZIP con el codigo anterior .... ver 0.20b (10/OCT/2022)

        </a>

        <ul>
                <li>Añadida funcionalidad para el cálculo de actividad extra. (Experimental). </li>
                <li>Ligera mejora en la parte visual y de presentación de resultados.</li>
            </ul>


    </div>

    <div>

        <a href="media/mychoavenomina030.zip">

            Aqui un archivo ZIP con el codigo en uso .... ver 0.30 (13/DEC/2022)

        </a>

        <ul>
                <li>Añadida funcionalidad para el cálculo de actividad extra, FINAL</li>
                <li>Corregidos fallos menores en todas las areas.</li>

        </ul>


    </div>

    <div>

        <a href="media/mychoavenomina040.zip">

            Aqui un archivo ZIP con el codigo en uso .... ver 0.40 (13/ENE/2023)

        </a>

        <ul>
                <li>Añadida funcionalidad para el cálculo de dietas.</li>
                <li>Añadida funcionalidad para crear totales conforme al mes del calculo.</li>
                <li>Error conocido con los calculos de act extra en destinos como MDE y PTY.</li>
                <li>Error conocido con el calculo de act nocturna y extra en servicios de tierra como el simulador.</li>
                <li>Corregidos fallos menores en todas las areas.</li>

        </ul>


    </div>

    <div>

        <a href="media/mychoavenomina050.zip">

            Aqui un archivo ZIP con el codigo en uso .... ver 0.50 (01/JUN/2023)

        </a>

        <ul>
                <li>Añadida funcionalidad para el cálculo de Reservas.</li>
                <li>Error conocido con el calculo de imaginarias q comprenden 2 dias.</li>
                <li>Error de representacion conocido con los calculos de dias que comprenden actividad en tierra y actividad en vuelo.</li>
                <li>Corregidos fallos en el orden de la representacion de servicios.</li>
                <li>Corregidos fallos menores en todas las areas.</li>

        </ul>


    </div>

    <div id="divActExtra">

        <a href="#divActExtra">

            Aquí una breve explicación de como funciona el código para el cálculo de la actividad Extra.

        </a>
        <p>
        Finalmente todos los calculos para determinar el cobro de la act extra se hacen conforme al MO.A cap 7.
        </p>
        <p>El cálculo de la actividad extra (y el cobro) se basa en las siguientes premisas: </p>
        <ul>
            <li>Que la actividad sea mayor que la actividad máxima sin extensiones de ningun tipo o ...</li>
            <li>Que la actividad acumulada sea mayor de 160h o prorrateo de ese límite.</li>
        </ul>
    </div>



    <a href="index.php" class="uk-button uk-button-primary">Volver</a>


</body>
</html>