<?php

class GuiaDespachoIngreso 
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
      case 'crear-guia-despacho-ingreso':
        # code...
        return $this::crearGuiaDespachoIngreso();
        break;
    
      case 'listar-guia-despacho-ingreso':
        # code...
        return $this::listarGuiaDespachoIngreso();
        break;

      case 'generaGuiaDespacho':
        # code...
        return $this::generaGuiaDespacho();
        break;  

       case 'finishIngresos':
         # code...
         return $this::finishIngresos();
         break; 

       case 'buscarXfechaIngreso':  
       case 'buscaLaGuia':
         # code...
         return $this::tablaGuiaDespacho();
         break; 

      case 'verificaNumGuia':
        # code...
        return $this::verificaNumGuia();
        break;  

      case 'ingresaProveedorFromIngreso':
        # code...
        return $this::ingresaProveedorFromIngreso();
        break;  


      case 'listarGuiaIngresoPagination':
        # code...
        return $this::tablaGuiaDespacho();
        break;  


    default:
      # code...
      return " {$this->error} para id : { $this->id } ";
      break;
  }
}

private function verificaNumGuia()
{
  
  //print_r( $_POST );

  $arr = $this->consultas->qGuiaDespachoIngreso( $_POST['num_guia'] );

  if( $arr['total-recs']  > 0 )
  {
    return "<span class='rogelio'><i class='far fa-thumbs-down'></i> NUMERO DE GUIA YA UTILIZADO</span>";
  }
  else{
    return "<span class='verde'><i class='far fa-thumbs-up'></i> NUMERO DE GUIA DISPONIBLE</span>";
  }
}




private function finishIngresos()
{
  //print_r( $_POST );

  if( $this::procesaDetalleGuiaDespachoIngresoUpdate() )
        $actualiza = true;
  else  $actualiza = false;

  if( $actualiza  )
        return $this::tablaResumen( $_POST['token'] );
  else  return "REGISTRO NO ACTUALIZADO"; 

}

/**
 * tablaResumen(): tabla de resumen de insumos en una guia de despachos
 * 
 * @param string token
 * @param int num_guia
 * @param int excel
 * 
 * @return string
 * 
 */


public function tablaExcel( $token = null, $num_guia=null )
{

  $code = "";
  $arr = $this->consultas->listaGuiaDespachoIngreso( $token );
  
  foreach ($arr['process'] as $key => $value) {
    # code...
    $data = [ '###num_guia###'  => $num_guia , 
              '###fecha###'     => $this::arreglaFechas( $value['fecha'] ), 
              '###proveedor###' => $value['nombreProveedor'],
              '###tabla###'     => $this::excelInsumos( $token )  
            
            ];
    $code .= $this::despliegueTemplate( $data, 'guia-ingreso-excel.html' );
  }

    return $code;
}

private function excelInsumos( $token = null )
{
  $arr = $this::trExcelInsumos( $token );

  $data = ['###tr###' => $arr['code'],'###total###' => $this::separa_miles( $arr['suma'] ) ];
  return $this::despliegueTemplate($data, 'excelInsumos.html');

}


private function trExcelInsumos( $token = null )
{
  $code = "";

  $arr = $this->consultas->listaDetalleGuiaDespachoIngreso( $token );

  $i =0 ; $suma = 0;
  foreach ($arr['process'] as $key => $value) {
    # code...

    $total = $value['cantidad'] * $value['valor'];
    $suma  = $suma + $total;

    $data = [ '###num###'       => $i+1, 
              '###codigo###'    => $value['codigo_final'] , 
              '###insumo###'    => $value['nombreInsumo'] ,
              '###familia###'   => $value['familia'] ,  
              '###stock###'     => $value['stock'] ,
              '###cantidad###'  => $value['cantidad'] ,
              '###valor###'     =>"$ ". $this::separa_miles( $value['valor'] ),
              '###total###'     =>"$ ". $this::separa_miles( $total )
  ];
    $code .= $this::despliegueTemplate( $data, 'tr-excelInsumos.html' );


    $i++;
  }

  $out['code'] =  $code;
  $out['suma'] =  $suma;
  
  return $out;
}

public function resumenInsumos( $id_insumo = null )
{
  return $this::tablaResumen( null,null,null, $id_insumo );
}


private function tablaResumen(  $token          = null, 
                                $num_guia       = null ,
                                $excel          = null , 
                                $id_insumo      = null,
                                $tipo_documento = null,
                                $proveedor      = null,
                                $fecha          = null ) 
{

  if( $id_insumo )
        $guia_desp ='<th>Num Guia</th>';
  else  $guia_desp =null;

  $target     = "excel-guia-despacho.php?num_guia={$num_guia}&token={$token}"; 
  $target_pdf = "pdf-ingresos.php?num_guia={$num_guia}&token={$token}&fecha={$fecha}&tipo_documento={$tipo_documento}&proveedor={$proveedor}"; 

  if( is_null( $excel ) )
        {
          $btn = null;
          $btn_pdf = null;
        }
  else{
          $btn = '<a href="'.$target.'" class="btn btn-success btn-sm outline-line-verde">
                      <i class="far fa-arrow-alt-circle-right"></i> Exportar a Excel
                  </a>'; 

          $btn_pdf = '<a href="'.$target_pdf.'" class="btn btn-danger btn-sm outline-line-rojo" target="_blank">
                          <i class="far fa-file-pdf"></i> Exportar a PDF
                      </a>';              
            }          


  $arr = $this::trResumen( $token , $id_insumo );
  $data = ['###th-v-total###' => '<th>Total</th>' , '###tr###' => $arr['code'] ,
           '###tfoot###'      => "<tr>
                                     <td colspan='7' align='right'><strong>TOTAL</strong></td>
                                     <td>$". $this::separa_miles( $arr['total'] )."</td>   
                                  </tr>",
           '###excel###'      => $btn,
           '###pdf###'        => $btn_pdf,
           '###guia-desp###'  => $guia_desp                       

];
  return $this::despliegueTemplate( $data, 'insumos-seleccion-2.html' );
} 

private function trResumen( $token = null , $id_insumo = null )
{
  $arr = $this->consultas->listaDetalleGuiaDespachoIngreso( $token , $id_insumo );

  $code = "";

  $i =0; $total =0;
  foreach ($arr['process'] as $key => $value) {
    # code...
 
    if( $id_insumo )
        $campo_guia ='<td>'. $this::sacaGuia( $value['token'] ).'</td>';
    else  $campo_guia =null;


    $total = $total + ( $value['valor'] * $value['cantidad'] );

    $data = ['###num###'          => $i+1 , 
             '###codigo###'       => $value['codigo_final'] ,
             '###insumo###'       => $value['nombreInsumo'],
             '###familia###'      =>  $value['familia'],
             '###stock###'        =>  $value['stock'],
             '###cantidad###'     =>  $value['cantidad'],
             '###campo-guia###'   =>  $campo_guia,
             '###valor###'        =>  $this::separa_miles( $value['valor'] ),
             '###valor-total###'  =>  $this::separa_miles( $value['valor'] * $value['cantidad'] ),
  ];
    $code .= $this::despliegueTemplate( $data , 'tr-insumos-3.html' ) ;

  $i++;

  }

  $out['code']  = $code;
  $out['total'] = $total;

  return $out;
}

/**
 * dataInsumos(): array de datos de insumos de Ingreso
 * 
 * @param string token
 * @param int id_insumo ( la verdad no se ocupa )
 * @return array()
 */
public function dataInsumos( $token = null, $id_insumo = null )
{
  $arr = $this->consultas->listaDetalleGuiaDespachoIngreso( $token , $id_insumo );
  $i =0; 
  $data = array();

  foreach ($arr['process'] as $key => $value) {
    # code...
    $data[ $i ]['codigo_final'] = $value['codigo_final'];
    $data[ $i ]['nombreInsumo'] = $value['nombreInsumo'];
    $data[ $i ]['familia']      = $value['familia'];
    $data[ $i ]['stock']        = $value['stock'];
    $data[ $i ]['cantidad']     = $value['cantidad'];
    $data[ $i ]['valor']        = $value['valor'];    
  $i++;
  }

  return $data;
}



private function sacaGuia( $token = null )
{
  $ARR = $this->consultas->listaGuiaDespachoIngreso( $token );

  $num_guia = "";

  foreach ($ARR['process'] as $key => $value) {
    # code...
    $num_guia .= $value['num_guia'];
  }


  return $num_guia;
  
}


private function generaGuiaDespacho()
{

 //print_r($_POST); 

  $code = "";

  if( $this::procesaDetalleGuiaDespachoIngreso() )
        $crea_cuerpo = true;
  else  $crea_cuerpo = false;  


  if( $this->consultas->ingresaGuiaDespachoIngreso( $_POST['num_guia'], $_POST['fecha'], $_POST['id_proveedor'],$_POST['token'] , 
                                                    $this->yo, $_POST['id_tipo_documento'] ) )
       { $ok = true; }
  else { $ok = false; }

  $arr = $this->consultas->listaGuiaDespachoIngreso( $_POST['token'] ); 

  foreach ($arr['process'] as $key => $value) {
    # code...
    $data = [ '###num_guia###'        => $_POST['num_guia'], 
              '###fecha###'           => $_POST['fecha'],
              '###proveedor###'       => $value['nombreProveedor']  ,
              '###token###'           => $_POST['token'],
              '###tipo-documento###'  => $value['tipo_documento'],
              '###tabla-insumos###'   => $this::tablaInsumosRepo( $_POST['token'] )
            
            ];
    $code .= $this::despliegueTemplate( $data, 'genera-guia-despacho-ingreso-2.html' );


  }

  if( $ok  )
        return $code;
  else  return "ERROR:NO DATA!!!"; 

}

private function procesaDetalleGuiaDespachoIngreso()
{
  $data = $this::separa( $_POST['id_insumo'], '&' );
  $j    = 0;

  $i = 0;
  foreach ($data as $key => $value) {
    $id_insumo = $this::separa( $value,"=" );

        if( $this->consultas->procesaDetalleGuiaDespachoIngreso( $id_insumo[1], $_POST['token'] ) )
          $j++;
        }

  if( $j > 0 )
        return true;
  else return  false  ;

}

private function procesaDetalleGuiaDespachoIngresoUpdate()
{
  $data_id_detalle = $this::separa( $_POST['id_detalle'], '&' );
  $data_cantidad   = $this::separa( $_POST['cantidad'], '&' );
  $data_valor      = $this::separa( $_POST['valor'], '&' );
  $data_insumo     = $this::separa( $_POST['id_insumo'], '&' );
  $data_stock      = $this::separa( $_POST['stock'], '&' );

  $j    = 0;
  $k    = 0;

  for ($i=0; $i < count( $data_id_detalle ); $i++) { 
    
    $id_detalle = $this::separa( $data_id_detalle[$i],"=" );
    $cantidad   = $this::separa( $data_cantidad[$i],"=" );
    $valor      = $this::separa( $data_valor[$i],"=" );
    $id_insumo  = $this::separa( $data_insumo[$i],"=" );
    $stock      = $this::separa( $data_stock[$i],"=" );

    if( $this->consultas->procesaDetalleGuiaDespachoIngreso( null, null,$cantidad[1], $valor[1], $id_detalle[1] ) )
        $j++;

    $stock_actualizado = $stock[1] + $cantidad[1];

    #echo "STOCK ACTUAL {$stock_actualizado}";

    if( $this->consultas->actualizaInsumos($stock_actualizado , $id_insumo[1]) )
        $k++;

  }

  if( $j > 0 )
        return true;
  else return  false  ;

}




private function tablaInsumosRepo( $token =null )
{
  $arr = $this::trTablaInsumoRepo( $token );

  ///$arr = $this::trInsumos();
  $data = ['###tr###' => $arr['code'] , '###th-v-total###' => null , '###tfoot###' => null ];
  return $this::despliegueTemplate( $data, 'insumos-seleccion-2.html' );

}

private function trTablaInsumoRepo( $token = null )
{

  $code = "";
  $arr = $this->consultas->listaDetalleGuiaDespachoIngreso( $token );

  $i = 0;
  foreach ($arr['process'] as $key => $value) {
    # code...

    $data = ['###num###'          => $i+1 , 
             '###codigo###'       => $value['codigo_final'] , 
             '###insumo###'       => $value['nombreInsumo'], 
             '###familia###'      => $value['familia'], 
             '###stock###'        => $value['stock'], 
             '###id###'           => $value['id'],
             '###id_insumo###'    => $value['id_insumo'],


            ];
    $code .= $this::despliegueTemplate( $data, 'tr-insumos-2.html' );


    $i++;
  }

  $out['code'] = $code;
  $out['sql']  = $arr['sql'];
  return $out;
}


private function crearGuiaDespachoIngreso()
{
  $arr_td = $this->consultas->tipoDocumento() ;
  $arr    = $this->consultas->listaProveedores();
  $sel    = new Select( $arr['process'],
                     'id',
                     'descripcion',
                     "id-proveedor",
                     "Proveedor", null,
                     1
                    );


  $sel_tipo_documento = new Select( $arr_td['process'],
                       'id','descripcion','id_tipo_documento', null,1 );



  $data = ['###tipo###'                  => 'INGRESO' , 
           '###select-proveedor###'      => $sel->getCode(),
           '###menu-aux###'              => $this->menu_aux , 
           '###token###'                 => $this->token           ,
           '###tabla-insumos###'         => $this::tablaInsumos(), 
           '###select-tipo-documento###' => $sel_tipo_documento->getCode(),
           '###modal###'                 => $this::modal( 'f-proveedor', 
                                                          '<i class="fas fa-angle-double-right"></i>',
                                                          'Formulario de Creacion de Proveedores', 
                                                          $this::f_proveedor() )
          
          ];
  return $this::despliegueTemplate( $data, 'genera-guia-despacho-ingreso.html' );
}

 private function ingresaProveedorFromIngreso()
 {
 //  print_r( $_POST );

  if( $this->consultas->procesaProveedores( htmlentities( $_POST['nombre_proveedor'] ),
                                            $this->token, 
                                            htmlentities( $_POST['rut_proveedor'] ) ) )
  {
    $ok = true;
  }
  else { $ok = false; }

  if( $ok )
  {
    $arr = $this->consultas->listaProveedores();
    $sel = new Select( $arr['process'],
                      'id',
                      'descripcion',
                      "id-proveedor",
                      "Proveedor", null,
                      1
                      ); 

    return $sel->getCode();                  
  
  }else{
    return "ERRROR: No se puede ingresar proveedores";
  }
 } 


private function f_proveedor()
{
  return $this::despliegueTemplate( [], 'f-proveedor.html' );
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



private function listarGuiaDespachoIngreso()
{
  //return "MODULO EN CONSTRUCCION PARA {$this->id}";

  $data = ['###menu-aux###'     => $this->menu_aux , 
           '###tipo###'         => 'Ingreso' , 
           '###tabla###'        => $this::tablaGuiaDespacho() ,
           '###buscar-fecha###' => $this::buscarFecha(), 
           '###buscar###'       => $this::search( 'Busque numero de Guia de despacho', 'num_guia', 'buscar-btn' ) 
          
          ];
  return $this::despliegueTemplate( $data, 'lista-guia-despacho-ingreso.html' );

}


private function buscarFecha()
{
  $arr    = $this->consultas->tipoDocumento();
  $select = new Select( $arr['process'],'id','descripcion','id_tipo_documento','Tipo de Documento'  );

  $data = ['###select###' => $select->getCode() ];
  return $this::despliegueTemplate( $data, 'buscar-fecha.html' );
}


/**
 * 
 */
private function tablaGuiaDespacho()
{
  $arr = $this::trTablaGuiaDespacho();

  if( $arr['total-recs'] > 0 )
  {
    $data = [ '###tr###'        => $arr['code'] , 
              '###nav-links###' => $arr['nav-links'] ];

    return $this::despliegueTemplate( $data,'tabla-guia-despacho-1.html' )  ;

  }else {

    $btn = "<a href='#' class='btn btn-sm btn-secondary' role='button'><i class='fas fa-sync-alt'></i> Recargar</a>";

    return "<i class='fas fa-exclamation-triangle'></i> <strong>ERROR:</strong> NO HAY REGISTROS EN ESTA BUSQUEDA {$btn}";
  }  
}

private function trTablaGuiaDespacho()
{
  $code = "";
  $i    = 0;
  
  if( !isset( $_POST['buscaFecha'] ) )
  {
    if( !isset( $_POST['quest'] ) )
          {
            $arr = $this->consultas->listaGuiaDespachoIngreso();
            $utils      = new utiles($arr['sql']);
            $rs_dd      = $utils->show();
            $nav_links  = $rs_dd['nav_links'];
            $param      = $rs_dd['result'] ;
          
          }
    else{
      $arr = $this->consultas->listaGuiaDespachoIngreso(null, 
            $_POST['num_guia']);

      $nav_links  = null;
      $param      = $arr['process'] ;      
    } 
  }else{

    $arr = $this->consultas->listaGuiaDespachoIngreso(null, 
                                                      null,
                                                      null, 1,
                                                      $_POST['fechaInicio'],
                                                      $_POST['fechaFinal'] ,
                                                      $_POST['id_tipo_documento']
                                                    );
    $nav_links  = null;
    $param      = $arr['process'] ;                                                       

  }

  foreach ($param as $key => $value) {
    # code...

    if( $value['id_estado'] == 1  )
            $estado = "OPERATIVO";
     else   $estado = "NO OPERATIVO";

    $data = [ '###num###'       => $i+1 , 
              '###num_guia###'  => $value['num_guia'],
              '###tipo-documento###' =>  $value['tipo_documento'],
              '###fecha###'     => $this::arreglaFechas( $value['fecha'] ),
              '###proveedor###' => $value['nombreProveedor'],
              '###estado###'    => $estado , 
              '###token###'     => $value['token'] , 
              '###usuario###'   => "{$value['nombres']} {$value['apaterno']}",              
              '###modal###'     => $this::modal("ver-insumos-{$value['token']}",
                                                 null, 
                                                'LISTA DE INSUMOS '.$value['tipo_documento'].' # '.$value['num_guia'],
                                                $this::tablaResumen( $value['token'], 
                                                                     $value['num_guia'] ,
                                                                     1,null,
                                                                     $value['tipo_documento'],
                                                                     $value['nombreProveedor'], 
                                                                     $this::arreglaFechas( $value['fecha'] ))
              
              )

            ];
    $code .= $this::despliegueTemplate( $data, 'tr-guias.html' );

    $i++;

  }

  $out['total-recs']  = $arr['total-recs'];
  $out['code']        = $code;
  $out['nav-links']   = $nav_links;
  $out['sql']  = $arr['sql'];


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