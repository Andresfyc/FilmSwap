<?php
require_once __DIR__.'/usuarios_utils.php';

use es\ucm\fdi\aw\Aplicacion;

function mostrarActoresDirectores($actorDirector) {
	$app = Aplicacion::getSingleton();
	$ad = $actorDirector ? "directores" : "actores";
	$user = $app->user();
	if ($app->usuarioLogueado()) {
		echo listaActoresDirectoresUser($user, 7, $actorDirector);
		echo "<a href='actoresDirectoresFavoritos.php?id={$user}&ad={$actorDirector}'> Ver tus {$ad} favoritos </a>";			
	} else {
		echo "<p>Inicia sesión para ver tus actores y directores</p>";
	}
}

function mostrarMenuAdmin(){
    $app = Aplicacion::getSingleton();
    if ($app->usuarioLogueado() && ($app->esAdmin() || $app->esGestor() || $app->esModerador())) {
		echo mostrarMenuPro();
    }
}

?>
<aside id="sidebarIzq">
	<div id="topSide">
        <a href="index.php"><img id="logo" src="img/logo2.png" alt="filmswap logo" ></a>
	</div>
    <div class="menuAdmin">
        <?php
            mostrarMenuAdmin();
        ?>
	<div id="contentSide">
		<h3> Tus actores favoritos </h3>
		<?php
			mostrarActoresDirectores(0);
		?>
		<h3> Tus directores favoritos </h3>
		<?php
			mostrarActoresDirectores(1);
		?>
	</div>
</aside>