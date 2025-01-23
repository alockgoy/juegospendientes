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

//obtener el ID del juego desde la URL
$id_juego = $_GET['id_juego'] ?? null;

if ($id_juego) {

    try {
        //consulta para eliminar el juego
        $sql = "DELETE FROM Juegos WHERE id = ?";
        $stmt = $conectar->prepare($sql); //preparar la consulta
        $stmt->bind_param("i", $id_juego); //blindar los parámetros

        //ejecutar la consulta
        if ($stmt->execute()) {
            //redirigir a la página principal
            header("Location: ./principal.php");
            exit();
        } else {
            echo "Error al eliminar el juego: " . $stmt->error;
        }

        //cerrar la declaración
        $stmt->close();
    } catch (mysqli_sql_exception $e) {
        //cerrar la conexión
        $conectar->close();
        die("Error borrando el juego: " . $e->getMessage());
    }


} else {
    echo "<p><strong>Se ha producido un error</strong></p>";
}

//cerrar la conexión
$conectar->close();
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