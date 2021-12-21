<?php
/**
* @author Claudio Guzman Herrera
* @version 1.0
*/
class Tipo
{
  private $id;
  private $yo;
  private $consultas;
  private $template;
  private $error;
  private $token;
  private $msg;
  private $btn;
  private $menu_admin;

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

    $this->menu_admin    = $this::menu_admin();


    $this->btn            = '<a href="content-page.php?id=bGlzdGFyLXRpcG9z" class="btn btn-sm btn-success" >
                                  Listar Tipos
                             </a>
    ';
  }

  private function control()
  {
    switch ($this->id)
    {
      case 'elimina-tipo':
        return $this::eliminaTipo();
        break;

      case 'editaTipoData':
        return $this::editaTipoData();
        break;

      case 'edita-tipo':
        return $this::editaTipo();
        break;

      case 'listar-tipos':
        return $this::listaTipo();
        break;

      case 'ingresaTipo':
        return $this::ingresaTipo();
        break;

      case 'crear-tipos':
        return $this::crearTipos();
        break;

      default:
        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }

  /**
   * eliminaTipo(): elimina Tipo
   *
   * @return string
   */
  private function  eliminaTipo()
  {

     if( $this->consultas->cambiaEstado( 'tipo','id_estado',2,$_POST['id_tipo'] ) )
     {  $this->msg ="Registro Eliminado ";$this->color="success";$this->icon=null; }
      else{

        $this->msg ="Error al Eliminar!" ;$this->color="danger";$this->icon=null;
      }

      return $this::notificaciones( $this->color,
                                    $this->icon ,
                                    $this->msg ).$this::listaTipo();
  }

  /**
   * editaTipoData(): procesamiento de fecha_modificacion
   *
   * @return string
   */
  private function editaTipoData()
  {
    if( $this->consultas->procesaTipo( $_POST['descripcion'], $_POST['id_tipo'] ) )
      { $this->msg ="Registro actualizado {$this->btn}";$this->color="success";$this->icon=null; }
    else{
        $this->msg ="Error al actualizar! {$this->btn}";$this->color="danger";$this->icon=null;
    }

    return $this::notificaciones( $this->color,
                                  $this->icon ,
                                  $this->msg );
  }

  private function editaTipo()
  {
    $code = "";
    $arr  = $this->consultas->listaTipo( $_POST['id_tipo'] );

    foreach ($arr['process'] as $key => $value) {

      $hidden = "<input type='hidden' name='id_tipo' id='id_tipo' value='{$_POST['id_tipo']}'>";

      $code  .= $this::despliegueTemplate( ['###hidden###'       => $hidden,
                                            '###menu-aux###'     => $this->menu_admin,
                                            '###descripcion###'  => $value['descripcion'],
                                            '###title###'        => 'Edición',
                                            '###id_button###'    => 'edit' ],'form-tipo.html'  );
    }
    return $code;
  }

  /**
   * listaTipo()
   *
   * @return string
   */
  private function listaTipo()
  {
      $arr = $this::trlistaTipo();

      if( $arr['total-recs'] > 0 )
      {
        $data = ['###total-recs###' =>  $arr['total-recs'],
                 '###menu-aux###'     => $this->menu_admin,
                 '###tr###'         =>  $arr['code']      ];
        return $this::despliegueTemplate( $data, "tabla-tipo.html" );

      }else{

        return $this::notificaciones( "danger", null,"No hay Registros"  );
      }
  }

  /**
   * trTistaTipo()
   *
   * @return string
   */
  private function trlistaTipo()
  {
    $code = "";
    $arr  = $this->consultas->listaTipo();
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

        $data = ['###num###'          => $i+1,
                 '###descripcion###'  => $value['descripcion'],
                 '###id###'           => $value['id']  ];

        $code .= $this::despliegueTemplate( $data , "tr-tipo.html" );

      $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];

    return $out;;
  }

  /**
   * ingresaTipo()
   *
   * @return string
   */
  private function ingresaTipo()
  {
      if( $this->consultas->procesaTipo( $_POST['descripcion'] ) )
        {  $color = "success"; $icon = null; $this->msg = "Registro Ingresado, {$this->btn}";  }
      else{
        {  $color = "danger"; $icon = null; $this->msg = "Error al Ingresar,  {$this->btn}";  }
      }

      return $this::notificaciones( $color, $icon, $this->msg );
  }

  private function crearTipos()
  {
      return $this::despliegueTemplate( ['###hidden###'       => null,
                                         '###descripcion###'  => null,
                                         '###menu-aux###'     => $this->menu_admin,
                                         '###title###'        => 'Ingreso',
                                         '###id_button###'    => 'send' ],'form-tipo.html'  );
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
      * menu_admin(), menu admin
      * @return string
      */
     private function menu_admin()
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
	  * despliegueTemplate(), metodo que sirve para procesar los templates
	  *
	  * @param  array   arrayData (array de datos)
	  * @param  array   tpl ( template )
	  * @return String
	  */
    private function despliegueTemplate($arrayData,$tpl){

     	  $tpl = $this->ruta.$tpl;

	      $this->template->setTemplate($tpl);
	      $this->template->llena($arrayData);

	      return $this->template->getCode();
	  }

  /**
  *
  */
  public function getCode()
  {
    return $this::control();
  }
}
