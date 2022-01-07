<?php
class GuiaDespachoEgreso
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
      $this->error          = "Esto es un Error";
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
  
      $this->btn_refresh = "<a href='content-page.php?id=aW5pY2lv' 
                               data-toggle='tooltip'
                               title='Actualizar'  
                               class='btn btn-sm btn-success outline-line-verde'>
                               <i class='fas fa-sync-alt'></i>
                           </a>";
  
  
  //#!/bin/bash
  
    $this->email = "alvaro.barria@socma.cl";
  
    $this->menu_aux = $this::menu_aux();
  
    }

    private function control()
    {
        switch ($this->id) {
            case 'crear-guia-despacho-egreso':
                # code...
                return $this::crearGuiaDespachoEgreso();
                break;
            
            case 'listar-guia-despacho-egreso':
                # code...
                return $this::listarGuiaDespachoEgreso();
                break;    

            case 'generaGuiaDespachoEgreso':
                # code...
                return $this::generaGuiaDespachoEgreso();
                break;    

            case 'finishEgresos':
              
              return $this::finishEgresos();
              break;  

            case 'buscarXfecha':  
            case 'buscaLaGuiaEgreso':
              # code...
              return $this::tablaGuiaDespacho();
              break;  

            case 'verificaNumGuiaEgreso':
              return $this::verificaNumGuiaEgreso();
              break;  


            case 'ingresaClienteFromEgreso':
              # code...
              return $this::ingresaClienteFromEgreso();
              break;  


            default:
                # code...
                return " {$this->error} para id : { $this->id } ";
                break;
        }
    }

    
    private function verificaNumGuiaEgreso()
    {
      
      $arr = $this->consultas->qGuiaDespachoEgreso( $_POST['num_guia'] );

      if( $arr['total-recs']  > 0 )
      {
        return "<span class='rogelio'><i class='far fa-thumbs-down'></i> NUMERO DE GUIA YA UTILIZADO</span>";
      }
      else{
        return "<span class='verde'><i class='far fa-thumbs-up'></i> NUMERO DE GUIA DISPONIBLE</span>";
      }
    }




    public function resumenInsumos( $id_insumo = null )
    {
      return $this::tablaResumen( null,null,null, $id_insumo );
    }


    private function finishEgresos()
    {
      //print_r( $_POST );

      if( $this::procesaDetalleGuiaDespachoEgresoUpdate() )
            $actualiza = true;
      else  $actualiza = false;

      if( $actualiza  )
            return $this::tablaResumen( $_POST['token'] );
      else  return "REGISTRO NO ACTUALIZADO";


    }


    private function tablaResumen( $token = null, $num_guia=null ,  $excel = null , $id_insumo = null) 
    {
    
      if( $id_insumo )
            $guia_desp ='<th>Num Guia</th>';
      else  $guia_desp =null;
    
      $target = "excel-guia-despacho.php?num_guia={$num_guia}&token={$token}&egreso=1"; 
    
      if( is_null( $excel ) )
            $btn = null;
      else  $btn = '<a href="'.$target.'" class="btn btn-success btn-sm outline-line-verde" target="_blank">
                      <i class="far fa-arrow-alt-circle-right"></i> Exportar a Excel
                    </a>'; 
    
      $arr = $this::trResumen( $token , $id_insumo );
      $data = ['###th-v-total###' => '<th>Total</th>' , '###tr###' => $arr['code'] ,
               '###tfoot###'      => "<tr>
                                         <td colspan='9' align='right'><strong>TOTAL</strong></td>
                                         <td>$". $this::separa_miles( $arr['total'] )."</td>   
                                      </tr>",
               '###excel###'      => $btn,
               '###guia-desp###'  => $guia_desp                       
    
    ];
      return $this::despliegueTemplate( $data, 'insumos-seleccion-3.html' );
    } 

    private function trResumen( $token = null , $id_insumo = null )
    {
      $arr = $this->consultas->listaDetalleGuiaDespachoEgreso( $token , $id_insumo );
    
      $code = "";
    
      $i =0; $total =0;
      foreach ($arr['process'] as $key => $value) {
        # code...
     
        if( $id_insumo )
              $campo_guia ='<td>'. $value['num_guia'] .'</td>';
        else  $campo_guia =null;
    
    
        $campo_guia ='<td>'. $value['num_guia'] .'</td>';


        $total = $total + ( $value['valor'] * $value['cantidad'] );
    
        $data = ['###num###'          => $i+1 , 
                 '###codigo###'       => $value['codigo_final'] ,
                 '###insumo###'       => $value['nombreInsumo'],
                 '###familia###'      =>  $value['familia'],
                 '###stock###'        =>  $value['stock'],
                 '###campo-guia###'   => $campo_guia,  
                 '###rs###'            => $this::sacaRs( $value['token'] ),
                 '###cantidad###'     =>  $value['cantidad'],
                 '###campo-guia###'   =>  $campo_guia,
                 '###valor###'        =>  $this::separa_miles( $value['valor'] ),
                 '###valor-total###'  =>  $this::separa_miles( $value['valor'] * $value['cantidad'] ),
      ];
        $code .= $this::despliegueTemplate( $data , 'tr-insumos-5.html' ) ;
    
      $i++;
    
      }
    
      $out['code']  = $code;
      $out['total'] = $total;
    
      return $out;
    
    }

    public function tablaExcelEgreso( $num_guia = null, $token = null )
    {
      $arr = $this::trResumen( $token );


      $data = [ '###num_guia###' => $num_guia, 
                '###token###'    => $token,
                '###tr###'       => $arr['code'],
                '###total###'    => $this::separa_miles( $arr['total'] ),     
    
    ];
      return $this::despliegueTemplate( $data, 'tabla-egreso-final.html' );
    }


    private function sacaRs( $token = null )
    {
      $arr = $this->consultas->listaGuiaDespachoEgreso( $token );

      $rs = "";

      foreach ($arr['process'] as $key => $value) {
        # code...
        $rs .= $value['rs'];
      }

      return $rs;


    }


    private function generaGuiaDespachoEgreso()
    {
      //  print_r( $_POST );

        $code ="";


        if( $this::procesaDetalleGuiaDespachoEgreso() )
              $crea_cuerpo = true;
        else  $crea_cuerpo = false; 


        //$code .= $this::procesaDetalleGuiaDespachoEgreso();


        if( $this->consultas->ingresaGuiaDespachoEgreso(    $_POST['num_guia']  ,
                                                            $_POST['fecha']  ,
                                                            $_POST['id_cliente']  ,
                                                            $_POST['rs']  ,
                                                            $_POST['token']  ,
                                                            $this->yo ))
        {       $ok = true; }
        else {  $ok = false; }


        $arr = $this->consultas->listaGuiaDespachoEgreso( $_POST['token'] ); 

        foreach ($arr['process'] as $key => $value) {

          $data = [ '###num_guia###'      => $_POST['num_guia'], 
                    '###fecha###'         => $_POST['fecha'],
                    '###cliente###'       => $value['nombreCliente']  ,
                    '###rs###'            => $value['rs'],
                    '###token###'         => $_POST['token'],
                    '###tabla-insumos###' => $this::tablaInsumosRepo( $_POST['token']  ) ];


          $code .= $this::despliegueTemplate( $data, 'genera-guia-despacho-egreso-2.html' );

        }

       return $code; 
    }

    private function tablaInsumosRepo( $token =null )
    {
      //$arr = $this::trTablaInsumoRepo( $token );
    
      $arr = $this::trInsumos2( $token );
      $data = ['###tr###'         => $arr['code'] , 
               '###th-v-total###' => null , 
               '###tfoot###'      => null,
               '###excel###'      => null,
               '###guia-desp###'  => '<th>Num.Guia</th>'
              ];
      return $this::despliegueTemplate( $data, 'insumos-seleccion-2.html' );
    
    }


    private function trInsumos2( $token )
    {
      $code = "";
      $arr = $this->consultas->listaDetalleGuiaDespachoEgreso( $token );
    
      $i = 0;
      foreach ($arr['process'] as $key => $value) {
        # code...
    
        $data = ['###num###'          => $i+1 , 
                 '###codigo###'       => $value['codigo_final'] ,
                 '###num_guia###'     => $value['num_guia'] , 
                 '###insumo###'       => $value['nombreInsumo'], 
                 '###familia###'      => $value['familia'], 
                 '###stock###'        => $value['stock'], 
                 '###valor###'        => $this::separa_miles( $value['valor'] ), 
                 '###id###'           => $value['id'],
                 '###id_insumo###'    => $value['id_insumo'],
    
    
                ];
        $code .= $this::despliegueTemplate( $data, 'tr-insumos-4.html' );
    
    
        $i++;
      }
    
      $out['code'] = $code;
      $out['sql']  = $arr['sql'];
      return $out;      
    }


    private function procesaDetalleGuiaDespachoEgreso()
    {
      $code = "";

      $data = $this::separa( $_POST['id_insumo'], '&' );
      $j    = 0;
    
      $i = 0;
      foreach ($data as $key => $value) {
        $id_insumo = $this::separa( $value,"=" );
                  
            if( $this->consultas->procesaDetalleGuiaDespachoEgreso( $id_insumo[1], $_POST['token'],null,
                                                                    $this::sacaValorActual($id_insumo[1]),
                                                                    $_POST['num_guia'] ) )
              {$j++;}

            //$i++;
            }
    
      if( $j > 0 )
            return true;
      else return  false  ;
    
      //return $code."---".$i;
    }
    
    /**
     * sacaValorActual
     */
    public function sacaValorActual( $id_insumo = null )
    {
      $valor = 0;
      $maxId = 0;

      //sacas el max id del insumo en 
      foreach ($this->consultas->maxIdIngresoInsumo( $id_insumo ) as $key => $value) {
        $maxId = $value['maxId'];
      }

      //sacas el maximoValor
      $arr = $this->consultas->listaDetalleGuiaDespachoIngreso( null,null, $maxId );  


      $i = 0;
      foreach ($arr['process'] as $key => $val) {
        # code...
        $valor = $val['valor'];
        $i++;
      }

      if( $i > 0 )
          if( $valor == '' )
                return 0;
          else  return $valor;
      else      return 0;  
    }


private function procesaDetalleGuiaDespachoEgresoUpdate()
{
  $data_id_detalle = $this::separa( $_POST['id_detalle'], '&' );
  $data_cantidad   = $this::separa( $_POST['cantidad'], '&' );  
  $data_insumo     = $this::separa( $_POST['id_insumo'], '&' );
  $data_stock      = $this::separa( $_POST['stock'], '&' );

  $j    = 0;
  $k    = 0;

  for ($i=0; $i < count( $data_id_detalle ); $i++) { 
    
    $id_detalle = $this::separa( $data_id_detalle[$i],"=" );
    $cantidad   = $this::separa( $data_cantidad[$i],"=" );    
    $id_insumo  = $this::separa( $data_insumo[$i],"=" );
    $stock      = $this::separa( $data_stock[$i],"=" );

    if( $this->consultas->procesaDetalleGuiaDespachoEgreso( null, null,$cantidad[1], null, null,$id_detalle[1] ) )
        $j++;

    $stock_actualizado = $stock[1] - $cantidad[1];

    if( $stock_actualizado < 0 )
      $stock_actualizado  = 0;

    if( $this->consultas->actualizaInsumos($stock_actualizado , $id_insumo[1]) )
        $k++;

  }

  if( $j > 0 )
        return true;
  else return  false  ;

}

  private function ingresaClienteFromEgreso()
  {

  //Array ( [id] => ingresaClienteFromEgreso [nombre_cliente] => jdskjdk 

  if( $this->consultas->procesaClientes( $_POST['nombre_cliente'] ) )
  {       $ok =true; } 
  else {  $ok =false; } 

  if( $ok )
  {
    $arr = $this->consultas->listaClientes();
    $sel = new Select( $arr['process'],
                       'id',
                       'descripcion',
                       "id-cliente",
                       "Cliente" , null,
                       1
                      );

    return $sel->getCode();                  
  }else{

    return "Error: No se puede crear cliente!";
  }  

    
  }

    private function crearGuiaDespachoEgreso()
    {
        $arr = $this->consultas->listaClientes();
        $sel = new Select( $arr['process'],
                           'id',
                           'descripcion',
                           "id-cliente",
                           "Cliente" , null,
                           1
                          );


          $data = [ '###tipo###'            => 'Egreso' , 
                    '###menu-aux###'        => $this->menu_aux, 
                    '###select-cliente###'  => $sel->getCode()    ,
                    '###token###'           => $this->token,    
                    '###tabla-insumos###'   => $this::tablaInsumos() ,
                    '###modal###'           => $this::modal( 'f-cliente', '<i class="fas fa-angle-double-right"></i>',
                                                             'Formulario de Creacion de Clientes', 
                                                             $this::f_cliente() )      
        ];
          return $this::despliegueTemplate( $data, 'genera-guia-despacho-egreso.html' );  
    }

    private function f_cliente()
    {
      return $this::despliegueTemplate( [], 'f-cliente.html' );
    }



    private function tablaInsumos()
    {
      $arr = $this::trInsumos();
    
      if( $arr['total-recs'] > 0 )
      {
        $data = ['###tr###' => $arr['code']  ];
        return $this::despliegueTemplate( $data, 'insumos-seleccion.html' );

      }else{
    
        return "<i class='fas fa-skull-crossbones'></i> ERROR: NO HAY REGISTROS EN ESTA BUSQUEDA";
      }
    }
    
    private function trInsumos()
    {
      $arr = $this->consultas->listaElementos();
    
      $code = "";
    
      foreach ($arr['process'] as $key => $value) {
        # code...
    
        if( $value['codigo_final'] == '' || $value['codigo_final'] == null )
          $codigo = "CODIGO NO ASIGNADO";
        else {
          $codigo = $value['codigo_final'];
        }    
    
        $data = [ '###codigo###'    => $codigo ,
                  '###insumo###'    => $value['nombre'] ,
                  '###id_insumo###' => $value['id'] , 
                  '###familia###'   => $value['nombreSubTipo'] , 
                  '###stock###'     => $value['stock']
                ];
        $code .= $this::despliegueTemplate( $data, 'tr-insumos.html' );
      }
    
    
      $out['code'] = $code;
      $out['total-recs'] = $arr['total-recs'];
    
      return $out;
    
    }


    private function listarGuiaDespachoEgreso()
    {
        //return "{$this->id} En Construccion";

        $data = [ '###menu-aux###'  => $this->menu_aux, 
                  '###tipo###'      => 'Egreso',
                  '###tabla###'     => $this::tablaGuiaDespacho() , 
                  '###buscar###'    => $this::search('Seleccione Numero Guia', 'num_guia', 'b-quest'),
                  '###buscar-fecha###' => $this::buscarFecha()
      
      ];
        return $this::despliegueTemplate( $data, 'lista-guia-despacho-egreso.html' );
    }

    private function buscarFecha()
    {
      $data = ['###select###' => null ];
      return $this::despliegueTemplate( $data, 'buscar-fecha.html' );
    }


    private function tablaGuiaDespacho()
    {

      $arr = $this::trTablaGuiaDespacho();

      $data = ['###tr###' => $arr['code'] ];
      return $this::despliegueTemplate( $data,'tabla-guia-despacho-2.html' );

    }


    private function trTablaGuiaDespacho()
    {
      $code = "";
      $i    = 0;
      
      if( !isset( $_POST['buscaFecha'] ) )
      {
        if( !isset( $_POST['quest'] ) )
                  $arr = $this->consultas->listaGuiaDespachoEgreso();
            else  $arr = $this->consultas->listaGuiaDespachoEgreso(null, 
                                                                 $_POST['num_guia']);
      }else{
                  $arr = $this->consultas->listaGuiaDespachoEgreso( null,
                                                                    null,
                                                                    1,
                                                                    $_POST['fechaInicio'] ,
                                                                    $_POST['fechaFinal']  );
      }

      foreach ($arr['process'] as $key => $value) {
        # code...
    
        if( $value['id_estado'] == 1  )
                $estado = "OPERATIVO";
         else   $estado = "NO OPERATIVO";
    
        $data = [ '###num###'       => $i+1 , 
                  '###num_guia###'  => $value['num_guia'],
                  '###fecha###'     => $this::arreglaFechas( $value['fecha'] ),
                  '###cliente###'   => $value['nombreCliente'],
                  '###estado###'    => $estado , 
                  '###rs###'        => $value['rs'] , 
                  '###token###'     => $value['token'] , 
                  '###usuario###'   => "{$value['nombres']} {$value['apaterno']}",
                  '###modal###'     => $this::modal("ver-insumos-{$value['token']}",
                                                     null, 
                                                    'LISTA DE INSUMOS GUIA DESPACHO # '.$value['num_guia'],
                                                    $this::tablaResumen( $value['token'], $value['num_guia'] , 1 )
                  
                  )
    
                ];
        $code .= $this::despliegueTemplate( $data, 'tr-guias-egreso.html' );
    
        $i++;
    
      }
    
      $out['total-recs'] = $arr['total-recs'];
      $out['code']=$code;
      $out['sql'] = $arr['sql'];
        
      return $out;
    
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

   private function search( $placeholder = null, $id_text = null, $id_button = null )
   {
     $data = ['@@@placeholder'    => $placeholder  ,
              '@@@id_text'        => $id_text ,
               '###id-button###'  => $id_button  ];
     return $this::despliegueTemplate( $data, "buscar.html" );
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