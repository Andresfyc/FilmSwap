-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 28-04-2021 a las 23:44:34
-- Versión del servidor: 10.4.17-MariaDB
-- Versión de PHP: 7.4.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `film_swap_2`
--

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`root`@`%` PROCEDURE `updateNumMessagesForum` (`evento_tema_id_in` INT)  BEGIN
	DECLARE total_messages INT;
    
    SELECT COUNT(*)
    INTO total_messages
    FROM foro_mensajes
    WHERE evento_tema = evento_tema_id_in;
    
    UPDATE foro_eventos_temas 
	SET 
		num_messages = total_messages
	WHERE
		id = evento_tema_id_in;
END$$

CREATE DEFINER=`root`@`%` PROCEDURE `updateRating` (`film_id_in` INT)  BEGIN
	DECLARE average DECIMAL(10,2);
    
    SELECT AVG(stars)
    INTO average
    FROM reviews
    WHERE film_id = film_id_in;
    
    UPDATE peliculas 
	SET 
		rating = average
	WHERE
		id = film_id_in;
    
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actores_directores`
--

CREATE TABLE `actores_directores` (
  `id` int(11) NOT NULL,
  `actor_director` tinyint(4) NOT NULL DEFAULT 0,
  `name` varchar(45) NOT NULL,
  `description` longtext NOT NULL,
  `birth_date` date NOT NULL,
  `nationality` varchar(45) NOT NULL,
  `image` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='El campo ''actor_director'' se pondrá a 0 si es un actor, y a 1 si es director.';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `amigos`
--

CREATE TABLE `amigos` (
  `id` int(11) NOT NULL,
  `user` varchar(25) NOT NULL,
  `friend` varchar(25) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_eventos_temas`
--

CREATE TABLE `foro_eventos_temas` (
  `id` int(11) NOT NULL,
  `name` varchar(45) NOT NULL,
  `description` longtext NOT NULL,
  `time` timestamp NULL DEFAULT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  `num_messages` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Si se incluye fecha (time), se considerará que es un evento, si no será un tema.';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `foro_mensajes`
--

CREATE TABLE `foro_mensajes` (
  `id` int(11) NOT NULL,
  `evento_tema` int(11) NOT NULL,
  `user` varchar(25) NOT NULL,
  `text` longtext NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Disparadores `foro_mensajes`
--
DELIMITER $$
CREATE TRIGGER `foro_mensajes_num_AFTER_DELETE` AFTER DELETE ON `foro_mensajes` FOR EACH ROW CALL updateNumMessagesForum(OLD.evento_tema)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `foro_mensajes_num_AFTER_INSERT` AFTER INSERT ON `foro_mensajes` FOR EACH ROW CALL updateNumMessagesForum(NEW.evento_tema)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `foro_mensajes_num_AFTER_UPDATE` AFTER UPDATE ON `foro_mensajes` FOR EACH ROW CALL updateNumMessagesForum(NEW.evento_tema)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notificaciones`
--

CREATE TABLE `notificaciones` (
  `id` int(11) NOT NULL,
  `user_review` varchar(25) NOT NULL,
  `user_notify` varchar(25) NOT NULL,
  `film_id` int(11) NOT NULL,
  `date_created` date NOT NULL,
  `review_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Si el campo de la review (review) está vacío, entonces el usuario que se espera que comente todavía no ha dejado su review. En caso de que este escriba el comentario, este se añadirá al campo review (review) y le aparecerá la notificación al usuario que estaba interesado.';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peliculas`
--

CREATE TABLE `peliculas` (
  `id` int(11) NOT NULL,
  `title` varchar(45) NOT NULL,
  `image` varchar(45) DEFAULT NULL,
  `date_released` date NOT NULL,
  `duration` int(11) NOT NULL,
  `country` varchar(45) DEFAULT NULL,
  `plot` longtext NOT NULL,
  `rating` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `peliculas_actores_directores`
--

CREATE TABLE `peliculas_actores_directores` (
  `id` int(11) NOT NULL,
  `film_id` int(11) NOT NULL,
  `actor_director_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user` varchar(25) NOT NULL,
  `film_id` int(11) NOT NULL,
  `review` longtext NOT NULL,
  `stars` int(11) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Disparadores `reviews`
--
DELIMITER $$
CREATE TRIGGER `reviews_rate_AFTER_DELETE` AFTER DELETE ON `reviews` FOR EACH ROW CALL updateRating (OLD.film_id)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `reviews_rate_AFTER_INSERT` AFTER INSERT ON `reviews` FOR EACH ROW CALL updateRating (NEW.film_id)
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `reviews_rate_AFTER_UPDATE` AFTER UPDATE ON `reviews` FOR EACH ROW CALL updateRating (NEW.film_id)
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `user` varchar(25) NOT NULL,
  `password` varchar(255) NOT NULL,
  `name` varchar(45) DEFAULT NULL,
  `image` varchar(45) DEFAULT NULL,
  `date_joined` date NOT NULL,
  `watching` int(11) DEFAULT NULL,
  `admin` tinyint(4) NOT NULL DEFAULT 0,
  `content_manager` tinyint(4) NOT NULL DEFAULT 0,
  `moderator` tinyint(4) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='La columna ''watching'' hará la función de estado del usuario. En caso de que esté viendo una película, el id de esta aparecerá en este campo. En caso de no estar viendo nada, este campo estará en NULL';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_actores_directores`
--

CREATE TABLE `usuarios_actores_directores` (
  `id` int(11) NOT NULL,
  `user` varchar(45) NOT NULL,
  `actores_directores_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actores_directores`
--
ALTER TABLE `actores_directores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indices de la tabla `amigos`
--
ALTER TABLE `amigos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_usuarios_amigos` (`friend`),
  ADD KEY `fk_amigos_usuarios` (`user`);

--
-- Indices de la tabla `foro_eventos_temas`
--
ALTER TABLE `foro_eventos_temas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indices de la tabla `foro_mensajes`
--
ALTER TABLE `foro_mensajes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `evento_tema_idx` (`evento_tema`),
  ADD KEY `user_idx` (`user`);

--
-- Indices de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `notificaciones_usuarios_comment_idx` (`user_review`),
  ADD KEY `notificaciones_usuarios_notify_idx` (`user_notify`),
  ADD KEY `notificaciones_peliculas_idx` (`film_id`),
  ADD KEY `notificaciones_reviews_idx` (`review_id`);

--
-- Indices de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`);

--
-- Indices de la tabla `peliculas_actores_directores`
--
ALTER TABLE `peliculas_actores_directores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `actor_id_idx` (`actor_director_id`),
  ADD KEY `film_id_idx` (`film_id`),
  ADD KEY `actor_film_id_idx` (`film_id`);

--
-- Indices de la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `user_idx` (`user`),
  ADD KEY `film_id_idx` (`film_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`user`),
  ADD UNIQUE KEY `user_UNIQUE` (`user`),
  ADD KEY `watching_film_id_idx` (`watching`);

--
-- Indices de la tabla `usuarios_actores_directores`
--
ALTER TABLE `usuarios_actores_directores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_UNIQUE` (`id`),
  ADD KEY `fk_actores_directores_usuarios_idx` (`user`),
  ADD KEY `fk_usuarios_actores_directores_idx` (`actores_directores_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actores_directores`
--
ALTER TABLE `actores_directores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `amigos`
--
ALTER TABLE `amigos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_eventos_temas`
--
ALTER TABLE `foro_eventos_temas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `foro_mensajes`
--
ALTER TABLE `foro_mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `peliculas`
--
ALTER TABLE `peliculas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `peliculas_actores_directores`
--
ALTER TABLE `peliculas_actores_directores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios_actores_directores`
--
ALTER TABLE `usuarios_actores_directores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `amigos`
--
ALTER TABLE `amigos`
  ADD CONSTRAINT `fk_amigos_usuarios` FOREIGN KEY (`user`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_amigos` FOREIGN KEY (`friend`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `foro_mensajes`
--
ALTER TABLE `foro_mensajes`
  ADD CONSTRAINT `fk_foro_mensajes_eventos_temas` FOREIGN KEY (`evento_tema`) REFERENCES `foro_eventos_temas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_foro_mensajes_usuarios` FOREIGN KEY (`user`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notificaciones`
--
ALTER TABLE `notificaciones`
  ADD CONSTRAINT `fk_notificaciones_peliculas` FOREIGN KEY (`film_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificaciones_reviews` FOREIGN KEY (`review_id`) REFERENCES `reviews` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificaciones_usuarios_notify` FOREIGN KEY (`user_notify`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notificaciones_usuarios_review` FOREIGN KEY (`user_review`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `peliculas_actores_directores`
--
ALTER TABLE `peliculas_actores_directores`
  ADD CONSTRAINT `fk_actores_directores_peliculas` FOREIGN KEY (`film_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_peliculas_actores_directores` FOREIGN KEY (`actor_director_id`) REFERENCES `actores_directores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_peliculas` FOREIGN KEY (`film_id`) REFERENCES `peliculas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_review_usuarios` FOREIGN KEY (`user`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fk_usuarios_peliculas` FOREIGN KEY (`watching`) REFERENCES `peliculas` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios_actores_directores`
--
ALTER TABLE `usuarios_actores_directores`
  ADD CONSTRAINT `fk_actores_directores_usuarios` FOREIGN KEY (`user`) REFERENCES `usuarios` (`user`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usuarios_actores_directores` FOREIGN KEY (`actores_directores_id`) REFERENCES `actores_directores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
