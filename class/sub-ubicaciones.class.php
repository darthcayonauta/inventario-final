<?php

/**
* @author Claudio Guzman Herrera
* @version 1.0
*/
class SubUbicaciones
{
  private $id;
  private $yo;
  private $consultas;
  private $template;
  private $error;
  private $token;
  private $msg;
  private $btn;

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

    $this->btn            = '<a href="content-page.php?id=bGlzdGFyLXN1Yi11YmljYWNpb25lcw==" class="btn btn-sm btn-success" >
                                  Listar Sub Ubicación
                             </a>
    ';

    $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItc3ViLXViaWNhY2lvbmVz" class="btn btn-sm btn-success" >
                                  Crear Sub Ubicación
                             </a>';

   $this->menu_aux       = $this::menu_admin();

  }

  private function control()
  {
    switch ($this->id) {

        case 'eliminaSubUbicacion':
          return $this::eliminaSubUbicacion();
          break;

        case 'editaSubUbicacion':
          return $this::editaSubUbicacion();
          break;

        case 'ingresaSubUbicacion':
          return $this::ingresaSubUbicacion();
          break;

        case 'crear-sub-ubicaciones':
          return $this::crearSubUbicaciones();
          break;

        case 'listar-sub-ubicaciones':
          return $this::listarSubUbicaciones();
          break;

        case 'editaSububicacionData':
          return $this::editaSububicacionData();
          break;

          case 'comboSubUbicacion':
            return $this::comboSubUbicacion();
            break;


      default:

        return " {$this->error} para id : { $this->id } ";
        break;
    }
  }

  private function comboSubUbicacion()
  {
    $arr = $this->consultas->listaSubUbicacion( null, $_POST['id_ubicacion'] );

    $sel = new Select( $arr['process'],'id','descripcion','sub-ubicacion', 'Sub Ubicacion' );
    return $sel->getCode();
   }

  private function eliminaSubUbicacion()
  {
    //Array ( [id] => eliminaSubUbicacion [id_sububicacion] => 1 )

    if( $this->consultas->cambiaEstado( 'sub_ubicacion','id_estado',2,$_POST['id_sububicacion'] ) )
    {  $this->msg ="Registro Eliminado ";$this->color="success";$this->icon=null; }
     else{

       $this->msg ="Error al Eliminar!" ;$this->color="danger";$this->icon=null;
     }

     return $this::notificaciones( $this->color,
                                   $this->icon ,
                                   $this->msg ).$this::listarSubUbicaciones();
  }

  private function editaSububicacionData()
  {
      if( $this->consultas->procesaSubUbicacion( $_POST['descripcion'],
                                                 $_POST['id_ubicacion'],
                                                 $_POST['id_sububicacion'] ) )
        { $this->msg ="Registro actualizado {$this->btn}";$this->color="success";$this->icon=null; }
      else{
          $this->msg ="Error al actualizar! {$this->btn}";$this->color="danger";$this->icon=null;
      }

      return $this::notificaciones( $this->color,
                                    $this->icon ,
                                    $this->msg );
  }

  private function editaSubUbicacion()
  {
      $arr = $this->consultas->listaSubUbicacion( $_POST['id_sububicacion'] );
      $code = "";

      foreach ($arr['process'] as $key => $value) {

        $hidden = "<input type='hidden'
                          name='id_sububicacion'
                          id='id_sububicacion'
                          value='{$_POST['id_sububicacion']}'>";

        $arr = $this->consultas->ubicacion();

        $select = new Select( $arr['process'], 'id','descripcion', 'id_ubication','Ubicacion',
                  $value['id_ubicacion']   );

        $data = [ '###descripcion###' => $value['descripcion'],
                  '###hidden###'      => $hidden,
                  '###title###'       => 'Edicion',
                  '###select###'      => $select->getCode(),
                  '###id_button###'   => 'edit','###menu_aux###'    => $this->menu_aux
                 ];

       $code .= $this::despliegueTemplate( $data, 'formulario-sub-ubicacion.html' );

      }

      return $code;
    }



  private function ingresaSubUbicacion()
  {
      if( $this->consultas->procesaSubUbicacion( $_POST['descripcion'], $_POST['id_ubicacion']) )
      {  $color = "success"; $icon = null; $this->msg = "Registro Ingresado, {$this->btn_crear} {$this->btn} ";  }
      else{
      {  $color = "danger"; $icon = null; $this->msg = "Error al Ingresar,  {$this->btn_crear} {$this->btn}";  }
      }

      return $this::notificaciones( $color, $icon, $this->msg );
  }

  private function crearSubUbicaciones()
  {
    //return "{$this->id}  en construccion";
    $arr = $this->consultas->ubicacion();

    $select = new Select( $arr['process'], 'id','descripcion', 'id_ubication','Ubicacion'   );


    $data = [ '###descripcion###' => null,
              '###hidden###'      => null,
              '###title###'       => 'Ingreso',
              '###select###'      => $select->getCode(),
              '###id_button###'   => 'send',
              '###menu_aux###'    => $this->menu_aux
             ];

    return $this::despliegueTemplate( $data, 'formulario-sub-ubicacion.html' );

  }

  private function listarSubUbicaciones()
  {
    $arr = $this::trListarSubUbicaciones();

    if( $arr['total-recs'] > 0 )
    {
      $data = ['###total-recs###' =>  $arr['total-recs'],
               '###tr###'         =>  $arr['code']  , '###menu_aux###'    => $this->menu_aux    ];
      return $this::despliegueTemplate( $data, "tabla-sub-ubicacion.html" );

    }else{

      return $this::notificaciones( "danger", null,"No hay Registros"  );
    }
  }

  private function trListarSubUbicaciones()
  {
    $code = "";
    $arr  = $this->consultas->listaSubUbicacion();

    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

        $data = ['###num###'          => $i+1,
                 '###descripcion###'  => $value['descripcion'],
                 '###ubicacion###'    => $value['nameUbicacion'],
                 '###id###'           => $value['id']  ];

        $code .= $this::despliegueTemplate( $data , "tr-sub-ubicacion.html" );

      $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];

    return $out;;
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
?>
