<?php

require_once __DIR__.'/includes/config.php';

$idReview = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);
$prevPage = filter_input(INPUT_GET, 'prevPage', FILTER_SANITIZE_STRING);
$prevPageId = filter_input(INPUT_GET, 'prevId', FILTER_SANITIZE_NUMBER_INT);

if (strlen($prevPageId) > 0) {
    $prev = RUTA_APP . $prevPage . ".php?id=" . $prevPageId;
} else {
    $prev = RUTA_APP . $prevPage . ".php";
}

$form = new es\ucm\fdi\aw\reviews\FormularioEditarReview($idReview, $prev);
$FormularioEditarReview = $form->gestiona();

$tituloPagina = 'Editar Review';

$contenidoPrincipal = <<<EOS
    <h1>Editar Review</h1>
    $FormularioEditarReview
EOS;

require __DIR__.'/includes/plantillas/plantilla.php';