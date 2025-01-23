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

//obtener el nombre de usuario desde la URL
$nombre_usuario = $_GET['nombre_usuario'] ?? null;

if ($nombre_usuario) {
    
    /* Esto es un poco más complejo
    Hay que eliminar los juegos añadidos por el usuario
        Eso incluye los registros de la tabla 'Anade' y la tabla 'Juegos'
    Luego de eso ya sí se puede eliminar el usuario
    */

    try {
        //eliminar los registros de la tabla 'Anade' asociados con el usuario
        $sqlBorrarAnade = "DELETE FROM Anade WHERE nombre_usuario = ?";
        $stmt = $conectar->prepare($sqlBorrarAnade);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $stmt->close();

        //eliminar los juegos
        $sqlBorrarJuegos = "DELETE FROM Juegos WHERE id NOT IN (SELECT id_juego FROM Anade)";
        $stmt = $conectar->prepare($sqlBorrarJuegos);
        $stmt->execute();
        $stmt->close();

        //eliminar el usuario
        $sqlBorrarUsuario = "DELETE FROM Usuarios WHERE nombre_usuario = ?";
        $stmt = $conectar->prepare($sqlBorrarUsuario);
        $stmt->bind_param("s", $nombre_usuario);
        $stmt->execute();
        $stmt->close();

        //redirigir al index
        header("Location: ../index.html");
        exit();
    } catch (mysqli_sql_exception $e) {
        //cerrar la conexión
        $conectar->close();
        die("Error borrando el usuario: " . $e->getMessage());
    }


} else {
    echo "<p><strong>Se ha producido un error</strong></p>";
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