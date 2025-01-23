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
<!-- Código HTML -->
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/principal.css" />
    <title>Tus juegos pendientes</title>
</head>

<body>

    <header>
        <h2>Tu lista de pendientes</h2>

        <nav>
            <section>
                <a href="../html/insertarJuego.html" class="anade">Añadir un juego</a>
            </section>

            <section>
                <a href="./logout.php">Cerrar sesión</a>
            </section>

            <section>
                <?php
                    echo "<a href='./borrarUsuario.php?nombre_usuario=" . $_SESSION['nombre_usuario'] . "' id='borrar' onclick='return confirmarEliminacion()'>Borrar cuenta</a>";
                ?>
            </section>

            <section>
                <?php 
                    echo "<a href='./modificarUsuario.php?nombre_usuario=" . $_SESSION['nombre_usuario'] . "'>Modificar cuenta</a>";
                ?>
            </section>
        </nav>
    </header>

    <main>
        <!--Abrir la tabla de los jueguitos-->
        <div class="contenedor">
            <!-- Código PHP -->
            <?php
            //la consulta esa del demonio
            $consultaJuegoPorUsuario = "SELECT juego.id, juego.poster, juego.nombre, juego.puntuacion_metacritic, juego.duracion_horas, juego.indicador 
            FROM Juegos as juego 
            INNER JOIN Anade as vincula ON vincula.id_juego = juego.id 
            INNER JOIN Usuarios as usuario ON vincula.id_usuario = usuario.id_usuario
            WHERE usuario.nombre_usuario = ? ORDER BY juego.indicador DESC; ";

            //obtener el nombre de usuario
            $nombreUsuario = $_SESSION['nombre_usuario'];

            try {
                //preparar la consulta
                $prepararConsulta = $conectar->prepare($consultaJuegoPorUsuario);
                $prepararConsulta->bind_param("s", $nombreUsuario); //blindar la consulta
                $prepararConsulta->execute(); //ejecutar la consulta
            
                //obtener el resultado
                $resultado = $prepararConsulta->get_result();

                //comprobar que hay al menos 1 resultado
                if ($resultado->num_rows > 0) {
                    //obtener datos
                    while ($fila = $resultado->fetch_assoc()) {
                        //depuración para mostrar el poster
                        $rutaPoster = "../img/avatares_juegos/" . htmlspecialchars($fila['poster']);

                        // Verificar si el poster existe antes de mostrarlo
                        if (file_exists($rutaPoster)) {
                            echo '<div class="imagen"><img src="' . $rutaPoster . '" alt="Poster del juego"></div>';
                        } else {
                            echo '<div class="imagen"><p>Imagen no disponible</p></div>';
                        }

                        // Mostrar otros datos
                        echo '<div class="texto"> Nombre: ' . htmlspecialchars($fila['nombre']) . '</div>';
                        echo '<div class="texto">Puntuación en Metacritic: ' . htmlspecialchars($fila['puntuacion_metacritic']) . '</div>';
                        echo '<div class="texto">Duración: ' . htmlspecialchars($fila['duracion_horas']) . '</div>';
                        echo '<div class="texto indicador"> Indicador: ' . htmlspecialchars($fila['indicador']) . '</div>';
                        echo '<br/>';
                        echo "<a href='./borrarJuego.php?id_juego=" . htmlspecialchars($fila['id']) . "' onclick='return confirmarEliminacion()'>Borrar juego</a>";
                    }
                } else {
                    echo "<p><strong>No hay datos disponibles</strong></p>";
                }

                //cerrar la conexión
                $conectar->close();

            } catch (mysqli_sql_exception $e) {
                //cerrar la conexión
                $conectar->close();
                die("Error generando la tabla: " . $e->getMessage());
            }
            ?>
        </div>
    </main>

    <script src="../js/app.js"></script>

    <footer>

    </footer>

</body>

</html>