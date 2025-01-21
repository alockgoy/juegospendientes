<?php 

    //depuración para mostrar errores
    error_reporting(E_ALL);
    ini_set('display_errors', 1);    

    //comprobar que existe el archivo de configuración
    $configPath = __DIR__ . '../sql/config.php';
    if (!file_exists($configPath)) {
        echo "<p><strong>No se encuentra el archivo de configuración</strong><p>";
    }

    //traer el archivo de configuración
    include_once $configPath;
    function getConexion() {

        //variables con los datos
        global $host, $usuario, $password, $nombreBaseDatos, $puerto;

        //intentar la conexión
        $conexion = new mysqli($host, $usuario, $password, $nombreBaseDatos, $puerto);

        if ($conexion->connect_error) {
            echo "<p>Error en la conexión: {$conexion->connect_error}</p>";
        } /*else{
            echo "<p>Conexión establecida</p>";
        }*/

        return $conexion;
    }

    //llamar al método que comprueba la conexión
    getConexion();

    //enlace para volver a index
    //echo "<a href= './index.php'>Volver a index</a>";

?>