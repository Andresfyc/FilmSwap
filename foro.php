<?php

require_once __DIR__.'/includes/config.php';

$eventosTemas = new es\ucm\fdi\aw\eventosTemas();

$tituloPagina = 'Foro';

$contenidoPrincipal=<<<EOS
	<h1>Eventos</h1>
	<div class="row-top">
		<div class="col" id="col-4-1">
			<p>Nombre</p>
		</div>
		<div class="col" id="col-4-2">
			<p>Descripción</p>
		</div>
		<div class="col" id="col-4-3">
			<p>Fecha y/o Hora</p>
		</div>
		<div class="col" id="col-4-4">
			<p>#Mensajes</p>
		</div>
	</div>
EOS;
$contenidoPrincipal .= $eventosTemas->listaEventos();

$contenidoPrincipal.=<<<EOS
	<h1>Temas</h1>
	<div class="row-top">
		<div class="col" id="col-3-1">
			<p>Nombre</p>
		</div>
		<div class="col" id="col-3-2">
			<p>Descripción</p>
		</div>
		<div class="col" id="col-3-3">
			<p>#Mensajes</p>
		</div>
	</div>
EOS;
$contenidoPrincipal .= $eventosTemas->listaTemas();


require __DIR__ . '/includes/plantillas/plantilla.php';