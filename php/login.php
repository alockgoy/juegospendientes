<?php
//depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

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

//si existe una cookie de sesión, redirigir a principal
if (isset($_SESSION['nombre_usuario'])) {
    header("Location: ./principal.php");
    exit();
}
if (isset($_COOKIE['usuario_logueado'])) {
    // Restaurar sesión desde la cookie
    $_SESSION['nombre_usuario'] = $_COOKIE['usuario_logueado'];
    header("Location: ./principal.php");
    exit();
}


//comprobar que se ha pulsado el botón de envío del formulario de iniciar sesión
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //asignar los valores introducidos a variables
    $nombreUsuario = $_POST['username'];
    $claveUsuario = $_POST['clave'];

    //buscar al usuario
    $consultaBuscarUsuario = "SELECT * FROM Usuarios WHERE nombre_usuario = ?";

    try {
        //preparar la consulta
        $prepararConsulta = $conectar->prepare($consultaBuscarUsuario);
        $prepararConsulta->bind_param("s", $nombreUsuario); //blindar la consulta
        $prepararConsulta->execute(); //ejecutar la consulta

        //obtener el resultado
        $resultado = $prepararConsulta->get_result();

        //comprobar que el usuario existe
        if ($resultado->num_rows > 0) {

            $usuario = $resultado->fetch_assoc();

            //comprobar la contraseña
            $hashedPassword = hash('sha256', $claveUsuario . $usuario['salt']);

            //comprobar que la contraseña es correcta
            if ($hashedPassword === $usuario['contrasena']) {

                //guardar la información del usuario en la sesión
                $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

                setcookie('usuario_logueado', $usuario['nombre_usuario'], time() + 3600, "/");

                //cerrar la conexión
                $conectar->close();

                //redirigir al usuario
                header("Location: ./principal.php");
                exit();
            } else{
                echo "<p>Error: Usuario o contraseña incorrectos.</p>";
            }
        } else{
            echo "<p>Error: Usuario o contraseña incorrectos.</p>";
        }

    } catch (mysqli_sql_exception $e) {
        //cerrar la conexión
        $conectar->close();
        die("Error al iniciar sesión: " . $e->getMessage());
    }


}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/errores.css">
    <title></title>
</head>
<body>
    
</body>
</html>