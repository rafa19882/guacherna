-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 26-11-2025 a las 19:16:28
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
-- Base de datos: `restaurante_pedidos`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `orden` int(11) DEFAULT 0,
  `activo` tinyint(4) DEFAULT 1,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `orden`, `activo`, `fecha_creacion`) VALUES
(1, 'Entradas', 1, 1, '2025-10-30 02:07:51'),
(2, 'Burgers', 2, 1, '2025-10-30 02:07:51'),
(3, 'Asados', 3, 1, '2025-10-30 02:07:51'),
(4, 'Sandwichs', 4, 1, '2025-10-30 02:07:51'),
(5, 'Salchipapas', 5, 1, '2025-10-30 12:30:01'),
(6, 'Mazorcas', 6, 1, '2025-10-30 12:30:17'),
(7, 'Chuzos Desgranados', 7, 1, '2025-10-30 12:30:29'),
(8, 'Perros', 8, 1, '2025-10-30 12:30:45'),
(9, 'Adicionales', 9, 1, '2025-10-30 12:31:05'),
(10, 'Bebidas', 10, 1, '2025-10-30 12:31:11');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  `numero_casa` varchar(50) NOT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `total_pedidos` int(11) DEFAULT 0,
  `total_gastado` decimal(10,2) DEFAULT 0.00,
  `fecha_registro` datetime DEFAULT current_timestamp(),
  `activo` tinyint(4) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `telefono`, `nombre`, `direccion`, `numero_casa`, `fecha_nacimiento`, `total_pedidos`, `total_gastado`, `fecha_registro`, `activo`) VALUES
(1, '3001234567', 'Juan Pérez', 'Calle 45 #23-10', 'Apto 302', '1990-05-15', 5, 95000.00, '2025-10-30 02:07:51', 1),
(2, '3109876543', 'María González', 'Carrera 50 #30-45', 'Casa 12', '1985-08-22', 3, 67000.00, '2025-10-30 02:07:51', 1),
(3, '3207654321', 'Carlos Rodríguez', 'Calle 72 #15-30', 'Torre B Apto 501', '1992-12-10', 9, 186000.00, '2025-10-30 02:07:51', 1),
(4, '3122198638', 'Rafael Cervantes', 'Calle 65 # 41-41', 'APTO - 14', '1988-10-02', 23, 726000.00, '2025-10-30 02:19:28', 1),
(5, '3005005060', 'Jose Ojito', 'Calle 47 #44-152', 'APTO 304', NULL, 5, 62000.00, '2025-10-31 23:42:29', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedidos`
--

CREATE TABLE `pedidos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `estado` enum('pendiente','preparando','listo','pagado','entregado') DEFAULT 'pendiente',
  `fecha_hora` datetime DEFAULT current_timestamp(),
  `direccion_entrega` varchar(200) DEFAULT NULL,
  `numero_entrega` varchar(50) DEFAULT NULL,
  `metodo_pago` enum('nequi','efectivo') DEFAULT 'nequi'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedidos`
--

INSERT INTO `pedidos` (`id`, `cliente_id`, `total`, `estado`, `fecha_hora`, `direccion_entrega`, `numero_entrega`, `metodo_pago`) VALUES
(1, 4, 21000.00, 'entregado', '2025-10-30 12:54:42', NULL, NULL, 'nequi'),
(2, 4, 22000.00, 'entregado', '2025-10-30 13:06:09', NULL, NULL, 'nequi'),
(3, 4, 52000.00, 'entregado', '2025-10-30 13:44:58', 'Carrera 43 #42-40', 'Sena', 'nequi'),
(4, 4, 58000.00, 'entregado', '2025-10-30 16:13:44', 'Calle 65 # 41-41', '14', 'nequi'),
(5, 4, 44000.00, 'entregado', '2025-10-30 21:29:14', 'Calle 65 # 41-41', '14', 'nequi'),
(6, 4, 31000.00, 'entregado', '2025-10-30 22:55:15', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(7, 4, 30000.00, 'entregado', '2025-10-31 01:41:49', 'Calle 65 # 41-41', 'APTO - 14', 'efectivo'),
(8, 4, 106000.00, 'entregado', '2025-10-31 01:50:42', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(9, 4, 32000.00, 'entregado', '2025-10-31 22:27:56', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(10, 4, 19000.00, 'entregado', '2025-10-31 23:06:01', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(11, 4, 30000.00, 'entregado', '2025-10-31 23:41:48', 'Calle 65 # 41-41', 'APTO - 14', 'efectivo'),
(12, 5, 30000.00, 'entregado', '2025-10-31 23:42:29', 'Calle 47 #44-152', 'APTO 304', 'efectivo'),
(13, 3, 30000.00, 'entregado', '2025-10-31 23:43:10', 'Calle 72 #15-30', 'Torre B Apto 501', 'efectivo'),
(14, 4, 22000.00, 'entregado', '2025-11-07 17:59:44', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(15, 4, 30000.00, 'entregado', '2025-11-07 18:02:28', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(16, 4, 30000.00, 'entregado', '2025-11-07 20:09:40', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(17, 4, 13500.00, 'entregado', '2025-11-07 20:25:10', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(18, 5, 7000.00, 'entregado', '2025-11-07 20:38:07', 'Calle 47 #44-152', 'APTO 304', 'nequi'),
(19, 4, 13500.00, 'listo', '2025-11-07 21:09:49', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(20, 4, 23000.00, 'listo', '2025-11-07 21:18:16', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(21, 1, 14000.00, 'listo', '2025-11-07 21:27:49', 'Test', '123', 'efectivo'),
(22, 5, 14000.00, 'entregado', '2025-11-07 23:43:09', 'Calle 47 #44-152', 'APTO 304', 'nequi'),
(23, 4, 7000.00, 'pendiente', '2025-11-08 00:18:46', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(24, 5, 2000.00, 'listo', '2025-11-08 00:27:01', 'Calle 47 #44-152', 'APTO 304', 'nequi'),
(25, 4, 25000.00, 'listo', '2025-11-08 00:28:40', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(26, 4, 30000.00, 'preparando', '2025-11-26 12:51:01', 'Calle 65 # 41-41', 'APTO - 14', 'nequi'),
(27, 5, 9000.00, 'pendiente', '2025-11-26 12:53:24', 'Calle 47 #44-152', 'APTO 304', 'efectivo'),
(28, 4, 52000.00, 'pendiente', '2025-11-26 12:53:24', 'Calle 65 # 41-41', 'APTO - 14', 'nequi');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_items`
--

CREATE TABLE `pedido_items` (
  `id` int(11) NOT NULL,
  `pedido_id` int(11) NOT NULL,
  `producto_id` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notas` text DEFAULT NULL COMMENT 'Notas especiales del cliente (ej: sin salsas, sin verduras)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido_items`
--

INSERT INTO `pedido_items` (`id`, `pedido_id`, `producto_id`, `cantidad`, `precio_unitario`, `subtotal`, `notas`) VALUES
(1, 1, 1, 1, 14000.00, 14000.00, NULL),
(2, 1, 3, 1, 7000.00, 7000.00, NULL),
(3, 2, 4, 1, 15000.00, 15000.00, NULL),
(4, 2, 3, 1, 7000.00, 7000.00, NULL),
(5, 3, 10, 1, 30000.00, 30000.00, NULL),
(6, 3, 8, 1, 22000.00, 22000.00, NULL),
(7, 4, 70, 1, 16000.00, 16000.00, NULL),
(8, 4, 78, 1, 35000.00, 35000.00, NULL),
(9, 4, 100, 1, 7000.00, 7000.00, NULL),
(10, 5, 38, 1, 40000.00, 40000.00, NULL),
(11, 5, 92, 1, 4000.00, 4000.00, NULL),
(12, 6, 93, 1, 7000.00, 7000.00, NULL),
(13, 6, 30, 1, 24000.00, 24000.00, NULL),
(14, 7, 17, 1, 30000.00, 30000.00, NULL),
(15, 8, 41, 1, 80000.00, 80000.00, NULL),
(16, 8, 36, 1, 26000.00, 26000.00, NULL),
(17, 9, 95, 1, 2000.00, 2000.00, NULL),
(18, 9, 77, 1, 30000.00, 30000.00, NULL),
(19, 10, 1, 1, 14000.00, 14000.00, NULL),
(20, 10, 2, 1, 5000.00, 5000.00, NULL),
(21, 11, 17, 1, 30000.00, 30000.00, NULL),
(22, 12, 17, 1, 30000.00, 30000.00, NULL),
(23, 13, 17, 1, 30000.00, 30000.00, NULL),
(24, 14, 23, 1, 22000.00, 22000.00, NULL),
(25, 15, 57, 1, 30000.00, 30000.00, NULL),
(26, 16, 57, 1, 30000.00, 30000.00, NULL),
(27, 17, 4, 1, 13500.00, 13500.00, 'Sin ensalada'),
(28, 18, 3, 1, 7000.00, 7000.00, 'Sin queso por favor'),
(29, 19, 4, 1, 13500.00, 13500.00, NULL),
(30, 20, 53, 1, 23000.00, 23000.00, NULL),
(31, 21, 1, 1, 14000.00, 14000.00, 'PRUEBA: Sin cebolla, con extra queso'),
(32, 22, 1, 1, 14000.00, 14000.00, NULL),
(33, 23, 98, 1, 7000.00, 7000.00, NULL),
(34, 24, 95, 1, 2000.00, 2000.00, NULL),
(35, 25, 14, 1, 25000.00, 25000.00, NULL),
(36, 26, 17, 1, 30000.00, 30000.00, NULL),
(37, 27, 19, 1, 9000.00, 9000.00, NULL),
(38, 28, 36, 2, 26000.00, 52000.00, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `categoria_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `activo` tinyint(4) DEFAULT 1,
  `en_oferta` tinyint(1) DEFAULT 0,
  `precio_oferta` decimal(10,2) DEFAULT NULL,
  `fecha_inicio_oferta` datetime DEFAULT NULL,
  `fecha_fin_oferta` datetime DEFAULT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `categoria_id`, `nombre`, `descripcion`, `precio`, `imagen_url`, `activo`, `en_oferta`, `precio_oferta`, `fecha_inicio_oferta`, `fecha_fin_oferta`, `fecha_creacion`) VALUES
(1, 1, '🍛 Picada de Bollo + Chorizo', 'Deliciosa picada de bollo limpio y chorizo santandereano', 14000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 12:33:18'),
(2, 1, '🍢 Pinchos de Pollo', '150 gr de pollo', 5000.00, '🍢', 1, 0, NULL, NULL, NULL, '2025-10-30 12:48:58'),
(3, 1, '🍢 Pinchos de Carne', '150 gr de carne de res', 7000.00, '🍢', 1, 1, 6500.00, '2025-11-26 10:00:00', '2025-11-30 23:59:00', '2025-10-30 12:49:41'),
(4, 1, '🌯 Burritos', 'Pan arabe con mix de carne y verduras', 15000.00, 'assets/images/productos/producto_4_1761949049.jpg', 1, 1, 13500.00, '2025-11-02 19:57:00', '2025-12-01 20:24:00', '2025-10-30 12:56:45'),
(5, 2, '🍔 Sencilla Carne', 'Carne, verduras, salsa de la casa', 15000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 12:58:20'),
(6, 2, '🍔 Sencilla Pollo', 'pollo, verduras y salsa de la casa', 16000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 12:59:25'),
(7, 2, '🍔🍟 Carne + Papas', 'combo hamburguesa de carne + porción de papas', 20000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:01:34'),
(8, 2, '🍔🍟 Pollo + Papas', 'combo hamburguesa de pollo + porción de papas', 22000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:14:02'),
(9, 2, '🍔 Doble Carne', 'Hamburguesa con porción doble de carne de la casa, mix de verduras y salsa de la casa', 26000.00, 'assets/images/productos/producto_9_1761949871.jpg', 1, 1, 23000.00, '2025-11-01 13:54:00', '2025-11-01 23:59:00', '2025-10-30 13:18:45'),
(10, 2, '🍔 Mixta', 'Hamburguesa con porción de carne y pollo, mix de verduras y salsa de la casa', 30000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:20:06'),
(11, 2, '🍔 Gratinada', 'Hamburguesa de carne, mix de verduras y salsa de la casa bañada en queso fundido', 24000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:21:51'),
(12, 2, '🍔 Supersold', 'Hamburguesa de carne de la casa, mix de verduras y salsa de la casa', 22000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:22:39'),
(13, 2, '🍔 Guacherna', 'Hamburguesa con mix de sabores', 35000.00, '🍔', 1, 0, NULL, NULL, NULL, '2025-10-30 13:23:11'),
(14, 3, '🍖 Cerdo', '250 gr de carne de cerdo', 25000.00, '🍳', 1, 0, NULL, NULL, NULL, '2025-10-30 13:25:47'),
(15, 3, '🍗 Pollo', '250 gr de pechuga de pollo', 25000.00, '🍳', 1, 0, NULL, NULL, NULL, '2025-10-30 13:26:53'),
(16, 3, '🥩 Lomo Ancho', '250 gr de carne de res tierno', 27000.00, '🍳', 1, 0, NULL, NULL, NULL, '2025-10-30 13:27:46'),
(17, 3, '🥩 Punta Gorda', '250 gr de carne de res', 30000.00, '🍳', 1, 0, NULL, NULL, NULL, '2025-10-30 13:29:05'),
(18, 3, '🍱 Parrilla Mixta', 'Mix de carne, pollo, chorizo, butifarra acompañado de papas fritas', 35000.00, '🍳', 1, 0, NULL, NULL, NULL, '2025-10-30 13:32:38'),
(19, 4, '🥪 Sencillo', 'Delicioso pan briosh, queso, verduras y salsa de la casa', 9000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:36:35'),
(20, 4, '🥪 Pollo', 'Delicioso pan briosh, pechuga de pollo, verduras y salsa de la casa', 15000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:47:13'),
(21, 4, '🥪 Carne', 'Delicioso pan briosh, Carne de res, verduras y salsa de la casa', 18000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:47:58'),
(22, 4, '🥪 Combinado', 'Delicioso pan briosh, carne y pollo, verduras y salsa de la casa', 18000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:50:20'),
(23, 4, '🥪 Mixto', 'Delicioso pan briosh, carne, pollo, queso, verduras y salsa de la casa', 22000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:53:55'),
(24, 4, '🥪 Escoces', 'Delicioso pan briosh, queso, verduras y salsa de la casa', 19000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:57:23'),
(25, 4, '🥪 Suizo', 'Delicioso pan briosh, queso, suiza, verduras y salsa de la casa', 17000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 13:59:24'),
(26, 4, '🥪 Trifasico', 'Delicioso pan briosh, queso, carne, pollo, chorizo, butifarra verduras y salsa de la casa', 25000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 14:02:04'),
(27, 4, '🥪 Guacherna', 'Delicioso pan briosh, queso, verduras y salsa de la casa', 30000.00, '🥪', 1, 0, NULL, NULL, NULL, '2025-10-30 14:03:10'),
(28, 5, '🍛 Sencilla', 'papa,salcicha,queso,papachongo,verduras y salsa de la casa', 13000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:08:23'),
(29, 5, '🍛 Super', '2 porciones de papa,2 salchichas, queso,papa chongo, verduras y salsa de la casa', 24000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:10:54'),
(30, 5, '🍛 Combinada', 'papa, salchicha, chorizo, butifarra, queso, papachongo, verduras y salsa de la casa', 24000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:13:14'),
(31, 5, '🍛 Salchichorizo', 'papa, salchicha, chorizo ,papachongo, queso, verduras y salsa de la casa', 22000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:15:40'),
(32, 5, '🍛 Salchibutifarra', 'papa, salchicha, butifarra, papachongo, queso, verduras y salsa de la casa', 20000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:17:37'),
(33, 5, '🍛 Salchipollo', 'papa,salchicha, pollo, papachongo, queso, verduras y salsa de la casa', 23000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:22:02'),
(34, 5, '🍛 Suiza', 'papa, salchicha suiza, queso,papachongo, verduras y salsa de la casa', 21000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:24:14'),
(35, 5, '🍛 Ranchera', 'papa,salchicha ranchera,papachongo, queso, verduras y salsa de la casa', 24000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:25:45'),
(36, 5, '🍛 Mixta', 'papa, salchicha, pollo, carne, papachongo, queso, verduras y salsa de la casa', 26000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:27:22'),
(37, 5, '🍱 Salvajada Personal', 'Carne, pollo, butifarra, chorizo, acompañado de papas fritas', 25000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:48:53'),
(38, 5, '🍱 Salvajada Personal x2', 'Carne, pollo, butifarra, chorizo, acompañado de papas fritas', 40000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:49:38'),
(39, 5, '🍱 Salvajada x3', 'Carne, pollo, butifarra, chorizo, acompañado de papas fritas', 50000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:50:10'),
(40, 5, '🍱 Guacherna', 'Carne, pollo, butifarra, chorizo, acompañado de papas fritas', 60000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:50:42'),
(41, 5, '🍱 Gran Guacherna Especial', 'Carne, pollo, butifarra, chorizo, acompañado de papas fritas', 80000.00, '🍛', 1, 0, NULL, NULL, NULL, '2025-10-30 14:51:29'),
(42, 6, '🌽 Sencilla', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 14000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 14:54:13'),
(43, 6, '🌽 Chorizo', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 22000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 14:58:20'),
(44, 6, '🌽 Butifarra', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 20000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 14:59:26'),
(45, 6, '🌽 Carne', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 25000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 14:59:55'),
(46, 6, '🌽 Pollo', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 23000.00, 'assets/images/productos/producto_46_1761949973.jpg', 1, 0, NULL, NULL, NULL, '2025-10-30 15:00:39'),
(47, 6, '🌽 Suiza', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 22000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:00:59'),
(48, 6, '🌽 Mixta', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 27000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:01:26'),
(49, 6, '🌽 Trifásica', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 30000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:01:54'),
(50, 6, '🌽 Combinada', 'Maíz tierno, chorizo y butifarra, bollo limpio, mix de verduras y salsa de la casa', 26000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:02:31'),
(51, 6, '🌽 Ranchera', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 24000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:03:26'),
(52, 6, '🌽 Guacherna', 'Maíz tierno, bollo limpio, mix de verduras y salsa de la casa', 35000.00, '🌽', 1, 0, NULL, NULL, NULL, '2025-10-30 15:03:49'),
(53, 7, '🥡 Cerdo', 'Bocados de cerdo, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 23000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:09:16'),
(54, 7, '🥡 Pollo', 'Bocados de pollo, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 24000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:10:04'),
(55, 7, '🥡 Carne', 'Bocados de carne de res, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 26000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:10:39'),
(56, 7, '🥡 Mixto', 'Bocados de chorizo y butifarra, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 30000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:11:44'),
(57, 7, '🥡 Trifásico', 'Bocados de carne, pollo y cerdo, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 30000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:12:59'),
(58, 7, '🥡 Combinado', 'Bocados de chorizo y butifarra, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 22000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:13:25'),
(59, 7, '🥡 Suizo', 'Bocados de salchicha suiza, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 21000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:14:52'),
(60, 7, '🥡 Guacherna', 'Bocados de cerdo, pollo, chorizo y butifarra, bollo limpio, papita chongo, mix de verduras y salsa de la casa', 40000.00, '🍝', 1, 0, NULL, NULL, NULL, '2025-10-30 15:15:40'),
(61, 8, '🌭 Sencillo', 'Salchicha, mix de verduras, salsa de la casa', 6000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:37:39'),
(62, 8, '🌭 Plancha', 'Salchicha, mix de verduras, salsa de la casa a la plancha', 7000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:38:17'),
(63, 8, '🌭🌭 Gemelo', 'Salchicha, mix de verduras, salsa de la casa', 10000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:38:44'),
(64, 8, '🌭 Mexicano', 'Salchicha, mix de verduras, salsa de la casa', 14000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:40:29'),
(65, 8, '🌭 Hawaiano', 'Salchicha, mix de verduras, salsa de la casa', 14000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:41:04'),
(66, 8, '🌭 Choriperro', 'Salchicha, mix de verduras, salsa de la casa', 13000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:41:36'),
(67, 8, '🌭 Choributi', 'Salchicha, mix de verduras, salsa de la casa', 13000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:42:11'),
(68, 8, '🌭 Ranchero', 'Salchicha, mix de verduras, salsa de la casa', 15000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:44:06'),
(69, 8, '🌭🥓 Escocés', 'Salchicha, mix de verduras, salsa de la casa', 17000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:44:29'),
(70, 8, '🌭 Suizo', 'Salchicha, mix de verduras, salsa de la casa', 16000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:45:51'),
(71, 8, '🌭 Perripollo', 'Salchicha, mix de verduras, salsa de la casa', 16000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:46:19'),
(72, 8, '🌭🥓 Perripollo - Suiza', 'Salchicha, mix de verduras, salsa de la casa', 23000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:46:51'),
(73, 8, '🌭 Medio Suizo', 'Salchicha, mix de verduras, salsa de la casa', 10000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:47:17'),
(74, 8, '🌭 Trifásico', 'Salchicha, mix de verduras, salsa de la casa', 21000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:47:42'),
(75, 8, '🌭 Italo Suizo', 'Salchicha, mix de verduras, salsa de la casa', 25000.00, 'assets/images/productos/producto_75_1761949953.jpg', 1, 0, NULL, NULL, NULL, '2025-10-30 15:48:07'),
(76, 8, '🌭 Italo Hawaiano', 'Salchicha, mix de verduras, salsa de la casa', 19000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:48:41'),
(77, 8, '🌭 Guacherna (Pepito)', 'Salchicha, mix de verduras, salsa de la casa', 30000.00, '🌭', 1, 0, NULL, NULL, NULL, '2025-10-30 15:49:11'),
(78, 8, '🌭🥓 Guacherna (Pepito Especial)', 'Salchicha, tocineta, aguacate, huevo, mix de verduras, salsa de la casa', 35000.00, '', 1, 0, NULL, NULL, NULL, '2025-10-30 15:50:50'),
(79, 9, '🍥 Bollo', 'Porcion de Bollo limpio', 3500.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:53:53'),
(80, 9, '🌽 Maíz', 'Porción de maíz', 3000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:54:40'),
(81, 9, '🍗 Pollo', 'Porción de Pollo', 9000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:55:12'),
(82, 9, '🍖 Carne', 'Porción de carne', 9000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:55:49'),
(83, 9, '🍠 Manguera', 'Porción de salchicha manguera', 4000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:56:44'),
(84, 9, '🧀 Gratinado', 'Porción de queso fundido', 6000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:57:29'),
(85, 9, '🍣 Chorizo', 'Porción de chorizo', 4000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:58:20'),
(86, 8, '🍣 Butifarra', 'Porción de butifarra', 4000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:58:51'),
(87, 9, '🍟 Francesa', 'Porción de papas fritas', 8000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:59:18'),
(88, 9, '🥓 Tocinetas', 'Porción de tocino', 5000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 15:59:54'),
(89, 9, '🍖 Jamón', 'Porción de jamón', 3000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 16:00:32'),
(90, 9, '🧀 Mozarella', 'Porción de queso mozarella', 5000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 16:01:04'),
(91, 9, '🍠 Suiza', 'Porción de salchicha suiza', 8000.00, '🍴', 1, 0, NULL, NULL, NULL, '2025-10-30 16:01:57'),
(92, 10, '🥤 Gaseosa 400 ml', 'Bebida personal de 400 ml', 4000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:02:47'),
(93, 10, '🥤 Gaseosa Litro', 'Bebida Gaseosa presentación de 1 litro', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:05:09'),
(94, 10, '🥤 Gaseosa 1.5 Lt', 'Bebida gaseosa presentación 1.5 litros', 9000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:05:47'),
(95, 10, '🥛 Agua', 'Botella de agua', 2000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:06:55'),
(96, 10, '🧃 Limonada', 'Jugo natural de Limón', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:07:33'),
(97, 10, '🍇 Mora', 'Jugo natural de mora', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:08:16'),
(98, 10, '🍓 Fresa', 'Jugo natural de fresa', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:08:48'),
(99, 10, '🥭 Mango', 'Jugo natural de mango', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:09:34'),
(100, 10, '🍋 Maracuyá', 'Jugo natural de maracuyá', 7000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:10:13'),
(101, 10, '🧃🍒 Limonada Cerezada', 'Jugo de limón con cerezas', 8000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:10:58'),
(102, 10, '🧃🥥 Limonada de Coco', 'Jugo natural de limón con coco', 8000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:11:29'),
(103, 10, '🧃🍃 Limonada Yerbabuena', 'Jugo natural de limón con hierba buena', 8000.00, '🥤', 1, 0, NULL, NULL, NULL, '2025-10-30 16:12:23');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `telefono` (`telefono`);

--
-- Indices de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pedidos_cliente` (`cliente_id`),
  ADD KEY `idx_pedidos_estado` (`estado`);

--
-- Indices de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `producto_id` (`producto_id`),
  ADD KEY `idx_pedido_items_pedido` (`pedido_id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_productos_categoria` (`categoria_id`),
  ADD KEY `idx_productos_oferta` (`en_oferta`,`activo`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `pedidos`
--
ALTER TABLE `pedidos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT de la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=104;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `pedidos`
--
ALTER TABLE `pedidos`
  ADD CONSTRAINT `pedidos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

--
-- Filtros para la tabla `pedido_items`
--
ALTER TABLE `pedido_items`
  ADD CONSTRAINT `pedido_items_ibfk_1` FOREIGN KEY (`pedido_id`) REFERENCES `pedidos` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_items_ibfk_2` FOREIGN KEY (`producto_id`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `productos_ibfk_1` FOREIGN KEY (`categoria_id`) REFERENCES `categorias` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
