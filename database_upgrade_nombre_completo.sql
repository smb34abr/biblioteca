USE biblioteca;

ALTER TABLE usuarios
  ADD COLUMN nombre_completo VARCHAR(150) NOT NULL DEFAULT '' AFTER nombre;

UPDATE usuarios
SET nombre_completo = nombre
WHERE nombre_completo = '';