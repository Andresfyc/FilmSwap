<?php
namespace es\ucm\fdi\aw\usuarios;

use es\ucm\fdi\aw\Aplicacion as App;
use es\ucm\fdi\aw\peliculas\Pelicula;
use es\ucm\fdi\aw\notificaciones\Notificacion;


class Usuario
{

    public static function login($user, $password)
    {
        $usuario = self::buscaUsuario($user);
        if ($usuario && $usuario->compruebaPassword($password)) {
            return $usuario;
        }
        return false;
    }

    public static function buscaUsuario($user)
    {
        $app = App::getSingleton();
        $conn = $app->conexionBd();
        $query = sprintf("SELECT * FROM usuarios U WHERE U.user = '%s'", $conn->real_escape_string($user));
        $rs = $conn->query($query);
        $result = false;
        if ($rs) {
            if ( $rs->num_rows == 1) {
                $fila = $rs->fetch_assoc();
                $usuario = new Usuario($fila['user'], $fila['password'], $fila['name'], $fila['image'], $fila['date_joined'], $fila['watching'], $fila['admin'], $fila['content_manager'], $fila['moderator'], $fila['premium'], $fila['premium_validity']);
                $result = $usuario;
            }
            $rs->free();
        } else {
            echo "Error al consultar en la BD: (" . $conn->errno . ") " . utf8_encode($conn->error);
            exit();
        }
        return $result;
    }
    
    public static function busqueda($search)
    {
		$result = [];

        $app = App::getSingleton();
        $conn = $app->conexionBd();
        $query = sprintf("SELECT * FROM usuarios U WHERE U.user LIKE '%s' OR U.name LIKE '%s'", 
        '%'.$conn->real_escape_string($search).'%',
        '%'.$conn->real_escape_string($search).'%');

		$rs = $conn->query($query);
		if ($rs) {
		  while($fila = $rs->fetch_assoc()) {
			$result[] = new Usuario($fila['user'], $fila['password'], $fila['name'], $fila['image'], $fila['date_joined'], $fila['watching'], $fila['admin'], $fila['content_manager'], $fila['moderator'], $fila['premium'], $fila['premium_validity']);
		  }
		  $rs->free();
		}

		return $result;
    }
    
    public static function crea($user, $password, $name, $image, $date_joined, $watching, $admin, $content_manager, $moderator,$premium)
    {

        $usuario = self::buscaUsuario($user);
        if ($usuario) {
            return false;
        }
        $image = $image == NULL ? "user_logged.png" : $image;
        $usuario = new Usuario($user, self::hashPassword($password), $name, $image, $date_joined, $watching, $admin, $content_manager, $moderator, $premium, null);
        return self::guarda($usuario);
    }

    public static function editar($user, $password, $passwordComprobar, $name, $image, $admin, $content_manager, $moderator, $esAdmin, $isNotSelf=false)
    {
        $usuario = self::buscaUsuario($user);
        if ($usuario && ($usuario->compruebaPassword($passwordComprobar) || ($esAdmin && $isNotSelf))) {
            $name = $name ?? $usuario->name;
            $image = strlen($image) < 1 ? $usuario->image : $image;
            $password = strlen($password) < 1 ? $usuario->password : self::hashPassword($password);
            $admin = $admin ?? $usuario->admin;
            $content_manager = $content_manager ??  $usuario->content_manager;
            $moderator = $moderator ?? $usuario->moderator;
            $usuario = new Usuario($user, $password, $name, $image, null, null, $admin, $content_manager, $moderator, $usuario->premium,$usuario->premiumValidity);

            return self::guarda($usuario);
        }
        return false;
    }
    
    private static function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
    
    public static function guarda($usuario)
    {
        if (self::buscaUsuario($usuario->user)) {
            return self::actualiza($usuario);
        }
        return self::inserta($usuario);
    }
    
    private static function inserta($usuario)
    {
        $app = App::getSingleton();
        $conn = $app->conexionBd();
        $query=sprintf("INSERT INTO usuarios(user, password, name, image, date_joined, admin, content_manager, moderator) VALUES('%s', '%s', '%s', '%s', CURDATE(), '%s', '%s', '%s')"
            , $conn->real_escape_string($usuario->user)
            , $conn->real_escape_string($usuario->password)
            , $conn->real_escape_string($usuario->name)
            , $conn->real_escape_string($usuario->image)
            , $conn->real_escape_string($usuario->admin)
            , $conn->real_escape_string($usuario->content_manager)
            , $conn->real_escape_string($usuario->moderator));
        if ( $conn->query($query) ) {
            $usuario->id = $conn->insert_id;
        } else {
            echo "Error al insertar en la BD: (" . $conn->errno . ") " . utf8_encode($conn->error);
            exit();
        }
        
        return $usuario;
    }
    
    private static function actualiza($usuario)
    {
        $app = App::getSingleton();
        $conn = $app->conexionBd();
        $query=sprintf("UPDATE usuarios U SET password='%s', name='%s', image='%s',  admin='%s', content_manager='%s', moderator='%s' WHERE U.user='%s'"
            , $conn->real_escape_string($usuario->password)
            , $conn->real_escape_string($usuario->name)
            , $conn->real_escape_string($usuario->image)
            , $usuario->admin
            , $usuario->content_manager
            , $usuario->moderator
            , $conn->real_escape_string($usuario->user));
        if ( $conn->query($query) ) {
            if ( $conn->affected_rows != 1) {
                echo "No se ha podido actualizar el usuario: " . $usuario->user;
                exit();
            }
        } else {
            echo "Error al insertar en la BD: (" . $conn->errno . ") " . utf8_encode($conn->error);
            exit();
        }
        
        return $usuario;
    }
	
	public static function listaAmigos($user, $limit=NULL)
	{
		$result = [];

        $app = App::getSingleton();
        $conn = $app->conexionBd();
		$query = sprintf("SELECT u2.* FROM amigos u JOIN usuarios u1 ON u1.user = u.user JOIN usuarios u2 ON u2.user = u.friend WHERE u1.user = '%s'", $conn->real_escape_string($user));
		if($limit) {
		  $query = $query . ' LIMIT %d';
		  $query = sprintf($query, $limit);
		}

		$rs = $conn->query($query);
		if ($rs) {
		  while($fila = $rs->fetch_assoc()) {
			$result[] = new Usuario($fila['user'], $fila['password'], $fila['name'], $fila['image'], $fila['date_joined'], $fila['watching'], $fila['admin'], $fila['content_manager'], $fila['moderator'], $fila['premium'], $fila['premium_validity']);
		  }
		  $rs->free();
		}

		return $result;
	}
	
	public static function listaUsuarios($limit=NULL)
	{
		$result = [];

        $app = App::getSingleton();
        $conn = $app->conexionBd();
		$query = "SELECT * FROM usuarios";
		if($limit) {
		  $query = $query . ' LIMIT %d';
		  $query = sprintf($query, $limit);
		}

		$rs = $conn->query($query);
		if ($rs) {
		  while($fila = $rs->fetch_assoc()) {
			$result[] = new Usuario($fila['user'], $fila['password'], $fila['name'], $fila['image'], $fila['date_joined'], $fila['watching'], $fila['admin'], $fila['content_manager'], $fila['moderator'], $fila['premium'], $fila['premium_validity']);
		  }
		  $rs->free();
		}

		return $result;
	}

    public static function isUsuarioAmigo($user, $amigo)
    {
      $app = App::getSingleton();
      $conn = $app->conexionBd();
      $query = sprintf("SELECT * FROM amigos WHERE user = '%s' and friend = '%s'", $user, $amigo);
      $rs = $conn->query($query);
      return $rs->num_rows;
    }
  
    public static function addAmigo($user, $amigo)
    {
      $result = false;
  
      $app = App::getSingleton();
      $conn = $app->conexionBd();
      $query = sprintf("INSERT INTO amigos(user, friend) VALUES('%s', '%s')"
      , $conn->real_escape_string($user)
      , $conn->real_escape_string($amigo));
      $result = $conn->query($query);
      if (!$result) {
        error_log($conn->error);  
      }
  
      return $result;
    }
  
    public static function delAmigo($user, $amigo)
    {
      $result = false;
  
      $app = App::getSingleton();
      $conn = $app->conexionBd();
      $query = sprintf("DELETE FROM amigos WHERE user = '%s' and friend = '%s'", $user, $amigo);
      $result = $conn->query($query);
      if (!$result) {
        error_log($conn->error);
      } else if ($conn->affected_rows != 1) {
        error_log("Se han borrado '$conn->affected_rows' !");
      }
  
      return $result;
    }


    public static function acPremium($meses, $user)
    {
        $result = false;

        $app = App::getSingleton();
        $conn = $app->conexionBd();

        $query = sprintf("UPDATE usuarios SET premium_validity = DATE_ADD(NOW(), INTERVAL %d *1 MONTH) , premium = 1  WHERE `user` = '%s' "
            , $meses
            , $conn->real_escape_string($user));
        $result = $conn->query($query);
        if (!$result) {
            error_log($conn->error);
        }

        return $result;
    }


    public static function setWatching($user, $id=NULL)
    {
        $result = false;

        $app = App::getSingleton();
        $conn = $app->conexionBd();

        if($id){
            $query = sprintf("UPDATE usuarios SET watching = %d  WHERE user = '%s' "
                , $id
                , $conn->real_escape_string($user));
        } else {
            $query = sprintf("UPDATE usuarios SET watching = null  WHERE user = '%s' "
                , $conn->real_escape_string($user));
        }
        $result = $conn->query($query);
        if (!$result) {
            error_log($conn->error);
        }

        return $result;
    }


    public static function borraPorUser($user)
    {
      $result = false;
  
      $app = App::getSingleton();
      $conn = $app->conexionBd();
      $query = sprintf("DELETE FROM usuarios WHERE user = '%s'", $user);
      $result = $conn->query($query);
      if (!$result) {
        error_log($conn->error);
      } else if ($conn->affected_rows != 1) {
        error_log("Se han borrado '$conn->affected_rows' !");
      }
  
      return $result;
    }


    private $user;

    private $password;
	
	private $name;

    private $image;

    private $date_joined;
	
	private $watching;

    private $admin;

    private $content_manager;
	
	private $moderator;

    private $premium;

    private $premiumValidity;
	
	private $film;

    private $completedNotifications;

    private function __construct($user, $password, $name, $image, $date_joined, $watching, $admin, $content_manager, $moderator, $premium, $premiumValidity)
    {
        $this->user= $user;
        $this->password = $password;
        $this->name = $name;
        $this->image = $image ?? 'user_logged.png';
        $this->date_joined = $date_joined;
        $this->watching = $watching;
        $this->admin = $admin;
        $this->content_manager = $content_manager;
        $this->moderator = $moderator;
        $this->premium = $premium;
        $this->premiumValidity = $premiumValidity;
		$this->film = Pelicula::buscaPorId($this->watching);
        $this->completedNotifications = Notificacion::getNotificacionesCompletadas($this->user);

    }



    public function film()
    {
        return $this->film;
    }

    public function completedNotifications()
    {
        return $this->completedNotifications;
    }

    public function user()
    {
        return $this->user;
    }

    public function name()
    {
        return $this->name;
    }

    public function image()
    {
        return $this->image;
    }

    public function date_joined()
    {
        return $this->date_joined;
    }

    public function watching()
    {
        return $this->watching;
    }

    public function admin()
    {
        return $this->admin;
    }

    public function content_manager()
    {
        return $this->content_manager;
    }

    public function moderator()
    {
        return $this->moderator;
    }

    public function premium()
    {
        return $this->premium;
    }

    public function premiumValidity()
    {
        return $this->premiumValidity;
    }

    public function compruebaPassword($password)
    {
        return password_verify($password, $this->password);
    }

    public function cambiaPassword($nuevoPassword)
    {
        $this->password = self::hashPassword($nuevoPassword);
    }
}
