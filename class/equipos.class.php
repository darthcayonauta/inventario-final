<?php

class Equipos
{
    private $id;
    private $yo;
    private $consultas;
    private $template;
    private $error;
    private $token;
    private $msg;
    private $btn;
    private $btn_critico;
    private $email;
    private $menu_aux;
  
    function __construct( $id = null )
    {
      $oConf                = new config();
      $cfg                  = $oConf->getConfig();
      $db                   = new mysqldb( 	$cfg['base']['dbhost'],
                                            $cfg['base']['dbuser'],
                                            $cfg['base']['dbpass'],
                                            $cfg['base']['dbdata'] );
  
      $this->yo             = $_SESSION['yo'];
      $this->tipo_usuario   = $_SESSION['tipo_usuario'];
      $this->id             = $id;
      $this->error          = "Esto es un Error para {$this->id}";
      $this->consultas 			= new querys( $db );
      $this->template  			= new template();
      $this->ruta      			= $cfg['base']['template'];
      $this->token 		      =  date("YmdHis");
      $this->fecha_hoy 		  =  date("Y-m-d");
  
      $this->btn            = '<a href="content-page.php?id=bGlzdGFySW52ZW50YXJpbw==" class="btn btn-sm btn-success" >
                                    Listar Inventario
                               </a>';
  
      $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXJJbnZlbnRhcmlv"
                                  class="btn btn-sm btn-secondary" >
                                    [ Crear Inventario ]
                               </a>';
  
  //#!/bin/bash
  
    $this->email = "alvaro.barria@socma.cl";
  
    $this->menu_aux = $this::menu_aux();   
}


 /**
  * control(): switch de peticiones de funciones
  * @return string 
   */   
 private function control()
    {
        switch ($this->id) {
            case 'crear-equipos':
                return $this::crearEquipos();
                break;
            
            case 'listar-equipos':
                # code...
                return $this::listarEquipos();
                break;    

             case 'ingresaEquipo1':
                 # code...
                 return $this::ingresaEquipo1();
                 break;   

            default:
                # code...
                return $this->error;
                break;
        }
    }

    private function ingresaEquipo1()
    {
        #print_r( $_POST );

        $data = $this::separa( $_POST['id_insumo'], '&' );
              
         $j = 0; 
        foreach ($data as $key => $value) {
          # code...
          $id_insumo = $this::separa( $value,"=" );
          
          if( $this->consultas->procesaRelElemento( $_POST['nombre_equipo'], 
                                                    $id_insumo[1],
                                                    $this->token,
                                                    $this->yo  
          )   )
            $j++;

        }

        if( $j > 0 )
          return "ELEMENTOS INGRESADOS";
        else   
          return "ERROR AL INGRESAR";
    }



    private function crearEquipos()
    {
        $data = ['###menu-aux###' => $this->menu_aux, '###tabla-insumos###' => $this::tablaInsumos() ];
        return $this::despliegueTemplate( $data, 'crea-equipos-1.html' );

        #return "{$this->id}  en construccion ";
    }


    private function tablaInsumos()
    {
        $arr = $this::listaTablaInsumos();


        $data = [ '###tr###' => $arr['code'] ];
        return $this::despliegueTemplate( $data, 'tabla-insumos.html' );
    }

    private function listaTablaInsumos()
    {
        $code = "";
        $arr = $this->consultas->listaElementos();

        foreach ($arr['process'] as $key => $value) {
            # code...
            $data = ['###id###' => $value['id'], '###nombre-insumo###' => $value['nombre'] ];
            $code .= $this::despliegueTemplate( $data, 'tr-tabla-insumos.html' );
        }

        $out['code'] = $code;

        return $out;
    }



    private function listarEquipos()
    {
        return "{$this->id}  en construccion ";
    }

   /**
    * menu_admin(), menu admin
    * @return string
    */
    private function menu_aux()
    {
     require_once( 'menu-admin.class.php' );
     try {
 
         $ob = new MenuAdmin();
         return $ob->getCode();
 
     } catch (\Throwable $th) {
       return "Error de clase {$th}";
     }
   }


  /**
   * modal(): extrae un modal desde una Clase
   *
   *@param string target
   *@param string img
   *@param string title
   *@param string content
   */
  private function modal( $target = null,$img = null, $title = null, $content = null )
  {
      require_once("modal.class.php");

      $ob_modal = new Modal($target ,$img , $title , $content );
      return $ob_modal->salida();
  }

  /**
   * notificaciones()
   * @param string tipo_alerta
   * @param string icon
   * @param string glosa
   * @return string
    */
   private function notificaciones( $tipo_alerta = null, $icon= null, $glosa = null )
   {
       return $this::despliegueTemplate( array( '@@@tipo-alert@@@' => $tipo_alerta,
                                                '@@@icon@@@'       => $icon,
                                                '@@@glosa'         => $glosa) , 'notificaciones.html' );
   }

   /**
    * buscar(): despliegue de formulario de búsqueda
    * @return string
    */
   private function buscar()
   {
     $a1 = $this->consultas->ubicacion();
     $sel1 = new Select( $a1['process'], 'id','descripcion','idUbicacion', 'Ubicacion' );

     $a2 = $this->consultas->listaTipo();
     $sel2 = new Select( $a2['process'], 'id','descripcion','id_tipo', 'Tipo' );

     return $this::despliegueTemplate( ['###select-ubicacion###' => $sel1->getCode(),
                                        '###select-tipo###'      => $sel2->getCode(),
    ], 'buscar-full.html' );
   }


  /**
  * arregla_fechas()
  *
  * @param  string FECHA
  * @return string
  */
    private function arreglaFechas( $FECHA=null ){

        if(!is_null( $FECHA )){
            $div = explode("-", $FECHA);

            return $div[2]."-".$div[1]."-".$div[0];
        }else
            return null;
    }

 /**
 * separa(): metodo que separa elementos distanciados por simbolos
 * @param string cadena
 * @param string simbolos
 * @return array
 */
 private function separa($cadena=null,$simbolo=null)
 {
   if( is_null($cadena) )
     return "";
   else
     return explode($simbolo,$cadena);
 }

  /**
   * codifica(): ressuelve codificar en uft8 o no dependiendo del server
   */
   private function codifica( $cadena = null, $accion = null  )
   {
     $ob_codifica = new Codifica( $cadena , $accion  );
     return $ob_codifica->resuelve();
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


 /**
 * getCode(): salida general del resultado del método de Control
 * @return string
 */
 public function getCode()
 {
   return $this::control();
 }
}
?>