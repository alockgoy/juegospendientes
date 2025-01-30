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
        echo "<a href='../html/login.html'>Volver atrás</a><br/><br/>";
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

    // obtener el nombre del usuario de la sesión
    $nombre_usuario = $_SESSION['nombre_usuario'];

    //obtener el ID del juego desde la URL
    $id_juego = $_GET['id_juego'] ?? null;

    /* Comprobaciones */

    //obtener el ID del usuario a partir de su nombre
    try {
        $consultaUsuario = "SELECT id_usuario FROM Usuarios WHERE nombre_usuario = ?;";
        $stmtUsuario = $conectar->prepare($consultaUsuario);
        $stmtUsuario->bind_param("s", $nombre_usuario);
        $stmtUsuario->execute();
        $stmtUsuario->bind_result($id_usuario);
        $stmtUsuario->fetch();
        $stmtUsuario->close();

        // Si no se encuentra el usuario, redirigir al login
        if (!$id_usuario) {
            header("Location: ../html/login.html");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("Error obteniendo ID del usuario: " . $e->getMessage());
    }

    //comprobar que el id del juego está vinculado al ID del usuario
    try {
        $comprobarUsuario = "SELECT * FROM `Anade` WHERE id_usuario = ? AND id_juego = ?;";
        $prepararConsulta = $conectar->prepare($comprobarUsuario);
        $prepararConsulta->bind_param("ii", $id_usuario, $id_juego);
        $prepararConsulta->execute();
        
        $resultado = $prepararConsulta->get_result();
        if ($resultado->num_rows === 0) {
            header("Location: https://www.youtube.com/watch?v=dQw4w9WgXcQ");
            exit();
        }
    } catch (mysqli_sql_exception $e) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("Error verificando la propiedad del juego: " . $e->getMessage());
    }

    /* Fin de las comprobaciones, por ahora */

    if ($id_juego) {

        try {
            //consultar la ruta del póster
            $sqlSelect = "SELECT poster FROM Juegos WHERE id = ?";
            $stmtSelect = $conectar->prepare($sqlSelect);
            $stmtSelect->bind_param("i", $id_juego);
            $stmtSelect->execute();
            $stmtSelect->bind_result($poster);
            $stmtSelect->fetch();
            $stmtSelect->close();

            //ruta completa del archivo
            $rutaPoster = "../img/avatares_juegos/" . $poster;

            //intentar eliminar el archivo físico
            // Intentar eliminar el archivo
            if ($poster && file_exists($rutaPoster)) {
                if (!unlink($rutaPoster)) {
                    echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
                    die("No se pudo eliminar el archivo de imagen.");
                }
            }

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
                echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
                echo "Error al eliminar el juego: " . $stmt->error;
            }

            //cerrar la declaración
            $stmt->close();
        } catch (mysqli_sql_exception $e) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            //cerrar la conexión
            $conectar->close();
            die("Error borrando el juego: " . $e->getMessage());
        }


    } else {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        echo "<p><strong>Se ha producido un error</strong></p>";
    }

    //cerrar la conexión
    $conectar->close();
    ?>

</body>

</html>