USE biblioteca;

CREATE TABLE editoriales (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL UNIQUE,
  pais VARCHAR(80),
  creado TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX (nombre)
) ENGINE=InnoDB;

ALTER TABLE libros
  ADD COLUMN editorial_id INT NULL AFTER precio,
  ADD CONSTRAINT fk_libros_editorial
    FOREIGN KEY (editorial_id) REFERENCES editoriales(id) ON DELETE SET NULL;