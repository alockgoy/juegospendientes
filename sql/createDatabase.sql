/* Creación de la base de datos */
CREATE DATABASE IF NOT EXISTS JuegosPendientes;
USE JuegosPendientes;

/* Tabla Usuarios */
CREATE TABLE Usuarios (
    nombre_usuario VARCHAR(50) PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    contrasena VARCHAR(100) NOT NULL
);

/* Tabla Juegos */
CREATE TABLE Juegos(
    id INT AUTO_INCREMENT PRIMARY KEY,
    poster VARCHAR(255),
    nombre VARCHAR(100) NOT NULL,
    puntuacion_metacritic DECIMAL(2, 1),
    duracion_horas INT NOT NULL,
    indicador DECIMAL(5, 2) GENERATED ALWAYS AS (puntuacion_metacritic / duracion_horas) STORED /* puntuacion_metacritic / duracion_horas */
);

/* Tabla intermedia Añade */
CREATE TABLE Anade (
    id_juego INT,
    nombre_usuario VARCHAR(50),
    PRIMARY KEY (id_juego, nombre_usuario),
    FOREIGN KEY (id_juego) REFERENCES Juegos(id) ON DELETE CASCADE,
    FOREIGN KEY (nombre_usuario) REFERENCES Usuarios(nombre_usuario) ON DELETE CASCADE
);

/* Crear un usuario que tenga permisos sobre esta base de datos sin necesidad de tirar de root*/
CREATE USER 'usuario_juegos'@'localhost' IDENTIFIED WITH caching_sha2_password BY 'Franceselquemehackee@123';
GRANT ALL PRIVILEGES ON JuegosPendientes.* TO 'usuario_juegos'@'localhost';
FLUSH PRIVILEGES;

/* Consulta para obtener todos los juegos */
SELECT * FROM Juegos;

/* Consulta para obtener todos los usuarios */
SELECT * FROM Usuarios;

/* Consulta para obtener los valores de la tabla para vincular */
SELECT * FROM Anade;

/* Consulta para obtener los juegos añadidos por un usuario concreto */
SELECT juego.poster, juego.nombre, juego.puntuacion_metacritic, juego.duracion_horas, juego.indicador FROM Juegos as juego INNER JOIN Anade as vincula ON vincula.id_juego = juego.id INNER JOIN Usuarios as usuario ON vincula.nombre_usuario = usuario.nombre_usuario WHERE usuario.nombre_usuario = ?; 