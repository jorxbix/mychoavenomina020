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
    <title>Como funciona myChoaveNomina?</title>
</head>
<body>

<div>
    Te agradezco cualquier comentario constructivo a:
    <a href="mailto:mychoavenomina@gmail.com">mychoavenomina@gmail.com</a>
</div>
<br>
<br>
<div>
<video src="media/howto.mp4"  width="90%" controls></video>
</div>


</body>
</html>