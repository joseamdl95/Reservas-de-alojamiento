CREATE DATABASE  IF NOT EXISTS `db_reserva_alojamientos` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci */;
USE `db_reserva_alojamientos`;
-- MySQL dump 10.13  Distrib 8.0.40, for Win64 (x86_64)
--
-- Host: localhost    Database: db_reserva_alojamientos
-- ------------------------------------------------------
-- Server version	5.5.5-10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `reserva`
--

DROP TABLE IF EXISTS `reserva`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `nombre_huesped` varchar(100) NOT NULL,
  `email_huesped` varchar(254) NOT NULL,
  `nombre_alojamiento` varchar(150) NOT NULL,
  `fecha_entrada` date NOT NULL,
  `fecha_salida` date NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente',
  `importe` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva`
--

LOCK TABLES `reserva` WRITE;
/*!40000 ALTER TABLE `reserva` DISABLE KEYS */;
INSERT INTO `reserva` VALUES (1,'Lucía Fernández','lucia.fernandez@email.com','Hotel Ritz Madrid','2026-11-10','2026-11-15','confirmada',1250.00,'Habitación doble con vistas al Paseo del Prado','2026-10-01 08:00:00'),(2,'Javier Gómez','javier.gomez@email.com','Apartamentos Sierra Nevada','2026-12-20','2026-12-27','pendiente',850.50,'Cerca de las pistas de esquí','2026-10-15 16:30:00'),(3,'Carmen López','carmen.lopez@email.com','Parador de Cádiz','2026-10-05','2026-10-10','cancelada',980.00,'Canceló por motivos personales','2026-09-20 07:15:00'),(4,'Alejandro Ruiz','alejandro.ruiz@email.com','Casa Rural El Olivo','2027-01-15','2027-01-18','confirmada',320.00,'Solicita cuna para bebé','2026-10-20 12:45:00'),(5,'Elena Martín','elena.martin@email.com','Hotel Bahía Príncipe','2027-02-01','2027-02-08','pendiente',1560.00,'Todo incluido','2026-10-25 10:20:00');
/*!40000 ALTER TABLE `reserva` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reserva_evento`
--

DROP TABLE IF EXISTS `reserva_evento`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reserva_evento` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `reserva_id` bigint(20) unsigned NOT NULL,
  `tipo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_reserva_evento_reserva_id` (`reserva_id`),
  CONSTRAINT `fk_reserva_id` FOREIGN KEY (`reserva_id`) REFERENCES `reserva` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reserva_evento`
--

LOCK TABLES `reserva_evento` WRITE;
/*!40000 ALTER TABLE `reserva_evento` DISABLE KEYS */;
INSERT INTO `reserva_evento` VALUES (1,1,'creada','Reserva creada','2026-10-01 08:00:00'),(2,1,'confirmada','Estado cambiado a confirmada','2026-10-02 07:30:00'),(3,2,'creada','Reserva creada','2026-10-15 16:30:00'),(4,3,'creada','Reserva creada','2026-09-20 07:15:00'),(5,3,'cancelada','Estado cambiado a cancelada','2026-09-25 14:00:00'),(6,4,'creada','Reserva creada','2026-10-20 12:45:00'),(7,4,'confirmada','Estado cambiado a confirmada','2026-10-21 08:00:00'),(8,5,'creada','Reserva creada','2026-10-25 10:20:00');
/*!40000 ALTER TABLE `reserva_evento` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-10-26 15:42:21
