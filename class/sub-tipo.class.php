<?php
/**
* @author Claudio Guzman Herrera
* @version 1.0
*/
class SubTipo
{
  private $id;
  private $yo;
  private $consultas;
  private $template;
  private $error;
  private $token;
  private $msg;
  private $btn;
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
    $this->menu_aux       = $this::menu_admin();


    $this->btn            = '<a href="content-page.php?id=bGlzdGFyLXN1YnRpcG9z" class="btn btn-sm btn-success" >
                                  Listar SubTipo
                             </a>
    ';
  }


  private function control()
  {
      switch ($this->id) {

        case 'elimina-subtipo':
          return $this::eliminaSubtipo();
          break;

        case 'editaSubTipoData':
          return $this::editaSubTipoData();
          break;

        case 'edita-subtipo':
          return $this::editaSubtipo();
          break;

        case 'ingresaSubTipo':
          return $this::ingresaSubTipo();
          break;

        case 'crear-subtipos':
          return $this::crearSubtipos();
          break;

        case 'listar-subtipos':
          return $this::listarSubtipos();
          break;

        case 'comboSubTipo':
          return $this::comboSubTipo();
          break;

        case 'buscaFam':
          # code...
          return $this::buscaFam();
          break;  


        default:

          return " {$this->error} para id : { $this->id } ";
          break;
      }
  }

 private function buscaFam(){
  //print_r( $_POST );
//
  //$data=[];
  //return $this::despliegueTemplate( $data,'txt-form.html' );

  return 654;

 }



 private function comboSubTipo()
 {
   //print_r( $_POST );
   $arr = $this->consultas->listaSubTipo( null, $_POST['id_tipo'] );

   $field[0]="descripcion";
   #$field[1]="codigo"; 

   $sel = new Select( $arr['process'], 'id', $field,'sub_tipo', 'Sub Categoría' );

   return $sel->getCode();
 }

  private function eliminaSubtipo()
  {
    //print_r( $_POST );

    if( $this->consultas->cambiaEstado( 'sub_tipo','id_estado',2,$_POST['id_subtipo'] ) )
    {  $this->msg ="Registro Eliminado ";$this->color="success";$this->icon=null; }
     else{

       $this->msg ="Error al Eliminar!" ;$this->color="danger";$this->icon=null;
     }

     return $this::notificaciones( $this->color,
                                   $this->icon ,
                                   $this->msg ).$this::listarSubtipos();
  }

  private function editaSubTipoData()
  {
    if( $this->consultas->procesaSubTipo( $_POST['descripcion'], $_POST['id_tipo'], $_POST['codigo'],$_POST['id_subtipo'] ) )
      { $this->msg ="Registro actualizado {$this->btn}";$this->color="success";$this->icon=null; }
    else{
        $this->msg ="Error al actualizar! {$this->btn}";$this->color="danger";$this->icon=null;
    }

    return $this::notificaciones( $this->color,
                                  $this->icon ,
                                  $this->msg );
  }

  private function editaSubtipo()
  {
    $arr = $this->consultas->listaSubTipo( $_POST['id_subtipo'] );
    $code = "";

    foreach ($arr['process'] as $key => $value) {

      $hidden = "<input type='hidden'
                        name='id_subtipo'
                        id='id_subtipo'
                        value='{$_POST['id_subtipo']}'>";

      $arr = $this->consultas->listaTipo();
      $select = new Select( $arr['process'], 'id','descripcion', 'id_tipo', 'Categoria', $value['id_tipo'] );

      $DATA = [ '###select###'      => $select->getCode(),
                '###hidden###'      => $hidden,
                '###menu_aux###'   => $this->menu_aux,  
                '###descripcion###' => $value['descripcion'],
                '###codigo###'      => $value['codigo'],
                '###id_button###'   => 'edit',                
                '###title###'       => 'Edición',
               ];
      $code .= $this::despliegueTemplate( $DATA, 'form-sub-tipo.html' );
    }

    return $code;
  }

  private function ingresaSubTipo()
  {
      if( $this->consultas->procesaSubTipo( $_POST['descripcion'], $_POST['id_tipo'], $_POST['codigo'] ) )
        {  $color = "success"; $icon = null; $this->msg = "Registro Ingresado, {$this->btn}";  }
      else{
        {  $color = "danger"; $icon = null; $this->msg = "Error al Ingresar,  {$this->btn}";  }
      }
      return $this::notificaciones( $color, $icon, $this->msg );
  }

  private function crearSubtipos()
  {
    //return "{$this->id} en construccion";
    $arr = $this->consultas->listaTipo();

    $select = new Select( $arr['process'], 'id','descripcion', 'id_tipo', 'Categoría' );

    $DATA = [ '###select###'      => $select->getCode(),
              '###hidden###'      => null,
              '###descripcion###' => null,
               '###menu_aux###'   => $this->menu_aux,
               '###codigo###'     => null,
              '###id_button###'   => 'send',
              '###title###'       => 'Ingreso',
             ];
    return $this::despliegueTemplate( $DATA, 'form-sub-tipo.html' );
  }

  /**
   * listaTipo()
   *
   * @return string
   */
  private function listarSubtipos()
  {
      $arr = $this::trListarSubtipos();

      if( $arr['total-recs'] > 0 )
      {
        $data = ['###total-recs###' =>  $arr['total-recs'],
                 '###menu-aux###'   => $this->menu_aux,
                 '###tr###'         =>  $arr['code']      ];
        return $this::despliegueTemplate( $data, "tabla-sub-tipo.html" );

      }else{

        return $this::notificaciones( "danger", null,"No hay Registros"  );
      }
  }

  /**
   * trTistaTipo()
   *
   * @return string
   */
  private function trListarSubtipos()
  {
    $code = "";
    $arr  = $this->consultas->listaSubTipo();
    $i    = 0;

    foreach ($arr['process'] as $key => $value) {

        if( $value['codigo'] =='' || $value['codigo'] == null )
             $codigo = "NO ASIGNADO";
        else $codigo = $value['codigo'];



        $data = ['###num###'          => $i+1,
                 '###descripcion###'  => $value['descripcion'],
                 '###tipo###'         => $value['nameTipo'],
                 '###codigo###'       => $codigo,
                 '###id###'           => $value['id']  ];

        $code .= $this::despliegueTemplate( $data , "tr-sub-tipo.html" );

      $i++;
    }

    $out['code']        = $code;
    $out['total-recs']  = $arr['total-recs'];

    return $out;;
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
?>
