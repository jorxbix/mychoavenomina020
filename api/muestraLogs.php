<?php
require_once "classes/Conexion.class.php";
$con=new Conexion();
$sql="SELECT * FROM log_accesos ORDER BY fecha DESC LIMIT 100;";
$resultado=$con->query($sql);
$registros=$resultado->fetchAll(PDO::FETCH_ASSOC);
// echo json_encode($registros);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>MuestraLogs</title>
    <style>
        table,th, td{

            border: 1px solid black;
            border-collapse: collapse;

        }
        table{
            width: 50%;
        }
        th{
            background-color: lightgrey;
        }
    </style>


</head>
<body>
<table>
        <tr>
            <th>fecha</th><th>id_acceso</th><th>ip</th>
        </tr>
        <?php
        foreach($registros as $reg){
            echo "<tr><td>{$reg['fecha']}</td><td>{$reg['id_acceso']}</td><td>{$reg['ip']}</td></tr>";
        }
        ?>
    </table>

</body>
</html>