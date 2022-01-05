<?php
/**
 * @author Ing. Claudio Guzman Herrera
 * @package None / PORDUKTION
 * @version 1.0
 * @copyright DYT SOCMA LIMITADA
 * 
 */

class OperarioProduccion 
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
      $this->error          = "Esto es un Error:::{$this->id}";
      $this->consultas 			= new querys( $db );
      $this->template  			= new template();
      $this->ruta      			= $cfg['base']['template'];
      $this->token 		      =  date("YmdHis");
      $this->fecha_hoy 		  =  date("Y-m-d");
  
      $this->btn_listar     = '<a href="content-page.php?id=bGlzdGFyLWNlbnRyb3M="
                                    class="btn btn-sm btn-success" >
                                    Listar Centros
                               </a>';
  
      $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItY2VudHJv"
                                  class="btn btn-sm btn-secondary" >
                                    [ Crear Centro ]
                               </a>';
  
    $this->email = "claudio.guzman@socma.cl";
    $this->menu_aux = $this::menu_aux();
    }

    private function control()
    {
        switch ($this->id) 
        {
            case 'colaboracion-operario':
                return  $this::colaboracionOperario();
                break;

            case 'ingresaKartMateriales':
                return $this::ingresaKartMateriales();
                break;  

            default:
                return $this->error;
                break;
        }
    }


    /**
     * ingresaKartMateriales(): ingresa el cuerpo de la recepccion de materiales
     * 
     *      
     */
    private function ingresaKartMateriales()
    {
 

      //sacara id del elemento      
      $arr = $this->consultas->listaElementos( null,1,null,null,null,null,null, $_POST['elemento'] );
      $id_insumo = 0;

      foreach ($arr['process'] as $key => $value) {
        $id_insumo = $value['id'];
      }

      if( $this->consultas->procesaCuerpoMaterialRs( $_POST['token'] ,
                                                     $id_insumo,
                                                     $_POST['id_unidad'], 
                                                     $this->fecha_hoy, 
                                                     $this->fecha_hoy , 
                                                     $_POST['cantidad'], 0 ) )                                                     
      { 
        return "DATA INGRESADA <br/> ".$this::tablaRecepcion( $_POST['token'] ); 
      }
      else{
        return "ERROR AL INGRESAR <br/>".$this::tablaRecepcion( $_POST['token'] );
      }
    }

    private function tablaRecepcion( $token = null )
    {
      $arr = $this::trRecepcion( $token );

      $DATA = [ '###tr###' => $arr['code']  ];
      return $this::despliegueTemplate( $DATA, 'TABLA-RECEPCION.html' );
      
    }

    private function trRecepcion( $token = null )
    {
      $code = "";
      $i    = 0;

      $arr = $this->consultas->listaCuerpoMaterialRs( null, $token );

      foreach ($arr['process'] as $key => $value) {
        # code...

        if( $value['id_estado']  == 1 )
              $estado = "RECEPCIONADA";
        else  $estado = "noRECEPCIONADA/ERROR"; 

        $DATA = ['###num###'                  => $i +1  , 
                '###ID###'                    => $value['id'],
                 '###insumo###'               => $value['nombreProducto'], 
                 '###unidad###'               => $value['nombreUnidad'],
                 '###fecha_ingreso###'        => $value['fecha'],
                 '###estado###'               => $estado , 
                 '###cantidad_solicitada###'  => $value['cantidad_recepcionada']];

        $code .= $this::despliegueTemplate( $DATA, 'TR-RECEPCION.html' );

        $i++;
      }


      $out['code'] = $code;
      $out['total-recs'] = $arr['total-recs'];
      return $out;

    }




    private function colaboracionOperario()
    {
       return $this::despliegueTemplate( ['###menu_aux###' => $this->menu_aux , 
                                          '###form###'     => $this::form()],
                                          'crea-colaboracion.html' );
    }

    private function form()
    {
      $arr = $this->consultas->unidades();
      $sel = new Select( $arr['process'], 
                        'id','descripcion',
                        'id_unidad',
                         'Unidad'   );

        return $this::despliegueTemplate( ['###select###' => $sel->getCode() , 
                                           '###token###'  => $this->token ],
                                          'form-recepcion.html' );
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
     * separa_miles(), coloca separador de miles en una cadena de caracteres
     *
     * @param  String num
     * @return String
     */
    private function separa_miles($num=null){
 
     return @number_format($num, 0, '', '.');
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
          else $tpl = "/home/claudio/webs/inventario/Templates/{$tpl}";
 
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