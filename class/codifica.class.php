<?php
/**
 *
 */
class Codifica
{

  private $cadena;
  private $accion;

  function __construct($cadena = null, $accion = null)
  {
    $this->cadena = $cadena;
    $this->accion = $accion;
  }

  public function resuelve()
  {
      switch ($this->accion) {
        case 1:
          return utf8_encode( $this->cadena );
          break;

        case 2:
          return utf8_decode( $this->cadena );
          break;

        default:
          return $this->cadena;
          break;
      }
  }
}
?>
