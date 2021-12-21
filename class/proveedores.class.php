<?php
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class Proveedores
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

    $this->btn_listar     = '<a href="content-page.php?id=bGlzdGFyLXByb3ZlZWRvcmVz" class="btn btn-sm btn-success" >
                                  Listar Proveedores
                             </a>';

    $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItcHJvdmVlZG9yZXM="
                                class="btn btn-sm btn-secondary" >
                                  [ Crear Proveedores ]
                             </a>';
  $this->menu_aux = $this::menu_aux();  
  $this->email = "claudio.guzman@socma.cl";

  }

  private function control()
  {
    switch ($this->id) {

      case 'ingresaProveedor':
        return $this::ingresaProveedor();
        break;

      case 'crear-proveedores':
        return $this::crearProveedores();
        break;

      case 'listar-proveedores':
        return $this::listarProveedores();
        break;

      case 'eliminaProveedor':
        return $this::eliminaProveedor();
        break;

      case 'editaProveedor':
        return $this::editaProveedor();
        break;

      case 'editaProveedorData':
        return $this::editaProveedorData();
        break;

      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }

  private function editaProveedorData()
  {
      if( $this->consultas->procesaProveedores( addslashes( $_POST['nombre_proveedor'] ),
                                                addslashes( $_POST['codigo_proveedor'] ),
                                                addslashes( $_POST['rut_proveedor'] ),
                                                $_POST['id_proveedor'] ) )
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

  private function editaProveedor()
  {
    $arr  = $this->consultas->listaProveedores( $_POST['id_proveedor'] );
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      $hidden = "<input type='hidden'
                        id='id_proveedor'
                        name='id_proveedor'
                        value ='{$_POST['id_proveedor']}'  >";

      $data = ['###title###'            => 'Edición',
               '###hidden###'           => $hidden,
               '###nombre_proveedor###' => $value['descripcion'],
               '###rut_proveedor###'    => $value['rut_proveedor'],
               '###codigo_proveedor###' => $value['codigo_proveedor'],
               '###menu_aux###'         => $this->menu_aux,
               '###id_button###'        => 'update'       ];
      $code .= $this::despliegueTemplate( $data, 'formulario-proveedores.html' );

    }

    return $code;

  }

  private function eliminaProveedor()
  {

    if( $this->consultas->cambiaEstado( 'proveedores', 'id_estado' ,2, $_POST['id_proveedor'] ) )
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
                                 "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" ).$this::listarProveedores();
  }

  private function ingresaProveedor()
  {

    if( $this->consultas->procesaProveedores( addslashes( $_POST['nombre_proveedor'] ),
                                              addslashes( $_POST['codigo_proveedor'] ),  
                                              addslashes( $_POST['rut_proveedor'] )) )
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

  private function crearProveedores()
  {
  //  return "MODULO EN CONSTRUCCION PARA {$this->id}";
    $data = ['###title###'            => 'Creación',
             '###hidden###'           => null,
             '###nombre_proveedor###' => null,
             '###rut_proveedor###'    => null,
             '###codigo_proveedor###' => null,
             '###menu_aux###'         => $this->menu_aux,
             '###id_button###'        => 'send'       ];
    return $this::despliegueTemplate( $data, 'formulario-proveedores.html' );

  }

  private function listarProveedores()
  {
    $arr = $this::trListarProveedores();

    if( $arr['total-recs'] > 0 )
    {
      $data = ['###tr###'         => $arr['code'],
               '###total-recs###' => $arr['total-recs'],
               '###menu_aux###'   => $this->menu_aux
             ];

      return $this::despliegueTemplate( $data, 'tabla-proveedores.html' );

    }else{

      $this->color  = "danger";
      $this->icon   = '<i class="far fa-thumbs-down"></i>';
      $this->glosa  = "No Hay Registros";

      return $this::notificaciones( $this->color,
                                    $this->icon,
                                   "{$this->glosa} {$this->btn_crear} {$this->btn_listar}" );
    }
  }

  private function trListarProveedores()
  {
    $arr  = $this->consultas->listaProveedores();
    $code = "";
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

      if( is_null( $value['rut_proveedor'] ) )
      {
        $rut_proveedor = "NO DATA";
      }else {

        $r = $this::separa( $value['rut_proveedor'],'-' );
        $rut_proveedor = $this::separa_miles( $r[0] ).' - '.$r[1] ;
      
      }

      if( is_null( $value['codigo_proveedor'] ) )
      {
        $codigo_proveedor = "NO DATA";
      }else {
        $codigo_proveedor = $value['codigo_proveedor'];
      }

      $data = ['###num###'                => $i+1,
               '###proveedor###'          => $value['descripcion'],
               '###rut-proveedor###'      => $rut_proveedor, 
               '###codigo-proveedor###'   => strtoupper(  $codigo_proveedor ), 
               '###id###'                 => $value['id']     ];

      $code .= $this::despliegueTemplate( $data, 'tr-proveedores.html' );

      $i++;
    }

    $out['code']       = $code;
    $out['total-recs'] = $arr['total-recs'];

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
