<?php
//traer el archivo de conexión
include_once "./conectar.php";

//establecer la conexión
$conectar = getConexion();

//comprobar que se puede establecer la conexión
if (!$conectar) {
    die("Error en la conexión a la base de datos");
}

//empezar una sesión
session_start();

//comprobar que se ha pulsado el botón de envío del formulario de iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //asignar los valores introducidos a variables
    $nombreUsuario = $_POST['username'];
    $claveUsuario = $_POST['clave'];

    
}
?>