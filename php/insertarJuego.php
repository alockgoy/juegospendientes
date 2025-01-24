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

//comprobar que se ha pulsado el botón de envío del formulario de añadir un juego
if ($_SERVER['REQUEST_METHOD'] == "POST") {
    //asignar los valores introducidos a variables
    $nombreJuego = $_POST['nombreJuego'];
    $puntajeMetacritic = $_POST['puntajeMetacritic'];
    $longitudJuego = $_POST['duracionHoras'];

    //procesar el poster
    if (isset($_FILES['poster']) && $_FILES['poster']['error'] == UPLOAD_ERR_OK) {
        $poster = $_FILES['poster']['name'];
        $poster_tmp = $_FILES['poster']['tmp_name'];
        $directorioPoster = '../img/avatares_juegos/';
        if (!is_dir($directorioPoster)) {
            mkdir($directorioPoster, 0755, true);
        }

        //crear un nombre único para el poster
        $nombreUnicoArchivo = uniqid("poster_") . "_" . basename($_FILES['poster']['name']);
        $rutaPoster = $directorioPoster . $nombreUnicoArchivo;

        // mover el archivo al directorio de poster
        if (!move_uploaded_file($poster_tmp, $rutaPoster)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            echo "<p>Error al subir el poster.</p>";
            exit();
        }

        // ruta final para guardar en la base de datos
        $poster = $nombreUnicoArchivo;
    } else {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        $error = $_FILES['poster']['error'];
        echo "<p>Error: No se ha subido ningún archivo o ha ocurrido un error al subir el archivo. Código de error: $error</p>";
        exit();
    }

    /* Comprobaciones */

    //comprobar que no se ha mandado el formulario con algún campo vacío
    if (empty($nombreJuego) || empty($puntajeMetacritic) || empty($longitudJuego) || empty($poster)) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("No puede haber campos vacíos.");
        //comprobar también que la duración y la puntuación son números
    } elseif (!is_numeric($puntajeMetacritic) || !is_numeric($longitudJuego)) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("La duración del juego y el puntaje en Metacritic deben ser números.");
        //comprobar también que el poster no pesa más de 2 MB
    } elseif ($_FILES['poster']['size'] > 2 * 1024 * 1024) { // 2MB en bytes
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("La imagen de portada no puede pesar más de 2MB.");
    } elseif (!in_array($_FILES['poster']['type'], ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'])) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("El archivo debe ser una imagen (jpeg, png, jpg o webp).");
    } elseif($puntajeMetacritic < 0 || $puntajeMetacritic > 100){
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        die("La puntuación debe estar entre 0 y 100.");
    }

    /* fin comprobaciones (por ahora) */

    try {
        //consulta para añadir el juego
        $consultaInsertarJuego = "INSERT INTO Juegos (poster, nombre, puntuacion_metacritic, duracion_horas) VALUES (?, ?, ?, ?);";

        //preparar la consulta
        $prepararConsulta = $conectar->prepare($consultaInsertarJuego);
        $prepararConsulta->bind_param("ssii", $poster, $nombreJuego, $puntajeMetacritic, $longitudJuego); //blindar los parámetros
        $prepararConsulta->execute(); //ejecutar la sentencia

        // Obtener el ID del juego recién insertado
        $idJuego = $conectar->insert_id;

        //obtener el id del usuario
        $consultaObtenerIdUsuario = "SELECT id_usuario FROM Usuarios WHERE nombre_usuario = ?;";
        $prepararConsultaUsuario = $conectar->prepare($consultaObtenerIdUsuario);
        $prepararConsultaUsuario->bind_param("s", $_SESSION['nombre_usuario']);
        $prepararConsultaUsuario->execute();
        $resultado = $prepararConsultaUsuario->get_result();
        $idUsuario = $resultado->fetch_assoc()['id_usuario'];

        // Insertar en la tabla 'Anade'
        $consultaInsertarAnade = "INSERT INTO Anade (id_juego, id_usuario) VALUES (?, ?);";
        $prepararConsultaAnade = $conectar->prepare($consultaInsertarAnade);
        $prepararConsultaAnade->bind_param("ii", $idJuego, $idUsuario);
        $prepararConsultaAnade->execute();


        //cerrar la conexión
        $conectar->close();

        //refirigir a la página principal
        header("Location: ./principal.php");
    } catch (mysqli_sql_exception $e) {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        //cerrar la conexión
        $conectar->close();
        die("Error añadiendo el juego: " . $e->getMessage());
    }
}

?>
    
</body>
</html>