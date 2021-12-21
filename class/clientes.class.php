<?php
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class Clientes
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

    $this->btn_listar     = '<a href="content-page.php?id=bGlzdGEtY2xpZW50ZXM="
                                  class="btn btn-sm btn-success" >
                                  Listar Clientes
                             </a>';

    $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItY2xpZW50ZXM="
                                class="btn btn-sm btn-secondary" >
                                  [ Crear Clientes ]
                             </a>';

  $this->email = "claudio.guzman@socma.cl";
  $this->menu_aux = $this::menu_aux();
  }

  private function control()
  {
    switch ($this->id) {

      case 'ingresaCliente':
        return $this::ingresaCliente();
        break;

      case 'crear-clientes':
        return $this::crearClientes();
        break;

      case 'lista-clientes':
        return $this::listarClientes();
        break;

      case 'eliminaCliente':
        return $this::eliminaCliente();
        break;

      case 'editaCliente':
        return $this::editaCliente();
        break;

      case 'editaClienteData':
        return $this::editaClienteData();
        break;

      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }

  private function editaClienteData()
  {
      if( $this->consultas->procesaClientes( addslashes( $_POST['nombre_cliente'] ),
                                                $_POST['id_cliente'] ) )
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

  private function editaCliente()
  {
    $arr  = $this->consultas->listaClientes( $_POST['id_cliente'] );
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      $hidden = "<input type='hidden'
                        id='id_cliente'
                        name='id_cliente'
                        value ='{$_POST['id_cliente']}'  >";

      $data = ['###title###'            => 'Edición',
               '###hidden###'           => $hidden,
               '###nombre_cliente###' => $value['descripcion'],
                '###menu_aux###'         => $this->menu_aux,
               '###id_button###'        => 'update'       ];
      $code .= $this::despliegueTemplate( $data, 'formulario-clientes.html' );

    }

    return $code;

  }

  private function eliminaCliente()
  {

    if( $this->consultas->cambiaEstado( 'clientes', 'id_estado' ,2, $_POST['id_cliente'] ) )
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
                                 "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" ).$this::listarClientes();
  }

  private function ingresaCliente()
  {

    if( $this->consultas->procesaClientes( addslashes( $_POST['nombre_cliente'] )) )
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

  private function crearClientes()
  {
  //  return "MODULO EN CONSTRUCCION PARA {$this->id}";
    $data = ['###title###'            => 'Creación',
             '###hidden###'           => null,
             '###nombre_cliente###'   => null,
             '###menu_aux###'         => $this->menu_aux,
             '###id_button###'        => 'send'       ];
    return $this::despliegueTemplate( $data, 'formulario-clientes.html' );

  }

  private function listarClientes()
  {
    $arr = $this::trListarClientes();

    if( $arr['total-recs'] > 0 )
    {

      if( $this->tipo_usuario == 3 )
            $menu_aux = $this::menu_admin();
      else  $menu_aux = null;

      $data = ['###tr###'         => $arr['code'],
               '###total-recs###' => $arr['total-recs'],
               '###menu_aux###'   => $this->menu_aux
             ];

      return $this::despliegueTemplate( $data, 'tabla-clientes.html' );

    }else{

      $this->color  = "danger";
      $this->icon   = '<i class="far fa-thumbs-down"></i>';
      $this->glosa  = "No Hay Registros";

      return $this::notificaciones( $this->color,
                                    $this->icon,
                                   "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" );
    }
  }

  private function trListarClientes()
  {
    $arr  = $this->consultas->listaClientes();
    $code = "";
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

      $data = ['###num###'        => $i+1,
               '###cliente###'  => $value['descripcion'],
               '###id###'         => $value['id']     ];

      $code .= $this::despliegueTemplate( $data, 'tr-clientes.html' );

      $i++;
    }

    $out['code']       = $code;
    $out['total-recs'] = $arr['total-recs'];

    return $out;
  }

  private function menu_admin()
  {
      $data = [];
      return $this::despliegueTemplate( $data, 'menu-admin.html' );
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
