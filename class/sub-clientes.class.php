<?php
/**
 * @author  Claudio Guzman Herrera
 * @version 1.0
 */
class SubClientes{

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
        case 'ver-subclientes':
          return $this::verSubClientes();
          break;

        case 'crear-subclientes':
          // code...
          return $this::crearSubClientes();
          break;

          case 'ingresaSubCliente':
            return $this::ingresaSubCliente();
            break;

          case 'eliminaSubCliente':
            return $this::eliminaSubCliente();
            break;

          case 'editaSubCliente':
            return $this::editaSubCliente();
            break;

          case 'actualizaSubCliente':
            return $this::actualizaSubCliente();
            break;


          case 'comboSubClientes':
            return $this::comboSubClientes();
            break;

        default:
          return $this->error;
          break;
      }
  }

private function comboSubClientes()
{

  $arr = $this->consultas->listaSubClientes( null, $_POST['id_cliente'] );

  $cliente[0]="nombres";
  $cliente[1]="apaterno";
  $cliente[2]="amaterno";

  $select = new select( $arr['process'], 'id', $cliente,'id_sub_cliente','Sub Clientes',null,'z' );

  return $select->getCode();
}

private function actualizaSubCliente()
{

if( $this->consultas->procesaSubCliente( htmlentities( $_POST['nombre_cliente']) ,
																	       htmlentities( $_POST['apaterno'] ),
																	       htmlentities( $_POST['amaterno'] ),
																	       htmlentities( $_POST['rut'] ),
																	       htmlentities( $_POST['email']) ,
																	       htmlentities( $_POST['fono'] ),
																	       htmlentities( $_POST['id_cliente']),
                                         $_POST['id_subcliente']
																	      )  )
{

  return $this::notificaciones('success',
                               '<i class="far fa-thumbs-up"></i>',
                               'Registro Actualizado!');

}else{
  return "Error, no se ha podido Actualizar el registro!";
}

}


private function editaSubCliente()
{

  $arr = $this->consultas->listaSubClientes( $_POST['id_subcliente']  );
  $code = "";
  foreach ($arr['process'] as $key => $value) {

     $hidden = "<input type='hidden' name='id_subcliente' id='id_subcliente' value='{$_POST['id_subcliente']}'>";

     $ar     = $this->consultas->listaClientes();

     $select =  new select($ar['process'],
                           'id',
                           'descripcion',
                           'id_cliente',
                           'Empresa',$value['id_cliente'],'z' );

      $data = ['###title###'          => 'Edición',
               '###nombre_cliente###' => $value['nombres'],
               '###apaterno###'       => $value['apaterno'],
               '###amaterno###'       => $value['amaterno'],
               '###rut###'            => $value['rut'],
               '###email###'          => $value['email'],
               '###fono###'           => $value['fono'],
               '###select###'         => $select->getCode(),
               '###hidden###'         => $hidden,
               '###menu_aux###'       => $this->menu_aux,
                '###id_button###'     => 'update'

              ];
      $code .= $this::despliegueTemplate( $data, 'formmulario-sub-cliente.html' );
  }
  return $code;
}


private function eliminaSubCliente()
{
  //Array ( [id] => eliminaSubCliente [id_subcliente] => 6 )
  if( $this->consultas->eliminaSubCliente( $_POST['id_subcliente'] ) )
  {
    return $this::notificaciones('danger',
                                 '<i class="far fa-thumbs-up"></i>',
                                 'Registro Eliminado!').$this::verSubClientes();
  }else{
    return "Error al Eliminar";
  }

}



private function ingresaSubCliente()
{

if( $this->consultas->procesaSubCliente( htmlentities( $_POST['nombre_cliente']) ,
																	       htmlentities( $_POST['apaterno'] ),
																	       htmlentities( $_POST['amaterno'] ),
																	       htmlentities( $_POST['rut'] ),
																	       htmlentities( $_POST['email']) ,
																	       htmlentities( $_POST['fono'] ),
																	       htmlentities( $_POST['id_cliente'])
																	      )  )
{

  return $this::notificaciones('success',
                               '<i class="far fa-thumbs-up"></i>',
                               'Registro Ingresado!').$this::verSubClientes();

 }else{ return 'Error al ingresar!!';}

}



private function crearSubClientes()
{
    $arr  = $this->consultas->listaClientes();

    $select =  new select($arr['process'], 'id','descripcion','id_cliente','Empresa',null,'z' );

  $data = ['###menu_aux###'       => $this->menu_aux,
           '###title###'          => 'Ingreso',
           '###hidden###'         => null,
           '###nombre_cliente###' => null  ,
           '###apaterno###'       =>null,
           '###amaterno###'       =>null,
           '###rut###'            =>null,
           '###email###'          => null,
           '###fono###'           => null,
           '###select###'         => $select->getCode(),
           '###id_button###'      => 'send'

        ];

  return $this::despliegueTemplate( $data, 'formmulario-sub-cliente.html' );

 }


  private function verSubClientes()
  {
    $arr = $this::trSubClientes();

    $data = ['###menu_aux###'   => $this->menu_aux,
             '###total-recs###' => $arr['total-recs'],
             '###tr###'         => $arr['code']
   ];

    return $this::despliegueTemplate( $data, 'tabla-subclientes.html' );

  }

  private function trSubClientes()
  {
    $arr = $this->consultas->listaSubClientes();
    $code = "";

    $i = 0;
    foreach ($arr['process'] as $key => $value) {

        $cliente = "{$value['nombres']} {$value['apaterno']} {$value['amaterno']}";

        $rut  = $this::separa( $value['rut'],'-' );

        $data = [ '###num###'     => $i+1,
                  '###cliente###' => $cliente,
                  '###empresa###' => $value['descripcion'],
                  '###rut###'     => $this::separa_miles( $rut[0] ).' - '.$rut[1],
                  '###email###'   => $value['email'],
                  '###fono###'    => $value['fono'],
                  '###id###'      => $value['id']
        ];
        $code .= $this::despliegueTemplate($data, 'tr-sub-clientes.html');

        $i++;
    }

    $out['total-recs'] = $arr ['total-recs'];
    $out['code'] = $code;

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
