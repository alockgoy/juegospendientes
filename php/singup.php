<?php
//traer el archivo de conexión
include_once "./conectar.php";

//establecer la conexión
$conectar = getConexion();

//comprobar que se puede establecer la conexión
if (!$conectar) {
    die("Error en la conexión a la base de datos");
}

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
        die("El nombre de usuario ya existe.");
    }

    //comprobar que no se ha mandado el formulario con algún campo vacío
    if (empty($nombreUsuario) || empty($correoUsuario) || empty($claveUsuario)) {
        die("No puede haber campos vacíos.");
        //comprobar también que el nombre de usuario "no es una cosa rara"
    } elseif (!preg_match("/^[a-zA-Z0-9]+$/", $nombreUsuario)) {
        die("El nombre de usuario solo puede contener letras y números.");
    }

    /* fin comprobaciones (por ahora) */

    //encriptar la contraseña
    $salt = rand(-1000000, 1000000);
    $hashPassword = hash('sha256', $claveUsuario . $salt);

    //insertar al nuevo usuario en la base de datos
    try {
        $consultaInsertarUsuario = "INSERT INTO Usuarios (nombre_usuario, email, contrasena) VALUES (?,?,?);";
        $prepararConsultaInsercion = $conectar->prepare($consultaInsertarUsuario); //preparar la sentencia
        $prepararConsultaInsercion->bind_param("sss", $nombreUsuario, $correoUsuario, $hashPassword); //blindar los parámetros
        $prepararConsultaInsercion->execute(); //ejecutar la consulta
        header("../html/login.html");
    } catch (mysqli_sql_exception $e) {
        die("Error creando al usuario: " . $e->getMessage());
    }
}
?>