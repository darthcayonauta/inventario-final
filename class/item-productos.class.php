<?PHP
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class ItemProductos
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

  function __construct($id=null)
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

        case 'eliminaItemProductos':
          return $this::eliminaItemProductos();
          break;


        case 'ingresaItemProducto':
          return $this::ingresaItemProducto();
          break;

        case 'crearItemProductos':

          return $this::crearItemProductos();
          break;

        case 'editaItemProductos':
          return $this::editaItemProductos();
          break;


        case 'actualizaItemProducto':
          return $this::actualizaItemProducto();
          // code...
          break;

        case 'listarItemProductos':
          return $this::listarItemProductos();
          break;
        default:
          // code...
          return $this->error;
          break;
      }
    }


    private function actualizaItemProducto()
    {
    //Array ( [id] => actualizaItemProducto [nombre_item] => test [id_item] => 1 )

    if( $this->consultas->procesaItemProductos( $_POST['nombre_item'],$_POST['id_item']  ) )
    {
      $tipo_alerta = "success";
      $icon = "<i class='far fa-thumbs-up'></i>";
      $msg  = "Registro Actualizado";

    }else{

      $tipo_alerta = "danger";
      $icon = "<i class='far fa-thumbs-up'></i>";
      $msg  = "Error al actualizar";
    }

    return $this::notificaciones( $tipo_alerta,$icon,$msg );
    }


    private function editaItemProductos()
    {

      $arr = $this->consultas->listarItemProductos( $_POST['id_item'] );
      $code ="";

      $hidden = "<input type='hidden' id='id_item' name='id_item' value='{$_POST['id_item']}'>";

      foreach ($arr['process'] as $key => $value) {

        $data = ['###title###'          => 'Edición',
                 '###hidden###'         => $hidden,
                 '###nombre_item###'    => $value['descripcion'],
                 '###id_button###'      => 'update',
                 '###menu_aux###'       => $this->menu_aux  ];

        return $this::despliegueTemplate( $data , "formulario-item-productos.html" );

      }

      return $code;
    }

    private function eliminaItemProductos()
    {
      //Array ( [id] => eliminaItemProductos [id_item] => 1 )

      if( $this->consultas->eliminaItemProductos( $_POST['id_item'] ) )
      {
        return $this::notificaciones( 'danger',
                                      '<i class="far fa-thumbs-up"></i>',
                                      'Registro Eliminado' ).$this::listarItemProductos();


      }else{ return "Error al Eliminar"; }

    }


    private function ingresaItemProducto()
    {
      //Array ( [id] => ingresaItemProducto [nombre_item] => test )
      //print_r( $_POST );

      if( $this->consultas->procesaItemProductos($_POST['nombre_item']))
      {
        return $this::notificaciones( 'success',
                                      '<i class="far fa-thumbs-up"></i>',
                                      'Registro Ingresado' ).$this::listarItemProductos();

          }
      else { return "error al ingresar "; }
    }


    private function crearItemProductos()
    {
      $data = ['###title###'          => 'Ingreso',
               '###hidden###'         => null,
               '###nombre_item###'    => null,
               '###id_button###'      => 'send',
               '###menu_aux###'       => $this->menu_aux  ];

      return $this::despliegueTemplate( $data , "formulario-item-productos.html" );
  }

    private function listarItemProductos()
    {

      $arr = $this::trItemProductos();

      $data =['###menu_aux###'    => $this->menu_aux,
              '###tr###'          => $arr['code'],
              '###total-recs###'  => $arr['total-recs']
     ];
      return $this::despliegueTemplate( $data, 'tabla-items.html' );
    }


    private function  trItemProductos()
    {
      $arr = $this->consultas->listarItemProductos();
      $code = "";
      $i = 0;

      foreach ($arr['process'] as $key => $value) {
        $data =[ '###num###'  => $i+1,
                 '###item###' => $value['descripcion']  ,
                 '###id###'   => $value['id']
        ];
        $code .= $this::despliegueTemplate( $data , 'tr-items.html' );

        $i++;
      }

      $out['code'] = $code;
      $out['total-recs'] = $arr['total-recs'];

      return $out;
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
