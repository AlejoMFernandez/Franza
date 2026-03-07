-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: franza
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `obras`
--

DROP TABLE IF EXISTS `obras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obras` (
  `obra_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `usuario_fk` int(10) unsigned NOT NULL,
  `carpeta` varchar(100) NOT NULL,
  `titulo` varchar(100) NOT NULL,
  `ubicacion` varchar(100) NOT NULL,
  `explicacion` varchar(500) NOT NULL,
  `tipo` varchar(100) NOT NULL,
  `imagen` varchar(255) NOT NULL,
  PRIMARY KEY (`obra_id`),
  KEY `obras_usuarios_fk_idx` (`usuario_fk`),
  CONSTRAINT `obras_usuarios_fk` FOREIGN KEY (`usuario_fk`) REFERENCES `usuarios` (`usuario_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=74 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras`
--

LOCK TABLES `obras` WRITE;
/*!40000 ALTER TABLE `obras` DISABLE KEYS */;
INSERT INTO `obras` VALUES (67,1,'reforma-de-azotea','Reforma de Azotea','San Miguel','La azotea presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie.','civil','AzoteaCaba_1771019121.jpg'),(68,1,'construccion-completa-de-hogar','Construccion completa de hogar','Barrio Cerrado ?','Explicación extensa de que se hizo','civil','CasaCharo_1771019219.jpg'),(69,1,'reforma-complete-de-balcon','Reforma complete de balcon','Villa del Parque','La zona de la terraza presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie. Paredes: Las mismas presentaban patologías graves por el ingreso constante de agua sobre un largo periodo de tiempo, generando asi una desconexión entre el revoque y la mamp','civil','CasaVillaDelParque_1771019252.jpg'),(70,1,'obra-particular','Obra particular','Zona Norte GBA','Mediante un proyecto se realizó la ampliación de una cocina existente en conexión con un nuevo quincho. Se demolieron paredes y se generaron nuevos refuerzos. Mediante vigas de encadenados se montó la nueva platea para soportar la carga del nuevo espacio. Se realizó una parrilla completa, techo a dos aguas de tejas francesas, nuevas ventanas en techo de paño fijo, un baño y la ampliación de la cocina existente.','civil','JorgelinaZonaNorte_1771019309.jpg'),(71,1,'ampliaci-on-y-reforma-de-vivienda-unifamiliar','Ampliación y reforma de vivienda unifamiliar','San Isidro - GBA','Mediante un proyecto, se ejecutó esta obra que comprendía el anexo de un nuevo espacio a uno existente y la reforma interna de los ambientes. Se realizaron tareas de hormigón visto - Pintura - Electricidad nueva - Gas - Aberturas - Techados - Aberturas Velux','civil','LambWeston_1771019353.jpg'),(72,1,'restauraci-on-de-fachada','Restauración de fachada','Mitre','Comenzamos con hidrolavado baja presión, retiro de vegetación en griteas y sellado de las mismas. Se restauraron los ornamentos de la facha y se repararon las partes afectadas por la corrosión debido a la exposición del hierro de estructuras al agua. Se pintó con 4 manos de pintura para muros de primera calidad y se pintaron los frentes de los locales comerciales en la PB','civil','ObraBmeMitre_1771019414.jpg'),(73,1,'reforma-completa-de-hogar','Reforma completa de hogar','Tres de Febrero','La zona de la terraza presentaba problemas de filtraciones hacia la PB por fisuras en su revestimiento. Se reemplazaron los dañados, previo a la reparación de la carpeta para generar el sello hidrófugo. Se quitaron y volvieron a generar las juntas de dilatación con Poliuretánico de primera marca. Se pintó toda la superficie. Paredes: Las mismas presentaban patologías graves por el ingreso constante de agua sobre un largo periodo de tiempo, generando asi una desconexión entre el revoque y la mamp','civil','ObraCanales_1771019471.jpg');
/*!40000 ALTER TABLE `obras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `obras_carrousel`
--

DROP TABLE IF EXISTS `obras_carrousel`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `obras_carrousel` (
  `obra_id` int(10) unsigned NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`obra_id`),
  KEY `fk_obras_carrousel_obras1_idx` (`obra_id`),
  CONSTRAINT `fk_obras_carrousel_obras1` FOREIGN KEY (`obra_id`) REFERENCES `obras` (`obra_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `obras_carrousel`
--

LOCK TABLES `obras_carrousel` WRITE;
/*!40000 ALTER TABLE `obras_carrousel` DISABLE KEYS */;
/*!40000 ALTER TABLE `obras_carrousel` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `rol_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(45) NOT NULL,
  PRIMARY KEY (`rol_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (1,'Administrador'),(2,'Usuario');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `usuarios` (
  `usuario_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `rol_fk` int(10) unsigned NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`usuario_id`),
  UNIQUE KEY `email_UNIQUE` (`email`),
  KEY `usuarios_roles_fk_idx` (`rol_fk`),
  CONSTRAINT `usuarios_roles_fk` FOREIGN KEY (`rol_fk`) REFERENCES `roles` (`rol_id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (1,1,'test@gmail.com','$2y$10$M8GspSy2Td5nKvVqkXJ0d.qNlOYaeh3DYfjRncPaRtaqfl5TLY2MK'),(2,2,'asd@gmail.com','$2y$10$SnP4uCkS76VNy8hqMZObA..x473qsKUJU/1KnJGEXOCNwRxrytNoa'),(3,2,'fernandezalejo981@gmail.com','$2y$10$ukLHlsAckU.IDPpQ4KvR1OIwri8OApMbqss5aluKu1ajlF6XVlqVO'),(6,1,'allegranza.emiliano@gmail.com','$2y$10$r8qLc24yeEmAmrVOOhGEEOhUFsD/CA4S4/QgXosnhwW5akJxNOaoq'),(7,2,'test2@gmail.com','$2y$10$plbED8nDBIvqNCs1PM2PHOfzp8/SPdt78/jNUBFnWRcvU22RR4.Gq');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Dumping routines for database 'franza'
--
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-02-13 22:06:35
