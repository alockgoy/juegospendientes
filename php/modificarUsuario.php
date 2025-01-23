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

//comprobar que existe una sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['nombre_usuario'])) {
    header("Location: ../html/login.html");
    exit();
}

?>

<!--Código HTML -->
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar mis datos</title>
</head>

<body>
    <h2>Cambiar mis datos</h2>

    <!-- Formulario -->
    <form action="" method="post">
        <!--Campo para modificar el nombre de usuario -->
        <label for="newUsername">Nuevo nombre de usuario</label>
        <input type="text" name="newUsername" placeholder="Escribe tu nuevo nombre de usuario" /> <br /> <br />

        <!--Campo para modificar el correo del usuario -->
        <label for="newEmail">Nuevo correo: </label>
        <input type="email" name="newEmail" placeholder="Escribe otra dirección de correo" /> <br /> <br />

        <!--Campo para modificar la contraseña -->
        <label for="oldPassword">Tu actual contraseña: </label>
        <input type="password" name="oldPassword" placeholder="Tu contraseña actual" /> <br />
        <label for="newPassword">Nueva contraseña: </label>
        <input type="password" name="newPassword" placeholder="Escribe tu nueva contraseña" /> <br />
        <label for="newPasswordCheck">Repite la nueva contraseña: </label>
        <input type="password" name="newPasswordCheck" /> <br />

        <!--Botón para enviar el formulario-->
        <button type="submit">Modificar datos</button>

        <!--Enlace al inico de sesión-->
        <section>
            <a href="./principal.php">Volver atrás</a>
        </section>
    </form>
</body>

</html>

<?php

//obtener el nombre de usuario desde la URL
$nombre_usuario = $_GET['nombre_usuario'] ?? null;

if ($nombre_usuario) {

    if ($_SERVER['REQUEST_METHOD'] == "POST") {

        //valores del formulario
        $nuevoNombreUsuario = $_POST['newUsername'];
        $nuevoCorreoUsuario = $_POST['newEmail'];
        $claveActual = $_POST['oldPassword'];
        $nuevaClave = $_POST['newPassword'];
        $nuevaClaveComprobar = $_POST['newPasswordCheck'];

        /* Lo voy a hacer caso por caso porque vaya lío */

        //caso del nombre de usuario
        if (!empty($nuevoNombreUsuario)) {
            try {
                //si el nombre de usuario se repite, saltará la excepción
                $sqlActualizarNombre = "UPDATE Usuarios SET nombre_usuario = ? WHERE nombre_usuario = ?;";
                $prepararConsulta = $conectar->prepare($sqlActualizarNombre); //preparar la consulta
                $prepararConsulta->bind_param("ss", $nuevoNombreUsuario, $nombre_usuario); //blindar los parámetros
                $prepararConsulta->execute(); //ejecutar la consulta

            } catch (mysqli_sql_exception $e) {
                //cerrar la conexión
                $conectar->close();
                die("Error actualizando el nombre de usuario: " . $e->getMessage());
            }
        }

        //caso del correo electrónico
        if (!empty($nuevoCorreoUsuario)) {
            try {
                //si el correo se repite, saltará la excepción
                $sqlActualizarCorreo = "UPDATE Usuarios SET email = ? WHERE nombre_usuario = ?;";
                $prepararConsulta = $conectar->prepare($sqlActualizarCorreo); //preparar la consulta
                $prepararConsulta->bind_param("ss", $nuevoCorreoUsuario, $nombre_usuario); //blindar los parámetros
                $prepararConsulta->execute(); //ejecutar la consulta
            } catch (mysqli_sql_exception $e) {
                //cerrar la conexión
                $conectar->close();
                die("Error actualizando el correo electrónico: " . $e->getMessage());
            }
        }

        //caso de la contraseña, este va a ser divertido
        if (!empty($nuevaClave) && !empty($nuevaClaveComprobar)) {
            //obtener la antigua contraseña
            $sqlObtenerClaveAntigua = "SELECT contrasena FROM Usuarios WHERE contrasena = ?;";
            $prepararConsulta = $conectar->prepare($sqlObtenerClaveAntigua); //preparar la consulta
            $prepararConsulta->bind_param("s", $claveActual); //blindar los parámetros
            $prepararConsulta->execute(); //ejecutar la consulta

            //obtener el resultado
            $resultado = $prepararConsulta->get_result();

            //comprobar que las contraseñas coinciden
            if ($resultado->num_rows === 1) {
                //consulta para actualizar la contraseña
                $sqlActualizarClave = "UPDATE Usuarios SET contrasena = ? WHERE nombre_usuario = ?;";
                $prepararConsulta = $conectar->prepare($sqlActualizarClave); //preparar la consulta
                $prepararConsulta->bind_param("ss", $nuevaClave, $nombre_usuario); //blindar los parámetros
                $prepararConsulta->execute(); //ejecutar la consulta
            } else {
                throw new Exception("Has introducido mal tu antigua contraseña.");
            }

        } else {
            throw new Exception("Los campos de contraseña no pueden estar vacíos.");
        }


        $conectar->close();
        header("Location: ./logout.php");
    } else {
        echo "<p><strong>Se ha producido un error</strong></p>";
        exit();
    }

}
?>