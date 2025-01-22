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

                //crear una cookie de sesión que caduque en una hora
                setcookie(session_name(), session_id(), time() + 3600, "/");

                //cerrar la conexión
                $conectar->close();

                //redirigir al usuario
                header("Location: ./principal.php");

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