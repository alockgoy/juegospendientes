<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/errores.css" />
    <title>Error</title>
</head>
<body>
    


<?php
//depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//comprobar que se ha pulsado el botón de envío del formulario de reportar un error
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //asignar los valores introducidos a variables
    $tipoError = $_POST['errorType'];
    $correoUsuario = $_POST['userEmail'];
    $errorDetallado = $_POST['errorDetails'];

    /* Comprobaciones*/

    //comprobar que no hay campos vacíos
    if (empty($tipoError) || empty($correoUsuario) || empty($errorDetallado)) {
        echo "<a href='../index.html'>Volver atrás</a><br/><br/>";
        die("<p class='error'>No puede haber campos vacíos</p>");
    }//comprobar que el correo cumple con la norma 
    elseif (!filter_var($correoUsuario, FILTER_VALIDATE_EMAIL)) {
        echo "<a href='../index.html'>Volver atrás</a><br/><br/>";
        die("<p class='error'>El correo introducido no es válido</p>");
    }//comprobar que la opción seleccionada es válida
    elseif ($tipoError != "other" && $tipoError != "website" && $tipoError != "account") {
        echo "<a href='../index.html'>Volver atrás</a><br/><br/>";
        die("<p class='error'>La opción introducida no es correcta.</p>");
    }

    /* Fin de comprobaciones (por ahora) */

    //Suponiendo que el anterior bloque funciona, mandar un correo con el reporte
    $destinatario = "sanchezjerezalonso@outlook.es"; //variable con el correo del destinatario
    $cabecera = "De: " . $correoUsuario; //variable con el correo del escritor

    try {
        mail($destinatario, $tipoError, $errorDetallado, $cabecera);
        header("Location: ../index.html");
    } catch (Exception $e) {
        echo "<p class='error'>Error al enviar el correo: " . $e->getMessage() . "</p>";
    }

}
?>

</body>
</html>