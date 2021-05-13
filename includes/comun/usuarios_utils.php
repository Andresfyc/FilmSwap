<?php

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\usuarios\Usuario;
use es\ucm\fdi\aw\actoresDirectores\ActorDirector;

/*
 * Funciones de apoyo
 */

function getDivUsuario() {
	$app = Aplicacion::getSingleton();
    $usuario = getUsuarioPorUser($app->user());
    if ($app->usuarioLogueado()) {
        $html=<<<EOS
            <div class="div-perfil">
            <img id="film_pic" src="img/usuarios/{$app->image()}" alt="user" width="150" height="150">
            <div>
            <p>Usuario: {$usuario->user()}</p>
            <p>Nombre completo: {$usuario->name()}</p>
            <p>Correo electrónico: </p>
            <p>Fecha de registro: {$usuario->date_joined()}</p>
            <p><a href="editarPerfil.php?user={$usuario->user()}">Editar Perfil</a></p>
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
        $href = './usuarios.php?id=' . $usuario->user();
        $peliculaWatching = $usuario->film();
        $watching = '';
        if ($peliculaWatching) {
            $watching .= "<p><a href=\"pelicula.php?id={$peliculaWatching->id()}\"> Viendo: {$peliculaWatching->title()} </a></p>";
        }

        $html .=<<<EOS
            <div class="div-usuarios">
            <div class="div-usuarios">
            <div class="usuarios">
            <img id="prof_pic" src="img/usuarios/{$usuario->image()}" alt="user" width="60" height="60">
            <div>
            <p><a href="$href">{$usuario->user()} </a></p>
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
        $href = './actorDirector.php?id=' . $actor->id();
        $year = substr($actor->birth_date(), 0, 4);

        $html .=<<<EOS
            <div class="div-actoresDirectores">
            <img id="prof_pic" src="img/actores_directores/{$actor->image()}" alt="actor/director" width="60" height="90">
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

function busquedaUsuarios($search)
{
    $usuarios = Usuario::busqueda($search);
    $html = '';
    if (!empty($usuarios)) {
        $html .= '<h3> Usuarios: </h3>';
        $html .= '<ul>';
        foreach($usuarios as $usuario) {
            $html .= "<p><a href=\"./usuario.php?id={$usuario->user()}\">{$usuario->name()} ({$usuario->user()})</a></p>";
        }
        $html .= '</ul>';
    }

    return $html;
}

function getUsuarioPorUser($user)
{
    return Usuario::buscaUsuario($user);
}