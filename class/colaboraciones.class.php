<?php
/**
 *
 */
class Colaboraciones
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

  function __construct($id = null)
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
    $this->token 		      =  date("Ymd");
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
    switch ( $this->id ) {
      case 'listarColaboraciones':

        return $this::listarColaboraciones();
      break;

      case 'generarColaboraciones':
          return $this::generarColaboraciones();
        break;

      case 'ingresaPreColaboracion':
        return $this::ingresaPreColaboracion();
        break;

      case 'eliminaColaboracion':
        return $this::eliminaColaboracion();
        break;

      case 'verColaboraciones':
        return $this::verColaboraciones();
        break;

      case 'modificarInventario':
        return $this::modificarInventario();
        break;

      default:
        return $this->error;
        break;
    }
  }

  private function eliminaColaboracion()
  {
    //Array ( [id] => eliminaColaboracion [id_colaboracion] => 30 [token] => E5SC6-20210514 )
    if( $this->consultas->eliminaColaboracion( $_POST['id_colaboracion'] ) )
    {

      $alert  = "warning";
      $icon   = '<i class="far fa-thumbs-up"></i>';
      $glosa  = "Registro Eliminado";

      

    }else{

      $alert  = "danger";
      $icon   = '<i class="far fa-thumbs-down"></i>';
      $glosa  = "Error al Eliminar";

    }

    return $this::notificaciones( $alert,$icon, $glosa ).$this::tablaPreColaboracion( $_POST['token'] );
  }

  /**
   * procesaSerialize()
   * @return boolean
   */
  private function procesaSerialize()
  {
    $code = "";
    $j    = 0;

    $arr_codigo_producto = $this::separa( $_POST['codigo_producto'], '&' );
    $arr_cantidad        = $this::separa( $_POST['cantidad'], '&' );

    for ($i=0; $i < count( $arr_codigo_producto ); $i++) { 
      
        $aux_cod_prod    = $this::separa( $arr_codigo_producto[$i],'=' );
        $aux_cantidad    = $this::separa( $arr_cantidad[$i],'=' );

        $stock_db   = 0;
        $suma_stock = 0;
        $ar         = $this->consultas->listaElementos( null,
                                                      1,
                                                      null,
                                                      null,
                                                      null,
                                                      null,
                                                      $aux_cod_prod[1]);

        foreach ($ar['process'] as $key => $value) 
        {
          $stock_db = $value['stock'];
        }

        $suma_stock = $aux_cantidad[1]+$stock_db;

        if( $this->consultas->actualizaStockElemento( $suma_stock, $aux_cod_prod[1]) )
          $j++;
    }

    if( $j > 0 )
          return true;
    else  return false;
  }


  private function ingresaPreColaboracion()
  {
    $arr = $this->consultas->listaElementos(null,'x',null,null,null,null,null,$_POST['titulo']);
    $codigo_elemento = "";

    foreach ($arr['process'] as $key => $value) {
      $codigo_elemento .= $value['codigo'];
    }

    $token = "E{$_POST['id_cliente']}SC{$_POST['id_sub_cliente']}-{$this->token}";

    if( $this->consultas->procesaPreColaboracion( $token,
                                                  $codigo_elemento,
                                                  $_POST['id_cliente'],
                                                  $_POST['id_sub_cliente'],
                                                  $this->yo, $_POST['cantidad'],
                                                  $_POST['id_unidad'] ))
    {

      return $this::notificaciones('success',
                                   '<i class="far fa-thumbs-up"></i>',
                                   'Registro Ingresado').$this::tablaPreColaboracion( $token  );
    }else{
      return $this::notificaciones('danger',
                                   '<i class="far fa-thumbs-down"></i>',
                                   'Error al ingresar/Registro repetido').$this::tablaPreColaboracion( $token );
    }
  }

  /**
   * modificarInventario(): con este metodo modificamos el valor del inventario final
   */
  private function modificarInventario()
  {
    if( $this:: procesaSerialize()  )
          $actualiza = "ok";
    else  $actualiza = "fail";
    
    if( $this->consultas->actualizaEstadoPreColaboracion( $_POST['token'] ) )
    {

      $alert  = "success";
      $icon   = '<i class="far fa-thumbs-up"></i>';
      $glosa  = "Los insumos aportados han sido ingresados al inventario";

    }else{

      $alert  = "danger";
      $icon   = '<i class="far fa-thumbs-down"></i>';
      $glosa  = "Error al Eliminar";

    }

    return $this::tablaPreColaboracion( $_POST['token'] ). $this::notificaciones($alert,$icon,$glosa);
  }

  private function verColaboraciones()
  {
    return $this::tablaPreColaboracion( $_POST['token'] );
  }

  private function tablaPreColaboracion( $token = null )
  {
    $detalles = $this->consultas->listaPreColaboracionDetalle( $token );

    $nombreEmpresa  = null;
    $sub_cliente    = null;
    $botoncillo     = null;

    foreach ($detalles['process'] as $key => $val) {

      $nombreEmpresa  = $val['nombreEmpresa'];
      $sub_cliente    = "{$val['nombres']} {$val['apaterno']} {$val['amaterno']}";

      if( $val['id_estado'] == 1  )
      {
        $botoncillo = '<button class="btn btn-sm btn-secondary"
                          id="ingresar-colaboracion-inventario">
                            <i class="fas fa-angle-double-right"></i> Ingresar a Inventario
                      </button>';
      }
    }

    $arr  = $this::trTablaColaboracion( $token, $id_estado );

    $data = [ '###tr###'                  => $arr['code'],
              '###codigo###'              => $token ,
              '###empresa###'             => $nombreEmpresa,
              '###representante###'       => $sub_cliente,
              '###total-recs###'          => $arr['total-recs'],
              '###ingresa-inventario###'  => $botoncillo      ];

    return $this::despliegueTemplate( $data,'tabla-pre-colaboracion.html' );
  }

  /**
   * trTablaColaboracion(): listado de colaboraciónote
   * @param  string token
   * @return string
   */
  private function trTablaColaboracion( $token = null )
  {
    $code = "";
    $arr = $this->consultas->listaPreColaboracion( $token );
    $i =0;

    foreach ($arr['process'] as $key => $value) {

      if($value['id_estado'] == 1 )
      {
        $estado = "No";
        $button ='<button type="button" id="elimina-colaboracion-###id###"
                    class="btn btn-sm btn-outline-danger">
                    <i class="far fa-trash-alt"></i>
                    </button>';

      }
      else  { $estado = "Si"; $button = null; }

      $sub_cliente = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

      $data  =[ '###num###'                 => $i+1,
                '###codigo_colaboracion###' => $value['token'] ,
                '###Producto###'            => $value['nombre']  ,
                '###cantidad###'            => $value['cantidad'],
                '###unidades###'            => $value['nombreUnidad'],
                '###empresa###'             => $value['nombreEmpresa'],
                '###representante###'       => $sub_cliente,
                '###codigo_producto###'     => $value['codigo_producto'],
                '###id###'                  => $value['id'],
                '###token###'               => $token,
                '###estado###'              => $estado,
                '###button###'              => $button                ];

      $code .= $this::despliegueTemplate( $data,'tr-pre-colaboracion.html' );

      $i++;
    }

    $out['code'] = $code;
    $out['total-recs'] = $arr['total-recs'];

    return $out;

  }

  /**
   * generarColaboraciones()
   *
   * @return string
   */
  private function generarColaboraciones()
  {
      $a3 = $this->consultas->unidades();
      $sel3 = new Select( $a3['process'], 'id','descripcion','id_unidad', 'Unidad', $value['id_unidad'] );


      $arr    = $this->consultas->listaClientes();
      $select = new select( $arr['process'],'id','descripcion','id_cliente','Cliente/Empresa',null,'z' );

      $data = ['###menu_aux###'        => $this->menu_aux,
               '###select-cliente###'  => $select->getCode(),
               '###select-unidades###' => $sel3->getCode(),
               '###tabla###'           => null

      ];
      return $this::despliegueTemplate( $data, 'genera-colaboraciones.html' );
  }

  /**
   * generarColaboraciones()
   *
   * @return string
   */
  private function listarColaboraciones()
  {
    //return "MODULO EN CONSTRUCCION {$this->id}";
    $arr = $this::trListarColaboraciones();


    $data = ['###menu_aux###'     => $this->menu_aux,
             '###tr###'           => $arr['code'],
             '###total-recs###'   => $arr['total-recs']
    ];
    return $this::despliegueTemplate( $data, 'tabla-colaboracion.html' );

  }


  private function trListarColaboraciones()
  {
    $arr  = $this->consultas->listaPreColaboracionDetalle();
    $code = "";
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

      $representante = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

      if( $value['id_estado'] == 1  )
            $estado = "NO";
      else  $estado = "SI";

      $data = ['###num###'            => $i+1,
               '###codigo###'         => $value['token'],
               '###empresa###'        => $value['nombreEmpresa'],
               '###representante###'  => strtoupper( $representante ),
               '###fecha###'          => $value['fecha'] ,
               '###inventariado###'   => $estado ];

      $code .= $this::despliegueTemplate( $data, 'tr-colaboracion.html' );

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
