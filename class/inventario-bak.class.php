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

//#!/bin/bash

  $this->email = "alvaro.barria@socma.cl";


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

      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
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
      																			      'no-image.png' 				 ) )
      { $ingresa = true;  $this->msg = null;  }else
      { $ingresa = false; $this->msg = "error en db"; }
    }else{

    //subida de archivo
    require_once("ftp.class.php");
    $ob_ftp = new FTP( "archivo", $this->token );

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
                                                        $ob_ftp->changeNameFile( $_FILES['archivo']['name'] ) ) )
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

          return "{$stock} {$this->msg}";

      }else{
          return "Error en actualizar";
      }

    }else{
      return "Error en todo!!! {$this->msg}";
    }

/*
    return $this->consultas->ingresaIngresosData( 	$_POST['codigo']          ,
                                                $_POST['id-inventario']  ,
                                                $_POST['insumo']  ,
                                                $_POST['id-proveedor']   ,
                                                $this->yo 			  ,
                                                $_POST['cantidad'] 		  ,
                                                $_POST['tipo_documento'] ,
                                                $_POST['num-documento']  ,
                                                'no-image.png' 				 ); */

  }

  private function ingresa_nombre_destino_from_egreso()
  {
    /*
      Array ( [id] => ingresa_nombre_destino_from_egreso [nombre_destino] => tes de destino [id_insumo] => 84 )
    */

    //print_r( $_POST );

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

    if( $this->consultas->ingresaEgresos(   $_POST['codigo'],
           																  $this->yo     ,
           																  $_POST['insumo']  ,
           																  $_POST['id_trabajo']  ,
           																  $_POST['id_cliente']  ,
           																  $_POST['id_destino']  ,
           																  $_POST['cantidad']  ,
           																  addslashes( $_POST['comentarios'] )  ) )
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

          return $stock;

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

  public function criticos()
  {
    $arr = $this::trCriticos();

    if( $arr['total-recs'] == 0 )
      return null;
    else{

      $code = "";

      $data = ['###tr###' => $arr['code'],
               '###total-recs###' => $arr['total-recs']
      ];

      $code .= $this::despliegueTemplate( $data, 'tabla-criticos.html',1 );

      $ob_mail = new mails( 'enviaStockCriticoUnitario',
                            $this->email,
                            $code   );

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
                                            $_POST['id_inventario'] ) )
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
          $this->msg ="Inventario Subido Correctamente";
          $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
      }
      else {
          $sube = false;
          $this->msg ="Error en Consulta";
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
          $this->msg ="Inventario Subido Correctamente";
          $this->icon ='<i class="fa fa-thumbs-o-up" aria-hidden="true"></i>';
        }
        else{
          $sube = false;
          $this->msg ="Error en Consulta";
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
                    $this->msg  =  "Error en Consulta";
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
            return $this::notificaciones( "success", $this->icon, "{$this->msg} {$this->btn_crear} {$this->btn}" );
    else    return $this::notificaciones( "danger",  $this->icon, "{$this->msg} {$this->btn_crear} {$this->btn}" );
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

        if( $this->tipo_usuario == 3 )
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


        $data = ['###title###'              => 'Editar',
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
                 '###btn-stck-critico###'   => $this->btn_critico,
                 '###descripcion###'        => $this::codifica( $value['descripcion'] ,1),
                 '###select-disabled1####'  => $this::seletcDisabled('sub-ubicacion',
                                                                    $this::codifica( $value['nombreSubUbicacion'],1 ),
                                                                    $value['id_sububicacion'] ),
                 '###select-disabled2####'  => $this::seletcDisabled('sub_tipo',
                                                                    $this::codifica( $value['nombreSubTipo'] ,1),
                                                                    $value['id_sub_tipo'] ),
                 '###select-unidad###'     => $sel3->getCode(),
                 '###id_button###'         => 'edit'                  ];

        $code .= $this::despliegueTemplate( $data, 'ventario.html' );

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
          if( $this->consultas->procesaElmento( addslashes(  $_POST['elemento']),
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
                                                $_POST['id_unidad'] ))
               {
                //aqui aplicas las gemelas
                if(  $this->consultas->procesaGemela( addslashes(  $_POST['elemento']),
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
        else   { $sube = false; $this->msg ="Error en Consulta";            $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
       else    { $sube = false; $this->msg ="Error FTP";                    $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
      else     { $sube = false; $this->msg ="Error Tipo de Archivo";        $this->icon ='<i class="fa fa-thumbs-o-down" aria-hidden="true"></i>'; }
    }else{

      if( $this->consultas->procesaElmento( addslashes(  $_POST['elemento']),
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
                                            $_POST['id_unidad'] ))
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
            return $this::notificaciones( "success", $this->icon, "{$this->msg} {$this->btn_crear}  {$this->btn}" );
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
             '###select-unidad###'      => $sel3->getCode(),
             '###id_button###'          => 'send'

              ];
    return $this::despliegueTemplate( $data, 'ventario.html' );
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
      $data = [];
      return $this::despliegueTemplate( $data, 'menu-admin.html' );
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

      if(  $this->tipo_usuario == 3  )
            $menu_aux = $this::menu_admin();
      else  $menu_aux = null;

      $data = ['###tr###'         => $arr['code'],
               '###total-recs###' => $arr['total-recs'],
               '###nav-links###'  => $arr['nav-links'],
               '###buscar###'     => $this::buscar(),
               '###btn###'        => $btn,
               '###menu_aux###'   => $menu_aux

     ];
      return $this::despliegueTemplate( $data , 'tabla-inventario.html' );

    }else{

      if(  $this->tipo_usuario == 3  )
            $menu_aux = $this::menu_admin();
      else  $menu_aux = null;

      $code = "";

      $code .= $menu_aux."<br>";
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

      if( $this->tipo_usuario == 3 )
      {
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
      }

      else {
        $this->btn = null;
        $this->ingreso = null;
      }

      if( $value['stock'] > $value['stock_minimo'] )
            $alerta = "<span class='ok'> Stock Ok</span>"  ;
      else  $alerta =  "<span class='peligro'>Stock Crítico</span>"  ;


      if( $value['stock'] < 0 )
            $stock = 0;
      else  $stock = $value['stock'];


      $data = ['###num###'            => $i+1,
               '###codigo###'         => $value['codigo'],
               '###nombre###'         => $this::codifica( substr( $value['nombre'], 0,28),null ),
               '###nombre2###'        => $this::codifica( $value['nombre'],null ),
               '###ubicacion###'      => $this::codifica( $value['nombreUbicacion'],null ),
               '###sub_ubicacion###'  => $this::codifica( $value['nombreSubUbicacion'],null),
               '###tipo###'           => $this::codifica( $value['nombreTipo'] ,null ),
               '###sub_tipo###'       => $this::codifica( $value['nombreSubTipo'] ,null),
               '###stock###'          => $stock,
               '###alerta###'         => $alerta,
               '###id###'             => $value['id'],
               '###modal-img###'      => $this::modal("imagen-modal-{$value['id']}", null,
                                                      null,
                                                      $this::contenidoImagen( $value['imagen'], $value['descripcion'] )  ),
               '###modal-historico###'=> $this::modal("modal-historico-{$value['id']}",null,
                                                      "Contenido Histórico de {$value['codigo']}",
                                                      $this::modalHistorico( $value['codigo'] )),

              '###modal-ingreso###'   => $this::modal("modal-ingreso-{$value['id']}",null,
                                                     "Formulario de Ingresos",
                                                     $this::formIngreso( $value['id'],
                                                                         $value['codigo'],
                                                                         $value['nombre']
                                                                       )),
              '###modal-egreso###'   => $this::modal("modal-egreso-{$value['id']}",null,
                                                    "Formulario de Egresos",
                                                    $this::formEgreso(  $value['id'],
                                                                        $value['codigo'],
                                                                        $value['nombre']) ),

               "###editar###" => $this->btn, '###ingreso###' => $this->ingreso,
               "###unidad###"  => $value['nombreUnidad']
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

      $data = ['###CODIGO###'           => $value['codigo'],
               '###PRODUCTO###'         => $this::codifica( $value['nombre'] , 2 ),
               '###UBICACION###'        => $this::codifica( $value['nombreUbicacion'] ,2 ),
               '###SUB-UBICACION###'    => $this::codifica( $value['nombreSubUbicacion'] ,2 ),
               '###CATEGORIA###'        => $this::codifica( $value['nombreTipo'] ,2 ),
               '###SUB-CATEGORIA###'    => $this::codifica( $value['nombreSubTipo'] ,2 ),
               '###STOCK###'            => $value['stock'],
               '###UNIDAD###'           => $value['unidad'],
               '###ALERTA###'           => $this::codifica( $alerta ,2 ),
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


private function formEgreso($id_inventario = null, $codigo = null, $insumo = null)
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

  $data = [ '###id###'                => $id_inventario,
            '###id_user###'           => $this->yo,
            '###codigo###'            => $codigo,
            '###insumo###'            => $insumo,
            '###select-cliente###'    => $sel1->getCode(),
            '###select-destino###'    => $sel2->getCode(),
            '###select-trabajo###'    => $sel3->getCode(),
  ];
  return $this::despliegueTemplate( $data, 'formulario-egreso.html' );

}


  private function formIngreso( $id_inventario = null, $codigo = null, $insumo = null )
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



  private function modalHistorico( $codigo = null )
  {
    ///return "contenido modificado para {$codigo}!";
    $data = ['###codigo###'         => $codigo,
             '###lista-ingresos###' => $this::listaIngrsesos( $codigo ) ,
             '###lista-egresos###'  => $this::listaEgresos( $codigo ) ];

    return $this::despliegueTemplate( $data, 'tab-historicos.html' );

  }

  private function listaIngrsesos( $codigo = null )
  {
    return $this::despliegueTemplate( ['###tr###' => $this::trlistaIngrsesos( $codigo ) ],
    'tabla-ingresos.html' );
  }

  private function trlistaIngrsesos( $codigo = null )
  {
    $code = "";
    $arr = $this->consultas->listaIngresos( $codigo );


    foreach ($arr['process'] as $key => $value) {

        $responsable = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

        $data = [ '###fecha###'           => $value['fecha'],
                  '###codigo###'          => $codigo,
                  '###nombre_producto###' => $value['insumo'],
                  '###responsable###'     => $responsable,
                  '###cantidad###'        => $value['cantidad'],
                  '###archivo###'         => $value['archivo'],
                  '###num_documento###'   => $value['num_documento'],
                  '###proveedor###'       => $value['nombreProveedor'],
                  '###tipo_documento###'  => $value['nombreTipoDocumento']
                 ];

        $code .= $this::despliegueTemplate( $data, 'tr-ingresos.html' );
    }

    return $code;

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

    $data = [ '###fecha###'           => $value['fecha'],
              '###codigo###'          => $value['codigo'],
              '###nombre_producto###' => $value['insumo'],
              '###responsable###'     => $responsable,
              '###trabajo###'         => $value['nombreTrabajo'],
              '###cliente###'         => $value['nombreCliente'],
              '###destino###'         => $value['nombreDestino'],
              '###cantidad###'        => $value['cantidad'],
              '###description###'     => $value['comentario']
             ];

    $code .= $this::despliegueTemplate( $data, 'tr-egresos.html' );

  }

  return $code;
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
