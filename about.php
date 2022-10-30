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

            Aqui un archivo ZIP con el codigo en uso .... ver 0.20b (10/OCT/2022)

        </a>

        <ul>
                <li>Añadida funcionalidad para el cálculo de actividad extra. (Experimental). </li>
                <li>Ligera mejora en la parte visual y de presentación de resultados.</li>
            </ul>


    </div>

    <div>

        <a href="#divActExtra">

            Aquí una breve explicación de como funciona el código para el cálculo de la actividad Extra.

        </a>
        <p>
        Y de la interpretación que he hecho del convenio (Que puede no ser correcta ... Por eso explico como funciona y solicito
            vuestro feedback a <a href="mailto:mychoavenomina@gmail.com"><strong>mychoavenomina@gmail.com</strong></a>.
        </p>
    </div>

    <div id="divActExtra">
        <p>El cálculo de la actividad extra (y el cobro) se basa en las siguientes premisas: </p>
        <ul>
            <li>Que la actividad sea mayor que la actividad máxima sin extensiones de ningun tipo o ...</li>
            <li>Que la actividad acumulada sea mayor de 160h o prorrateo de ese límite.</li>
        </ul>
        <p>
            Así pues el programa calcula cual es el máximo de actividad normal, si esa actividad es superada en el servicio,
            toda la actividad es contabilizada como extra.
            El algoritmo para ese calculo de la actividad máxima normal sale de esta fuente:
            <a href="http://www.flightimelimits.com/en/eu-ops-2002/">www.flightimelimits.com</a>
            <p>Para el cálculo de las zonas horarias y estado de aclimatación el programa hace uso de una extensa tabla
                que ofrecen de manera gratuita en: <a href="https://openflights.org/data.html">https://openflights.org</a> y sin la cual los cálculos no
                serían posibles.
            </p>

            <p>Ejemplo 1: (nos centramos en el SEGUNDO servicio UIO-MAD).

                <p><code>
                CO	08/08/2022 23:45	MAD	09/08/2022 10:25	UIO	AEA039	CR	788<br>
                CO	10/08/2022 12:55	UIO	10/08/2022 13:45	GYE	AEA039	CR	789<br>
                CO	10/08/2022 16:15	GYE	11/08/2022 03:00	MAD	AEA040	CR	789<br>
                </code></p>

                <ul>
                    <li>Máximo fdp NORMAL = 13h.</li>
                    <li>Vuelo de dos sectores (sin reducción por número de sectores).</li>
                    <li>Hay 6 horas de diferencia, con lo cual hay que calcular a que TimeZone se tiene que referir el wocl.</li>
                    <li>El timeZone (al que se referencia el periodo wocl) es Europe/Madrid pq hace menos de 48h que saliste de Madrid.</li>
                    <li>Horas firma y desfirma en hora de Madrid: (UTC +2) firma: 10/8/2022@13:40 y desfirma: 11/8/2022@5:30. Periodo wocl
                     es entre: 02:00 hours and 05:59 hours:</li>
                     <li>Tiempo encrochateado (disculpar el palabrejo) entre las 0200am LT Madrid y las 0530am LT Madrid. Total 03h30m.</li>
                     <li>Reducción por TERMINAR en wocl: 50% del tiempo encrochateado con lo cual reducimos en 01h45m.</li>
                     <li>Límite Actividad normal = 13h-01h45m = <strong>11h15m</strong>.</li>
                     <li>El servicio tiene 015:50h de actividad que es mayor, con lo que te corresponde recibir act extra esas 015:50h.</li>

                </ul>
            </p>
            <p>Ejemplo 2: (nos centramos en el segundo servicio UIO-MAD). Es un servicio inventado, puede que no exista el perfil.
            <code>
                <p>
                CO 06/08/2022 23:45 MAD 07/08/2022 10:25 UIO AEA039 CR 788<br>
                CO 10/08/2022 12:55 UIO 10/08/2022 13:45 GYE AEA039 CR 789<br>
                CO 10/08/2022 13:45 GYE 10/08/2022 14:45 UIO AEA039 CR 789<br>
                CO 10/08/2022 16:15 UIO 11/08/2022 03:00 MAD AEA040 CR 789<br>
                </p>
            </code>
                <ul>
                    <li>Máximo fdp NORMAL = 13h.</li>
                    <li>Vuelo de 4 sectores (reducción de 30min).</li>
                    <li>Hay 6 horas de diferencia, con lo cual hay que calcular a que TimeZone se tiene que referir el wocl.</li>
                    <li>El timeZone (al que se referencia el periodo wocl) es America/Guayaquil pq hace mas de 48h que saliste de Madrid.</li>
                    <li>Horas firma y desfirma en hora de Guayaquil (UTC -5): firma: 10/8/2022@06:40 y desfirma: 10/8/2022@22:30. Periodo wocl
                     es entre: 02:00 hours and 05:59 hours: NO APLICA.</li>
                     <li>Tiempo encrochateado (disculpar el palabrejo) entre las 0200am LT Guayaquil y las 0530am LT Guayaquil. Total 0h.</li>
                     <li>Límite Actividad normal = 13h-30m = <strong>12h30m</strong>.</li>
                     <li>El servicio tiene 015:50h de actividad que es mayor, con lo que te corresponde recibir act extra esas 015:50h.</li>

                </ul>



            </p>


    </div>

    <a href="index.php" class="uk-button uk-button-primary">Volver</a>


</body>
</html>