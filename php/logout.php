<?php
//iniciar la sesión
session_start();

//destruir todas las variables de sesión
$_SESSION = array();

//destruir la sesión
session_destroy();

//redirigir al usuario a la página de inicio de sesión
header("Location: ../index.html");
exit();
?>