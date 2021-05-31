<?php

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\actoresDirectores\ActorDirector;
use es\ucm\fdi\aw\reviews\Review;
use es\ucm\fdi\aw\peliculas\Pelicula;

/*
 * Funciones de apoyo
 */

 
function getDivUsuario($user, $isSelf) {
    $RUTA_APP = RUTA_APP;
	$app = Aplicacion::getSingleton();
    $usuario = getUsuarioPorUser($user);
    $perfil = '';
    if ($isSelf) {
        $perfil = '<p><a href="'.$RUTA_APP.'editarPerfil.php?user='.$usuario->user().'">Editar Perfil</a></p>';
    }
    if ($app->usuarioLogueado() || !$isSelf) {
        $html=<<<EOS
            <div class="div-perfil">
            <img id="film_pic" src="img/usuarios/{$usuario->image()}" alt="user" >
            <div>
            <p>Usuario: {$usuario->user()}</p>
            <p>Nombre completo: {$usuario->name()}</p>
            <p>Correo electrónico: </p>
            <p>Fecha de registro: {$usuario->date_joined()}</p>
            $perfil
            </div>
            </div>
        EOS;
        return $html;
    }
}

function listaAmigos($user, $limit=NULL)
{
    $usuarios = Usuario::listaAmigos($user, $limit);
    $html = '<div>';
    foreach($usuarios as $usuario) {
        $peliculaWatching = $usuario->film();
        $watching = '';
        $swapper='<p><a href="perfil.php?user='.$usuario->user().'"> Swapper: '.$usuario->user().' </a></p>';
        if ($peliculaWatching) {
            $watching .= '<p><a href="'.RUTA_APP.'pelicula.php?id='.$peliculaWatching->id().'"> Viendo: '.$peliculaWatching->title().' </a></p>';
        }

        $html .=<<<EOS
            <div class="div-usuarios">
            <div class="div-usuarios">
            <div class="usuarios">
            <img id="prof_pic" src="img/usuarios/{$usuario->image()}" alt="user" >
            <div>
            $swapper
            <p>{$usuario->name()} </p>
            </div>
            </div>
            <div>
            $watching
            </div>
            </div>
        EOS;
    }
    $html .= '</div>';

    return $html;
}


function listaActoresDirectoresUser($user = NULL, $limit = NULL, $actorDirector)
{		
    $html = '<div>';
    $actores = ActorDirector::buscaActoresDirectoresPorUser($user, $limit, $actorDirector);
    foreach($actores as $actor) {
        $href = RUTA_APP.'actorDirector.php?id=' . $actor->id();
        $year = substr($actor->birth_date(), 0, 4);

        $html .=<<<EOS
            <div class="div-actoresDirectores">
            <img id="prof_pic_long" src="img/actores_directores/{$actor->image()}" alt="actor/director" >
            <div>
            <p><a href="$href">{$actor->name()}</a></p>
            <p>{$year}</p>
            </div>
            </div>
        EOS;
    }
    $html .= '</div>';

    return $html;
}

function listaReviewsUser($user = NULL)
{		
	$app = Aplicacion::getSingleton();

    $reviews = Review::buscaReviewsPorIdUser($user);
	$html = '<div>';
	if($reviews !=null){
        
        foreach($reviews as $review) {
			$pelicula = Pelicula::buscaPorId($review->film_id());
            $html .= '<div class="div-reviewsPeli">';
            $html .= '<div>';
			$html .= "<p><a href=\"./pelicula.php?id={$pelicula->id()}\">{$pelicula->title()}</a></p>";
            $html .= "<p>Puntuación: {$review->stars()}/5</p>";
            $html .= "<p>{$review->time_created()}</p>";
            $html .= "<p>{$review->user()}</p><p>";
			if ($app->usuarioLogueado() && ($app->esModerador() || $app->esAdmin() || $review->user() == $app->user())) {
                $html .= "<a href=\"./editarReview.php?id={$review->id()}\">Editar</a>";
                $html .= "<a href=\"./eliminarReview.php?id={$review->id()}\"> Eliminar</a>";
            }			
            $html .= '</p></div>';
            $html .= "<p>{$review->review()}</p>";
            $html .= '</div>';

        }
        
    }
    $html .= '</div>';

    return $html;
}

function busquedaUsuarios($search)
{
    $usuarios = Usuario::busqueda($search);
    $html = '';
    if (!empty($usuarios)) {
        $html .= '<h3> Usuarios: </h3>';
        $html .= '<ul>';
        foreach($usuarios as $usuario) {
            $html .= '<p><a href="'.RUTA_APP.'usuario.php?id='.$usuario->user().'">'.$usuario->name().' ('.$usuario->user().')</a></p>';
        }
        $html .= '</ul>';
    }

    return $html;
}

function getUsuarioPorUser($user)
{
    return Usuario::buscaUsuario($user);
}

function getReviewPorUser($user)
{
    return Review::buscaReviewsPorIdUser($user);
}
