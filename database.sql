CREATE DATABASE IF NOT EXISTS biblioteca CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE biblioteca;

CREATE TABLE usuarios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  rol ENUM('admin','bibliotecario') NOT NULL DEFAULT 'bibliotecario'
) ENGINE=InnoDB;

CREATE TABLE libros (
  id INT AUTO_INCREMENT PRIMARY KEY,
  titulo VARCHAR(150) NOT NULL,
  autor VARCHAR(100) NOT NULL,
  isbn VARCHAR(20) UNIQUE,
  categoria VARCHAR(60),
  anio SMALLINT,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  ejemplares INT NOT NULL DEFAULT 1,
  disponibles INT NOT NULL DEFAULT 1,
  INDEX (titulo), INDEX (autor)
) ENGINE=InnoDB;

CREATE TABLE socios (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  email VARCHAR(120) NOT NULL UNIQUE,
  telefono VARCHAR(20),
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE prestamos (
  id INT AUTO_INCREMENT PRIMARY KEY,
  libro_id INT NOT NULL,
  socio_id INT NOT NULL,
  fecha_prestamo DATE NOT NULL,
  fecha_limite DATE NOT NULL,
  fecha_devolucion DATE NULL,
  multa DECIMAL(8,2) NOT NULL DEFAULT 0,
  FOREIGN KEY (libro_id) REFERENCES libros(id),
  FOREIGN KEY (socio_id) REFERENCES socios(id)
) ENGINE=InnoDB;

INSERT INTO libros (titulo,autor,isbn,categoria,anio,precio,ejemplares,disponibles) VALUES
('Cien años de soledad','Gabriel García Márquez','9780307474728','Novela',1967,0.00,3,3),
('El laberinto de la soledad','Octavio Paz','9786071611772','Ensayo',1950,0.00,2,2),
('Pedro Páramo','Juan Rulfo','9788437604947','Novela',1955,0.00,2,2),
('Clean Code','Robert C. Martin','9780132350884','Tecnología',2008,0.00,2,2);
INSERT INTO socios (nombre,email,telefono) VALUES
('Ana Torres','ana@ejemplo.com','5512345678'),
('Luis Ramírez','luis@ejemplo.com','5587654321');
