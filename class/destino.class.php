<?php
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class Destino
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
    $this->menu_aux       = $this::menu_aux();


    $this->btn_listar     = '<a href="content-page.php?id=bGlzdGEtZGVzdGlubw=="
                                  class="btn btn-sm btn-success" >
                                  Listar Destino
                             </a>';

    $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItZGVzdGlubw=="
                                class="btn btn-sm btn-secondary" >
                                  [ Crear Destino ]
                             </a>';

  $this->email = "claudio.guzman@socma.cl";

  }

  private function control()
  {
    switch ($this->id) {

      case 'ingresaDestino':
        return $this::ingresaDestino();
        break;

      case 'crear-destino':
        return $this::crearDestino();
        break;

      case 'lista-destino':
        return $this::listarDestino();
        break;

      case 'eliminaDestino':
        return $this::eliminaDestino();
        break;

      case 'editaDestino':
        return $this::editaDestino();
        break;

      case 'editaDestinoData':
        return $this::editaDestinoData();
        break;

      case 'relDestinoCliente':
        return $this::relDestinoCliente();
        break;

      case 'quitarAsignacion':
        return $this::quitarAsignacion();
        break;

      case 'cambiaAsignacion':
      case 'asignaCliente':
        return $this::asignaCliente();
        break;


      case 'asignaClienteData':
        return $this::asignaClienteData();
        break;


      case 'cambiaClienteData':
        return $this::cambiaClienteData();
        break;

      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }

private function cambiaClienteData()
{
  //Array ( [id] => cambiaClienteData [id_cliente] => 10 [id_centro] => 26 )
  //print_r( $_POST );


  if( $this->consultas->procesaAsignaClienteData( $_POST['id_centro'], $_POST['id_cliente'],'z' ) )
  {
    $this->color  = "success";
    $this->icon   = '<i class="far fa-thumbs-up"></i>';
    $this->glosa  = "Registro Actualizado!!!";

  }else{

    $this->color  = "danger";
    $this->icon   = '<i class="far fa-thumbs-up"></i>';
    $this->glosa  = "Error al Actualizar!!!";
  }

  return $this::notificaciones( $this->color,
                                $this->icon,
                               "{$this->glosa}" );

}

 private function asignaClienteData()
 {


   if( $this->consultas->procesaAsignaClienteData( $_POST['id_centro'], $_POST['id_cliente']  ) )
   {
     $this->color  = "success";
     $this->icon   = '<i class="far fa-thumbs-up"></i>';
     $this->glosa  = "Registro Asignado!!!";

   }else{

     $this->color  = "danger";
     $this->icon   = '<i class="far fa-thumbs-down"></i>';
     $this->glosa  = "Error al asignar!!!";
   }

   return $this::notificaciones( $this->color,
                                 $this->icon,
                                "{$this->glosa}" ).$this::relDestinoCliente();
 }

  private function asignaCliente()
  {
    $arr  = $this->consultas->listaDestino( $_POST['id_destino'] );
    $code = "";

    foreach ($arr['process'] as $key => $value)
    {

      $ar = $this->consultas->listaClientes();

      if( $this->id == 'asignaCliente' )
          {
            $select = new select( $ar['process'],'id','descripcion','id_cliente','Cliente/Empresa',null,'z' );
            $id_button = "send";
          }
      else
          {
            $select = new select( $ar['process'],'id','descripcion','id_cliente',
                                'Cliente/Empresa',
                                $_POST['id_cliente'],'z' );

            $id_button = "update";
                              }


      $data = [ '###menu_aux###'  => $this->menu_aux,
                '###CENTRO###'    => strtoupper($value['descripcion'])  ,
                '###select###'    => $select->getCode(),
                '###id_centro###' =>  $_POST['id_destino'],
                '###id_button###' => $id_button,
                '###hidden###'    =>   null
     ];
      $code .= $this::despliegueTemplate( $data, 'asigna-cliente.html' );
    }


    return $code;

  }

  private function editaDestinoData()
  {
      if( $this->consultas->procesaDestino( addslashes( $_POST['nombre_destino'] ),
                                                        $_POST['id_destino'] ) )
      {
        $this->color  = "success";
        $this->icon   = '<i class="far fa-thumbs-up"></i>';
        $this->glosa  = "Actualizado!!!";

      }else{

        $this->color  = "danger";
        $this->icon   = '<i class="far fa-thumbs-down"></i>';
        $this->glosa  = "error al Actualizar";
      }

      return $this::notificaciones( $this->color,
                                    $this->icon,
                                   "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" );
  }

  private function editaDestino()
  {
    $arr  = $this->consultas->listaDestino( $_POST['id_destino'] );
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      $hidden = "<input type='hidden'
                        id='id_destino'
                        name='id_destino'
                        value ='{$_POST['id_destino']}'  >";

      $data = ['###title###'            => 'Edición',
               '###hidden###'           => $hidden,
               '###nombre_destino###'   => $value['descripcion'],
               '###menu_aux###'         => $this->menu_aux,
               '###id_button###'        => 'update'       ];
      $code .= $this::despliegueTemplate( $data, 'formulario-destino.html' );

    }

    return $code;

  }

  private function eliminaDestino()
  {

    if( $this->consultas->cambiaEstado( 'destino', 'id_estado' ,2, $_POST['id_destino'] ) )
    {
      $this->color  = "success";
      $this->icon   = '<i class="far fa-thumbs-up"></i>';
      $this->glosa  = "Registro Eliminado";

    }else{

      $this->color  = "danger";
      $this->icon   = '<i class="far fa-thumbs-down"></i>';
      $this->glosa  = "error al Eliminar";
    }

    return $this::notificaciones( $this->color,
                                  $this->icon,
                                 "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" ).$this::listarDestino();
  }

  private function ingresaDestino()
  {

    if( $this->consultas->procesadestino( addslashes( $_POST['nombre_destino'] )) )
    {
      $this->color  = "success";
      $this->icon   = '<i class="far fa-thumbs-up"></i>';
      $this->glosa  = "Registro Ingresado";

    }else{
      $this->color  = "danger";
      $this->icon   = '<i class="far fa-thumbs-down"></i>';
      $this->glosa  = "error al ingresar";
    }

    return $this::notificaciones( $this->color,
                                  $this->icon,
                                 "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" );
  }

  private function crearDestino()
  {
  //  return "MODULO EN CONSTRUCCION PARA {$this->id}";
    $data = ['###title###'            => 'Creación',
             '###hidden###'           => null,
             '###nombre_destino###'   => null,
             '###menu_aux###'         => $this->menu_aux,
             '###id_button###'        => 'send'       ];
    return $this::despliegueTemplate( $data, 'formulario-destino.html' );

  }

  private function listarDestino()
  {
    $arr = $this::trListarDestino();

    if( $arr['total-recs'] > 0 )
    {

      if( $this->tipo_usuario == 3 )
            $menu_aux = $this::menu_admin();
      else  $menu_aux = null;

      $data = ['###tr###'         => $arr['code'],
               '###total-recs###' => $arr['total-recs'],
               '###menu_aux###'   => $this->menu_aux
             ];

      return $this::despliegueTemplate( $data, 'tabla-destino.html' );

    }else{

      $this->color  = "danger";
      $this->icon   = '<i class="far fa-thumbs-down"></i>';
      $this->glosa  = "No Hay Registros";

      return $this::notificaciones( $this->color,
                                    $this->icon,
                                   "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" );
    }
  }

  private function trListarDestino()
  {
    $arr  = $this->consultas->listaDestino();
    $code = "";
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

      $data = ['###num###'        => $i+1,
               '###destino###'  => $value['descripcion'],
               '###id###'         => $value['id']     ];

      $code .= $this::despliegueTemplate( $data, 'tr-destino.html' );

      $i++;
    }

    $out['code']       = $code;
    $out['total-recs'] = $arr['total-recs'];

    return $out;
  }

  private function relDestinoCliente()
  {
    $arr = $this::trDestinoCliente();

    $data = ['###menu_aux###'   => $this->menu_aux,
            '###tr###'          => $arr['code'],
            '###total-recs###'  => $arr['total-recs']
    ];
    return $this::despliegueTemplate( $data, 'tabla-destino-cliente.html' );
  }

 private function trDestinoCliente()
 {
   $code ="";
   $i = 0;

   $arr = $this->consultas->relDestinoCliente();

   foreach ($arr['process'] as $key => $value) {

     if( $value['id_cliente'] =='' )
        {  $cliente_empresa = "No Asignado";
            $asignar = "<button class='btn btn-sm btn-secondary'
                        id ='asignar-cliente-{$value['id']}'  >
                              <i class='fas fa-angle-double-right'></i> Asignar
                        </button>";
            $id_cliente = 0;
        }
     else{

           $cliente_empresa =  "<strong>".$this::sacaClientes( $value['id_cliente'] )."</strong>";
           $asignar = "<button class='btn btn-sm btn-danger'
                               id ='quitar-cliente-{$value['id']}' >
                            <i class='fas fa-trash'></i>  Quitar Asignación
                       </button>
                       <button class='btn btn-sm btn-info'
                                           id ='cambia-cliente-{$value['id']}' >
                                        <i class='far fa-edit'></i>  Cambia Asignación
                                   </button>


                       ";

          $id_cliente = $value['id_cliente'];
     }

     $data = ['###num###'             => $i+1,
              '###destino###'         => $value['descripcion'],
              '###id###'              => $value['id']  ,
              '###asignar###'         => $asignar,
              '###cliente.empresa###' => $cliente_empresa  ,
              '###id_cliente###'      => $id_cliente         ];

     $code .= $this::despliegueTemplate($data, 'tr-destino-cliente.html');

     $i++;
   }

   $out['code']= $code;
   $out['total-recs']= $arr['total-recs'];

   return $out;
 }


 private function sacaClientes( $id_cliente = null )
 {
   $arr = $this->consultas->listaClientes( $id_cliente );

  $cliente = null;
   foreach ($arr['process'] as $key => $value) {
     $cliente = $value['descripcion'];
   }

   return $cliente;
 }

 private function quitarAsignacion()
 {

   if( $this->consultas->quitarDestinoCliente( $_POST['id_destino'],$_POST['id_cliente']))
   {
      $button = "<a href='content-page.php?id=cmVsRGVzdGlub0NsaWVudGU=' class='btn btn-sm btn-dark'>
                  <i class='fas fa-sync-alt'></i>  Refresh
                </a>
      ";

          return "REGISTRO ELIMINADO {$button}";
   }else  return "No se pudo eliminar registro!";

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
