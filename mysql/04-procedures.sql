DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `deleteFilmWatched`(IN `film_id_in` INT, IN `user_in` VARCHAR(25) CHARSET utf8mb4)
DELETE FROM usuarios_peliculas_vistas
WHERE user = user_in AND film_id = film_id_in$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `insertFilmWatched`(IN `film_id_in` INT, IN `user_in` VARCHAR(25) CHARSET utf8mb4, IN `rating_in` INT)
INSERT INTO usuarios_peliculas_vistas (user, film_id, rating)
VALUES (user_in, film_id_in, rating_in)
ON DUPLICATE KEY UPDATE rating = rating_in$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `insertIgnoreFilmWatched`(IN `film_id_in` INT, IN `user_in` VARCHAR(25) CHARSET utf8mb4, IN `rating_in` INT)
INSERT IGNORE INTO usuarios_peliculas_vistas (user, film_id, rating)
VALUES (user_in, film_id_in, rating_in)$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `updateRating`(IN `film_id_in` INT)
BEGIN
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

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `updateFilmWatched`(IN `film_id_in` INT, IN `user_in` VARCHAR(45) CHARSET utf8mb4, IN `rating_in` INT)
UPDATE usuarios_peliculas_vistas
SET rating = rating_in
WHERE user = user_in AND film_id = film_id_in$$
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `updateNumMessagesForum`(IN `evento_tema_id_in` INT)
BEGIN
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
DELIMITER ;

DELIMITER $$
CREATE DEFINER=`film_swap`@`%` PROCEDURE `insertNotificationReview`(IN `user_review` VARCHAR(25) CHARSET utf8mb4, IN `film_id` INT, IN `review_id` INT)
if (EXISTS(SELECT * FROM notificaciones n WHERE n.user_review = user_review AND n.film_id = film_id)) 
 then
 	UPDATE notificaciones n SET n.review_id = review_id WHERE n.user_review = user_review AND n.film_id = film_id;
 end if$$
DELIMITER ;
