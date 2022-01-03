<?php
/**
 * @author Ing. Claudio Guzman Herrera
 * @package None
 * @version 1.0
 * @copyright DYT SOCMA LIMITADA
 * 
 */

class Centros 
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
      $this->error          = "Esto es un Error:::{$this->id}";
      $this->consultas 			= new querys( $db );
      $this->template  			= new template();
      $this->ruta      			= $cfg['base']['template'];
      $this->token 		      =  date("YmdHis");
      $this->fecha_hoy 		  =  date("Y-m-d");
  
      $this->btn_listar     = '<a href="content-page.php?id=bGlzdGFyLWNlbnRyb3M="
                                    class="btn btn-sm btn-success" >
                                    Listar Centros
                               </a>';
  
      $this->btn_crear      = '<a href="content-page.php?id=Y3JlYXItY2VudHJv"
                                  class="btn btn-sm btn-secondary" >
                                    [ Crear Centro ]
                               </a>';
  
    $this->email = "claudio.guzman@socma.cl";
    $this->menu_aux = $this::menu_aux();
    }

    private function control()
    {

        switch ($this->id) {
            case 'crear-centro':
                # code...
                return $this::crearCentro();
                break;
            
            case 'listar-centros':
                # code...
                return $this::listarCentros();
                break;    

            case 'ingresa-centro':
                # code...
                return $this::ingresaCentro();
                break;    

            case 'cambia-estado-centro':    
                return $this::cambiaEstadoCentro();
                break;    

            case 'edita-estado-centro':
                return $this::editaEstadoCentro()    ;                
                break;

           case 'actualiza-centro':
               # code...
               return $this::actualizaCentro();
               break;     

            default:
                # code...
                return $this->error;
                break;
        }
    }    

    private function actualizaCentro()
    {
        //print_r( $_POST );

        if( $this->consultas->procesaCentro( htmlentities(  addslashes( $_POST['nombre_centro']  )),
        $_POST['id_cliente'],null,$_POST['id_centro']  ) )
        {
        return  $this::notificaciones('success',
            '<i class="far fa-thumbs-up"></i>',
            "DATA ACTUALIZADA {$this->btn_listar} {$this->btn_crear}" )  ;

        }else{

            return "ERROR AL INGRESAR";
            }
    }

    private function cambiaEstadoCentro()
    {
        //print_r( $_POST );
        if( $this->consultas->cambiaEstadoCentro( $_POST['id_centro'],$_POST['id_estado']  )   )
        {
            return "<strong>ESTADO CAMBIADO</strong>".$this::tablaCentros();
        }
        else{

            return $this::tablaCentros();
        }
    }


    /**
     * ingresaCentro()
     * 
     * @return string
     */
    private function ingresaCentro() 
    {
    
        if( $this->consultas->procesaCentro( htmlentities(  addslashes( $_POST['nombre_centro']  )),
                                              $_POST['id_cliente']  ) )
        {
            return  $this::notificaciones('success',
                                          '<i class="far fa-thumbs-up"></i>',
                                          "DATA INGRESADA {$this->btn_listar} {$this->btn_crear}" )  ;

        }else{

            return "ERROR AL INGRESAR";
        }
    }

    /**
     * 
     * crearCentro(): formulario de creacion de centros
     * 
     * @return string
     */
    private function crearCentro()
    {
        $arr = $this->consultas->listaClientes();    
        $sel = new Select( $arr['process'],'id','descripcion','id_cliente','Cliente',null,1 );    
     
        $data = [ '###menu_aux###'      => $this->menu_aux , 
                  '###title###'         => 'Ingreso'  ,
                  '###nombre-centro###' => null,
                  '###select###'        => $sel->getCode(),
                  '###button-id###'     => 'send'    ,
                  '###hidden###'        => null ];

        return $this::despliegueTemplate( $data, 'form-centro.html' );

    }


    private function  editaEstadoCentro()
    {
        //print_r( $_POST );
        $arr = $this->consultas->listaCentros( $_POST['id_centro'] );
        $code = "";

        foreach ($arr['process'] as $key => $value) {
            $arr = $this->consultas->listaClientes();    
            $sel = new Select( $arr['process'],'id','descripcion','id_cliente','Cliente',$value['id_cliente'],1 ); 

            $hidden = "<input type='hidden' id='id_centro' value='{$value['id']}'>";


            # code...
            $data = ['###menu_aux###'       => $this->menu_aux,
                     '###title###'          => 'Edición' ,
                     '###nombre-centro###'  => $value['descripcion'],
                     '###select###'         => $sel->getCode() ,
                     '###hidden###'         => $hidden,
                     '###button-id###'      => 'update'];
            $code .= $this::despliegueTemplate( $data, 'form-centro.html' );

        }


        return $code;
    }

    /**
     * 
     * listarCentros(): listar centros
     * @return string
     * 
     */
    private function listarCentros()
    {
        return $this::despliegueTemplate( ['###menu_aux###' => $this->menu_aux , 
                                           '###listado###'  => $this::tablaCentros() 
                                        ], 'lista-centros.html' );     

    }

    private function tablaCentros()
    {
        $arr = $this::trCentros();


        $data = [ '###tr###' => $arr['code'] , '###total-recs###' => $arr['total-recs']  ];
        return $this::despliegueTemplate( $data, 'tabla-centros.html' );
    }


    private function trCentros()
    {
        $code = "";
        $arr = $this->consultas->listaCentros();

        $i =0;
        foreach ($arr['process'] as $key => $value) {
            # code...

            if( $value['id_estado'] == 1  )
                 $estado = "VIGENTE";
            else $estado = "<strong>NO - VIGENTE</strong>";   

           
            if( $value['id_estado'] == 1 )
                $btn_actualiza = "<button class='btn btn-secondary btn-sm outline-line-gris'
                                        id='edita-centro-###id###'>
                                    <i class='far fa-edit'></i>
                                </button>"  ;
            else $btn_actualiza = null;

            $data = ['###num###'            => $i+1,
                     '###nombre-centro###'  => strtoupper( $value['descripcion'] )  , 
                     '###id###'             => $value['id'],
                     '###cliente###'        => $value['nombreCliente'],
                     '###estado###'         => $estado,
                     '###btn###'            => $btn_actualiza,    
                     '###id_estado###'      => $value['id_estado'] ];

            $code .= $this::despliegueTemplate( $data, 'tr-centros.html' );

            $i++;
        }

        $out['total-recs'] =$arr['total-recs'];
        $out['code'] =$code;

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