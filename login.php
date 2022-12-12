<?php
require_once "api/funcionalidad.php";

if(isset($_POST['login']) && isset($_POST['pass'])){

    $unUsuario=new Usuario($_POST['login'],$_POST['pass']);

    if(isset($unUsuario->id)){

        $mensaje="";
        $unUsuario->doLogin();
        header('Location: index.php');

    }else{

        $mensaje= "Neng: USUARIO INVALIDO, intentalo de nuevo.";
    }

}else{
    //no han ingresado los datos
    $mensaje= "Inserta Login y Password.";

}



?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilo_login.css">
    <title>Login MyChoaveNomina</title>

</head>
<body>
<h1>Bienvenido a myChoaveNomina</h1>
<form id="formLogin" method="POST" action="login.php" >
    <label for="login">Login</label>
    <input type="text" id="login" name="login" ><br>
    <label for="pass">Password</label>
    <input type="password" id="pass" name="pass"><br>

    <input type="submit" id="btnEnviar">
    <input type="reset" id="btnReset">
</form>

<?php echo $mensaje; ?>
<br>
<br>
<br>
<br>
<br>

<p id="pieVersion">myChoaveNomina v0.20b 10/OCT/2022</p>
</body>
</html>