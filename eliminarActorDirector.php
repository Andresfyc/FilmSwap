<?php

require_once __DIR__.'/includes/config.php';
require_once __DIR__.'/includes/comun/peliculas_utils.php';

use es\ucm\fdi\aw\Aplicacion;

$idActorDirector = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$prevPage = filter_input(INPUT_GET, 'prevPage', FILTER_SANITIZE_STRING);

$actorDirector = getActorDirectorPorId($idActorDirector);

$tituloPagina = 'Eliminar Actor/Director';

$prev = urldecode($prevPage);
if(array_key_exists('cancelar', $_POST)) {
    header('Location: '.$prev);
}
else if(array_key_exists('eliminar', $_POST)) {
    $app = Aplicacion::getSingleton();
    if ($app->usuarioLogueado() && ($app->esAdmin() || $app->esGestor())) {
        eliminarActorDirectorPorId($idActorDirector);
        header('Location: '.$prev);
    }
}

$contenidoPrincipal = <<<EOS
    <h1>Eliminar Actor/Director</h1>
    <p> ¿Quiere eliminar definitivamente el actor/director "{$actorDirector->name()}"?</p>
    <form method="post">
        <input type="submit" name="cancelar"
                class="button" value="Cancelar" />
            
        <input type="submit" name="eliminar"
                class="button" value="Eliminar" />
    </form>
EOS;

require __DIR__.'/includes/plantillas/plantilla.php';