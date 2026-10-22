# Biblioteca Central · Sistema de gestión

Aplicación web para administrar el catálogo, los socios y los préstamos de una biblioteca.

**Stack:** HTML5 · CSS3 · JavaScript · PHP 8 (PDO) · MySQL

## Funciones
- Inicio de sesión con contraseñas cifradas (`password_hash`) y sesión regenerada
- Dashboard con indicadores y préstamos recientes
- CRUD de libros (búsqueda por título, escritor, editorial o ISBN) y de socios (filtro instantáneo)
- Catálogo de editoriales relacionado con libros
- Catálogo de escritores con relación de varios escritores por libro
- Registro del precio de cada ejemplar y cálculo del valor total del inventario en el dashboard
- Préstamos y devoluciones con control de disponibilidad en transacción
- Detección de préstamos vencidos y multa configurable por día de retraso (`MULTA_POR_DIA`)
- Roles `admin` y `bibliotecario`; administración de usuarios exclusiva del admin
- Paginación y búsqueda de libros, socios y préstamos
- Exportación a CSV y vista imprimible para guardar como PDF desde el navegador
- API REST JSON de solo lectura, autenticada con HTTP Basic

## Seguridad
Consultas preparadas (anti SQL injection), escape de salida con `htmlspecialchars` (anti XSS), token CSRF en todos los formularios, integridad referencial con claves foráneas.

## Instalación (XAMPP / Laragon / WAMP)
1. Copia la carpeta `biblioteca` en `htdocs` (o `www`).
2. Importa `database.sql` en phpMyAdmin.
3. Ajusta credenciales en `config/db.php` si es necesario.
4. Abre `http://localhost/biblioteca`
5. Acceso demo: `admin@biblioteca.test` / `admin123` (se crea en el primer arranque; cámbiala).

Si ya tienes la base de datos creada, ejecuta una vez `database_upgrade.sql` en phpMyAdmin para añadir roles y multas, `database_upgrade_price.sql` para agregar el precio, `database_upgrade_editoriales.sql` para crear el catálogo editorial y `database_upgrade_escritores.sql` para crear escritores y relacionarlos con sus libros actuales. Los precios existentes se inicializan en cero: actualízalos desde **Libros** para obtener el valor real del inventario. Los libros actuales quedan sin editorial hasta que se la asignes. En una instalación nueva, importa `database.sql`. El admin crea cuentas de bibliotecario y otros admins desde **Usuarios**. La multa inicial es de 5 por día vencido y se cambia en `config/db.php`.

## Exportar
En Libros, Socios y Préstamos puedes descargar como CSV todos los resultados que coincidan con la búsqueda actual. En Editoriales y Escritores puedes exportar el catálogo con país y cantidad de libros asociados. La opción de PDF abre una vista de impresión; elige **Guardar como PDF** en el diálogo de impresión del navegador.

## API
Los endpoints son de solo lectura y requieren HTTP Basic con el correo y contraseña de un usuario de la aplicación. En producción, publícala únicamente detrás de HTTPS.

```text
GET /biblioteca/api/?recurso=libros
GET /biblioteca/api/?recurso=socios&pagina=2&limite=20
GET /biblioteca/api/?recurso=prestamos&q=Clean
```

La respuesta incluye `datos` y `paginacion`; los recursos válidos son `libros`, `socios` y `prestamos`. Los libros exponen `escritores` como lista relacionada y conservan `autor` por compatibilidad. Se puede buscar con `q`, elegir `pagina` y fijar `limite` entre 1 y 100.

## Estructura
```
config/db.php        credenciales
includes/init.php    conexión PDO, sesión, helpers y CSRF
includes/layout.php  cabecera y pie compartidos
index.php  libros.php  socios.php  prestamos.php  login.php  logout.php
usuarios.php  editoriales.php  escritores.php  reportes.php  exportar.php  api/index.php
database.sql  database_upgrade.sql  database_upgrade_price.sql
database_upgrade_editoriales.sql  database_upgrade_escritores.sql
assets/css  assets/js
```

