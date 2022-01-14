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

            case 'eliminaRowRecepcion':
              # code...
              return $this::eliminaRowRecepcion();
              break;  

             case 'ingresaEncabezadoMaterialRs':
               # code...
               return $this::ingresaEncabezadoMaterialRs();
               break; 

             case 'listado-colaboracion':
               # code...
               return $this::listarColaboracion();
               break; 

            case 'buscaRsMaterials':
              # code...
              return $this::buscaRsMaterials();
              break;  

            case 'tablaListarColaboracion':
              # code...
              return $this::tablaListarColaboracion();
              break;  



            default:
                return $this->error;
                break;
        }
    }

    private function search( $placeholder = null, $id_text = null, $id_button = null )
    {
      $data = ['@@@placeholder'    => $placeholder  ,
               '@@@id_text'        => $id_text ,
                '###id-button###'  => $id_button  ];
      return $this::despliegueTemplate( $data, "buscar.html" );
    }
 





    private function listarColaboracion()
    {
      $data = ['###menu_aux###' => $this->menu_aux ,
               "###buscar###"   => $this::search('Ingrese RS para buscar','rs','buscarXRS'), 
               '###listado###' => $this::tablaListarColaboracion()  ];
      return $this::despliegueTemplate( $data, 'listar-colaboracion.html' );
    } 

    private function buscaRsMaterials()
    {
      return $this::tablaListarColaboracion();
    }


    private function tablaListarColaboracion()
    {
      $arr = $this::trListarColaboracion();

      if( $arr['total-recs'] > 0 )
      {
          return $this::despliegueTemplate( [ '###tr###'         => $arr['code'], 
                                              '###TOTAL-RECS###' => $arr['total-recs'] ,
                                              '###nav-links###'  => $arr['nav-links']  
                                            
                                            ],
                                               'TABLA-COLABORACION.html'    );
      }else{
        return  $this::notificaciones( 'warning',null, 
                                        '<p class="h5"><i class="far fa-meh"></i> 
                                            <strong>NO HAY RESULTADOS O REGISTROS ASOCIADOS</strong>
                                        </p>' )    ;
      }
    }  

    private function trListarColaboracion()
    {
      
      if( !isset( $_POST['rs'] ) )
            {
              $arr = $this->consultas->listaRecepcionesOperador();              
              $utils      = new utiles($arr['sql']);
              $rs_dd      = $utils->show();
              $nav_links  = $rs_dd['nav_links'];
              $param      = $rs_dd['result'] ;
            
            
            }
      else  {
              $arr = $this->consultas->listaRecepcionesOperador( $_POST['rs'] );
              $param = $arr['process'];  
              $nav_links = null;      
      }
      
      
      $code = "";
      $i = 0;

      foreach ($param as $key => $value) {
        

        if( $value['id_estado'] ==1  )        
              $estado = "RECEPCIONADA";
        else  $estado = "VALIDADA";


        $data = ['###NUM###'                => $i+1 , 
                 '###RS###'                 => $value['rs'],
                 '###TOKEN###'              => $value['token'], 
                 '###FECHA###'              => $this::arreglaFechas( $value['fecha'] ), 
                 '###FECHA-MODIFICACION###' => $this::arreglaFechas( $value['fecha_modificacion'] ),
                 '###ESTADO###'             => $estado ,
                 '###CREADO-POR###'         => "{$value['nombres']} {$value['apaterno']}"  ,
                 '###ID###'                 => $value['id'],
                 '###modal###'              => $this::modal( "detalle-insumos-{$value['id']}" , 
                                                             '<i class="fas fa-angle-double-right"></i>',
                                                             "Detalles de recepción {$value['rs']}-{$value['token']}" ,
                                                             $this::tablaRecepcion( $value['token'] )
                                                             )];
        $code .= $this::despliegueTemplate( $data, 'TR-COLABORACION.html' );

        $i++;
      }

      $out['code'] = $code;
      $out['total-recs'] = $arr['total-recs'];
      $out['nav-links'] = $nav_links;

      return $out;

    }


    private function ingresaEncabezadoMaterialRs()
    {
     
      if( $this->consultas->ingresaEncabezadoMaterialRs( $_POST['token'],$_POST['rs'], $this->yo, $_POST['fecha']) )
           {
            
            $ob_mail = new mails( $this->email, $this::plantillaMsg( $_POST['rs'] )  );

            if( $ob_mail->getCode() )
                  $ok = true;
            else  $ok = false; 


            $data = [ '###rs###'      => $_POST['rs'], 
                      '###fecha###'   => $_POST['fecha'] , 
                      '###listado###' => $this::tablaRecepcion( $_POST['token'] )  ];

            return $this::despliegueTemplate( $data , 'resumen-rs.html' )  ;
          
          
          }
      else { return "ERROR AL INGRESAR";  }

     //enviar mail...
     
    }

    private function plantillaMsg( $rs = null )
    {
      $data = ['###rs###' => $rs , '###email###' => $this->email];
      return $this::despliegueTemplate( $data, 'mensaje-recepcion.html' );
    }





    private function eliminaRowRecepcion()
    {
    
      if ( $this->consultas->eliminaCuerpoMaterialesRs( $_POST['id_row'] ) )
            $msg = "<i class='far fa-thumbs-up'></i> REGISTRO ELIMINADO";
      else  $msg = "<i class='far fa-thumbs-down'></i> ERROR AL ELIMINAR"; 

      return $msg.$this::tablaRecepcion( $_POST['token'] );

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
        return "<i class='far fa-thumbs-up'></i> DATA INGRESADA <br/> ".$this::tablaRecepcion( $_POST['token'] ); 
      }
      else{
        return "<i class='far fa-thumbs-down'></i> ERROR AL INGRESAR <br/>".$this::tablaRecepcion( $_POST['token'] );
      }
    }

    private function tablaRecepcion( $token = null )
    {
      $arr = $this::trRecepcion( $token );

      
      switch ($this->id) 
      {
        case 'tablaListarColaboracion':
        case 'ingresaEncabezadoMaterialRs':
        case 'listado-colaboracion':
        case 'buscaRsMaterials':
          
          $btn = null;
          break;
        
        default:
          
          $btn = '<button class="btn btn-secondary btn-block outline-line-gris" id="guardarDataRecepcionRs">
                      <i class="fas fa-angle-double-right"></i> Enviar Datos
                  </button>';
          break;
      }

      $DATA = [ '###tr###' => $arr['code'] , '###btn###' => $btn  ];
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


       switch ($this->id) {

         case 'eliminaRowRecepcion': 
         case 'ingresaKartMateriales':

          $btn = '       <button class="btn btn-danger btn-sm outline-line-rojo" 
                            id="elimina-elemento-'.$value['id'].'">
                            <i class="far fa-trash-alt"></i>
                          </button>';
           break;
         
         default:
           $btn = null;
           break;
       } 

        $DATA = [ '###num###'                  => $i +1  , 
                  '###ID###'                   => $value['id'],
                  '###insumo###'               => $value['nombreProducto'], 
                  '###unidad###'               => $value['nombreUnidad'],
                  '###fecha_ingreso###'        => $this::arreglaFechas(  $value['fecha'] ), 
                  '###estado###'               => $estado , 
                  '###btn###'                  => $btn, 
                  '###token###'                => $token, 
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