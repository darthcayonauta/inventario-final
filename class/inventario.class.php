<?php
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class Inventario
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

      case 'fileForm':
        return $this::fileForm();
        break;

      case 'inicio':
      case 'buscarInventario':
      case 'listarInventario':
        return $this::listarInventario();
        break;

      case 'crearInventario':
        return $this::crearInventario();
        break;

      case 'ingresaInventarioData':
        return $this::ingresaInventarioData();
        break;

      case 'editarInventario':
        return $this::editarInventario();
        break;

      case 'editaInventarioData':
        return $this::editaInventarioData();
        break;

      case 'enviaStockCriticoUnitario':
        return $this::enviaStockCriticoUnitario();
        break;

      case 'ingresaDataIngreso':
        return $this::ingresaDataIngreso();
        break;

      case 'ingresaDataIngreso2':
        return $this::ingresaDataIngreso2();
        break;

      case 'ingresaDataEgreso':
        return $this::ingresaDataEgreso();
        break;

      case 'ingresa_nombre_trabajo_from_egreso':
        return $this::ingresa_nombre_trabajo_from_egreso();
        break;


      case 'ingresa_nombre_destino_from_egreso':

        return $this::ingresa_nombre_destino_from_egreso();
        break;

      case 'registro-acciones':
        return $this::registraAcciones();
        break;


      case 'deleteInventario':
        return $this::deleteInventario();
        break;

      case 'verificaDispCodigo':
        # code...
        return $this::verificaDispCodigo();
        break;  

        case 'guia-despacho':
          # code...

          return $this::guiaDespacho();
          break;

       case 'buscarGuiaDespacho':
      
        return $this::tablaGuiaDespacho();
        break;

       case 'guia-despacho-egresos':
         # code...
         return $this::guiaDespachoEgresos();
         break; 

       case 'buscarGuiaDespachoEgreso':
         # code...
         return $this::tablaGuiaDespachoEgreso();
         break; 

      case 'sacaAutoCodigoFinal':
        # code...
        return $this::sacaAutoCodigoFinal();
        break;  


      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }


private function sacaAutoCodigoFinal()
{
  $arr = $this->consultas->maxCodigoImsumo( $_POST['id_subtipo'] );
  $code = "";

  foreach ($arr['process'] as $key => $value) {

    $cod_final = $value['max_codigo_final'] +1;
    
    //en caso de resultar 1
    if( $cod_final == 1 )
    {

      switch ($_POST['id_subtipo']) {
        case 95:
          
          $cod_final = 1010001;
          break;
        
        case 90:
          
          $cod_final = 1020001;
          break;  

        case 91:
          
          $cod_final = 1030001;
          break;

        case 78:
          
          $cod_final = 1040001;
          break;          

        case 94:
          
          $cod_final = 1050001;
          break;

        case 92:
          
          $cod_final = 1060001;
          break;          

        case 93:
          
          $cod_final = 1070001;
          break;

        case 96:  
          $cod_final = 1080001;
          break;

        case 97:  
          $cod_final = 1090001;
          break;



        default:
          # code...
          break;
        }
      }
    }

    # code...
    $data = ['###codigo_final###' => $cod_final ];
    $code .= $this::despliegueTemplate( $data, 'codigo-final.html' );

  return $code;
}

 private function buscarGuiaDespachoEgreso()
 {
   print_r( $_POST );
 }

  private function guiaDespachoEgresos()
  {
    $data = ['###tipo###'       => 'Egresos', 
             '###menu-aux###'   => $this->menu_aux  ,
             '###buscar###'     => $this::search('Ingrese Guia de Despacho ( Egreso )',
                                                 'guia-despacho',
                                                 'buscar-guia-despacho'),
             '###tabla###'     => $this::tablaGuiaDespachoEgreso()                                                    
  
  ];

    return $this::despliegueTemplate( $data, 'guias-despacho.html' );
  }


  private function tablaGuiaDespachoEgreso()
  { 
    //print_r( $_POST );

    $arr =  $this::trTablaGuiaDespachoEgreso();

    if( $arr['total-recs'] > 0 )
    {
      $data = ['###total-recs###' => $arr['total-recs'] , 
               '###tr###' => $arr['code'] , '###guia-rs###' => 'RS'   ];
      return $this::despliegueTemplate( $data, 'tabla-guia-despacho.html' );

    }else return "NO EXISTEN DATOS!!!";

  }


  private function trTablaGuiaDespachoEgreso()
  {
    $code = "";
    if( !isset( $_POST['guia-despacho'] ) )
          $arr = $this->consultas->guiasDespachoEgreso();
    else  $arr = $this->consultas->guiasDespachoEgreso( $_POST['guia-despacho'] );


    $i = 0;
    foreach ($arr['process'] as $key => $value) {
      # code...
      $data = [ '###num###'           => $i+1 , 
                '###estado###'        => $this::determinaEstado( $value['id_estado'] ),
                '###guia_despacho###' => $value['id_proyecto'] , 
                '###modal###'         => $this::modal(  "detalle-guia-{$value['id_proyecto']}", null, 
                                                        "RS/Guia Despacho ( Egreso ):  {$value['id_proyecto']}",
                                                        $this::listaEgresos2( $value['id_proyecto'] ) )  ];
      $code .= $this::despliegueTemplate( $data, 'tr-tabla-guia-despacho.html' );

      $i++;
    }

    $out['code'] = $code;
    $out['total-recs'] = $arr['total-recs'];

    return $out;
  }

  private function listaEgresos2( $id_proyecto = null  )
  {

    $arr = $this::trListaEgresos2( $id_proyecto );


    return $this::despliegueTemplate( ['###tr###'             => $arr['code'],
                                       '###total###'          => $this::separa_miles(  $arr['suma_total'] ),  
                                       '###guia-despacho###'  => $id_proyecto    ],
                                       'tabla-egresos-2.html');
  }
  
  private function trListaEgresos2( $id_proyecto = null  )
  {
    $arr = $this->consultas->listaEgresos2( $id_proyecto );
    $code = "";
  

    $suma_total = 0;
    foreach ($arr['process'] as $key => $value) {
  
      $responsable = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

      if( is_null( $value['valor'] ) )
            $valor = 0;
      else  $valor = $value[ 'valor' ];   

      $suma_total = $suma_total + ( $valor * $value['cantidad'] );

      $data = [ '###fecha###'           => $value['fecha'],
                '###codigo###'          => $value['codigo'],
                '###nombre-producto###' => $value['insumo'],
                '###responsable###'     => $responsable,           
                '###cantidad###'        => $value['cantidad'],
                '###description###'     => $value['comentario'],
                '###valor-unitario###'  => $this::separa_miles(  $valor ), 
                '###valor-total###'     => $this::separa_miles(  $valor * $value['cantidad'] ) ];
  
      $code .= $this::despliegueTemplate( $data, 'tr-egresos-2.html' );
  
    }
  
    $out ['code'] = $code; 
    $out ['suma_total'] = $suma_total;

    return $out;
  }


public function egresosExcel( $id_proyecto = null )
{

  $arr = $this::trListaEgresos2( $id_proyecto );

  $data = ['###tr###'             => $arr['code'], 
           '###guia-despacho###'  => $id_proyecto , 
           '###suma_total###'     => $this::separa_miles( $arr['suma_total'] )                 ];
  return  $this::despliegueTemplate( $data, 'tabla-egresos-excel.html' );  
}



  private function search( $placeholder = null, $id_text = null, $id_button = null )
  {
    $data = ['@@@placeholder'    => $placeholder  ,
             '@@@id_text'        => $id_text ,
              '###id-button###'  => $id_button  ];
    return $this::despliegueTemplate( $data, "buscar.html" );
  }


private function guiaDespacho()
{
  $data = ['###menu-aux###' => $this->menu_aux , 
           '###tipo###'     => 'Ingreso',             
           '###tabla###'    => $this::tablaGuiaDespacho(),
           '###buscar###'   => $this::search('Ingrese Guia de Despacho','guia-despacho','buscar-btn')
          ];
  return $this::despliegueTemplate( $data, 'guias-despacho.html' );

}

private function tablaGuiaDespacho()
{

  $arr = $this::trTablaGuiaDespacho();

  if( $arr['total-recs'] > 0 )
  {
    $data = [ '###total-recs###' => $arr['total-recs'] ,
              '###tr###'         => $arr['code'] , '###guia-rs###'  => 'GUIA DE DESPACHO',    ];

    return $this::despliegueTemplate( $data, 'tabla-guia-despacho.html' );

  }else return "NO EXISTEN REGISTROS.-";

}

private function trTablaGuiaDespacho()
{
  $code = "";

  if( !isset( $_POST['guia-despacho'] ) )
        $arr = $this->consultas->guiasDespacho();
  else  $arr = $this->consultas->guiasDespacho( $_POST['guia-despacho'] ); 

  $i =0;
  foreach ($arr['process'] as $key => $value) {
    # code...
    $data = ['###num###'          => $i +1 , 
            '###guia_despacho###' => $value['num_documento'],
            '###estado###'        => $this::determinaEstado( $value['id_estado'] ),
            '###modal###'         => $this::modal( "detalle-guia-{$value['num_documento']}", null, 
                                                   "Guia de Despacho:  {$value['num_documento']}",
                                                   $this::listaIngrsesos_guia( $value['num_documento'] )  )  ];


    $code .= $this::despliegueTemplate( $data, 'tr-tabla-guia-despacho.html' );
  
    $i++;
  }

 $out['code'] = $code;
 $out['total-recs'] = $arr['total-recs'];
 
 return $out;

}

/**
 * determinaEstado()
 * @param int id_estado
 * @return string
 */
private function determinaEstado( $id_estado = null )
{
  switch ($id_estado) {
    case 1:
      # code...
      return "VIGENTE";      
      break;
    case 2:
      # code...
      return "ANULADA";      
      break;

    default:
      # code...
      return "NO DETERMINADA";      
      break;
  }
}


private function verificaDispCodigo()
{
  #print_r( $_POST );

  $arr = $this->consultas-> listaCodigoFinal( $_POST['codigo-final']);

  if(  $arr['total-recs'] > 0 )
  {
    return "<span class='rogelio'><i class='far fa-thumbs-down'></i> CODIGO OCUPADO, NO UTILIZAR</span>";
  
  }else{

      $codigo_final = $_POST['codigo-final'];

      if( $codigo_final > 1010000 &&  $codigo_final < 1071000)
      {
        return "<span class='verde'><i class='far fa-thumbs-up'></i> CODIGO DISPONIBLE</span>";
      }else{
        return "<span class='rogelio'><i class='far fa-thumbs-down'></i> CODIGO NO VALIDO</span>";
      }
  }
}


 private function registraAcciones()
 {
   switch ($this->tipo_usuario) {
     case 3:
     case 5:
       $menu_aux = $this::menu_admin();
       break;

     default:
       $menu_aux = null;
       break;
     }

  $arr = $this::trRegistraAcciones();

   $data = ['###menu_aux###'   => $menu_aux ,
            '###total-recs###' => $arr['total-recs'],
            '###tr###'         => $arr['code']
  ];

   return $this::despliegueTemplate( $data, 'lista-eventos-admin.html' );

 }

 private function trRegistraAcciones()
 {
    $arr  = $this->consultas->eventosAdmin();
    $code = "";

    $i = 0;
    foreach ($arr['process'] as $key => $value) {

      $responsable = "{$value['nombres']}  {$value['apaterno']} {$value['amaterno']}";

      $data = ['###fecha###' => $value['fecha'],
               '###num###'   => $i +1,
               '###descripcion###' => $value['descripcion'],
               '###responsable###' => $responsable

              ];

      $code .= $this::despliegueTemplate( $data, "tr-eventos-admin.html" );

    $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];

    return $out;
 }

  private function ingresaDataIngreso2()
  {

    /*desgloce*/

    if( $_FILES['archivo']['size'] == 0 )
    {
      if( $this->consultas->ingresaIngresosData( 	$_POST['codigo']          ,
       																			      $_POST['id_inventario']  ,
       																			      $_POST['insumo']  ,
       																			      $_POST['id-proveedor']   ,
       																			      $this->yo 			  ,
      																			      $_POST['cantidad'] 		  ,
      																			      $_POST['tipo-documento'] ,
      																			      $_POST['num-documento']  ,
      																			      'no-image.png', $_POST['valor'], $_POST['guia_despacho'] 				 ) )
      { $ingresa = true;  $this->msg = null;  }else
      { $ingresa = false; $this->msg = "error en db"; }
    }else{

    //subida de archivo
    require_once("ftp2.class.php");
    $ob_ftp = new FTP2( "archivo", $this->token );

      if( $ob_ftp->validaFile( $_FILES["archivo"]["name"] ) )
          if( $ob_ftp->procesaFTP() )
            if( $this->consultas->ingresaIngresosData( 	$_POST['codigo']          ,
                                                        $_POST['id_inventario']  ,
                                                        $_POST['insumo']  ,
                                                        $_POST['id-proveedor']   ,
                                                        $this->yo 			  ,
                                                        $_POST['cantidad'] 		  ,
                                                        $_POST['tipo-documento'] ,
                                                        $_POST['num-documento']  ,
                                                        $ob_ftp->changeNameFile( $_FILES['archivo']['name'] ),$_POST['valor'], 
                                                        $_POST['guia_despacho']  ) )
            {
                $ingresa = true;  $this->msg = null;
            }else{
                $ingresa = false; $this->msg = "error en db";
            }
          else{ $ingresa = false; $this->msg = "error en FTP";  }
        else{   $ingresa = false; $this->msg = "error en Tipo de Archivo";   }
    }

    if( $ingresa  )
    {

      if( $this->consultas->actualizaStock( $_POST['cantidad'],
                                            $_POST['id_inventario'] ) )
      {
          $arr = $this->consultas->listaElementos( $_POST['id_inventario'] );
          $stock = 0;
          foreach ($arr['process'] as $key => $value) {
            $stock = $value['stock'];
          }

          return "{$stock} <br/> {$this->btn_refresh}";

      }else{
          return "Error en actualizar";
      }

    }else{
      return "Error en todo!!! {$this->msg}";
    }

  }

  private function ingresa_nombre_destino_from_egreso()
  {


    if( $this->consultas->procesadestino( addslashes( $_POST['nombre_destino'] )) )
    {
      $msg = "<strong>Trabajo Ingresado Correctamente</strong>";
      $arr = $this->consultas->listaDestino();

      $ob_select = new Select( $arr['process'], 'id','descripcion',
                              "id-destino-{$_POST['id_insumo']}",
                              'Destino' );

      return "{$msg}<br>{$ob_select->getCode()}";

    }
    else{

      $msg = "<strong>Error al Ingresar Destino</strong>";
      $arr = $this->consultas->listaDestino();

      $ob_select = new Select( $arr['process'], 'id','descripcion',
                              "id-destino-{$_POST['id_insumo']}",
                              'Destino' );

      return "{$msg}<br>{$ob_select->getCode()}";
    }
  }

  /**
   *
   */
  private function ingresa_nombre_trabajo_from_egreso()
  {

      if( $this->consultas->procesaTrabajos( addslashes( $_POST['nombre_trabajo'] ) ) )
      {

        $msg = "<strong>Trabajo Ingresado Correctamente</strong>";
        $arr = $this->consultas->listaTrabajos();

        $ob_select = new Select( $arr['process'], 'id','descripcion',
                                "id-trabajo-{$_POST['id_insumo']}",
                                'Trabajo' );

        return "{$msg}<br>{$ob_select->getCode()}";

      }else{

        $msg = "<strong>Error al Ingresar Trabajo</strong>";
        $arr = $this->consultas->listaTrabajos();

        $ob_select = new Select( $arr['process'], 'id','descripcion',
                                "id-trabajo-{$_POST['id_insumo']}",
                                'Trabajo' );

        return "{$msg}<br>{$ob_select->getCode()}";

      }
  }

  private function ingresaDataEgreso()
  {
    //$id_proyecto = "{$_POST['id_trabajo']}{$_POST['id_cliente']}{$_POST['id_destino']}";

    if( $this->consultas->ingresaEgresos(   $_POST['codigo'],
           																  $this->yo     ,
           																  $_POST['insumo']  ,
           																  $_POST['id_trabajo']  ,
           																  $_POST['id_cliente']  ,
           																  $_POST['id_destino']  ,
           																  $_POST['cantidad']  ,
           																  addslashes( $_POST['comentarios'] ) , $_POST['rs'], $_POST['valor']    ) )
    { $ingresa = true; }
    else {
      $ingresa = false;
    }

    if( $ingresa  )
    {
      if( $this->consultas->actualizaStock( $_POST['cantidad'],
                                            $_POST['id_inventario'] , 1 ) )
      {
          $arr = $this->consultas->listaElementos( $_POST['id_inventario'] );
          $stock = 0;
          foreach ($arr['process'] as $key => $value) {
            $stock = $value['stock'];
          }

          return "{$stock}  <br/>  {$this->btn_refresh}";

      }else{
          return "Error en actualizar";
      }

    }else{
      return "Error en todo!!!";
    }
  }

  private function ingresaDataIngreso()
  {
    if( $this->consultas->ingresaIngresosData(  $_POST['codigo'],
                                                $_POST['id_inventario'],
                                                addslashes( $_POST['insumo'] ),
                                                $_POST['id_proveedor'],
                                                $this->yo,
                                                $_POST['cantidad']) )
        {  $ingresa = true; }
    else{  $ingresa = false; }

    if( $ingresa  )
    {
      if( $this->consultas->actualizaStock( $_POST['cantidad'],
                                            $_POST['id_inventario'] ) )
      {
          //return $_POST['cantidad'];

          $arr = $this->consultas->listaElementos( $_POST['id_inventario'] );
          $stock = 0;
          foreach ($arr['process'] as $key => $value) {
            $stock = $value['stock'];
          }

          return $stock;

      }else{
          return "Error en actualizar";
      }

    }else{
      return "Error en todo!!!";
    }
  }

  /**
   * criticos(): envio de mensajes criticos
   * @return string
   */
  public function criticos()
  {
    /*
    Ingresando las alertas
    */

    if( $this->consultas->ingresaAlertas() )
          $ingresaAlert = true;
    else  $ingresaAlert = false;

    /*
      consultando las alertas
    */
    $arr_alertas = $this->consultas->alertas();

    /*
    resto del codigo
    */
    $arr = $this::trCriticos();

    if( $arr['total-recs'] == 0 )
      return null;
    else{

      $code = "";

      $data = ['###tr###' => $arr['code'],
               '###total-recs###' => $arr['total-recs']
      ];

      $code .= $this::despliegueTemplate( $data, 'tabla-criticos.html',1 );

      if( $arr_alertas['total-recs'] < 4  )
      {
        $ob_mail = new mails( 'enviaStockCriticoUnitario',
                              $this->email,
                              $code   );

      }else{
        $ob_mail = new mails( 'enviaStockCriticoUnitarioSup',
                              $this->email,
                              $code   );

        //eliminas los registro de alerta
        if( $this->consultas->EliminaAlertas() )
              $del = true;
        else  $dek = false;
      }


      if( $ob_mail->getCode() )
           return "Msg enviado";
      else return "El Msg no se puede enviar";


    }
  }

  /**
   * trCriticos(): lista de elementos criticos para ser enviados por mail
   *
   * @return string
   */
  private function trCriticos()
  {
    $arr  = $this->consultas->criticos();
    $i    = 0;
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      $data = ['###num###'            => $i+1,
               '###codigo###'         => $value['codigo'],
               '###nombre###'         => $this::codifica( substr( $value['nombre'], 0,28),2 ),
               '###nombre2###'        => $this::codifica( $value['nombre'],2 ),
               '###ubicacion###'      => $this::codifica( $value['nombreUbicacion'],2 ),
               '###sub_ubicacion###'  => $this::codifica( $value['nombreSubUbicacion'],2),
               '###tipo###'           => $this::codifica( $value['nombreTipo'] ,2 ),
               '###sub_tipo###'       => $this::codifica( $value['nombreSubTipo'] ,2),
               '###stock###'          => $value['stock'],
               '###id###'             => $value['id']];

      $code .= $this::despliegueTemplate( $data, "tr-criticos.html",1 );
    }

    $out['code'] = $code;
    $out['total-recs'] = $arr['total-recs'];

    return $out;
  }

  private function editaInventarioData()
  {
    if( isset( $_POST['archivo'] ) )
    {
      if( $this->consultas->procesaElmento( $_POST['elemento'],
                                            $_POST['descripcion'],
                                            $_POST['cantidad'],
                                            $_POST['cantidad_minima'],
                                            $this->yo,
                                            $_POST['id_tipo'],
                                            $_POST['sub_tipo'],
                                            $_POST['idUbicacion'],
                                            $_POST['sub-ubicacion'],
                                            null,
                                            $_POST['archivo'],
                                            $_POST['id_unidad'],
                                            $_POST['codigo_final'],
                                            $_POST['id_proveedores'],
                                            $_POST['valor'],
                                            $_POST['id_afirmacion'],
                                            $_POST['id_inventario']  ) )
      {
          //procesaGemela
          if ( $this->consultas->procesaGemela( addslashes( $_POST['elemento']),
                                                $_POST['descripcion'],
                                                $_POST['cantidad'],
                                                $_POST['cantidad_minima'],
                                                $this->yo,
                                                $_POST['id_tipo'],
                                                $_POST['sub_tipo'],
                                                $_POST['idUbicacion'],
                                                $_POST['sub-ubicacion'],
                                                $_POST['codigo'] ,
                                                $_POST['archivo'] ))
          {
                 $ok = true;
          }else{ $ok = false;  }

          $sube = true;
          $this->msg ="Inventario Actualizado Correctamente";
          $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
      }
      else {
          $sube = false;
          $this->msg ="Error en Consulta 1";
          $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';

        }
    }
    else{
      if( $_FILES['archivo']['size'] == 0 )
      {
        if( $this->consultas->procesaElmento( $_POST['elemento'],
                                              $_POST['descripcion'],
                                              $_POST['cantidad'],
                                              $_POST['cantidad_minima'],
                                              $this->yo,
                                              $_POST['id_tipo'],
                                              $_POST['sub_tipo'],
                                              $_POST['idUbicacion'],
                                              $_POST['sub-ubicacion'],
                                              null,
                                              'no-image.png',
                                              $_POST['id_unidad'],
                                              $_POST['codigo_final'],
                                              $_POST['id_proveedores'],
                                              $_POST['valor'],
                                              $_POST['id_afirmacion'],
                                              $_POST['id_inventario'] ) )
        {

          if ( $this->consultas->procesaGemela( addslashes( $_POST['elemento']),
                                                $_POST['descripcion'],
                                                $_POST['cantidad'],
                                                $_POST['cantidad_minima'],
                                                $this->yo,
                                                $_POST['id_tipo'],
                                                $_POST['sub_tipo'],
                                                $_POST['idUbicacion'],
                                                $_POST['sub-ubicacion'],
                                                $_POST['codigo'] ,
                                                'no-image.png' ))
          {
                 $ok = true;
          }else{ $ok = false;  }

          $sube = true;
          $this->msg ="Inventario Actualizado Correctamente";
          $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
        }
        else{
          $sube = false;
          $this->msg ="Error en Consulta 2";

          $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';
        }
      }else{

        require_once("ftp.class.php");
        $ob_ftp = new FTP( "archivo", $this->token );

          if( $ob_ftp->validaFile( $_FILES["archivo"]["name"] ) )
              if( $ob_ftp->procesaFTP() )
                  if( $this->consultas->procesaElmento( $_POST['elemento'],
                                                        $_POST['descripcion'],
                                                        $_POST['cantidad'],
                                                        $_POST['cantidad_minima'],
                                                        $this->yo,
                                                        $_POST['id_tipo'],
                                                        $_POST['sub_tipo'],
                                                        $_POST['idUbicacion'],
                                                        $_POST['sub-ubicacion'],
                                                        null,
                                                        $ob_ftp->changeNameFile( $_FILES['archivo']['name'] ),
                                                        $_POST['id_unidad'],
                                                        $_POST['codigo_final'],
                                                        $_POST['id_proveedores'],
                                                        $_POST['valor'],
                                                        $_POST['id_afirmacion'],
                                                        $_POST['id_inventario'] ) )
                  {

                    if ( $this->consultas->procesaGemela( addslashes( $_POST['elemento']),
                                                          $_POST['descripcion'],
                                                          $_POST['cantidad'],
                                                          $_POST['cantidad_minima'],
                                                          $this->yo,
                                                          $_POST['id_tipo'],
                                                          $_POST['sub_tipo'],
                                                          $_POST['idUbicacion'],
                                                          $_POST['sub-ubicacion'],
                                                          $_POST['codigo'] ,
                                                          $ob_ftp->changeNameFile( $_FILES['archivo']['name'] ) ))
                    {
                           $ok = true;
                    }else{ $ok = false;  }

                    $sube       = true;
                    $this->msg  = "Inventario Subido Correctamente";
                    $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';

                  }else{
                    $sube       = false;
                    $this->msg  =  "Error en Consulta 3";
                    $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';

                  }
            else{
                    $sube = false;
                    $this->msg ="Error en FTP";
                    $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';
            }
        else{
                    $sube = false;
                    $this->msg ="Error en Tipo de Archivo";
                    $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>';
        }
      }
    }

    if( $sube )
    {
      //colocar en guia de despacho de ingreso

            //añades ingreso en la base de datos
   
       //añades ingreso en la base de datos
        if( $this->consultas->ingresaGuiaDespachoIngreso(  $this->token,
                                                           $this->fecha_hoy,
                                                           $_POST['id_proveedores'],
                                                           $this->token,
                                                           $this->yo,4	 ) )
        {     $ingresa = true; }
        else{ $ingresa = false; }

        if( $ingresa )
        {

      
        if( $this->consultas->procesaDetalleGuiaDespachoIngreso( $_POST['id_inventario'], 
                                                                $this->token,
                                                                $_POST['cantidad'], 
                                                                $_POST['valor'], 
                            ) )
        {     $ingresa = false; }
        else{ $ingresa = false; }                                                               
        }
        else{ $ingresa = false; }











      //agregar en registro de acciones de usuario
       $descripcionEvento = "Insumo {$_POST['elemento']} actualizado";

       if( $this->consultas->ingresaEventosAdmin( addslashes( $descripcionEvento ),
                                                  $this->yo ) )
             $ok1 = true;
       else  $ok1 = false;

       //$this->msg = $this->consultas->ingresaEventosAdmin( $descripcionEvento, $this->yo );

      return $this::notificaciones( "success",
                                    $this->icon,
                                    "{$this->msg} {$this->btn_crear} {$this->btn}" );
    }
    else{

      //return $q;

      return $this::notificaciones( "danger",
                                    $this->icon,
                                    "{$this->msg} {$this->btn_crear} {$this->btn}" );
    }
  }

  /**
   * editarInventario(): despliegue del formulario de edicion
   * @param
   * @return string
   */
  private function editarInventario()
  {
      $code = "";
      $arr = $this->consultas->listaElementos( $_POST['id_inventario'] );

      foreach ($arr['process'] as $key => $value) {

        $hidden ="<input type='hidden' name='id_inventario' id='id_inventario' value='".$_POST['id_inventario']."' >";

        $hidden_codigo    ="<input  type='hidden'
                                      name='codigo'
                                      id='codigo'
                                      value='".$value['codigo']."' >";

        $a1 = $this->consultas->ubicacion();
        $sel1 = new Select( $a1['process'], 'id','descripcion','idUbicacion', 'Ubicacion', $value['id_ubicacion'] );

        $a2 = $this->consultas->listaTipo();
        $sel2 = new Select( $a2['process'], 'id','descripcion','id_tipo', 'Tipo', $value['id_tipo'] );

        if( $this->tipo_usuario == 5 ||  $this->tipo_usuario == 3) 
        {
          $select['ubicacion'] = $sel1->getCode();
          $select['tipo']      = $sel2->getCode();
          $archivo             = $this::archivoForm( $value['imagen'] );
          $disabled            = null;
          $hidden_elemento     = null;
          $hidden_stock_min    = null;
          $hidden_descripcion  = null;

        }else{

          $select['ubicacion'] = $this::seletcDisabled('idUbicacion',
                                                      $this::codifica(  $value['nombreUbicacion'] , 2 ),
                                                       $value['id_ubicacion']   );

          $select['tipo']      = $this::seletcDisabled('id_tipo',
                                                      $this::codifica(  $value['nombreTipo'] ,2 ),
                                                       $value['id_tipo']   );

          $archivo             =  $value['imagen'];

          $hidden_elemento    ="<input  type='hidden'
                                        name='elemento'
                                        id='elemento'
                                        value='".$this::codifica( $value['nombre'] ,2)."' >";

          $hidden_stock_min   ="<input  type='hidden'
                                        name='cantidad_minima'
                                        id='cantidad_minima'
                                        value='".$this::codifica( $value['stock_minimo'],2 )."' >";

          $hidden_descripcion   ="<input  type='hidden'
                                        name='descripcion'
                                        id='descripcion'
                                        value='". $this::codifica( $value['descripcion'] ,2 )."' >";

          $disabled            = "disabled";
        }

        if( $value['stock_minimo'] > $value['stock']  )
          $this->btn_critico ="<button class = 'btn btn-sm btn-outline-danger' id='envia-msg'>
                                  Enviar Mensaje Stock Crítico
                                </button>";
        else $this->btn_critico = null;


        $a3 = $this->consultas->unidades();
        $sel3 = new Select( $a3['process'], 'id','descripcion','id_unidad', 'Unidad', $value['id_unidad'] );


        $arr_fam = $this->consultas->listaSubTipo( );
        $sel_fam = new Select( $arr_fam['process'], 'id', 'descripcion','sub_tipo', 'Familia', $value['id_sub_tipo']);

        $arr_proveedores = $this->consultas->listaProveedores();
        $sel_proveedores = new Select( $arr_proveedores['process'], 'id', 'descripcion','id_proveedores', 'Proveedor',$value['id_proveedor'],1 );

        if( $value['codigo_final'] =='' || $value['codigo_final'] ==null )
        {
          $codigo_final = null;
          $disabled_codigo_final = null;
          $hidden_codigo_final = null;

        }else{

          $codigo_final = $value['codigo_final'];
          $disabled_codigo_final = null;
          $hidden_codigo_final = null;
        }

        $arr_afirmacion = $this->consultas->afirmacion();
        $sel_afirmacion = new Select( $arr_afirmacion['process'], 'id','descripcion','id_afirmacion', 'Si o No' );

        $btn_movimientos='<a
                            class="btn btn-sm btn-secondary outline-line-gris rounded-pill"
                            data-toggle="modal"
                            data-target="#modal-historico"
                            data-placement ="top" title = "Movimientos Históricos"
                            >
                        <i class="far fa-eye"></i> Movimientos
                      </a>';


        $data = ['###title###'              => 'Editar',
                 '###modal###'              => $this::modal("modal-historico",null,
                                                            "Contenido Histórico de {$value['nombre']} / Codigo: {$codigo_final}",
                                                            $this::modalHistorico(  $value['codigo'], $value['id'] ) ), 
                 '###elemento###'           => $this::codifica( $value['nombre'] ,2 ),
                 '###id_elemento###'        => $value['id'],
                 '###hidden_elemento###'    => $hidden_elemento,                 
                 '###hidden_stock_min###'   => $hidden_stock_min,
                 '###hidden_descripcion###' => $hidden_descripcion,
                 '###hidden_codigo###'      => $hidden_codigo,
                 '###cantidad###'           => $value['stock'],
                 '###cantidad_minima###'    => $value['stock_minimo'],
                 '###select-ubicacion###'   => $select['ubicacion'],
                 '###select-Tipo###'        => $select['tipo'],
                 '###notificacion###'       => $this::notificaciones( 'warning',null,'( * ) Campo Obligatorio' ),
                 '###target###'             => 'editaInventarioData',
                 '###hidden###'             => $hidden,
                 '###file###'               => $archivo,
                 '###disabled###'           => $disabled,
                 '###select-familia###'     => $sel_fam->getCode(),  
                 '###select-proveedor###'   => $sel_proveedores->getCode(),  
                 '###disabled_codigo_final###' => $disabled_codigo_final,
                 '###hidden_codigo_final###' => $hidden_codigo_final,
                 '###codigo_final###'        => $codigo_final,                    
                 '###btn-stck-critico###'   => $this->btn_critico,
                 '###btn-movimientos###'    => $btn_movimientos,
                 '###descripcion###'        => $this::codifica( $value['descripcion'] ,1),
                 '###select-disabled1####'  => $this::seletcDisabled('sub-ubicacion',
                                                                    $this::codifica( $value['nombreSubUbicacion'],1 ),
                                                                    $value['id_sububicacion'] ),
                 '###select-disabled2####'  => $this::seletcDisabled('sub_tipo',
                                                                    $this::codifica( $value['nombreSubTipo'] ,1),
                                                                    $value['id_sub_tipo'] ),
                 '###select-unidad###'     => $sel3->getCode(),
                 '###valor###'             => $this::sacaValorActual( $value['id'] ), 
                 '###select-operativo###'  => $sel_afirmacion->getCode(),
                 '###id_button###'         => 'edit'                  ];

        $code .= $this::despliegueTemplate( $data, 'formulario-inventario.html' );

      }

      return $code;
  }

  /**
   * enviaStockCriticoUnitario()
   * @param
   * @return string
   */
  private function enviaStockCriticoUnitario()
  {
    $code = "";

    $arr = $this->consultas->listaElementos( $_POST['id_elemento'] );
    $i = 0;
    foreach ($arr['process'] as $key => $value) {

      $data = ['###elemento###' => $value['nombre'],
               '###codigo###'   => $_POST['codigo'] ];

      $code .= $this::despliegueTemplate( $data, 'msg-unitario.html' );
      $i++;

    }

    $ob_mail = new mails('enviaStockCriticoUnitario',
                         $this->email,
                          $code );

    if( $ob_mail->getCode() )
    {
          $this->color  = "success";
          $this->icon   = '<i class="far fa-thumbs-up"></i>';
          $this->msg    = "Mensaje Enviado";

    }else{

        $this->color  = "danger";
        $this->icon   = '<i class="far fa-thumbs-down"></i>';
        $this->msg    = "Error al enviar";
    }

    return $this::notificaciones($this->color,$this->icon, $this->msg);

  }


  private function archivoForm( $fileName = null )
  {
      $data = ['###fileName###' => $fileName ];
      return $this::despliegueTemplate( $data, 'archivo-form.html' );
  }


  /**
   * ingresaInventarioData(): ingresar la data del inventario
   * @return string
   */
  private function ingresaInventarioData()
  {

    if( $_FILES['archivo']['size'] != 0  )
    {
      require_once("ftp.class.php");
      $ob_ftp = new FTP( "archivo", $this->token );

        if( $ob_ftp->validaFile( $_FILES["archivo"]["name"] ) )
         if( $ob_ftp->procesaFTP() )
          if( $this->consultas->procesaElmento( htmlentities(  addslashes(  $_POST['elemento']) ),
                                                $_POST['descripcion'],
                                                $_POST['cantidad'],
                                                $_POST['cantidad_minima'],
                                                $this->yo,
                                                $_POST['id_tipo'],
                                                $_POST['sub_tipo'],
                                                $_POST['idUbicacion'],
                                                $_POST['sub-ubicacion'],
                                                $this::crearCodigo( $_POST['idUbicacion'],
                                                                    $_POST['sub-ubicacion'],
                                                                    $_POST['id_tipo'],
                                                                    $_POST['sub_tipo'] ),
                                                $ob_ftp->changeNameFile( $_FILES["archivo"]["name"] ),
                                                $_POST['id_unidad'], $_POST['codigo_final'], $_POST['id_proveedores'] ,$_POST['valor'], $_POST['id_afirmacion'] ))
               {
                //aqui aplicas las gemelas
                if(  $this->consultas->procesaGemela( htmlentities( addslashes(  $_POST['elemento'] ) ),
                                                      $_POST['descripcion'],
                                                      $_POST['cantidad'],
                                                      $_POST['cantidad_minima'],
                                                      $this->yo,
                                                      $_POST['id_tipo'],
                                                      $_POST['sub_tipo'],
                                                      $_POST['idUbicacion'],
                                                      $_POST['sub-ubicacion'],
                                                      $this::crearCodigo( $_POST['idUbicacion'],
                                                                          $_POST['sub-ubicacion'],
                                                                          $_POST['id_tipo'],
                                                                          $_POST['sub_tipo'] ),
                                                      $ob_ftp->changeNameFile( $_FILES["archivo"]["name"] )))
                {
                  $ok = true;
                }
                else{
                  $ok = false;
                }

                $sube = true;
                $this->msg ="Archivo Subido Correctamente";
                $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';

              }
        else   { $sube = false; $this->msg ="Error en Consulta";     $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
       else    { $sube = false; $this->msg ="Error FTP";             $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
      else     { $sube = false; $this->msg ="Error Tipo de Archivo"; $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
    }else{

      if( $this->consultas->procesaElmento( htmlentities( addslashes(  $_POST['elemento']) ),
                                            $_POST['descripcion'],
                                            $_POST['cantidad'],
                                            $_POST['cantidad_minima'],
                                            $this->yo,
                                            $_POST['id_tipo'],
                                            $_POST['sub_tipo'],
                                            $_POST['idUbicacion'],
                                            $_POST['sub-ubicacion'],
                                            $this::crearCodigo( $_POST['idUbicacion'],
                                                                $_POST['sub-ubicacion'],
                                                                $_POST['id_tipo'],
                                                                $_POST['sub_tipo'] ),
                                            'no-image.png',
                                            $_POST['id_unidad'],$_POST['codigo_final'], $_POST['id_proveedores'] ,$_POST['valor'], $_POST['id_afirmacion'] ))
           {
             //aqui aplicas las gemelas
             if( $this->consultas->procesaGemela( addslashes(  $_POST['elemento']),
                                                   $_POST['descripcion'],
                                                   $_POST['cantidad'],
                                                   $_POST['cantidad_minima'],
                                                   $this->yo,
                                                   $_POST['id_tipo'],
                                                   $_POST['sub_tipo'],
                                                   $_POST['idUbicacion'],
                                                   $_POST['sub-ubicacion'],
                                                   $this::crearCodigo( $_POST['idUbicacion'],
                                                                       $_POST['sub-ubicacion'],
                                                                       $_POST['id_tipo'],
                                                                       $_POST['sub_tipo'] ),
                                                   'no-image.png' ))
              {
                $ok = true;
              }
              else{
                $ok = false;
              }

              $sube = true;
              $this->msg ="Inventario Subido Correctamente";
              $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
             }
      else { $sube = false; $this->msg ="Error en Consulta";
             $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
    }

     if( $sube )
      {
        //añades ingreso en la base de datos
        if( $this->consultas->ingresaGuiaDespachoIngreso(  $this->token,
                                                           $this->fecha_hoy,
                                                           $_POST['id_proveedores'],
                                                           $this->token,
                                                           $this->yo,4	 ) )
            { $ingresa = true; }
        else{ $ingresa = false; }

        if( $ingresa )
        {
          $max_idElemento = 0;

          foreach ($this->consultas->maxElemento() as $key => $value) {
            # code...
            $max_idElemento = $value['maxElemento'];
          }


          if( $this->consultas->procesaDetalleGuiaDespachoIngreso( $max_idElemento, 
                                                                   $this->token,
                                                                   $_POST['cantidad'], 
                                                                   $_POST['valor'], 
                                                                   			) )
          {     $ingresa = false; }
          else{ $ingresa = false; }                                                               
        }
        else{
                $ingresa = false;
        }




        //agregar en registro de acciones de usuario
         $descripcionEvento = "Insumo {$_POST['elemento']} Ingresado";

         if( $this->consultas->ingresaEventosAdmin( addslashes( $descripcionEvento ),
                                                    $this->yo ) )
               {$ok1 = true;}
         else  {$ok1 = false;}

         return $this::notificaciones( "success",
                                        $this->icon,
                                        "{$this->msg} {$this->btn_crear}  {$this->btn}" );
       }
      else  return $this::notificaciones( "danger",  $this->icon, "{$this->msg} {$this->btn_crear} {$this->btn}" );
  }

  /**
   * crearCodigo(): Creacion de Codigos
   * @param  integer id_ubicacion
   * @param  integer id_sububicacion
   * @param  integer id_tipo
   * @param  integer sub_tipo
   * @return string
   */
  private function crearCodigo( $id_ubicacion     = null,
                                $id_sububicacion  = null,
                                $id_tipo          = null,
                                $id_sub_tipo      = null )
  {
      $max = null;
      foreach ($this->consultas->maxElemento() as $key => $value)
      {
        $max = $value['maxElemento'] +1;
      }

      return "{$max}U{$id_ubicacion}SU{$id_sububicacion}T{$id_tipo}ST{$id_sub_tipo}";
  }

  /**
   * crearInventario(): despliegue de interfaz de formulario de entrada de inventario
   * @return string
   */
  private function crearInventario()
  {

    $a1 = $this->consultas->ubicacion();
    $sel1 = new Select( $a1['process'], 'id','descripcion','idUbicacion', 'Ubicacion' );

    $a2 = $this->consultas->listaTipo();
    $sel2 = new Select( $a2['process'], 'id','descripcion','id_tipo', 'Categoría' );

    $a3 = $this->consultas->unidades();
    $sel3 = new Select( $a3['process'], 'id','descripcion','id_unidad', 'Unidad' );

    $arr_fam = $this->consultas->listaSubTipo( );
    $sel_fam = new Select( $arr_fam['process'], 'id', 'descripcion','sub_tipo', 'Familia' );

    $arr_proveedores = $this->consultas->listaProveedores();
    $sel_proveedores = new Select( $arr_proveedores['process'], 'id', 'descripcion','id_proveedores', 'Proveedor',null,1 );

    $arr_afirmacion = $this->consultas->afirmacion();
    $sel_afirmacion = new Select( $arr_afirmacion['process'], 'id','descripcion','id_afirmacion', 'Si o No',1 );

    //1. sub-ubicacion
    //2. sub_tipo

    $data = ['###title###'              => 'Ingreso',
             '###elemento###'           => null,
             '###cantidad###'           => null,
             '###cantidad_minima###'    => null,
             '###select-ubicacion###'   => $sel1->getCode(),
             '###select-Tipo###'        => $sel2->getCode(),
             '###notificacion###'       => $this::notificaciones( 'warning',null,'( * ) Campo Obligatorio' ),
             '###target###'             => 'ingresaInventarioData',
             '###hidden###'             => null,
             '###file###'               => $this::fileForm(),
             '###descripcion###'        => null,
             '###hidden_codigo###'      => null,
             '###select-disabled1####'  => $this::seletcDisabled('sub-ubicacion','Sub Ubicacion'),
             '###select-disabled2####'  => $this::seletcDisabled('sub_tipo','Sub_Tipo'),
             '###hidden_elemento###'    => null,
             '###hidden_stock_min###'   => null,
             '###hidden_descripcion###' => null,
             '###btn-stck-critico###'   => null,
             '###hidden_codigo_final###' => null,
             '###codigo_final###'       => null,
             '###disabled_codigo_final###' => null,
             '###valor###'              => null,
             '###btn-movimientos###'    => null,
             '###modal###'              => null,
             '###select-familia###' => $sel_fam->getCode(),
             '###select-unidad###'      => $sel3->getCode(),
             '###select-proveedor###' => $sel_proveedores->getCode(),
             '###select-operativo###' => $sel_afirmacion->getCode(), 
             '###id_button###'          => 'send'

              ];
    return $this::despliegueTemplate( $data, 'formulario-inventario.html' );
  }


  private function seletcDisabled( $identificador = null, $nombre = null, $valor = null )
  {
    $data = [ '###identificador###' => $identificador,
              '###nombre###'        => $nombre,
              '###valor###'         => $valor         ];

    return $this::despliegueTemplate( $data, 'select-disabled.html' );
  }


  private function fileForm()
  {
    return $this::despliegueTemplate( [], 'file-form.html' );
  }

  private function menu_admin()
  {
     
      return $this->menu_aux;
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

   //eliminaElemento
   private function deleteInventario()
   {
     if( $this->consultas->eliminaElemento( $_POST['id_inventario'] ) )
     {

      $estado = "danger";
      $icon   = '<i class="far fa-thumbs-up"></i>';
      $glosa  = "Registro Eliminado"; 

      //agregar en registro de acciones de usuario
      $descripcionEvento = "Insumo {$_POST['elemento']} eliminado";

      if( $this->consultas->ingresaEventosAdmin( addslashes( $descripcionEvento ),
                                                 $this->yo ) )
            $ok1 = true;
      else  $ok1 = false;


     }else{

            $estado = "danger";
            $icon   = '<i class="far fa-thumbs-down"></i>';
            $glosa  = "Error al Eliminar!"; 
     
    }

     return $this::notificaciones($estado,$icon,$glosa).$this::listarInventario();
   }


  /**
   * listarInventario(): listado de inventarios
   * @param
   * @return string
   */
  private function listarInventario()
  {
    $arr = $this::trListarInventario();

    if( $arr['total-recs'] > 0 )
    {
      if( $this->tipo_usuario == 3 || $this->tipo_usuario == 1 )
            $btn = $this->btn_crear;
      else  $btn = null;

      //if(  $this->tipo_usuario == 3  )
      //      $menu_aux = $this::menu_admin();
      //else  $menu_aux = null;

      switch ($this->tipo_usuario) {
        case 3:
        case 5:
          $menu_aux = $this::menu_admin();

          $this->registros='<a href="content-page.php?id=cmVnaXN0cm8tYWNjaW9uZXM="
                                class="btn-sm btn-secondary btn rounded-pill outline-line-gris" id="registro-acciones">
                                    <i class="fas fa-ellipsis-v"></i> Registros
                            </a>'; 
          break;

        default:
          $menu_aux = null;
          $this->registros = null;
          break;
      }


      $data = ['###tr###'         => $arr['code'] ,
               '###total-recs###' => $arr['total-recs'],
               '###nav-links###'  => $arr['nav-links'],
               '###buscar###'     => $this::buscar(),
               '###btn###'        => $btn,
               '###menu_aux###'   => $menu_aux,
               '###registros###'  => $this->registros

     ];
      return $this::despliegueTemplate( $data , 'tabla-inventario.html' );

    }else{

      if(  $this->tipo_usuario == 3  )
            $menu_aux = $this::menu_admin();
      else  $menu_aux = null;

      $code = "";

      $code .= $this->menu_aux."<br>";
      $code .= $this::notificaciones('danger','',"Elementos no ingresados / No Existentes...<br>" );

      return $code;

    }
  }

  /**
   * trListarInventario(): listado de inventarios
   * @param
   * @return string
   */
  private function trListarInventario()
  {
      $code       = "";

      if( !isset( $_POST['buscar_action'] ) )
      {
        $arr        = $this->consultas->listaElementos();
        $utils      = new utiles($arr['sql']);
        $rs_dd      = $utils->show();
        $nav_links  = $rs_dd['nav_links'];
        $param      = $rs_dd['result'] ;
      }else{

        $arr        = $this->consultas->listaElementos( null,
                                                        $_POST['buscar_action'],
                                                        $_POST['idUbicacion'],
                                                        $_POST['id_tipo'],
                                                        $_POST['sub_ubicacion'],
                                                        $_POST['sub_tipo'],
                                                        $_POST['codigo'],
                                                        $_POST['nombre']  );
        $nav_links  = null;
        $param      = $arr['process'] ;

      }

    $i = 0;
    foreach ($param as $key => $value) {
  

      switch ($this->tipo_usuario) {
        case 3:
            $this->btn = '
            <button class="btn btn-info btn-sm"
                id="editar-'.$value['id'].'"
                data-toggle ="tooltip"
                data-placement="top"
                title="Editar Insumo"
                >
                <i class="far fa-edit"></i>
            </button>
          ';

          $this->ingreso = '
          <button class="btn btn-sm btn-secondary"
                  data-toggle="modal"
                  data-target="#modal-ingreso-'.$value['id'].'"
                  data-placement ="top"
                  title = "Ingresos"
                  >
            In
          </button>
          ';
          
          $this->delete = null;
          
        break;  
        case 5:
        $this->btn = '
          <button class="btn btn-secondary btn-sm"
              id="editar-'.$value['id'].'"
              data-toggle ="tooltip"
              data-placement="top"
              title="Editar Insumo"
              >
              <i class="far fa-edit"></i>
          </button>
        ';

        $this->ingreso = '
        <button class="btn btn-sm btn-secondary"
                data-toggle="modal"
                data-target="#modal-ingreso-'.$value['id'].'"
                data-placement ="top"
                title = "Ingresos"
                >
          In
        </button>
        ';
   
        $this->delete = '
            <button class="btn btn-danger btn-sm outline-line-rojo"
                id="delete-'.$value['id'].'"
                data-toggle ="tooltip"
                data-placement="top"
                title="Eliminar"
                >
                <i class="fas fa-trash"></i>
            </button>
          ';

          break;

        default:
          $this->btn        = null;
          $this->ingreso    = null;
          $this->delete     = null;
          
          break;
      }


      if( $value['stock'] > $value['stock_minimo'] )
            $alerta = "<span class='ok'><i class='far fa-check-circle'></i> Stock Ok</span>"  ;
      else  $alerta =  "<span class='peligro'><i class='fas fa-exclamation-triangle'></i> Stock Crítico</span>"  ;


      if( $value['stock'] < 0 )
            $stock = 0;
      else  $stock = $value['stock'];

      if( $value['codigo_final'] == null || $value['codigo_final'] == '' )
        $codigo_final = "NO ASIGNADO";
      else  $codigo_final = $value['codigo_final'];



      $data = ['###num###'            => $i+1,
               '###codigo###'         => $codigo_final,
               '###nombre###'         => $this::codifica( substr( $value['nombre'], 0,28),null ),
               '###nombre2###'        => $this::codifica( $value['nombre'],null ),
               '###ubicacion###'      => $this::codifica( $value['nombreUbicacion'],null ),
               '###sub_ubicacion###'  => $this::codifica( $value['nombreSubUbicacion'],null),
               '###tipo###'           => $this::codifica( $value['nombreTipo'] ,null ),
               '###sub_tipo###'       => $this::codifica( $value['nombreSubTipo'] ,null),
               '###stock###'          => $stock,
               '###alerta###'         => $alerta,
               '###delete###'         => $this->delete,               
               '###imagen###'         => $value['imagen'],  
               '###id###'             => $value['id'],
               '###modal-img###'      => $this::modal("imagen-modal-{$value['id']}", null,
                                                      null,
                                                      $this::contenidoImagen( $value['imagen'], $value['descripcion'] )  ),
               '###modal-historico###'=> $this::modal("modal-historico-{$value['id']}",null,
                                                      "Contenido Histórico de {$value['nombre']} / Codigo: {$codigo_final}",
                                                      $this::modalHistorico(  $value['codigo'], $value['id'] ) ),

               '###modal-ingreso###'   => $this::modal("modal-ingreso-{$value['id']}",null,
                                                      "Formulario de Ingresos",
                                                      $this::formIngreso( $value['id'],
                                                                          $value['codigo'],
                                                                          $value['nombre'], $codigo_final
                                                                        )),
               '###modal-egreso###'   => $this::modal("modal-egreso-{$value['id']}",null,
                                                      "Formulario de Egresos",
                                                      $this::formEgreso(  $value['id'],
                                                                      $value['codigo'],
                                                                      $value['nombre'], $codigo_final) ),
               "###editar###" => $this->btn, '###ingreso###' => $this->ingreso,
               "###unidad###"  => $value['nombreUnidad'],
               "###valor###"   => $this::separa_miles(  $this::sacaValorActual( $value['id'] ) )
              ];

      $code .= $this::despliegueTemplate( $data , 'tr-inventario.html' );

      $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];
    $out['nav-links']   = $nav_links;
    $out['sql']         = $arr['sql'];

    return $out;
  }


private function historicos( $codigo_final = null )
{

  $arr = $this::trHistoricos( $codigo_final );

  if( $arr['total-recs'] > 0)
  {
    $data = ['###tr###' => $arr['code'], '###codigo_final###' => $codigo_final ];
    return $this::despliegueTemplate( $data, 'tabla-historica.html' );
  }else{

    return "SIN MOVIMIENTOS.-";

  }
}

private function trHistoricos( $codigo_final = null )
{
  $code = "";
  $arr = $this->consultas->listaMirror( $codigo_final );

  foreach ($arr['process'] as $key => $value) {
    # code...
  
    $user = "{$value['nombres']} {$value['apaterno']}";

    $data = ['###stock###'              => $value['stock'], 
             '###stock_minimo###'       => $value['stock_minimo'],
             '###costo###'              => $this::separa_miles( $value['precio'] ),
             '###costo-total###'        => $this::separa_miles( $value['stock']*$value['precio'] ),
             '###fecha_modificacion###' => $value['fecha_modificacion'],
             '###proveedor###'          => $value['proveedor'],
             '###modificado_por###'     => $user,
             '###operativo###'          => $value['operativo'],
             '###familia###'            => $value['familia'],  
  ];
    $code .= $this::despliegueTemplate( $data,'tr-historica.html' );
  
  }

  $out['code'] = $code;
  $out['total-recs'] = $arr['total-recs'];

  return $out;


}

public function excelHistoricos( $codigo_final = null )
{
  $ARR = $this::trHistoricos( $codigo_final );

  $data = [ '###codigo_final###' => $codigo_final, '###tr###' => $ARR['code']  ];
  return $this::despliegueTemplate( $data, 'excel-historico.html' );
}

public function listaExcel()
{
  $arr = $this::trListaExcel();

  $data = ['###tr###' => $arr['code'] ];
  return $this::despliegueTemplate( $data, 'tabla-excel.html' );

}

private function trListaExcel()
{
    $arr  =  $this->consultas->listaElementos();
    $code = "";
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

      if( $value['stock'] > $value['stock_minimo'] )
            $alerta = "Stock Ok"  ;
      else  $alerta = "Stock Crítico"  ;

      if( $value['codigo_final'] =='' || is_null( $value['codigo_final'] )  )
            $codigo_final = "No Asignado";
      else  $codigo_final =  "<strong>".$value['codigo_final']."</strong>"; 

      if( $i%2==0 )
      {
        $color = "#FFFFFF";
      }else $color = "#EFEFEF";




      $data = ['###CODIGO###'           => $codigo_final,
               '###PRODUCTO###'         => $this::codifica( $value['nombre'] , 2 ),
               '###UBICACION###'        => $this::codifica( $value['nombreUbicacion'] ,2 ),
               '###SUB-UBICACION###'    => $this::codifica( $value['nombreSubUbicacion'] ,2 ),
               '###CATEGORIA###'        => $this::codifica( $value['nombreTipo'] ,2 ),
               '###SUB-CATEGORIA###'    => $this::codifica( $value['nombreSubTipo'] ,2 ),
               '###STOCK###'            => $value['stock'],
               '###UNIDAD###'           => $value['nombreUnidad'],
               '###COLOR###'            => $color,
               '###ALERTA###'           => $this::codifica( $alerta ,2 ),
               '###VALOR-FINAL###'      => $this::separa_miles(  $this::sacaValorActual( $value['id']  ))
     ];
      $code .= $this::despliegueTemplate( $data, 'tr-excel.html' );

      $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];
    $out['nav-links']   = $nav_links;
    $out['sql']         = $arr['sql'];

    return $out;
}


private function sacaValorActual( $id_elemento = null )
{
  require_once("guia-despacho-egreso.class.php");

  $ob_guia_egreso = new GuiaDespachoEgreso();
  return $ob_guia_egreso->sacaValorActual( $id_elemento );

}




private function formEgreso($id_inventario = null, $codigo = null, $insumo = null, $codigo_final=null)
{
  $arr1 = $this->consultas->listaClientes();
  $sel1 = new Select( $arr1['process'],
                     'id',
                     'descripcion',
                     "id-cliente-{$id_inventario}",
                     "Cliente"
                    );

  $arr2 = $this->consultas->listaDestino();
  $sel2 = new Select( $arr2['process'],
                     'id',
                     'descripcion',
                     "id-destino-{$id_inventario}",
                     "Destino/Ruta"
                    );

  $arr3 = $this->consultas->listaTrabajos();
  $sel3 = new Select( $arr3['process'],
                     'id',
                     'descripcion',
                     "id-trabajo-{$id_inventario}",
                     "Trabajo"
                    );

  //determinas el ultimo valor del insumo adquirido                  
  $valor = $this::lastValorIngresos( $codigo );

  $data = [ '###id###'                => $id_inventario,
            '###id_user###'           => $this->yo,
            '###codigo###'            => $codigo,
            '###insumo###'            => $insumo,
            '###codigo-final###'      => $codigo_final, 
            '###select-cliente###'    => $sel1->getCode(),
            '###select-destino###'    => $sel2->getCode(),
            '###select-trabajo###'    => $sel3->getCode(),
            '###valor###'             => $valor
  ];
  return $this::despliegueTemplate( $data, 'formulario-egreso.html' );

}


  private function formIngreso( $id_inventario = null, $codigo = null, $insumo = null, $codigo_final = null )
  {

    /*
    $arr = $this->consultas->listaProveedores();
    $sel = new Select( $arr['process'],
                       'id',
                       'descripcion',
                       "id-proveedor-{$id_inventario}",
                       "Proveedor"
                      );

    $arr2 = $this->consultas->tipoDocumento();
    $sel2 = new Select( $arr2['process'],
                       'id',
                       'descripcion',
                       "tipo-documento-{$id_inventario}",
                       "Tipo de Documento"
                      );
    */

    $arr = $this->consultas->listaProveedores();
    $sel = new Select( $arr['process'],
                       'id',
                       'descripcion',
                       "id-proveedor",
                       "Proveedor"
                      );

    $arr2 = $this->consultas->tipoDocumento();
    $sel2 = new Select( $arr2['process'],
                       'id',
                       'descripcion',
                       "tipo-documento",
                       "Tipo de Documento"
                      );

    $data = [ '###id###'                     => $id_inventario,
              '###id_user###'                => $this->yo,
              '###codigo###'                 => $codigo,
              '###codigo_final###'           => $codigo_final,
              '###insumo###'                 => $insumo,
              '###select-proveedor###'       => $sel->getCode(),
              '###select-tipo-documento###'  => $sel2->getCode()
    ];
    return $this::despliegueTemplate( $data, 'formulario-ingreso.html' );
  }


  public function tags()
  {
    $arr  = $this->consultas->listaElementos();
    $i    = 0;
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      if( $i < ( $arr['total-recs'] -1 ) )
            $code .= "'{$value['nombre']}',";
      else  $code .= "'{$value['nombre']}'";
    }

    return $code;
  }



  private function modalHistorico( $codigo = null , $id_insumo = null )
  {
    ///return "contenido modificado para {$codigo}!";
    $data = ['###codigo###'         => $codigo,
             '###lista-ingresos###' => $this::listaIngrsesos( $id_insumo ) ,
             '###lista-egresos###'  => $this::listaEgresosMod( $id_insumo ) ];

    return $this::despliegueTemplate( $data, 'tab-historicos.html' );

  }

  private function listaEgresosMod( $id_insumo = null )
  {
    if( require_once( 'guia-despacho-egreso.class.php' ) )
    {
      $ob_egresos = new GuiaDespachoEgreso();
      return $ob_egresos->resumenInsumos( $id_insumo );
    }
  }




  private function listaIngrsesos( $id_insumo = null )
  {
    //return $this::despliegueTemplate( ['###tr###' => $this::trlistaIngrsesos( $codigo ) ],
    //'tabla-ingresos.html' );

    if( require_once( 'guia-despacho-ingreso.class.php' ) )
    {
      $ob_guia = new GuiaDespachoIngreso();
      return $ob_guia->resumenInsumos( $id_insumo );
    }

  }

  private function trlistaIngrsesos( $codigo = null )
  {
    $code = "";
    $arr = $this->consultas->listaIngresos( $codigo );


    foreach ($arr['process'] as $key => $value) {

        $responsable = "{$value['nombres']} {$value['apaterno']}";

        $data = [ '###fecha###'           => $value['fecha'],
                  '###codigo###'          => $codigo,
                  '###nombre_producto###' => $value['insumo'],
                  '###responsable###'     => $responsable,
                  '###guia_despacho###'   => $value['guia_despacho'],
                  '###cantidad###'        => $value['cantidad'],
                  '###valor###'           => $this::separa_miles(  $value['valor'] ),
                  '###valor-total###'     => $this::separa_miles(  $value['valor'] * $value['cantidad']   ),
                  '###num_documento###'   => $value['num_documento'],
                  '###proveedor###'       => $value['nombreProveedor'],
                  '###tipo_documento###'  => $value['nombreTipoDocumento']
                 ];

        $code .= $this::despliegueTemplate( $data, 'tr-ingresos.html' );
    }

    return $code;

  }


  private function listaIngrsesos_guia( $guia_despacho = null )
  {

    $ARR =$this::trlistaIngrsesos_guia( $guia_despacho );


    return $this::despliegueTemplate( [ '###tr###' => $ARR['code'], 
                                        '###guia-despacho###' => $guia_despacho,
                                        '###valor-total###' => $ARR['valor-total'] ],
    'tabla-ingresos-guia-despacho.html' );
  }

  private function trlistaIngrsesos_guia( $guia_despacho = null )
  {
    $code = "";
    $arr = $this->consultas->listaIngresos2( $guia_despacho );

    $suma_total = 0;
    foreach ($arr['process'] as $key => $value) {

        $responsable = "{$value['nombres']} {$value['apaterno']}";

        $total =  $value['valor'] * $value['cantidad'];


        $data = [ '###fecha###'           => $value['fecha'],
                  '###codigo###'          => $codigo,
                  '###producto###'        => $value['insumo'],
                  '###responsable###'     => $responsable,
                  '###guia_despacho###'   => $value['guia_despacho'],
                  '###cantidad###'        => $value['cantidad'],
                  '###valor###'           => $this::separa_miles(  $value['valor'] ),
                  '###valor-total###'     => $this::separa_miles(  $value['valor'] * $value['cantidad']   ),
                  '###num_documento###'   => $value['num_documento'],
                  '###proveedor###'       => $value['nombreProveedor'],
                  '###tipo_documento###'  => $value['nombreTipoDocumento']
                 ];

        $code .= $this::despliegueTemplate( $data, 'tr-ingresos-guia-despacho.html' );
        
        $suma_total = $suma_total+$total;

    }

    $out['code'] = $code;
    $out['valor-total'] =  $this::separa_miles( $suma_total );

    return $out;

  }

public function excelGuiaDespacho( $guia_despacho = null )
{
  $arr = $this::trExcelGuiaDespacho( $guia_despacho );

  $data = [ '###guia-despacho###' => $guia_despacho, 
            '###tr###'            => $arr['code'],
            '###valor-total###'   => $arr['valor-total']  

];
  return $this::despliegueTemplate( $data, "excel-guia.html" );
}

private function trExcelGuiaDespacho( $guia_despacho = null )
{
  $code = "";
  $arr = $this->consultas->listaIngresos2( $guia_despacho );

  $suma_total = 0;


    foreach ($arr['process'] as $key => $value) {

      $responsable = "{$value['nombres']} {$value['apaterno']}";

      $total =  $value['valor'] * $value['cantidad'];


      $data = [ '###fecha###'           => $value['fecha'],
                '###codigo###'          => $codigo,
                '###producto###'        => $value['insumo'],
                '###responsable###'     => $responsable,
                '###guia_despacho###'   => $value['guia_despacho'],
                '###cantidad###'        => $value['cantidad'],
                '###valor###'           => $this::separa_miles(  $value['valor'] ),
                '###valor-total###'     => $this::separa_miles(  $value['valor'] * $value['cantidad']   ),
                '###num_documento###'   => $value['num_documento'],
                '###proveedor###'       => $value['nombreProveedor'],
                '###tipo_documento###'  => $value['nombreTipoDocumento']
              ];

      $code .= $this::despliegueTemplate( $data, 'tr-excel-guia.html' );
      
      $suma_total = $suma_total+$total;

  }

  $out['code'] = $code;
  $out['valor-total'] =  $this::separa_miles( $suma_total );

  return $out;

}


private function listaEgresos( $codigo = null  )
{
  return $this::despliegueTemplate( ['###tr###' => $this::trListaEgresos( $codigo ) ],
                                    'tabla-egresos.html');
}

private function trListaEgresos( $codigo = null  )
{
  $arr = $this->consultas->listaEgresos( $codigo );
  $code = "";

  foreach ($arr['process'] as $key => $value) {

    $responsable = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

    //$valor = $this::lastValorIngresos( $value['codigo'] );

    $valor = $value['valor'] ;

    $data = [ '###fecha###'           => $value['fecha'],
              '###codigo###'          => $value['codigo'],
              '###nombre_producto###' => $value['insumo'],
              '###responsable###'     => $responsable,                                          
              '###cantidad###'        => $value['cantidad'],
              '###description###'     => $value['comentario'],
              '###valor-unitario###'  => $this::separa_miles( $valor  ),
              '###valor-total###'     => $this::separa_miles( $valor * $value['cantidad'] ),
              '###rs###'              => $this::existencia(  $value['id_proyecto'] )  


             ];

    $code .= $this::despliegueTemplate( $data, 'tr-egresos.html' );

  }

  return $code;
}


private function existencia( $algo = null )
{
  if( is_null(  $algo ) )
        return "SIN REGISTRO";
  else  return $algo ;
}


private function lastValorIngresos( $codigo = null )
{
  $valor = "";

  foreach ($this->consultas->lastValorIngresos( $codigo ) as $key => $value) {
    $valor .= $value[ 'valor' ];

  }
  return $valor;
}




  private function contenidoImagen( $IMAGEN = null, $DESCRIPTION = null )
  {
      $data = ['###imagen###' => $IMAGEN , '###description###' => $DESCRIPTION ];
      return $this::despliegueTemplate( $data , 'modal-imagen.html'  );
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
