USE biblioteca;

ALTER TABLE usuarios
  ADD COLUMN rol ENUM('admin','bibliotecario') NOT NULL DEFAULT 'bibliotecario';

UPDATE usuarios
SET rol = 'admin'
WHERE id = (SELECT id FROM (SELECT MIN(id) AS id FROM usuarios) AS primer_usuario);

ALTER TABLE prestamos
  ADD COLUMN multa DECIMAL(8,2) NOT NULL DEFAULT 0;