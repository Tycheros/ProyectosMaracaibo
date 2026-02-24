-- phpMyAdmin SQL Dump
-- version 4.9.0.1
-- https://www.phpmyadmin.net/
--
-- Servidor: sql110.infinityfree.com
-- Tiempo de generación: 23-02-2026 a las 21:39:37
-- Versión del servidor: 11.4.10-MariaDB
-- Versión de PHP: 7.2.22

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT = 0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `if0_41183042_bd_proyectos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades`
--

CREATE TABLE `actividades` (
  `id_actividad` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `fecha_estimada` date DEFAULT NULL,
  `estado` enum('pendiente','en_progreso','completada') DEFAULT 'pendiente',
  `evidencia_foto` varchar(255) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ajustes`
--

CREATE TABLE `ajustes` (
  `id_ajuste` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `bio` text DEFAULT NULL,
  `mostrar_telefono` tinyint(1) DEFAULT 0,
  `mostrar_comunidad` tinyint(1) DEFAULT 1,
  `metodo_contacto` varchar(20) DEFAULT 'whatsapp',
  `telegram_user` varchar(100) DEFAULT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `ajustes`
--

INSERT INTO `ajustes` (`id_ajuste`, `id_usuario`, `bio`, `mostrar_telefono`, `mostrar_comunidad`, `metodo_contacto`, `telegram_user`) VALUES
(1, 7, 'Hola, soy el creador todo poderoso.', 1, 1, 'telegram', 'SrTycheros'),
(2, 5, '', 1, 1, 'whatsapp', ''),
(3, 11, '', 1, 1, 'whatsapp', ''),
(4, 12, '', 0, 1, 'whatsapp', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `icono` varchar(50) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id_categoria`, `nombre`, `descripcion`, `icono`, `estado`) VALUES
(1, 'salud', 'Proyectos relacionados con salud comunitaria, medicina preventiva y atención médica', 'fa-heartbeat', 1),
(2, 'educacion', 'Iniciativas educativas, capacitaciones, materiales didácticos y apoyo escolar', 'fa-graduation-cap', 1),
(3, 'infraestructura', 'Mejoras en espacios públicos, reparaciones y construcción comunitaria', 'fa-building', 1),
(4, 'medio_ambiente', 'Conservación ambiental, limpieza, reforestación y reciclaje', 'fa-tree', 1),
(5, 'alimentacion', 'Seguridad alimentaria, comedores comunitarios y huertos urbanos', 'fa-utensils', 1),
(6, 'cultura', 'Actividades culturales, deportivas y recreativas para la comunidad', 'fa-theater-masks', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `chat_proyectos`
--

CREATE TABLE `chat_proyectos` (
  `id_mensaje` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `mensaje` text NOT NULL,
  `fecha_envio` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `donaciones`
--

CREATE TABLE `donaciones` (
  `id_donacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` varchar(20) NOT NULL DEFAULT 'oferta',
  `titulo` varchar(200) NOT NULL,
  `categoria` varchar(50) NOT NULL,
  `id_proyecto` int(11) DEFAULT NULL,
  `modo` enum('oferta','solicitud') NOT NULL DEFAULT 'oferta',
  `descripcion` text NOT NULL,
  `cantidad` varchar(100) NOT NULL,
  `ubicacion` varchar(200) DEFAULT NULL,
  `contacto` varchar(255) DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('ofrecida','aceptada','rechazada','entregada','cancelada','revision') DEFAULT 'ofrecida',
  `fecha_oferta` datetime DEFAULT current_timestamp(),
  `fecha_entrega` datetime DEFAULT NULL,
  `evidencia_entrega` varchar(255) DEFAULT NULL,
  `certificado_generado` tinyint(1) DEFAULT 0,
  `condiciones` text DEFAULT NULL,
  `condicion` enum('regalo','prestamo') DEFAULT 'regalo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `donaciones`
--

INSERT INTO `donaciones` (`id_donacion`, `id_usuario`, `tipo`, `titulo`, `categoria`, `id_proyecto`, `modo`, `descripcion`, `cantidad`, `ubicacion`, `contacto`, `imagen`, `estado`, `fecha_oferta`, `fecha_entrega`, `evidencia_entrega`, `certificado_generado`, `condiciones`, `condicion`) VALUES
(1, 2, 'oferta', 'Donación General', 'salud', NULL, 'oferta', 'Donación de prueba', '', 'Centro', NULL, 'assets/uploads/ejemplo.png', 'ofrecida', '2026-02-12 17:08:09', NULL, NULL, 0, NULL, 'regalo'),
(2, 2, 'oferta', 'Ibuprofeno 600mgr', 'salud', NULL, 'oferta', 'Una cajita pa los mil dolores', '', 'Cuatricentenario', NULL, 'assets/uploads/1771359989_d_RobloxScreenShot20240924_184934995.png', 'ofrecida', '2026-02-17 16:26:29', NULL, NULL, 0, NULL, 'regalo'),
(3, 2, 'oferta', 'Andadera para Edilia', 'salud', NULL, 'solicitud', 'Se necesita ayuda para una persona mayor que no puede caminar ', '', 'Francisco Eugenio Bustamante', NULL, 'assets/uploads/1771360050_d_lv_0_20260101110929.jpg', 'ofrecida', '2026-02-17 16:27:30', NULL, NULL, 0, NULL, 'regalo'),
(4, 7, 'oferta', 'Ibuprofeno', 'alimentos', NULL, 'oferta', 'uwu', '', 'jhjhj', NULL, NULL, 'ofrecida', '2026-02-18 10:47:53', NULL, NULL, 0, NULL, 'regalo'),
(5, 7, 'oferta', 'AAAUXILIOOO ME ESTAN MATAANDO', 'alimentos', NULL, 'solicitud', 'SEXO RICO', '', 'DONDE SEA, APURENSE', NULL, NULL, 'ofrecida', '2026-02-18 10:48:42', NULL, NULL, 0, NULL, 'regalo'),
(6, 7, 'oferta', 'kILO DE queso para arepas', 'alimentos', NULL, 'oferta', 'uwu', '', 'Oi osi', NULL, 'assets/uploads/1771440580_699609c46285b.png', 'ofrecida', '2026-02-18 10:49:40', NULL, NULL, 0, NULL, 'regalo'),
(7, 5, 'oferta', 'Kg de Queso', 'alimentos', NULL, 'oferta', 'Queso 1000', '', 'Barrio San Kennedy 2', NULL, NULL, 'ofrecida', '2026-02-18 17:10:05', NULL, NULL, 0, NULL, 'regalo'),
(8, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 16, 'oferta', 'El usuario ha ofrecido los siguientes insumos para el proyecto:\n- 1 x Laptop\n\n', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 14:19:32', NULL, NULL, 0, NULL, 'regalo'),
(9, 9, 'oferta', 'Aporte a Proyecto', 'materiales', 11, 'oferta', 'El usuario ha ofrecido los siguientes insumos:\n- 1 x 1 gatita, una sabana de tigre\n\ntengo una sabana de princesa, te sirve?', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 14:55:03', NULL, NULL, 0, NULL, 'regalo'),
(10, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 17, 'oferta', 'El usuario ha ofrecido los siguientes insumos:\n- 2 x sdsd\n\n', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 15:24:01', NULL, NULL, 0, NULL, 'regalo'),
(11, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 17, 'oferta', 'El usuario ha ofrecido los siguientes insumos:\n- 1 x sdsd\n\nDonde sea', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 15:26:29', NULL, NULL, 0, NULL, 'regalo'),
(12, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 17, 'oferta', 'El usuario ha ofrecido los siguientes insumos:\n- 1 x sdsd\n\nHola amor, quiero sexo luego de darte ese \"sdsd\"', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 15:48:26', NULL, NULL, 0, NULL, 'regalo'),
(13, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 16, 'oferta', 'El usuario ha ofrecido los siguientes insumos:\n- 1 x Laptop\n\nHola', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 15:49:32', NULL, NULL, 0, NULL, 'regalo'),
(14, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 16, 'oferta', 'Materiales ofrecidos:\n• 1 x Laptop\n\nNota: Holaaa', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 18:22:52', NULL, NULL, 0, NULL, 'regalo'),
(15, 7, 'oferta', 'Aporte a Proyecto', 'materiales', 16, 'oferta', 'Materiales ofrecidos:\n• 3 x Hojas Blancas\n\nNota: holaaaa q tal', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 18:23:52', NULL, NULL, 0, NULL, 'regalo'),
(16, 11, 'oferta', 'Oferta Privada', 'materiales', 16, 'oferta', 'Te han ofrecido los siguientes recursos:\n\n• 1 x Laptop\n\nNota del usuario: hola :o', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 18:32:22', NULL, NULL, 0, NULL, 'regalo'),
(17, 11, 'oferta', 'Oferta Privada', 'materiales', 16, 'oferta', 'Te han ofrecido los siguientes recursos:\n\n• 1 x Hojas Blancas\n\nNota del usuario: purbea 1', '', 'Maracaibo', NULL, NULL, 'ofrecida', '2026-02-22 18:51:55', NULL, NULL, 0, NULL, 'regalo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `habilidades`
--

CREATE TABLE `habilidades` (
  `id_habilidad` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `habilidades`
--

INSERT INTO `habilidades` (`id_habilidad`, `nombre`, `descripcion`, `categoria`) VALUES
(1, 'Primeros Auxilios', 'Conocimientos básicos de primeros auxilios y atención pre-hospitalaria', 'salud'),
(2, 'Enfermería', 'Cuidados básicos de enfermería y atención a pacientes', 'salud'),
(3, 'Docencia', 'Habilidad para enseñar y transmitir conocimientos', 'educacion'),
(4, 'Carpintería', 'Trabajos en madera y reparaciones básicas', 'infraestructura'),
(5, 'Albañilería', 'Construcción y reparación de estructuras básicas', 'infraestructura'),
(6, 'Jardinería', 'Cuidado de plantas y mantenimiento de áreas verdes', 'medio_ambiente'),
(7, 'Cocina', 'Preparación de alimentos a escala comunitaria', 'alimentacion'),
(8, 'Deportes', 'Conducción de actividades deportivas y recreativas', 'cultura'),
(9, 'Música', 'Enseñanza o ejecución de instrumentos musicales', 'cultura'),
(10, 'Informática', 'Conocimientos básicos de computación y tecnología', 'educacion'),
(11, 'Logística', 'Organización y coordinación de eventos y actividades', 'infraestructura'),
(12, 'Traducción', 'Traducción de documentos o interpretación', 'educacion');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `log_actividades`
--

CREATE TABLE `log_actividades` (
  `id_log` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `fecha_accion` datetime DEFAULT current_timestamp(),
  `ip_usuario` varchar(45) DEFAULT NULL,
  `detalles` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `log_actividades`
--

INSERT INTO `log_actividades` (`id_log`, `id_usuario`, `accion`, `tabla_afectada`, `registro_id`, `fecha_accion`, `ip_usuario`, `detalles`) VALUES
(1, 2, 'REGISTRO_USUARIO', 'usuarios', 2, '2026-02-11 21:35:00', NULL, 'Nuevo usuario: Admin Tesis'),
(2, 1, 'REGISTRO_USUARIO', 'usuarios', 1, '2026-02-11 22:26:18', NULL, 'Nuevo usuario: Admin Sistema');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id_notificacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('sistema','proyecto','voluntariado','donacion') DEFAULT 'sistema',
  `mensaje` text NOT NULL,
  `enlace` varchar(255) DEFAULT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `notificaciones`
--

INSERT INTO `notificaciones` (`id_notificacion`, `id_usuario`, `tipo`, `mensaje`, `enlace`, `leido`, `fecha_creacion`) VALUES
(1, 7, 'donacion', '¡Nuevo aporte! Alguien ofreció insumos para tu proyecto: \'Busco Gatita\'. Revisa tus donaciones.', 'perfil.html?tab=donaciones', 1, '2026-02-22 14:55:03'),
(2, 7, 'donacion', '¡Tienes ayuda! Alguien ofreció recursos para: Diseño de una tesis', 'obtener.html?id=16', 1, '2026-02-22 18:32:22'),
(3, 7, 'donacion', '¡Tienes ayuda! Alguien ofreció recursos para: Diseño de una tesis', 'obtener.html?id=17', 1, '2026-02-22 18:51:55'),
(4, 7, 'proyecto', '? ¡El proyecto \'Diseño de una tesis\' ha comenzado su ejecución! Revisa el cronograma.', 'proyecto.html?id=15', 1, '2026-02-23 17:13:09'),
(5, 9, 'proyecto', '? ¡El proyecto \'Diseño de una tesis\' ha comenzado su ejecución! Revisa el cronograma.', 'proyecto.html?id=15', 0, '2026-02-23 17:13:09'),
(6, 7, 'proyecto', '? ¡El proyecto \'sdsdsd\' ha comenzado su ejecución! Revisa el cronograma.', 'proyecto.html?id=14', 0, '2026-02-23 17:36:26');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `participaciones`
--

CREATE TABLE `participaciones` (
  `id_participacion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp()
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `participaciones`
--

INSERT INTO `participaciones` (`id_participacion`, `id_usuario`, `id_proyecto`, `fecha_registro`) VALUES
(1, 7, 12, '2026-02-21 13:12:08'),
(2, 7, 11, '2026-02-21 14:39:32'),
(3, 7, 15, '2026-02-22 14:05:21'),
(4, 9, 15, '2026-02-22 14:40:58'),
(5, 7, 17, '2026-02-22 15:26:12'),
(6, 11, 16, '2026-02-22 18:49:01'),
(7, 7, 16, '2026-02-22 19:36:51'),
(8, 7, 14, '2026-02-23 03:46:50'),
(9, 7, 10, '2026-02-23 04:07:01');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proyectos`
--

CREATE TABLE `proyectos` (
  `id_proyecto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `titulo` varchar(200) NOT NULL,
  `descripcion` text NOT NULL,
  `materiales` text DEFAULT NULL,
  `categoria` enum('salud','educacion','infraestructura','medio_ambiente','alimentacion','cultura') NOT NULL,
  `ubicacion` varchar(200) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp(),
  `estado` enum('recaudacion','en_progreso','completado','cancelado','revision') DEFAULT 'recaudacion',
  `meta_votos` int(11) DEFAULT 50,
  `meta_voluntarios` int(11) DEFAULT 10,
  `latitud` varchar(50) DEFAULT NULL,
  `longitud` varchar(50) DEFAULT NULL,
  `galeria` text DEFAULT NULL,
  `direccion_exacta` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `proyectos`
--

INSERT INTO `proyectos` (`id_proyecto`, `id_usuario`, `titulo`, `descripcion`, `materiales`, `categoria`, `ubicacion`, `imagen`, `fecha_creacion`, `estado`, `meta_votos`, `meta_voluntarios`, `latitud`, `longitud`, `galeria`, `direccion_exacta`) VALUES
(1, 2, 'Iluminación LED Calle 72', 'Sustitución del cableado y luminarias antiguas por tecnología LED solar para mejorar la seguridad nocturna de la zona comercial.', NULL, 'infraestructura', 'Calle 72 con Av. Bella Vista', NULL, '2026-02-11 21:35:00', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(2, 1, 'Prueba desde CLI', 'Descripcion de prueba', NULL, 'infraestructura', 'Zona CLI', NULL, '2026-02-11 22:37:33', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(3, 1, 'hola', 'sdsd', NULL, 'salud', 'sfddsd', 'assets/uploads/1770864685_a77cd0df4d5807e4ab37a05ac5fcc0405fd611f3.png', '2026-02-11 22:51:25', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(4, 1, 'Diseño de una tesis', 'fd', NULL, 'infraestructura', 'sfsf', NULL, '2026-02-11 22:52:13', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(5, 1, 'dsds', 'sds', NULL, 'infraestructura', 'dsdsd', NULL, '2026-02-11 23:49:23', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(6, 1, 'Casa de Abrigo Simoncito', 'Casa de cuidado en condiciones deplorables ', '10 sacos cemento, pintura', 'infraestructura', 'Barrio San Kennedy 2', NULL, '2026-02-17 18:53:17', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(7, 7, 'hola', 'wweew', 'wewewe', 'infraestructura', 'wewew', NULL, '2026-02-18 18:16:20', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(8, 5, 'Casa de Abrigo Simoncito', '123', '7913', 'educacion', 'Barrio Libertador', NULL, '2026-02-18 18:34:34', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(9, 7, 'hola', 'dsdsd', 'sdsd', 'infraestructura', 'dsds', NULL, '2026-02-18 18:39:56', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(10, 7, 'sdsdsd', 'sdsdsdsd', 'sdsd', 'infraestructura', 'sdsd', NULL, '2026-02-18 18:54:04', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(11, 7, 'Busco Gatita', 'Hola, busco Gatita que me maulle por las noches ', '1 gatita, una sabana de tigre', '', 'Francisco Eugenio Bustamante ', 'assets/uploads/1771569270_699800763d020_1000167383.jpg', '2026-02-19 22:34:30', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(12, 7, 'Ayudenme a culiar', 'Deivids necesita dinero para viajar y coger con su novia, necesitamos una donacion de 300$ ', '300$, 1 Caja de condones, aceite, 2 metros de mecate', 'salud', 'San Miguel', NULL, '2026-02-19 22:46:02', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(13, 7, 'hola', 'dfdf', '', 'infraestructura', 'dfdfd', NULL, '2026-02-22 12:58:34', 'recaudacion', 50, 10, NULL, NULL, NULL, NULL),
(14, 7, 'sdsdsd', 'sds', '', 'infraestructura', 'sdsd', NULL, '2026-02-22 13:49:38', 'en_progreso', 50, 10, '10.654', '-71.635', NULL, NULL),
(15, 7, 'Diseño de una tesis', 'Hola, ayudame a crear una tesis, no tengo los recursos y me van a raspar :c', '[{\"item\":\"Laptop\",\"cantidad\":\"1\",\"tipo\":\"Pr\\u00e9stamo\",\"recibido\":1},{\"item\":\"Hojas\",\"cantidad\":\"80\",\"tipo\":\"Donaci\\u00f3n\"}]', 'educacion', 'Francisco Eugenio Bustamante, calle 66a', 'assets/uploads/1771797117_efbcf9d7-0f0c-46a5-bc6b-c04e55396dc6-pica.png', '2026-02-22 13:51:57', 'en_progreso', 20, 2, '10.64543058021379', '-71.6936143466286', '[\"assets\\/uploads\\/1771797117_0_8f464896-6245-4ff3-bb5d-06e9ce8f5aa6.jpg\",\"assets\\/uploads\\/1771797117_1_7100a697-4036-4ba6-bab9-5aa8702ce32f.jpg\"]', NULL),
(16, 7, 'Diseño de una tesis', 'Holaaa', '[{\"item\":\"Laptop\",\"cantidad\":\"1\",\"tipo\":\"Préstamo\"},{\"item\":\"Hojas Blancas\",\"cantidad\":\"40\",\"tipo\":\"Donación\"}]', 'educacion', 'Francisco Eugenio Bustamante, calle 66a', 'assets/uploads/1771797239_8f464896-6245-4ff3-bb5d-06e9ce8f5aa6.jpg', '2026-02-22 13:53:59', 'recaudacion', 11, 2, '10.638465465659705', '-71.68688535690309', NULL, NULL),
(17, 7, 'Diseño de una tesissds', 'sddsd', '[{\"item\":\"sdsd\",\"cantidad\":\"2\",\"tipo\":\"Donaci\\u00f3n\",\"recibido\":2}]', 'educacion', 'Cristo de Aranza', 'assets/uploads/1771802494_[1241] avatar 𓂃꙳⋆ (1).jpg', '2026-02-22 15:21:34', 'recaudacion', 10, 2, '10.607294554665343', '-71.63060188293458', NULL, 'sdsdsd');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reportes`
--

CREATE TABLE `reportes` (
  `id_reporte` int(11) NOT NULL,
  `id_proyecto` int(11) DEFAULT NULL,
  `id_usuario_reportado` int(11) DEFAULT NULL,
  `id_donacion` int(11) DEFAULT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_reporte` enum('avance','problema','final') NOT NULL,
  `descripcion` text NOT NULL,
  `porcentaje_avance` decimal(5,2) DEFAULT 0.00,
  `fecha_reporte` datetime DEFAULT current_timestamp(),
  `evidencias` text DEFAULT NULL,
  `actividades_realizadas` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `reportes`
--

INSERT INTO `reportes` (`id_reporte`, `id_proyecto`, `id_usuario_reportado`, `id_donacion`, `id_usuario`, `tipo_reporte`, `descripcion`, `porcentaje_avance`, `fecha_reporte`, `evidencias`, `actividades_realizadas`) VALUES
(1, 17, NULL, NULL, 7, '', 'uwu', '0.00', '2026-02-23 04:36:22', NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `seguimientos`
--

CREATE TABLE `seguimientos` (
  `id_seguimiento` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `fecha_seguimiento` datetime DEFAULT current_timestamp(),
  `notificaciones_activas` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `cedula` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` text DEFAULT NULL,
  `comunidad` varchar(100) DEFAULT NULL,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `ultimo_acceso` datetime DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido') DEFAULT 'activo',
  `rol` enum('usuario','donante','voluntario','administrador') DEFAULT 'usuario',
  `habilidades` text DEFAULT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `email`, `password_hash`, `cedula`, `telefono`, `direccion`, `comunidad`, `fecha_registro`, `ultimo_acceso`, `estado`, `rol`, `habilidades`, `foto_perfil`) VALUES
(1, 'Admin Sistema', 'admin@sistema.com', 'hash_ficticio', NULL, NULL, NULL, NULL, '2026-02-11 22:26:18', NULL, 'activo', 'administrador', NULL, NULL),
(2, 'Admin Tesis', 'admin@tesis.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '04121703213', NULL, NULL, '2026-02-11 21:35:00', NULL, 'activo', 'administrador', 'Logística,Construcción', 'assets/avatars/avatar8.png'),
(5, 'Armando Machado', 'machadoarmando04@gmail.com', '$2y$10$H20FowTNJqgwVuxMETGQieDPkwYB2dNviDVuqZ5WjBd4gKR7.a/kW', '27722771', '04146723635', 'Barrio Libertador', 'Antonio Borjas Romero', '2026-02-17 18:35:20', NULL, 'activo', 'usuario', 'Docencia,Construcción,Arte', 'assets/avatars/avatar7.png'),
(6, 'Victor Rodriguez', 'artedevic@gmail.com', '$2y$10$EUHkzf2VZ6BNoW0.BVgKCuJsYxUyhGZHXXOZLRGQtzz5sIkiH4EXm', '30806888', '04226457873', 'La Victoria', 'Caracciolo Parra Pérez', '2026-02-17 18:39:11', NULL, 'activo', 'usuario', 'Arte', 'assets/avatars/avatar1.png'),
(7, 'Gabriel Gutierrez', 'enjerugabo@gmail.com', '$2y$10$MdZiijNjpCggL/YJg/6nYOpTkleqH49v1kUiUIsx3d2GaJ/X67oZK', '30249838', '04121703213', 'Circunvalacion 3', 'Francisco Eugenio Bustamante', '2026-02-17 19:03:31', NULL, 'activo', 'usuario', 'Logística,Construcción,Deportes', 'assets/avatars/avatar8.png'),
(8, 'josueh levit pina hernan', 'Josueh39479994@gmail.co', '$2y$10$G6KgLhZNaLvqkEvpnysqaevVA1bePwPzg9pQ3E7XLOBd18ZH8YTce', 'v30479994', '04246687481', 'barrio puerto rico', 'Cacique Mara', '2026-02-18 09:24:00', NULL, 'activo', 'usuario', '', 'assets/avatars/avatar10.png'),
(9, 'Mainerys Marcano', 'mainerysm@gmail.com', '$2y$10$a85zZKU8O2AAuyq/Z3XWDutCQ0cJYQD/2083RsWMyq.OHMmrvjCla', '30550768', '04146395341', 'Las lomas', 'Raúl Leoni', '2026-02-21 17:29:53', NULL, 'activo', 'usuario', 'Logística', 'assets/avatars/avatar3.png'),
(11, 'Tester de Sistema', 'test@test', '$2y$10$yxtx2jUiFOhlVBCbHZJdcOszXvOSKEUSlXLIb4LuCAsDu/LW054qy', '001', '04121703213', 'Calle 66a', 'Francisco Eugenio Bustamante', '2026-02-22 18:28:47', NULL, 'activo', 'usuario', 'Logística', 'assets/avatars/avatar4.png'),
(12, 'Victor', 'victorino080402@gmail.com', '$2y$10$KQrVLnaqf21j3JTtPBFRB.ghRqWYWgdnQ5wHUF4i474i.4tOe3cMK', '30971648', '04246455584', 'La Victoria', 'Coquivacoa', '2026-02-23 11:56:34', NULL, 'activo', 'usuario', '', 'assets/avatars/avatar7.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `votos`
--

CREATE TABLE `votos` (
  `id_voto` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_proyecto` int(11) NOT NULL,
  `tipo_voto` enum('positivo','negativo','abstencion') NOT NULL,
  `fecha_voto` datetime DEFAULT current_timestamp(),
  `ip_voto` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `votos`
--

INSERT INTO `votos` (`id_voto`, `id_usuario`, `id_proyecto`, `tipo_voto`, `fecha_voto`, `ip_voto`) VALUES
(1, 2, 5, 'positivo', '2026-02-12 00:23:24', NULL),
(2, 2, 4, 'negativo', '2026-02-12 00:26:28', NULL),
(3, 2, 1, 'positivo', '2026-02-12 00:26:49', NULL),
(5, 1, 3, 'positivo', '2026-02-12 00:35:25', NULL),
(6, 2, 3, 'negativo', '2026-02-12 09:05:54', NULL),
(10, 2, 2, 'positivo', '2026-02-12 09:06:45', NULL),
(19, 7, 6, 'positivo', '2026-02-18 18:27:08', NULL),
(21, 5, 6, 'negativo', '2026-02-18 18:33:22', NULL),
(25, 7, 4, 'positivo', '2026-02-18 18:35:09', NULL),
(28, 7, 11, 'positivo', '2026-02-19 22:40:16', NULL),
(29, 7, 12, 'positivo', '2026-02-23 04:03:54', NULL),
(30, 7, 15, 'positivo', '2026-02-22 13:52:37', NULL),
(31, 7, 16, 'positivo', '2026-02-22 13:54:12', NULL),
(32, 9, 15, 'positivo', '2026-02-22 14:40:17', NULL),
(33, 9, 11, 'positivo', '2026-02-22 14:54:20', NULL),
(34, 7, 17, 'positivo', '2026-02-22 17:36:17', NULL),
(36, 7, 14, 'negativo', '2026-02-23 04:22:25', NULL),
(37, 7, 1, 'positivo', '2026-02-23 04:33:11', NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades`
--
ALTER TABLE `actividades`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  ADD PRIMARY KEY (`id_ajuste`),
  ADD UNIQUE KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id_categoria`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_estado_categoria` (`estado`);

--
-- Indices de la tabla `chat_proyectos`
--
ALTER TABLE `chat_proyectos`
  ADD PRIMARY KEY (`id_mensaje`),
  ADD KEY `id_proyecto` (`id_proyecto`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD PRIMARY KEY (`id_donacion`),
  ADD KEY `idx_estado_donacion` (`estado`),
  ADD KEY `idx_tipo_donacion` (`modo`),
  ADD KEY `idx_fecha_oferta` (`fecha_oferta`),
  ADD KEY `idx_usuario_donacion` (`id_usuario`),
  ADD KEY `idx_proyecto_donacion` (`id_proyecto`),
  ADD KEY `idx_donaciones_estado_fecha` (`estado`,`fecha_oferta`);

--
-- Indices de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  ADD PRIMARY KEY (`id_habilidad`),
  ADD UNIQUE KEY `nombre` (`nombre`),
  ADD KEY `idx_categoria_habilidad` (`categoria`);

--
-- Indices de la tabla `log_actividades`
--
ALTER TABLE `log_actividades`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `idx_fecha_accion` (`fecha_accion`),
  ADD KEY `idx_accion` (`accion`),
  ADD KEY `idx_usuario_log` (`id_usuario`),
  ADD KEY `idx_tabla_afectada` (`tabla_afectada`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id_notificacion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `participaciones`
--
ALTER TABLE `participaciones`
  ADD PRIMARY KEY (`id_participacion`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_proyecto` (`id_proyecto`);

--
-- Indices de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD PRIMARY KEY (`id_proyecto`),
  ADD KEY `idx_estado_proyecto` (`estado`),
  ADD KEY `idx_categoria` (`categoria`),
  ADD KEY `idx_ubicacion` (`ubicacion`),
  ADD KEY `idx_fecha_creacion` (`fecha_creacion`),
  ADD KEY `idx_usuario_proyecto` (`id_usuario`),
  ADD KEY `idx_proyectos_estado_fecha` (`estado`,`fecha_creacion`);

--
-- Indices de la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD PRIMARY KEY (`id_reporte`),
  ADD KEY `idx_tipo_reporte` (`tipo_reporte`),
  ADD KEY `idx_fecha_reporte` (`fecha_reporte`),
  ADD KEY `idx_proyecto_reporte` (`id_proyecto`),
  ADD KEY `idx_usuario_reporte` (`id_usuario`);

--
-- Indices de la tabla `seguimientos`
--
ALTER TABLE `seguimientos`
  ADD PRIMARY KEY (`id_seguimiento`),
  ADD UNIQUE KEY `unique_seguimiento_usuario_proyecto` (`id_usuario`,`id_proyecto`),
  ADD KEY `idx_fecha_seguimiento` (`fecha_seguimiento`),
  ADD KEY `idx_usuario_seguimiento` (`id_usuario`),
  ADD KEY `idx_proyecto_seguimiento` (`id_proyecto`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_cedula` (`cedula`),
  ADD KEY `idx_comunidad` (`comunidad`),
  ADD KEY `idx_estado` (`estado`),
  ADD KEY `idx_rol` (`rol`);

--
-- Indices de la tabla `votos`
--
ALTER TABLE `votos`
  ADD PRIMARY KEY (`id_voto`),
  ADD UNIQUE KEY `unique_voto_usuario_proyecto` (`id_usuario`,`id_proyecto`),
  ADD KEY `idx_tipo_voto` (`tipo_voto`),
  ADD KEY `idx_fecha_voto` (`fecha_voto`),
  ADD KEY `idx_usuario_voto` (`id_usuario`),
  ADD KEY `idx_proyecto_voto` (`id_proyecto`),
  ADD KEY `idx_votos_proyecto_tipo` (`id_proyecto`,`tipo_voto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades`
--
ALTER TABLE `actividades`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `ajustes`
--
ALTER TABLE `ajustes`
  MODIFY `id_ajuste` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `chat_proyectos`
--
ALTER TABLE `chat_proyectos`
  MODIFY `id_mensaje` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `donaciones`
--
ALTER TABLE `donaciones`
  MODIFY `id_donacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `habilidades`
--
ALTER TABLE `habilidades`
  MODIFY `id_habilidad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `log_actividades`
--
ALTER TABLE `log_actividades`
  MODIFY `id_log` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id_notificacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `participaciones`
--
ALTER TABLE `participaciones`
  MODIFY `id_participacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `proyectos`
--
ALTER TABLE `proyectos`
  MODIFY `id_proyecto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `reportes`
--
ALTER TABLE `reportes`
  MODIFY `id_reporte` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `seguimientos`
--
ALTER TABLE `seguimientos`
  MODIFY `id_seguimiento` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `votos`
--
ALTER TABLE `votos`
  MODIFY `id_voto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=38;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `donaciones`
--
ALTER TABLE `donaciones`
  ADD CONSTRAINT `donaciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `donaciones_ibfk_2` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_donacion_proyecto` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`);

--
-- Filtros para la tabla `proyectos`
--
ALTER TABLE `proyectos`
  ADD CONSTRAINT `proyectos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `reportes`
--
ALTER TABLE `reportes`
  ADD CONSTRAINT `reportes_ibfk_1` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON DELETE CASCADE,
  ADD CONSTRAINT `reportes_ibfk_2` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `seguimientos`
--
ALTER TABLE `seguimientos`
  ADD CONSTRAINT `seguimientos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `seguimientos_ibfk_2` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON DELETE CASCADE;

--
-- Filtros para la tabla `votos`
--
ALTER TABLE `votos`
  ADD CONSTRAINT `votos_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`) ON DELETE CASCADE,
  ADD CONSTRAINT `votos_ibfk_2` FOREIGN KEY (`id_proyecto`) REFERENCES `proyectos` (`id_proyecto`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
