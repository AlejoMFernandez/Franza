DROP TABLE IF EXISTS `obras`;
DROP TABLE IF EXISTS `usuarios`;
DROP TABLE IF EXISTS `roles`;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 22-08-2025 a las 05:04:46
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `franza`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `obras`
--

CREATE TABLE `obras` (
  `obra_id` int(10) UNSIGNED NOT NULL,
  `usuario_fk` int(10) UNSIGNED NOT NULL,
  `carpeta` varchar(100) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `explicacion` varchar(500) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`obra_id`),
  KEY `obras_usuarios_fk_idx` (`usuario_fk`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `obras`
--

INSERT INTO `obras` (`obra_id`, `usuario_fk`, `carpeta`, `titulo`, `ubicacion`, `explicacion`, `tipo`, `imagen`) VALUES
(25, 1, 'CasaCharo', 'Construccion completa de hogar', 'Barrio Cerrado', 'Explicación extensa de que se hizo', 'civil', 'CasaCharo.jpg'),
(26, 1, 'JorgelinaZonaNorte', 'Obra particular', 'Zona Norte GBA', 'Mediante un proyecto se realizó la ampliación de una cocina existente en conexión con un nuevo quincho. Se demolieron paredes y se generaron nuevos refuerzos. Mediante vigas de encadenados se montó la nueva platea para soportar la carga del nuevo espacio. Se realizó una parrilla completa, techo a dos aguas de tejas francesas, nuevas ventanas en techo de paño fijo, un baño y la ampliación de la cocina existente.', 'civil', 'JorgelinaZonaNorte.jpg'),
(27, 1, 'AzoteaCaba', 'Reforma de Azotea', 'San Miguel', 'La azotea presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie.', 'civil', 'AzoteaCaba.jpg'),
(28, 1, 'CasaVillaDelParque', 'Reforma complete de balcon', 'Villa del Parque', 'La zona de la terraza presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie. Paredes: Las mismas presentaban patologías graves por el ingreso constante de agua sobre un largo periodo de tiempo, generando asi una desconexión entre el revoque y la mamp', 'civil', 'CasaVillaDelParque.jpg'),
(29, 1, 'ObraBmeMitre', 'Restauración de fachada', 'Mitre - CABA', 'Comenzamos con hidrolavado  baja presión, retiro de vegetación en griteas y sellado de las mismas. Se restauraron los ornamentos de la facha y se repararon las partes afectadas por la corrosión debido a la exposición del hierro de estructuras al agua. Se pintó con 4 manos de pintura para muros de primera calidad y se pintaron los frentes de los locales comerciales en la PB', 'civil', 'ObraBmeMitre.jpg'),
(30, 1, 'LambWeston', 'Ampliación y reforma de vivienda unifamiliar', 'San Isidro - GBA', 'Mediante un proyecto, se ejecutó esta obra que comprendía el anexo de un nuevo espacio a uno existente y la reforma interna de los ambientes. Se realizaron tareas de hormigón visto - Pintura - Electricidad nueva - Gas - Aberturas - Techados - Aberturas Velux', 'civil', 'LambWeston.jpg'),
(31, 1, 'ObraCanales', 'Reforma completa de hogar', '3 de Febrero', 'La zona de la terraza presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie. Paredes: Las mismas presentaban patologías graves por el ingreso constante de agua sobre un largo periodo de tiempo, generando asi una desconexión entre el revoque y la mamp', 'civil', 'ObraCanales.jpg'),
(32, 1, 'test', 'Test de obra industrial', 'Ubicacion test obra indusltrial', 'Explicación extensa de que se hizo en la obra industrial', 'industrial', 'test.jpg'),
(33, 1, 'CasaNueva', 'Construcción de casa nueva', 'Barrio Abierto', 'Descripción de la construcción de una casa nueva.', 'civil', 'CasaNueva.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `rol_id` int(10) UNSIGNED NOT NULL,
  `nombre` varchar(45) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`rol_id`, `nombre`) VALUES
(1, 'Administrador'),
(2, 'Usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `usuario_id` int(10) UNSIGNED NOT NULL,
  `rol_fk` int(10) UNSIGNED NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`usuario_id`, `rol_fk`, `email`, `password`) VALUES
(1, 1, 'test@gmail.com', '$2y$10$M8GspSy2Td5nKvVqkXJ0d.qNlOYaeh3DYfjRncPaRtaqfl5TLY2MK'),
(2, 2, 'asd@gmail.com', '$2y$10$SnP4uCkS76VNy8hqMZObA..x473qsKUJU/1KnJGEXOCNwRxrytNoa');

-- --------------------------------------------------------

--
-- Indices de la tabla `obras`
--
-- (elimina o comenta esta sección, ya no es necesaria)
-- ALTER TABLE `obras`
--   ADD PRIMARY KEY (`obra_id`),
--   ADD KEY `obras_usuarios_fk_idx` (`usuario_fk`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`rol_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`usuario_id`),
  ADD UNIQUE KEY `email_UNIQUE` (`email`),
  ADD KEY `usuarios_roles_fk_idx` (`rol_fk`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `obras`
--
ALTER TABLE `obras`
  MODIFY `obra_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `rol_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `usuario_id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `obras`
--
ALTER TABLE `obras`
  ADD CONSTRAINT `obras_usuarios_fk` FOREIGN KEY (`usuario_fk`) REFERENCES `usuarios` (`usuario_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_roles_fk` FOREIGN KEY (`rol_fk`) REFERENCES `roles` (`rol_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
