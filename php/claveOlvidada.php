<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" type="text/css" href="../css/errores.css">
    <title>Document</title>
</head>

<body>
    <?php
    //depuración
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);

    //traer el archivo autoload del phpmailer
    require '../vendor/autoload.php';

    //usar el php mailer
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    //traer el archivo de conexión
    include_once "./conectar.php";

    //establecer la conexión
    $conectar = getConexion();

    //comprobar que se puede establecer la conexión
    if (!$conectar) {
        die("<p class='error'>Error en la conexión a la base de datos</p>");
    }

    //comprobar que se ha pulsado el botón de envío del formulario de crear cuenta
    if ($_SERVER['REQUEST_METHOD'] == "POST") {
        //asignar los valores introducidos a variables
        $correo = $_POST['email'];

        /* Comprobaciones */

        //comprobar que el campo no está vacío
        if (empty($correo)) {
            echo "<a href='../html/login.html'>Volver atrás</a><br/><br/>";
            die("<p class='error'>No puedes dejar el campo vacío.</p>");
        }

        //comprobar que el correo electrónico es válido
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo "<a href='../html/login.html'>Volver atrás</a><br/><br/>";
            die("<p class='error'>El correo electrónico no es válido.</p>");
        }

        //comprobar que el correo del usuario existe en la base de datos
        $sqlExisteCorreo = "SELECT email FROM Usuarios WHERE email = ?;";
        $prepararConsulta = $conectar->prepare($sqlExisteCorreo); //preparar la consulta
        $prepararConsulta->bind_param("s", $correo); //blindar el parámetro
        $prepararConsulta->execute(); //ejecutar la consulta
        $resultado = $prepararConsulta->get_result(); //obtener el resultado
        if ($resultado->num_rows === 0) {
            echo "<a href='../html/login.html'>Volver atrás</a><br/><br/>";
            die("<p class='error'>No se encuentra una cuenta con ese correo en la web.</p>");
        }

        /* Fin de las comprobaciones (por ahora) */

        /*Suponiendo que el correo introducido existe en la base de datos */

        //generar una nueva contraseña
        $nuevaClave = bin2hex(random_bytes(8));

        //actualizar la contraseña en la base de datos
        try {
            $salt = rand(-1000000, 1000000);
            $nuevoHashedPassword = hash('sha256', $nuevaClave . $salt);
            $sqlActualizarClave = "UPDATE Usuarios SET contrasena = ?, salt = ? WHERE email = ?;";
            $prepararConsulta = $conectar->prepare($sqlActualizarClave); // preparar la consulta
            $prepararConsulta->bind_param("sis", $nuevoHashedPassword, $salt, $correo); // blindar los parámetros
            $prepararConsulta->execute(); // ejecutar la consulta
        } catch (mysqli_sql_exception $e) {
            //cerrar la conexión
            $conectar->close();
            die("<p class='error'>Error cambiando la contraseña: " . $e->getMessage() . "</p>");
        }


        //configurar PHPMailer y mandar la nueva contraseña
        $mail = new PHPMailer(true);
        try {
            // Configurar SMTP
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';  // Servidor SMTP de Gmail
            $mail->SMTPAuth = true;
            $mail->Username = 'correo'; // TU correo de Gmail
            $mail->Password = 'clave'; // Contraseña de la aplicación generada
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = 587;

            // Configuración del correo
            $mail->setFrom('correo', 'usuario'); // De: el correo del usuario que genera la contraseña
            $mail->addAddress($correo); // A: el correo de destino
            //$mail->addReplyTo($correoUsuario); // Opción de responder al correo del usuario
    
            // Contenido del correo
            $mail->isHTML(true);
            $mail->Subject = "Clave nueva generada.";
            $mail->Body = "<p>¡Hola $correo! Se ha recibido una solicitud para cambiar tu contraseña. ¡No te preocupes!</p>
            <p>Tu nueva contraseña es: <strong>$nuevaClave</strong></p>
            <p>Puedes cambiarla siempre que quieras desde el apartado de modificar tu cuenta</p>";

            // Enviar el correo
            $mail->send();
            header("Location: ../index.html");
        } catch (Exception $e) {
            echo "<p class='error'>Error al enviar la nueva contraseña: {$mail->ErrorInfo}</p>";
        }
    }
    ?>
</body>

</html>