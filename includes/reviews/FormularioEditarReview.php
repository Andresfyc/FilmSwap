<?php
namespace es\ucm\fdi\aw\reviews;

use es\ucm\fdi\aw\Aplicacion;
use es\ucm\fdi\aw\Form;

class FormularioEditarReview extends Form
{

  private $id;
  private $review;

  public function __construct($id)
  {
    parent::__construct('formEditarReview', $id);
    $this->id = $id;
  }

  
  protected function generaCamposFormulario($datos, $errores = array())
  {    
    $id = $datos['id'] ?? $this->id;
    $this->review = Review::buscaPorId($id);
    $filmId = $datos['filmId'] ?? $this->review->film_id();
    $reviewStr = $datos['reviewStr'] ?? $this->review->review();
    $rating = $datos['rating'] ?? $this->review->stars();
      

    // Se generan los mensajes de error si existen.
    $htmlErroresGlobales = self::generaListaErroresGlobales($errores);
    $errorReview = self::createMensajeError($errores, 'reviewStr', 'span', array('class' => 'error'));
    $errorRating = self::createMensajeError($errores, 'rating', 'span', array('class' => 'error'));


    $camposFormulario = <<<EOF
    <fieldset>
        $htmlErroresGlobales
        <input class="control" type="hidden" name="id" value="$id" readonly/>
        <input class="control" type="hidden" name="filmId" value="$filmId" readonly/>
        <div class="grupo-control">
            <label>Review:</label> <input class="control" type="text" name="reviewStr" value="$reviewStr" />$errorReview
            <label>Review:</label> <input class="control" type="number" name="rating" value="$rating" />$errorRating
        </div>
        
        <div class="grupo-control"><button type="submit" name="editar">Confirmar</button></div>
    </fieldset>
    EOF;
    return $camposFormulario;
  }

  /**
   * Procesa los datos del formulario.
   */
  protected function procesaFormulario($datos)
  {

    $result = array();
    $app = Aplicacion::getSingleton();
        
    $id = $datos['id'] ?? null;
    $filmId = $datos['filmId'] ?? null;

    $reviewStr = $datos['reviewStr'] ?? null;
    if ( empty($reviewStr) ) {
        $result['reviewStr'] = "La review no puede quedar vacía";
    }

    $rating = $datos['rating'] ?? null;
    if ( empty($rating) || $rating < 1 || $rating > 5 ) {
        $result['rating'] = "La puntuación debe estar entre 1 y 5";
    }

    if (count($result) === 0) {
        $review = Review::buscaPorId($id);
        if ($app->usuarioLogueado() && ($app->esModerador() || $app->esAdmin() || $app->user() == $review->user())) {
            $review = Review::editar($id,null,null,$reviewStr,$rating,null);
            if ( ! $review  ) {
                $result[] = "La review ya existe";
            } else {
                $result = RUTA_APP."/pelicula.php?id={$filmId}";
            }
        }
    }
    return $result;
  }
}