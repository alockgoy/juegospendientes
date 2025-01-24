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

    //obtener el nombre de usuario desde la URL
    $nombre_usuario = $_GET['nombre_usuario'] ?? null;

    if ($nombre_usuario) {

        /* Esto es un poco más complejo
        Hay que eliminar los juegos añadidos por el usuario
            Eso incluye los registros de la tabla 'Anade' y la tabla 'Juegos'
        Luego de eso ya sí se puede eliminar el usuario
        */

        try {
            //obtener el id del usuario
            $consultaObtenerIdUsuario = "SELECT id_usuario FROM Usuarios WHERE nombre_usuario = ?;";
            $prepararConsultaUsuario = $conectar->prepare($consultaObtenerIdUsuario);
            $prepararConsultaUsuario->bind_param("s", $_SESSION['nombre_usuario']);
            $prepararConsultaUsuario->execute();
            $resultado = $prepararConsultaUsuario->get_result();
            $idUsuario = $resultado->fetch_assoc()['id_usuario'];

            //obtener los pósters de los juegos asociados al usuario
            $consultaPosters = "
            SELECT j.poster 
            FROM Juegos j
            INNER JOIN Anade a ON j.id = a.id_juego
            WHERE a.id_usuario = ?;";
            $stmtPosters = $conectar->prepare($consultaPosters);
            $stmtPosters->bind_param("i", $idUsuario);
            $stmtPosters->execute();
            $resultPosters = $stmtPosters->get_result();

            //eliminar los archivos de los pósters
            while ($row = $resultPosters->fetch_assoc()) {
                $rutaPoster = "../img/avatares_juegos/" . $row['poster'];
                if (file_exists($rutaPoster)) {
                    unlink($rutaPoster);
                }
            }
            $stmtPosters->close();


            //eliminar los registros de la tabla 'Anade' asociados con el usuario
            $sqlBorrarAnade = "DELETE FROM Anade WHERE id_usuario = ?";
            $stmt = $conectar->prepare($sqlBorrarAnade);
            $stmt->bind_param("i", $idUsuario);
            $stmt->execute();
            $stmt->close();

            /*eliminar los juegos (en principio esta consulta ya no es necesaria)
            $sqlBorrarJuegos = "DELETE FROM Juegos WHERE id NOT IN (SELECT id_juego FROM Anade)";
            $stmt = $conectar->prepare($sqlBorrarJuegos);
            $stmt->execute();
            $stmt->close();*/

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
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            //cerrar la conexión
            $conectar->close();
            die("Error borrando el usuario: " . $e->getMessage());
        }


    } else {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        echo "<p><strong>Se ha producido un error</strong></p>";
    }

    ?>

</body>

</html>