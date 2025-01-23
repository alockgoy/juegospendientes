/* Creación de la base de datos */
CREATE DATABASE IF NOT EXISTS JuegosPendientes;
USE JuegosPendientes;

/* Tabla Usuarios */
CREATE TABLE Usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre_usuario VARCHAR(50),
    email VARCHAR(100) NOT NULL,
    salt VARCHAR(255) NOT NULL,
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
    id_usuario INT,
    PRIMARY KEY (id_juego, id_usuario),
    FOREIGN KEY (id_juego) REFERENCES Juegos(id) ON DELETE CASCADE,
    FOREIGN KEY (id_usuario) REFERENCES Usuarios(id_usuario) ON DELETE CASCADE
);

/* Crear un usuario que tenga permisos sobre esta base de datos sin necesidad de tirar de root*/
CREATE USER 'usuario_juegos'@'localhost' IDENTIFIED BY 'Franceselquemehackee@123';
GRANT ALL PRIVILEGES ON JuegosPendientes.* TO 'usuario_juegos'@'localhost';
FLUSH PRIVILEGES;

------------------------------------------------------------------------------------------------------------------

/* Eliminar al usuario (por el motivo que sea) */
DROP USER "usuario_juegos"@localhost;

/* Consulta para obtener todos los juegos */
SELECT * FROM Juegos;

/* Consulta para obtener todos los usuarios */
SELECT * FROM Usuarios;

/* Consulta para obtener los valores de la tabla para vincular */
SELECT * FROM Anade;

/* Consulta para obtener los juegos añadidos por un usuario concreto */
SELECT juego.poster, juego.nombre, juego.puntuacion_metacritic, juego.duracion_horas, juego.indicador FROM Juegos as juego INNER JOIN Anade as vincula ON vincula.id_juego = juego.id INNER JOIN Usuarios as usuario ON vincula.nombre_usuario = usuario.nombre_usuario WHERE usuario.nombre_usuario = ? ORDER BY juego.indicador DESC; 

/* Insertar ejemplos en la tabla Usuarios */
INSERT INTO Usuarios (nombre_usuario, email, contrasena) VALUES
('user1', 'user1@example.com', 'password1'),
('user2', 'user2@example.com', 'password2'),
('user3', 'user3@example.com', 'password3'),
('user4', 'user4@example.com', 'password4'),
('user5', 'user5@example.com', 'password5');

/* Insertar ejemplos en la tabla Juegos */
INSERT INTO Juegos (poster, nombre, puntuacion_metacritic, duracion_horas) VALUES
('poster1.jpg', 'Game One', 8.5, 20),
('poster2.jpg', 'Game Two', 7.0, 15),
('poster3.jpg', 'Game Three', 9.3, 40),
('poster4.jpg', 'Game Four', 6.7, 10),
('poster5.jpg', 'Game Five', 8.2, 25);

/* Insertar ejemplos en la tabla Añade */
INSERT INTO Anade (id_juego, nombre_usuario) VALUES
(1, 'user1'),
(2, 'user1'),
(3, 'user2'),
(4, 'user3'),
(5, 'user4'),
(1, 'user5'),
(3, 'user4'),
(2, 'user3'),
(5, 'user1'),
(4, 'user2');

