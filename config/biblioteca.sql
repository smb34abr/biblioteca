-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 22-10-2026 a las 16:44:13
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `biblioteca`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `editoriales`
--

CREATE TABLE `editoriales` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `pais` varchar(80) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `editoriales`
--

INSERT INTO `editoriales` (`id`, `nombre`, `pais`, `creado`) VALUES
(1, 'Penguin Random House Grupo Editorial', 'Barcelona, España', '2026-10-22 07:53:05'),
(2, 'Alfaomega Grupo Editor', 'México', '2026-10-22 07:55:19'),
(3, 'Agencia Nacional de ISBN de Mexico', 'México', '2026-10-22 07:57:02'),
(5, 'Editorial Aguilar', 'Madrid, España', '2026-10-22 08:04:30'),
(6, 'Grupo Editorial Norma', 'Colombia', '2026-10-22 08:06:07');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escritores`
--

CREATE TABLE `escritores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(120) NOT NULL,
  `pais` varchar(80) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `escritores`
--

INSERT INTO `escritores` (`id`, `nombre`, `pais`, `creado`) VALUES
(1, 'Baltazar Zúñiga Álvarez', 'México', '2026-10-22 08:19:12'),
(2, 'Denise Dresser', 'México', '2026-10-22 08:19:12'),
(3, 'Isabel Allende', 'Chile', '2026-10-22 08:19:12'),
(4, 'Mark Wolynn', 'Estados Unidos', '2026-10-22 08:19:12'),
(5, 'Sarah Jane Stratford', 'Estados Unidos', '2026-10-22 08:19:12');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libros`
--

CREATE TABLE `libros` (
  `id` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `autor` varchar(500) NOT NULL,
  `isbn` varchar(20) DEFAULT NULL,
  `categoria` varchar(60) DEFAULT NULL,
  `anio` smallint(6) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `editorial_id` int(11) DEFAULT NULL,
  `ejemplares` int(11) NOT NULL DEFAULT 1,
  `disponibles` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `libros`
--

INSERT INTO `libros` (`id`, `titulo`, `autor`, `isbn`, `categoria`, `anio`, `precio`, `editorial_id`, `ejemplares`, `disponibles`) VALUES
(1, 'Este dolor no es mio', 'Mark Wolynn', '9788484456810', 'Autoayuda, psicología y superación personal', 2023, 40.00, 2, 1, 1),
(2, 'Las historias que nos contamos', 'Baltazar Zúñiga Álvarez', '9786076963807', 'Autoayuda', 2024, 418.32, 3, 1, 1),
(3, 'Violeta', 'Isabel Allende', '9786073810845', 'Ficción histórica', 2022, 429.00, 1, 1, 1),
(4, 'El país de uno', 'Denise Dresser', '9786071106285', 'Ensayo político', 2011, 80.00, 5, 1, 1),
(5, 'La guardia de media noche', 'Sarah Jane Stratford', '9789584525864', 'ficción histórica, fantasía y vampiros', 2009, 80.00, 6, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `libro_escritor`
--

CREATE TABLE `libro_escritor` (
  `libro_id` int(11) NOT NULL,
  `escritor_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `libro_escritor`
--

INSERT INTO `libro_escritor` (`libro_id`, `escritor_id`) VALUES
(1, 4),
(2, 1),
(3, 3),
(4, 2),
(5, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `prestamos`
--

CREATE TABLE `prestamos` (
  `id` int(11) NOT NULL,
  `libro_id` int(11) NOT NULL,
  `socio_id` int(11) NOT NULL,
  `fecha_prestamo` date NOT NULL,
  `fecha_limite` date NOT NULL,
  `fecha_devolucion` date DEFAULT NULL,
  `multa` decimal(8,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `socios`
--

CREATE TABLE `socios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(120) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `creado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `nombre_completo` varchar(150) NOT NULL DEFAULT '',
  `email` varchar(120) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('admin','bibliotecario') NOT NULL DEFAULT 'bibliotecario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `nombre_completo`, `email`, `password_hash`, `rol`) VALUES
(1, 'bibliotecario', 'bibliotecario', 'admin@biblioteca.test', '$2y$10$keTVjh2DbvU6QeYH1uEQh.8q20.q8gNiI8zcfuiW83gXTyPcx6oa2', 'bibliotecario'),
(2, 'abernal', 'Alberto Bernal', 'smb34abr@gmail.com', '$2y$10$Cxqy1MBg6MqZjQ6GLMoa5e0.HcWYvSg/oWxCwxIr1kLOelbwiAM1u', 'admin');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `nombre_2` (`nombre`);

--
-- Indices de la tabla `escritores`
--
ALTER TABLE `escritores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `nombre_2` (`nombre`);

--
-- Indices de la tabla `libros`
--
ALTER TABLE `libros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `isbn` (`isbn`),
  ADD KEY `titulo` (`titulo`),
  ADD KEY `autor` (`autor`),
  ADD KEY `fk_libros_editorial` (`editorial_id`);

--
-- Indices de la tabla `libro_escritor`
--
ALTER TABLE `libro_escritor`
  ADD PRIMARY KEY (`libro_id`,`escritor_id`),
  ADD KEY `escritor_id` (`escritor_id`);

--
-- Indices de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `libro_id` (`libro_id`),
  ADD KEY `socio_id` (`socio_id`);

--
-- Indices de la tabla `socios`
--
ALTER TABLE `socios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `editoriales`
--
ALTER TABLE `editoriales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `escritores`
--
ALTER TABLE `escritores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `libros`
--
ALTER TABLE `libros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `prestamos`
--
ALTER TABLE `prestamos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `socios`
--
ALTER TABLE `socios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `libros`
--
ALTER TABLE `libros`
  ADD CONSTRAINT `fk_libros_editorial` FOREIGN KEY (`editorial_id`) REFERENCES `editoriales` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `libro_escritor`
--
ALTER TABLE `libro_escritor`
  ADD CONSTRAINT `libro_escritor_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `libro_escritor_ibfk_2` FOREIGN KEY (`escritor_id`) REFERENCES `escritores` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `prestamos`
--
ALTER TABLE `prestamos`
  ADD CONSTRAINT `prestamos_ibfk_1` FOREIGN KEY (`libro_id`) REFERENCES `libros` (`id`),
  ADD CONSTRAINT `prestamos_ibfk_2` FOREIGN KEY (`socio_id`) REFERENCES `socios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
