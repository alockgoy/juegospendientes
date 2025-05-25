<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/errores.css">
    <title></title>
</head>

<body>
    <?php
    //depuración
    // ini_set('display_errors', 1);
    // ini_set('display_startup_errors', 1);
    // error_reporting(E_ALL);
    
    //traer el archivo de conexión
    include_once "./conectar.php";

    //establecer la conexión
    $conectar = getConexion();

    //comprobar que se puede establecer la conexión
    if (!$conectar) {
        echo "<a href='../html/login.html'>Volver atrás</a><br/><br/>";
        die("<p>Error en la conexión a la base de datos</p>");
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

        /* Comprobaciones */

        //comprobar que no se ha mandado el formulario con algún campo vacío
        if (empty($nombreJuego) || empty($puntajeMetacritic) || empty($longitudJuego)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>No puede haber campos vacíos.</p>");
        }

        //comprobar que la duración y la puntuación son números
        if (!is_numeric($puntajeMetacritic) || !is_numeric($longitudJuego)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>La duración del juego y el puntaje en Metacritic deben ser números.</p>");
        }

        //comprobar que la puntuación está entre 0 y 100
        if ($puntajeMetacritic < 0 || $puntajeMetacritic > 100) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>La puntuación debe estar entre 0 y 100.</p>");
        }

        // Verificar si hubo un error al subir el archivo
        if ($_FILES['poster']['error'] !== UPLOAD_ERR_OK) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            switch ($_FILES['poster']['error']) {
                case UPLOAD_ERR_INI_SIZE:
                case UPLOAD_ERR_FORM_SIZE:
                    die("<p>La imagen de portada no puede pesar más de 2MB.<p>");
                case UPLOAD_ERR_NO_FILE:
                    die("<p>No se ha subido ningún archivo.</p>");
                default:
                    die("<p>Error al subir el archivo. Código de error: " . $_FILES['poster']['error'] . " </p>");
            }
        }

        // Verificar que el archivo no pese más de 2 MB
        if ($_FILES['poster']['size'] > 2 * 1024 * 1024) { // 2MB en bytes
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>La imagen de portada no puede pesar más de 2MB.</p>");
        }

        // Verificar que el archivo subido sea una imagen válida
        $poster_tmp = $_FILES['poster']['tmp_name'];
        $infoImagen = getimagesize($poster_tmp);
        if ($infoImagen === false) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>El archivo subido no es una imagen válida.</p>");
        }

        // Verificar el tipo MIME de la imagen
        $mimePermitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp'];
        if (!in_array($infoImagen['mime'], $mimePermitidos)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>El archivo debe ser una imagen (jpeg, png, jpg o webp).</p>");
        }

        /* fin comprobaciones (por ahora) */

        //procesar el poster
        $poster = $_FILES['poster']['name'];
        $directorioPoster = '../img/avatares_juegos/';

        // Crear el directorio si no existe
        if (!is_dir($directorioPoster)) {
            if (!mkdir($directorioPoster, 0755, true)) {
                echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
                die("<p>Error: No se pudo crear el directorio para las imágenes.</p>");
            }
        }

        // Verificar que el directorio sea escribible
        if (!is_writable($directorioPoster)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            die("<p>Error: El directorio de imágenes no tiene permisos de escritura.</p>");
        }

        // eliminar espacios en blanco en el nombre del archivo del poster
        $poster = str_replace(' ', '_', $poster);

        //crear un nombre único para el poster
        $nombreUnicoArchivo = uniqid("poster_") . "_" . basename($_FILES['poster']['name']);
        $rutaPoster = $directorioPoster . $nombreUnicoArchivo;

        // mover el archivo al directorio de poster
        if (!move_uploaded_file($poster_tmp, $rutaPoster)) {
            echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
            // Información adicional para debugging
            $error_info = error_get_last();
            // echo "<p>Error al subir el poster.</p>";
            // echo "<p>Ruta origen: " . $poster_tmp . "</p>";
            // echo "<p>Ruta destino: " . $rutaPoster . "</p>";
            // echo "<p>Directorio existe: " . (is_dir($directorioPoster) ? 'Sí' : 'No') . "</p>";
            // echo "<p>Directorio escribible: " . (is_writable($directorioPoster) ? 'Sí' : 'No') . "</p>";
            // if ($error_info) {
            //     echo "<p>Error del sistema: " . $error_info['message'] . "</p>";
            // }
            exit();
        }

        // ruta final para guardar en la base de datos
        $poster = $nombreUnicoArchivo;

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
            die("<p>Error añadiendo el juego: " . $e->getMessage() . " </p>");
        }
    } else {
        echo "<a href='./principal.php'>Volver atrás</a><br/><br/>";
        echo "<p>Error: Método de solicitud no válido.</p>";
        exit();
    }

    ?>

</body>

</html>