<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/errores.css">
    <title></title>
</head>
<body>
<?php
//traer el archivo de conexión
include_once "./conectar.php";

//establecer la conexión
$conectar = getConexion();

//comprobar que se puede establecer la conexión
if (!$conectar) {
    echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
    die("Error en la conexión a la base de datos");
}

//empezar una sesión
session_start();

//comprobar que se ha pulsado el botón de envío del formulario de crear cuenta
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //asignar los valores introducidos a variables
    $nombreUsuario = $_POST['username'];
    $correoUsuario = $_POST['correo'];
    $claveUsuario = $_POST['clave'];

    /* Comprobaciones */

    //comprobar que el nombre de usuario no se repite
    $consultaBuscaUsuario = "SELECT nombre_usuario FROM Usuarios WHERE nombre_usuario = ?";
    $prepararConsulta = $conectar->prepare($consultaBuscaUsuario); //preparar la consulta
    $prepararConsulta->bind_param("s", $nombreUsuario); //blindar el parámetro
    $prepararConsulta->execute(); //ejecutar la consulta
    $resultado = $prepararConsulta->get_result(); //obtener el resultado
    if ($resultado->num_rows > 0) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        die("El nombre de usuario ya existe.");
    }

    //comprobar que el correo electrónico no se repite
    $consultaBuscaCorreo = "SELECT email FROM Usuarios WHERE email = ?;";
    $prepararConsulta = $conectar->prepare($consultaBuscaCorreo); //preparar la consulta
    $prepararConsulta->bind_param("s", $correoUsuario); //blindar el parámetro
    $prepararConsulta->execute(); //ejecutar la consulta
    $resultado = $prepararConsulta->get_result(); //obtener el resultado
    if ($resultado->num_rows > 0) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        die("El correo introducido ya existe.");
    }

    //comprobar que no se ha mandado el formulario con algún campo vacío
    if (empty($nombreUsuario) || empty($correoUsuario) || empty($claveUsuario)) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        die("No puede haber campos vacíos.");
        //comprobar también que el nombre de usuario "no es una cosa rara"
    } elseif (!preg_match("/^[a-zA-Z0-9]+$/", $nombreUsuario)) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        die("El nombre de usuario solo puede contener letras y números.");
    } elseif (!filter_var($correoUsuario, FILTER_VALIDATE_EMAIL)) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        die("El correo electrónico no es válido.");
    }

    /* fin comprobaciones (por ahora) */

    //encriptar la contraseña
    $salt = rand(-1000000, 1000000);
    $hashPassword = hash('sha256', $claveUsuario . $salt);

    //insertar al nuevo usuario en la base de datos
    try {
        $consultaInsertarUsuario = "INSERT INTO Usuarios (nombre_usuario, email, salt, contrasena) VALUES (?,?,?,?);";
        $prepararConsultaInsercion = $conectar->prepare($consultaInsertarUsuario); //preparar la sentencia
        $prepararConsultaInsercion->bind_param("ssis", $nombreUsuario, $correoUsuario, $salt ,$hashPassword); //blindar los parámetros
        $prepararConsultaInsercion->execute(); //ejecutar la consulta
        $_SESSION['nombre_usuario'] = $nombreUsuario; //guardar la información de la sesión
        //cerrar la conexión
        $conectar->close();
        header("Location: ./principal.php");
    } catch (mysqli_sql_exception $e) {
        echo "<a href='../html/singup.html'>Volver atrás</a><br/><br/>";
        //cerrar la conexión
        $conectar->close();
        die("Error creando al usuario: " . $e->getMessage());
    }
}
?>
    
</body>
</html>