USE biblioteca;

CREATE TABLE escritores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  pais VARCHAR(80),
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (nombre)
) ENGINE=InnoDB;

CREATE TABLE libro_escritor (
  libro_id INT NOT NULL,
  escritor_id INT NOT NULL,
  PRIMARY KEY (libro_id, escritor_id),
  FOREIGN KEY (libro_id) REFERENCES libros(id) ON DELETE CASCADE,
  FOREIGN KEY (escritor_id) REFERENCES escritores(id) ON DELETE CASCADE,
  INDEX (escritor_id)
) ENGINE=InnoDB;

ALTER TABLE libros
  MODIFY COLUMN autor VARCHAR(500) NOT NULL;

INSERT IGNORE INTO escritores (nombre)
SELECT DISTINCT TRIM(autor) FROM libros WHERE TRIM(autor) <> '';

INSERT IGNORE INTO libro_escritor (libro_id, escritor_id)
SELECT l.id, e.id
FROM libros l
JOIN escritores e ON e.nombre=TRIM(l.autor)
WHERE TRIM(l.autor) <> '';