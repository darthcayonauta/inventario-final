<?php

class MenuAdmin{


 private $ruta;
 private $template;
 private $tipo_usuario;
 private $menu_aux;


function __construct()
{
  $oConf                = new config();
  $cfg                  = $oConf->getConfig();

  $this->yo             = $_SESSION['yo'];
  $this->tipo_usuario   = $_SESSION['tipo_usuario'];
  $this->error          = "Esto es un Error";
  $this->template  			= new template();
  $this->ruta      			= $cfg['base']['template'];

  $this->menu_aux = $this::menu_admin();
}



 private function menu_admin()
 {
     $data = [];
     switch ($this->tipo_usuario) {
       case 3:
         return $this::despliegueTemplate( $data, 'menu-admin-simple.html' );
         break;

       case 5:
         return $this::despliegueTemplate( $data, 'menu-admin.html' );
         break;

       case 6:
          return $this::despliegueTemplate( $data, 'menu-operador.html' );
          break;


       default:
         return null;
         break;
     }
 }

 /**
  * despliegueTemplate(), metodo que sirve para procesar los templates
  *
  * @param  array   arrayData (array de datos)
  * @param  array   tpl ( template )
  * @return String
  */
  private function despliegueTemplate($arrayData,$tpl, $ruta_abs =null ){

       if( is_null( $ruta_abs ) )
           $tpl = $this->ruta.$tpl;
        else $tpl = "/home/inventario/public_html/Templates/{$tpl}";

      $this->template->setTemplate($tpl);
      $this->template->llena($arrayData);

      return $this->template->getCode();
  }


 public function getCode()
 {
   return $this->menu_aux;
 }

}
 ?>
