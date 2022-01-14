<?php

/**
 * @author Ing. Claudio Guzman Herrera
 * @package None / PORDUKTION
 * @version 1.0
 * @copyright DYT SOCMA LIMITADA
 * 
 */
 class JefeProduccion 
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
    private $tplFolder;


    function __construct( $id = null )
    {
      $oConf                = new config();
      $cfg                  = $oConf->getConfig();
      $db                   = new mysqldb( 	$cfg['base']['dbhost'],
                                            $cfg['base']['dbuser'],
                                            $cfg['base']['dbpass'],
                                            $cfg['base']['dbdata'] );
  
      $this->yo                 = $_SESSION['yo'];
      $this->tipo_usuario       = $_SESSION['tipo_usuario'];
      $this->id                 = $id;
      $this->error              = "Esto es un Error:::{$this->id}";
      $this->consultas 			= new querys( $db );
      $this->template  			= new template();
      $this->ruta      			= $cfg['base']['template'];
      $this->token 		        =  date("YmdHis");
      $this->fecha_hoy 		    =  date("Y-m-d");  
      $this->email              = "claudio.guzman@socma.cl";
      $this->menu_aux           = $this::menu_aux();
      $this->tplFolder          = "jefe-produccion";  

    } 
    

  private function control()
  {
    switch ($this->id) {
        case 'colaboracion-jefe-produccion':
            
            return $this::colaboracionJefeProduccion();
            break;
        
        case 'tablaListadoJefeProduccion':
            # code...
            return $this::listado();
            break;    


        case 'validaRecepcion':
            # code...
            return $this::validaRecepcion();
            break;    

        case 'validaTotal':
          # code...
          return $this::validaTotal();
          break;  

        default:
            # code...
            return $this->error;
            break;
    }
  }   

  private function validaTotal()
  {
  
    if( $this->consultas->actualizaEncabezadoMaterialRs( $_POST['token'] , $this->yo ) )
          $ok = true;
    else  $ok = false;  

    if( $this::actualizaInventario() )
          $ok = true;
     else $ok = false;    


    if( $this::procesaSerialize() )
    {
      $code  = $this::notificaciones( "success", '<i class="far fa-thumbs-up"></i>', 'INFORMACION VALIDADA' );      
      $code .= $this::tablaRecepcion( $_POST['token']  ); 

      $ob_mails = new mails( $this->email, $this::mensaje( $_POST['token'], $_POST['rs'] ) );
      
      if( $ob_mails->getCode() )
            $send = true;
      else  $send = false; 

      return $code;
    }
    else   return "Errrror";
  }

 private function mensaje( $token = null, $rs = null )
 {
    $data = ['###token###'    => $token , 
             '###rs###'       => $rs ,
             '###listado###'  => $this::tablaRecepcion( $token  ) ];

    return $this::despliegueTemplate( $data, "{$this->tplFolder}/mensaje-jefes.html" );

 } 


  private function actualizaInventario()
  {
    //listaElementos( $id_inventario )
    $cantidad_ok       = $this::separa( $_POST['cantidad_ok'], "&" );
    $id_elemento       = $this::separa( $_POST['id_elemento'], "&" );
    $code = "";


    $j =0;
    for ($i=0; $i < count( $id_elemento ) ; $i++) { 

      $cant_ok        = $this::separa( $cantidad_ok[$i], "=" );
      $id_elem        = $this::separa( $id_elemento[$i], "=" );

      $stock_actual = 0;
      $arr = $this->consultas->listaElementos( $id_elem[1] );

      foreach ($arr['process'] as $key => $value) {
        # code...
        $stock_actual = $value['stock'];
      }

      $new_stock = $stock_actual + $cant_ok[1];

      if( $this->consultas->actualizaElementoRs ( $id_elem[1],  $new_stock ) )
        $j++;
     
    }

    if( $j > 0 )
          return true;
    else  return false; 

  }



  /**
   * 
   */
  private function procesaSerialize()
  {
    $cantidad_ok       = $this::separa( $_POST['cantidad_ok'], "&" );
    $id_insumo         = $this::separa( $_POST['id_insumo'], "&" );
    $cantidad_recibida = $this::separa( $_POST['cantidad_recibida'], "&" );

    $k = 0 ;
    for ($i=0; $i < count( $cantidad_ok  ); $i++) { 
      
      $cant_ok        = $this::separa( $cantidad_ok[$i], "=" );
      $id             = $this::separa( $id_insumo[$i], "=" );
      $cant_recibida  = $this::separa( $cantidad_recibida[$i], "=" );

      if( $this->consultas->procesaCuerpoMaterialRs(  null,
                                                      null,
                                                      null,
                                                      null,
                                                      null,
                                                      null,
                                                      $cant_ok[1],
                                                      $this->yo,											 
                                                      $id[1]) )
      {
        $k++;
      }
    }

    if( $k > 0 )
          return true;
    else  return false; 
  }


  private function validaRecepcion()
  {    
      $arr = $this->consultas->listaRecepcionesOperador(null, $_POST['token'] );

      $creado_por = "";

      foreach ($arr['process'] as $key => $value) {        
        $creado_por .= "{$value['nombres']} {$value['apaterno']}";
      }

      $data = [ '###rs###'      => $_POST['rs'], 
                '###token###'   => $_POST['token'] ,
                '###creado_por###' => strtoupper( $creado_por ),
                '###listado###' => $this::listadoMaterial( $_POST['token'] , $_POST['rs']  )  
              
              
              ];
      return $this::despliegueTemplate( $data, "{$this->tplFolder}/resumen-rs.html" );

  }

  private function listadoMaterial( $token = null, $rs = null )
  {
      $arr  = $this::trListadoMaterial( $token ); 
      $data = ['###tr###' => $arr['code'] , '###token###' => $token, '###rs###' => $rs   ];
      return $this::despliegueTemplate( $data, "{$this->tplFolder}/lista-materiales.html" );  
  } 

 
  private function trListadoMaterial( $token = nul )
  {
    $arr = $this->consultas->listaCuerpoMaterialRs( null, $token ); 

    $code ="";

    $i =0;
    foreach ($arr['process'] as $key => $value) {
      # code...
      if( $value['id_estado']  == 1 )
            $estado = "RECEPCIONADA";
      else  $estado = "Revisada"; 


      $DATA = [ '###num###'                  => $i +1  , 
                '###ID###'                   => $value['id'],
                '###insumo###'               => $value['nombreProducto'], 
                '###id_elemento###'          => $value['id_elemento'],
                '###unidad###'               => $value['nombreUnidad'],
                '###fecha_ingreso###'        => $this::arreglaFechas(  $value['fecha'] ), 
                '###estado###'               => $estado ,                  
                '###token###'                => $token, 
                '###stock###'                => $value['stock'], 
                '###fecha_actualizacion###'  => $this::arreglaFechas(  $value['fecha_modificacion'] ), 
                '###cantidad_solicitada###'  => $value['cantidad_recepcionada']];

          $code .= $this::despliegueTemplate( $DATA, "{$this->tplFolder}/tr-materiales.html" );
          $i++;
    }

    $out['code'] = $code;

    return $out;

  }



  private function colaboracionJefeProduccion()
  {
    $data = ['###menu_aux###' => $this->menu_aux,
             '###buscar###'   => null,
             '###listado###'  => $this::listado()  
            
            ];
    return $this::despliegueTemplate( $data, "{$this->tplFolder}/admin-screen.html"  );

  }

  private function listado()
  {
    $arr = $this::trListado();

    $data =['###tr###'          => $arr['code'] ,
            '###TOTAL-RECS###'  => $arr['total-recs'],
            '###nav-links###'   => $arr['nav-links']                  
        
        
        ];
    return $this::despliegueTemplate( $data, "{$this->tplFolder}/tabla-recepcion.html"  );
  }

  /**
   * trListado(): detalle del listado
   * 
   * @return string
   */
  private function trListado()
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
            {$estado = "RECEPCIONADA"; $disabled = null;}
      else  {$estado = "VALIDADA"; $disabled = "disabled"; }


      $data = ['###NUM###'                => $i+1 , 
               '###RS###'                 => $value['rs'],
               '###TOKEN###'              => $value['token'], 
               '###FECHA###'              => $this::arreglaFechas( $value['fecha'] ), 
               '###FECHA-MODIFICACION###' => $this::arreglaFechas( $value['fecha_modificacion'] ),
               '###ESTADO###'             => $estado ,
               '###CREADO-POR###'         => "{$value['nombres']} {$value['apaterno']}"  ,
               '###ID###'                 => $value['id'],
               '###DISABLED###'           => $disabled,
               '###modal###'              => $this::modal( "detalle-insumos-{$value['id']}" , 
                                                           '<i class="fas fa-angle-double-right"></i>',
                                                           "Detalles de recepción RS:{$value['rs']} / Code: {$value['token']}" ,
                                                           $this::tablaRecepcion( $value['token'] )
                                                           )];
      $code .= $this::despliegueTemplate( $data, "{$this->tplFolder}/tr-recepcion.html" );

      $i++;
    }

    $out['code'] = $code;
    $out['total-recs'] = $arr['total-recs'];
    $out['nav-links'] = $nav_links;

    return $out;

  }


  private function tablaRecepcion( $token = null )
  {
    $arr = $this::trRecepcion( $token );
    $btn = null;

    $DATA = [ '###tr###' => $arr['code'] , '###btn###' => $btn  ];
    return $this::despliegueTemplate( $DATA, "{$this->tplFolder}/lista-materiales-clean.html" );
    
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
      else  $estado = "VALIDADA"; 


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
                '###fecha_actualizacion###'  => $this::arreglaFechas(  $value['fecha_modificacion'] ), 
                '###estado###'               => $estado , 
                '###btn###'                  => $btn, 
                '###stock###'                   => $value['stock'], 
                '###token###'                => $token, 
                '###cantidad_solicitada###'  => $value['cantidad_recepcionada'], 
                '###cantidad_ok###'          => $value['cantidad_aprobada']
              ];

      $code .= $this::despliegueTemplate( $DATA, "{$this->tplFolder}/tr-materiales-clean.html" );

      $i++;
    }


    $out['code'] = $code;
    $out['total-recs'] = $arr['total-recs'];
    return $out;

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

 }//fin de clase
 
?>