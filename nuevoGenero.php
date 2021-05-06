<?php

require_once __DIR__.'/includes/config.php';

$prevPage = filter_input(INPUT_GET, 'prevPage', FILTER_SANITIZE_STRING);
$prevPageId = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT);

if (strlen($prevPageId) > 0) {
    $prev = "./" . $prevPage . ".php?id=" . $prevPageId;
} else {
    $prev = "./" . $prevPage . ".php";
}


$form = new es\ucm\fdi\aw\FormularioNuevoGenero($prev);
$htmlFormNuevoGenero = $form->gestiona();

$tituloPagina = 'Añadir Género';

$contenidoPrincipal = <<<EOS
<h1>Añadir Género</h1>
$htmlFormNuevoGenero
EOS;

require __DIR__.'/includes/plantillas/plantilla.php';