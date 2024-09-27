-- MariaDB dump 10.19  Distrib 10.4.24-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: cinemaBDD
-- ------------------------------------------------------
-- Server version	10.4.24-MariaDB

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
-- Table structure for table `bookings`
--

DROP TABLE IF EXISTS `bookings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `bookings` (
  `booking_id` char(36) NOT NULL,
  `date` datetime NOT NULL,
  `status_id` int(11) DEFAULT NULL,
  `payment_amount` decimal(10,2) NOT NULL,
  `seat_number` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  `hall_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`booking_id`),
  KEY `session_id` (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `hall_id` (`hall_id`),
  KEY `status_id` (`status_id`),
  CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_3` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`hall_id`) ON DELETE CASCADE,
  CONSTRAINT `bookings_ibfk_4` FOREIGN KEY (`status_id`) REFERENCES `status` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `bookings`
--

LOCK TABLES `bookings` WRITE;
/*!40000 ALTER TABLE `bookings` DISABLE KEYS */;
INSERT INTO `bookings` VALUES ('08088a99-d164-11ee-a3c9-34415d418993','2024-02-22 10:23:05',2,15.20,2,1,'4b0adac0-d15f-11ee-a3c9-34415d418993',1),('13978e2d-d223-11ee-a3c9-34415d418993','2024-02-23 09:10:30',2,9.20,1,1,'0877882c-d223-11ee-a3c9-34415d418993',1),('3b3c35c8-d164-11ee-a3c9-34415d418993','2024-02-22 10:24:31',2,38.00,5,5,'4b0adac0-d15f-11ee-a3c9-34415d418993',10),('43397553-d164-11ee-a3c9-34415d418993','2024-02-22 10:24:44',2,18.40,2,5,'4b0adac0-d15f-11ee-a3c9-34415d418993',10),('47d82d59-d164-11ee-a3c9-34415d418993','2024-02-22 10:24:52',2,18.40,2,5,'4b0adac0-d15f-11ee-a3c9-34415d418993',10),('69c5fd73-d162-11ee-a3c9-34415d418993','2024-02-22 10:11:30',2,92.00,10,1,'4b0adac0-d15f-11ee-a3c9-34415d418993',1),('a7d0b753-d18e-11ee-a3c9-34415d418993','2024-02-22 15:28:08',2,59.00,10,7,'4b0adac0-d15f-11ee-a3c9-34415d418993',5),('bdbf594c-d4a7-11ee-a3c9-34415d418993','2024-02-26 14:05:06',2,9.20,1,1,'4b0adac0-d15f-11ee-a3c9-34415d418993',1),('ccb57b4b-d21f-11ee-a3c9-34415d418993','2024-02-23 08:47:02',2,5.90,1,1,'4b0adac0-d15f-11ee-a3c9-34415d418993',1),('e0d7d2e7-7cc8-11ef-86dc-5c31e1d58621','2024-09-27 14:06:07',2,9.20,1,2,'cdeeebd0-7cc8-11ef-86dc-5c31e1d58621',2);
/*!40000 ALTER TABLE `bookings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cinemas`
--

DROP TABLE IF EXISTS `cinemas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `cinemas` (
  `cinema_id` char(36) NOT NULL,
  `name` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `halls_number` int(11) DEFAULT NULL,
  PRIMARY KEY (`cinema_id`),
  CONSTRAINT `CONSTRAINT_1` CHECK (`halls_number` >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cinemas`
--

LOCK TABLES `cinemas` WRITE;
/*!40000 ALTER TABLE `cinemas` DISABLE KEYS */;
INSERT INTO `cinemas` VALUES ('487ef5c9-a0d2-11ee-8705-34415d418993','Cineplex Odeon','New York City',10),('4880cec4-a0d2-11ee-8705-34415d418993','AMC Theatres','Los Angeles',8),('e69e48a5-ae09-11ee-ab73-34415d418993','City Cinema','City Center',5);
/*!40000 ALTER TABLE `cinemas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `halls`
--

DROP TABLE IF EXISTS `halls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `halls` (
  `hall_id` int(11) NOT NULL AUTO_INCREMENT,
  `cinema_id` char(36) DEFAULT NULL,
  `hall_name` varchar(255) NOT NULL,
  `hall_capacity` int(11) DEFAULT NULL,
  PRIMARY KEY (`hall_id`),
  KEY `cinema_id` (`cinema_id`),
  CONSTRAINT `halls_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`) ON DELETE CASCADE,
  CONSTRAINT `CONSTRAINT_1` CHECK (`hall_capacity` >= 0)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `halls`
--

LOCK TABLES `halls` WRITE;
/*!40000 ALTER TABLE `halls` DISABLE KEYS */;
INSERT INTO `halls` VALUES (1,'487ef5c9-a0d2-11ee-8705-34415d418993','Hall 1',80),(2,'487ef5c9-a0d2-11ee-8705-34415d418993','Hall 2',120),(3,'487ef5c9-a0d2-11ee-8705-34415d418993','Hall 3',90),(4,'487ef5c9-a0d2-11ee-8705-34415d418993','Hall 4',100),(5,'4880cec4-a0d2-11ee-8705-34415d418993','Hall A',110),(6,'4880cec4-a0d2-11ee-8705-34415d418993','Hall B',95),(7,'4880cec4-a0d2-11ee-8705-34415d418993','Hall C',80),(8,'4880cec4-a0d2-11ee-8705-34415d418993','Hall D',120),(9,'e69e48a5-ae09-11ee-ab73-34415d418993','Small Hall',50),(10,'e69e48a5-ae09-11ee-ab73-34415d418993','Medium Hall',70),(11,'e69e48a5-ae09-11ee-ab73-34415d418993','Large Hall',100),(12,'e69e48a5-ae09-11ee-ab73-34415d418993','VIP Hall',30);
/*!40000 ALTER TABLE `halls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movie_cinema_relationship`
--

DROP TABLE IF EXISTS `movie_cinema_relationship`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movie_cinema_relationship` (
  `movie_id` int(11) NOT NULL,
  `cinema_id` char(36) NOT NULL,
  PRIMARY KEY (`movie_id`,`cinema_id`),
  KEY `cinema_id` (`cinema_id`),
  CONSTRAINT `movie_cinema_relationship_ibfk_1` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE,
  CONSTRAINT `movie_cinema_relationship_ibfk_2` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movie_cinema_relationship`
--

LOCK TABLES `movie_cinema_relationship` WRITE;
/*!40000 ALTER TABLE `movie_cinema_relationship` DISABLE KEYS */;
INSERT INTO `movie_cinema_relationship` VALUES (1,'487ef5c9-a0d2-11ee-8705-34415d418993'),(1,'4880cec4-a0d2-11ee-8705-34415d418993'),(1,'e69e48a5-ae09-11ee-ab73-34415d418993'),(2,'487ef5c9-a0d2-11ee-8705-34415d418993'),(2,'4880cec4-a0d2-11ee-8705-34415d418993'),(2,'e69e48a5-ae09-11ee-ab73-34415d418993'),(3,'487ef5c9-a0d2-11ee-8705-34415d418993'),(3,'4880cec4-a0d2-11ee-8705-34415d418993'),(3,'e69e48a5-ae09-11ee-ab73-34415d418993'),(4,'487ef5c9-a0d2-11ee-8705-34415d418993'),(4,'4880cec4-a0d2-11ee-8705-34415d418993'),(4,'e69e48a5-ae09-11ee-ab73-34415d418993'),(5,'487ef5c9-a0d2-11ee-8705-34415d418993'),(5,'e69e48a5-ae09-11ee-ab73-34415d418993');
/*!40000 ALTER TABLE `movie_cinema_relationship` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `movies`
--

DROP TABLE IF EXISTS `movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `movies` (
  `movie_id` int(11) NOT NULL AUTO_INCREMENT,
  `movie_title` varchar(255) DEFAULT NULL,
  `movie_description` text DEFAULT NULL,
  `movie_duration` int(11) DEFAULT NULL,
  `movie_genre` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`movie_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `movies`
--

LOCK TABLES `movies` WRITE;
/*!40000 ALTER TABLE `movies` DISABLE KEYS */;
INSERT INTO `movies` VALUES (1,'The Matrix','A computer hacker learns about the true nature of his reality',136,'Sci-Fi'),(2,'Inception','A thief who enters the dreams of others to steal their secrets',148,'Sci-Fi'),(3,'The Shawshank Redemption','Two imprisoned men bond over several years, finding solace and eventual redemption through acts of common decency.',142,'Drama'),(4,'The Godfather','The aging patriarch of an organized crime dynasty transfers control of his clandestine empire to his reluctant son.',175,'Crime'),(5,'The Dark Knight','When the menace known as The Joker emerges from his mysterious past, he wreaks havoc and chaos on the people of Gotham.',152,'Action');
/*!40000 ALTER TABLE `movies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `payments`
--

DROP TABLE IF EXISTS `payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `payments` (
  `id` char(36) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `date` datetime NOT NULL,
  `booking_id` char(36) DEFAULT NULL,
  `payment_type_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `booking_id` (`booking_id`),
  KEY `payment_type_id` (`payment_type_id`),
  CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`booking_id`) ON DELETE CASCADE,
  CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`payment_type_id`) REFERENCES `paymenttypes` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `payments`
--

LOCK TABLES `payments` WRITE;
/*!40000 ALTER TABLE `payments` DISABLE KEYS */;
INSERT INTO `payments` VALUES ('099814ce-d164-11ee-a3c9-34415d418993',15.20,'2024-02-22 10:23:07','08088a99-d164-11ee-a3c9-34415d418993',2),('15e54668-d223-11ee-a3c9-34415d418993',9.20,'2024-02-23 09:10:33','13978e2d-d223-11ee-a3c9-34415d418993',2),('3befb2c2-d164-11ee-a3c9-34415d418993',38.00,'2024-02-22 10:24:32','3b3c35c8-d164-11ee-a3c9-34415d418993',1),('43f78857-d164-11ee-a3c9-34415d418993',18.40,'2024-02-22 10:24:45','43397553-d164-11ee-a3c9-34415d418993',1),('48ed92dd-d164-11ee-a3c9-34415d418993',18.40,'2024-02-22 10:24:54','47d82d59-d164-11ee-a3c9-34415d418993',1),('6b46d497-d162-11ee-a3c9-34415d418993',92.00,'2024-02-22 10:11:32','69c5fd73-d162-11ee-a3c9-34415d418993',1),('c46c384a-d18e-11ee-a3c9-34415d418993',59.00,'2024-02-22 15:28:56','a7d0b753-d18e-11ee-a3c9-34415d418993',1),('cdb7759c-d21f-11ee-a3c9-34415d418993',5.90,'2024-02-23 08:47:04','ccb57b4b-d21f-11ee-a3c9-34415d418993',1),('ce147812-d4aa-11ee-a3c9-34415d418993',9.20,'2024-02-26 14:27:02','bdbf594c-d4a7-11ee-a3c9-34415d418993',2),('e43dcc49-7cc8-11ef-86dc-5c31e1d58621',9.20,'2024-09-27 14:06:13','e0d7d2e7-7cc8-11ef-86dc-5c31e1d58621',1);
/*!40000 ALTER TABLE `payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `paymenttypes`
--

DROP TABLE IF EXISTS `paymenttypes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `paymenttypes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type_name` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `type_name` (`type_name`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `paymenttypes`
--

LOCK TABLES `paymenttypes` WRITE;
/*!40000 ALTER TABLE `paymenttypes` DISABLE KEYS */;
INSERT INTO `paymenttypes` VALUES (1,'Cash'),(2,'Credit');
/*!40000 ALTER TABLE `paymenttypes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `roles` (
  `role_id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  PRIMARY KEY (`role_id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES (3,'COMPLEX_USER'),(2,'ROLE_ADMIN'),(1,'ROLE_USER');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_halls`
--

DROP TABLE IF EXISTS `session_halls`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_halls` (
  `session_id` int(11) NOT NULL,
  `hall_id` int(11) NOT NULL,
  PRIMARY KEY (`session_id`,`hall_id`),
  KEY `hall_id` (`hall_id`),
  CONSTRAINT `session_halls_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `session_halls_ibfk_2` FOREIGN KEY (`hall_id`) REFERENCES `halls` (`hall_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_halls`
--

LOCK TABLES `session_halls` WRITE;
/*!40000 ALTER TABLE `session_halls` DISABLE KEYS */;
INSERT INTO `session_halls` VALUES (1,1),(2,2),(3,3),(4,9),(5,10),(6,11),(7,5),(8,8),(10,10),(11,1),(12,1),(13,1),(14,1),(15,2);
/*!40000 ALTER TABLE `session_halls` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `session_movies`
--

DROP TABLE IF EXISTS `session_movies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `session_movies` (
  `session_id` int(11) NOT NULL,
  `movie_id` int(11) NOT NULL,
  PRIMARY KEY (`session_id`,`movie_id`),
  KEY `movie_id` (`movie_id`),
  CONSTRAINT `session_movies_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `sessions` (`session_id`) ON DELETE CASCADE,
  CONSTRAINT `session_movies_ibfk_2` FOREIGN KEY (`movie_id`) REFERENCES `movies` (`movie_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `session_movies`
--

LOCK TABLES `session_movies` WRITE;
/*!40000 ALTER TABLE `session_movies` DISABLE KEYS */;
INSERT INTO `session_movies` VALUES (1,1),(2,2),(3,3),(4,4),(5,1),(6,4),(7,1),(8,1),(10,2),(11,1),(12,1),(13,3),(14,4),(15,5);
/*!40000 ALTER TABLE `session_movies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `sessions` (
  `session_id` int(11) NOT NULL AUTO_INCREMENT,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL,
  `cinema_id` char(36) NOT NULL,
  `date` date DEFAULT NULL,
  `user_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`session_id`),
  KEY `user_id` (`user_id`),
  KEY `cinema_id` (`cinema_id`),
  CONSTRAINT `sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`),
  CONSTRAINT `sessions_ibfk_2` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`)
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
INSERT INTO `sessions` VALUES (1,'18:00:00','20:10:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-09','b04633b9-a0d4-11ee-8705-34415d418993'),(2,'19:30:00','21:30:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-10','66c20506-a0d4-11ee-8705-34415d418993'),(3,'15:00:00','17:30:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-11','66c20506-a0d4-11ee-8705-34415d418993'),(4,'20:00:00','23:00:00','e69e48a5-ae09-11ee-ab73-34415d418993','2024-02-13','66c20506-a0d4-11ee-8705-34415d418993'),(5,'19:30:00','21:30:00','e69e48a5-ae09-11ee-ab73-34415d418993','2024-02-14','66c20506-a0d4-11ee-8705-34415d418993'),(6,'21:00:00','23:30:00','e69e48a5-ae09-11ee-ab73-34415d418993','2024-02-15','b04633b9-a0d4-11ee-8705-34415d418993'),(7,'21:00:00','23:00:00','4880cec4-a0d2-11ee-8705-34415d418993','2024-02-15','b04633b9-a0d4-11ee-8705-34415d418993'),(8,'17:00:00','19:00:00','4880cec4-a0d2-11ee-8705-34415d418993','2024-02-14','b04633b9-a0d4-11ee-8705-34415d418993'),(10,'15:00:00','17:00:00','e69e48a5-ae09-11ee-ab73-34415d418993','2024-02-14','a4b25bc0-b92f-11ee-a9cd-34415d418993'),(11,'21:00:00','23:00:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-09','66c20506-a0d4-11ee-8705-34415d418993'),(12,'21:00:00','23:00:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-03-01','66c20506-a0d4-11ee-8705-34415d418993'),(13,'17:00:00','19:00:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-11','66c20506-a0d4-11ee-8705-34415d418993'),(14,'12:00:00','14:00:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-06-11','66c20506-a0d4-11ee-8705-34415d418993'),(15,'12:00:00','14:00:00','487ef5c9-a0d2-11ee-8705-34415d418993','2024-02-11','66c20506-a0d4-11ee-8705-34415d418993');
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `status`
--

DROP TABLE IF EXISTS `status`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `status` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `status` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `status`
--

LOCK TABLES `status` WRITE;
/*!40000 ALTER TABLE `status` DISABLE KEYS */;
INSERT INTO `status` VALUES (1,'pending'),(2,'confirmed'),(3,'cancelled');
/*!40000 ALTER TABLE `status` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `userroles`
--

DROP TABLE IF EXISTS `userroles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `userroles` (
  `userId` char(36) NOT NULL,
  `roleId` int(11) NOT NULL,
  PRIMARY KEY (`userId`,`roleId`),
  KEY `roleId` (`roleId`),
  CONSTRAINT `userroles_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`user_id`),
  CONSTRAINT `userroles_ibfk_2` FOREIGN KEY (`roleId`) REFERENCES `roles` (`role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `userroles`
--

LOCK TABLES `userroles` WRITE;
/*!40000 ALTER TABLE `userroles` DISABLE KEYS */;
INSERT INTO `userroles` VALUES ('0877882c-d223-11ee-a3c9-34415d418993',1),('4b0adac0-d15f-11ee-a3c9-34415d418993',1),('53c580f6-aa5c-11ee-ae5c-34415d418993',1),('53c580f6-aa5c-11ee-ae5c-34415d418993',3),('66c20506-a0d4-11ee-8705-34415d418993',2),('a4b25bc0-b92f-11ee-a9cd-34415d418993',3),('af66754a-d18b-11ee-a3c9-34415d418993',1),('b04633b9-a0d4-11ee-8705-34415d418993',3),('cdeeebd0-7cc8-11ef-86dc-5c31e1d58621',1);
/*!40000 ALTER TABLE `userroles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `users` (
  `user_id` char(36) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `cinema_id` char(36) DEFAULT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `cinema_id` (`cinema_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`cinema_id`) REFERENCES `cinemas` (`cinema_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES ('0877882c-d223-11ee-a3c9-34415d418993','test1','test1@example.com','$2y$10$GZiu8QSlMVZZ1zb93nxkFeqSwAUBIeYGg3Yd1R5i.YI.D6SWiM.3i','487ef5c9-a0d2-11ee-8705-34415d418993'),('4b0adac0-d15f-11ee-a3c9-34415d418993','test_user','test@example.com','$2y$10$hv2m6oFnpMs6sZmpyNK1r.iWEJO/CU96h7b95VjYCC5Msw.lGdn8G','487ef5c9-a0d2-11ee-8705-34415d418993'),('53c580f6-aa5c-11ee-ae5c-34415d418993','sara_smith','sara.smith@example.com','$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW','4880cec4-a0d2-11ee-8705-34415d418993'),('66c20506-a0d4-11ee-8705-34415d418993','john_doe','john.doe@example.com','$2y$10$NUgkWY5ITHem8PNlUf6xOOCeJ73mjWGgUF85BwhzpnZcTs17.l7pW','487ef5c9-a0d2-11ee-8705-34415d418993'),('a4b25bc0-b92f-11ee-a9cd-34415d418993','Callie_Hawkins','callie.hawkins@example.com','$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW','e69e48a5-ae09-11ee-ab73-34415d418993'),('af66754a-d18b-11ee-a3c9-34415d418993','backup_test','backup_test@example.com','$2y$10$WQBOPvLNNw.XDq1P94.x3eFItdOVG4Oj0DPHij2.bFxNj9OWgDY0y','487ef5c9-a0d2-11ee-8705-34415d418993'),('b04633b9-a0d4-11ee-8705-34415d418993','jane_smith','jane.smith@example.com','$2y$10$tw1OSgut/alnJEIEP.R.dOG/dyXAxgHqszO4DyjzwHfAJupWQtfqW','487ef5c9-a0d2-11ee-8705-34415d418993'),('cdeeebd0-7cc8-11ef-86dc-5c31e1d58621','test','test@test.com','$2y$10$M/WCjt8Z2e8YTY3JSk89kOu9LSmA85aUAzWVPDHEX0lJROSP3EwSi','487ef5c9-a0d2-11ee-8705-34415d418993');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2024-09-27 14:55:45
