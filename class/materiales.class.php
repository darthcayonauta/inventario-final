<?php 

class Materiales 
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
      $this->consultas 		  = new querys( $db );
      $this->template  		  = new template();
      $this->ruta      		  = $cfg['base']['template'];
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
  

    /**
     * control(): procesamiento de funciones principales
     * @return string
     */
    private function control()
    {
        switch ($this->id) {
            case 'generar-materiales':
                # code...
                return $this::generarMateriales();
                break;
            
            case 'listar-materiales':
                return $this::listarMateriales();
            break;    

            case 'ingresaCabeceraMateriales':
              # code...
              return $this::ingresaCabeceraMateriales();
              break;  

            case 'sacaStock':
              return $this::sacaStock();
              break;  

            case 'ingresaMaterialData':
              return $this::ingresaMaterialData();
              break;  

            case 'eliminaRegistroCuerpoMaterial':
              return $this::eliminaRegistroCuerpoMaterial();
              break;  

            case 'editaMaterialData':
              return $this::editaMaterialData();
              break;  

            case 'eliminaListaMaterial':
              return $this::eliminaListaMaterial();
              break;  

            case 'editaListaMaterial':
              # code...
              return $this::editaListaMaterial();
              break;  

            case 'detalleListaMaterial':
              # code...
              return $this::detalleListaMaterial();
              break;  

            case 'editaCabeceraMateriales':
              # code...
              return $this::editaCabeceraMateriales();
              break;  

            default:
                return "{$this->error} :: {$this->id}";
                break;
        }
    }

    private function detalleListaMaterial()
    {
     // print_r( $_POST );
      $code = "";
      $arr = $this->consultas->listaEncabezadoMaterial( $_POST['id_material'] );

      foreach ($arr['process'] as $key => $value) 
      {
        
        $data = ['###menu_aux###'           => $this->menu_aux,
                 '###centro###'             => $value['nombreCentro'],
                 '###num_modulos###'        => $value['modulos'],
                 '###num_jaulas###'         => $value['jaulas'],
                 '###calidad###'            => $value['calidad'],
                 '###camaras_x_jaula###'    => $value['camaras_x_jaula'], 
                 '###codigo###'             => $_POST['codigo'], 
                 '###enc-codigo###'         => base64_encode( $_POST['codigo'] ), 
                 '###titulo###'             => strtoupper($value['titulo'] ),                  
                 '###listado###'            => $this::listaMaterialView( $_POST['codigo'] )
      ];
        $code .= $this::despliegueTemplate( $data, 'detalle-lista-material.html' );        
      }
      return $code;
    }


    private function listaMaterialView( $codigo = null )
    { 
      //return "listado en construccion para codigo :: {$codigo}";

      $code ="";
      $arr  = $this->consultas->listarItemProductosMateriales( $codigo );

      foreach ($arr['process'] as $key => $value) {
        
         $detalle = $this::trTablaMaterialesResumen( $codigo,$value['id_item']  );

         $data = ['###ITEM###'        => strtoupper(  $value['nombreItemProducto'] ), 
                  '###tr###'          => $detalle['code'] ,
                  '###suma_total###'  => $this::separa_miles(  $detalle['suma_total'] )
        ];
         $code .= $this::despliegueTemplate( $data, 'listado-detalle-material.html' ); 

      }

      return $code;

    } 

    /**
     * editaListaMaterial(): modulo que edita los materiales y toda la wea
     * 
     * @return string
     */
    private function editaListaMaterial()
    {
      //Array ( [codigo] => MU5C11-20210531111104 [id_material] => 5 [id] => editaListaMaterial ) 
      /* 
          [codigo] => MU5C11-20210531111104
          [id_material] => 5 [id] => editaListaMaterial

          print_r( $_POST );
      */

      $code = "";
      $arr = $this->consultas->listaEncabezadoMaterial( $_POST['id_material'] );

      foreach ($arr['process'] as $key => $value) {
      
        $button_add ='<div class="col-sm-6">
                         <button class="btn btn-block btn-secondary"
                                 data-toggle="modal"
                                 data-target="#add-material" >
                          <i class="far fa-bell"></i> Agregar Material
                         </button> 
                      </div>';

        $ar      = $this->consultas->listaDestino();
        $select  = new Select(  $ar['process'],
                                'id',
                                'descripcion',
                                'id_centro',
                                'Centro',$value['centro'],'z'  );
  
        $hidden         = "<input type='hidden' id='id_encabezado_material' value='{$_POST['id_material']}'>";
        $hidden_codigo  = "<input type='hidden' id='codigo' value='{$_POST['codigo']}'>";

        $data = ['###menu_aux###'           => $this->menu_aux,
                  '###buton_id###'          => 'update',
                  '###title###'             => strtoupper('Edicion de Listas de Material'),
                  '###hidden###'            => $hidden,
                  '###hidden-codigo###'     => $hidden_codigo,
                  '###titulo###'            => $value['titulo'],
                  '###cantidad-modulos###'  => $value['modulos'], 
                  '###cantidad-jaulas###'   => $value['jaulas'], 
                  '###camaras_x_jaula###'   => $value['camaras_x_jaula'], 
                  '###calidad###'           => $value['calidad'], 
                  '###select-centro####'    => $select->getCode(),
                  '###number###'            => 6,
                  '###button-add###'        => $button_add,
                  '###modal###'             => $this::modal('add-material',
                                                            '<i class="far fa-bell"></i>',
                                                            'Agregar Material',
                                                            $this::creaListaMateriales( $_POST['codigo']  ) ),
                  '###listado###'           => $this::tablaMaterialesResumen( $_POST['codigo'] )           ];
        $code .= $this::despliegueTemplate( $data, "form-titulo-materiales.html" ); 

      }

      return $code;
    }

    /**
     * creaListaMateriales(): formulario de creacion de listas de materiales
     * @param string codigo
     * @return string
     */
    private function creaListaMateriales( $codigo = null )
    {

     // $arr_destino = $this->consultas->listaDestino(  $_POST['id_centro'] );
//
     // $destino = "";
     // foreach ($arr_destino['process'] as $key => $value) {
     //   # code...
     //   $destino .= $value['descripcion'];
     // }

      $arr_unidades = $this->consultas->unidades();
      $sel_unidades = new select( $arr_unidades['process'],'id','descripcion','id_unidad','Unidades',null,
                                 'z' );

      $arr_items    = $this->consultas->listarItemProductos();
      $sel_items    = new select( $arr_items['process'],'id','descripcion','id_item','Items',null,
                                 'z' );

      $arr_insumos  = $this->consultas->listaElementos();
      $sel_insumos  = new select( $arr_insumos['process'],'codigo','nombre','id_insumo','Insumo',null,
                                 'z' );

      $arr_proveedor  = $this->consultas->listaProveedores();
      $sel_proveedor  = new select( $arr_proveedor['process'],'id','descripcion','id_proveedor','Proveedor',null,
                                                            'z' );

      $data = ['###codigo###'            => $codigo,
               '###select-unidad###'     => $sel_unidades->getCode(), 
               '###select-insumo###'     => $sel_insumos->getCode(),
               '###select-proveedor###'  => $sel_proveedor->getCode(),
               '###select-item###'       => $sel_items->getCode(),
    ];
      return $this::despliegueTemplate( $data, 'crea-lista-materiales.html' );
    }

    /**
     * editaCabeceraMateriales(): edita encabezado 
     * 
     * @return string
     */
    private function editaCabeceraMateriales()
    {
      
      if( $this->consultas->procesaEncabezadoMaterial( htmlentities( addslashes( $_POST['titulo'] ) ),
                                                       $_POST['id_centro'],
                                                       htmlentities( addslashes( $_POST['cantidad_modulos'] ) ),
                                                       htmlentities( addslashes( $_POST['cantidad_jaulas'])),
                                                       htmlentities( addslashes( $_POST['calidad'] )),
                                                       htmlentities( addslashes( $_POST['camaras_x_jaula'] )),
                                                       $_POST['codigo'],
                                                       $_POST['id_encabezado_material']
      ) )
      { return  $this::notificaciones('success','<i class="far fa-edit"></i>','Registro Actualizado'); }
      else{
        return "Error al editar!!!";
      } 

    }

    /**
     * 
     */
    private function eliminaListaMaterial()
    {
      if( $this->consultas->eliminaListaMaterial($_POST['codigo']) )
      {
        if( $this->consultas->eliminaMaterial( null, $_POST['codigo'] ) )
        {
              $ok = true;
        }else $ok = false;

        return $this::listarMateriales().$this::notificaciones(  'danger',
                                       '<i class="fas fa-trash"></i>', 
                                       'Registro Eliminado' );
      }
      else{
        return "Error al eliminar";
      }
    }


    private function editaMaterialData()
    {
      if( $this->consultas->procesaCuerpoMaterial( $_POST['codigo'],
                                                   htmlentities( addslashes( $_POST['requerimiento']) ),
                                                   htmlentities( addslashes( $_POST['id_unidad']) ),
                                                   htmlentities( addslashes( $_POST['id_insumo']) ),
                                                   htmlentities( addslashes( $_POST['id_proveedor']) ),
                                                   htmlentities( addslashes( $_POST['stock']) ),
                                                   htmlentities( addslashes( $_POST['a_comprar']) ),
                                                   htmlentities( addslashes( $_POST['precio_unitario']) ),
                                                   htmlentities( addslashes( $_POST['valor_total']) ), 
                                                   htmlentities( addslashes( $_POST['id_item']) ),
                                                   'z',
                                                   $_POST['id_material']                ) )
      {
       // return "Registro Actualizado";
       return $this::notificaciones(  'secondary',
                                      '<i class="far fa-edit"></i>', 
                                      'Registro Actualizado' ).$this::tablaMaterialesResumen( $_POST['codigo'] );
      }
      else{
        return "Error al actualizar....";
      }
    }


    /**
     *  eliminaRegistroCuerpoMaterial(): elimina registro
     * 
     * */  
    private function eliminaRegistroCuerpoMaterial()
    {
      //print_r( $_POST );

      if( $this->consultas->eliminaMaterial( $_POST['id_cuerpo'] ) )
      {
        return $this::notificaciones( 'danger',
                                      '<i class="fas fa-trash"></i>', 
                                      'Registro Eliminado' ).$this::tablaMaterialesResumen( $_POST['codigo'] );
      }else{

        return "Error al Eliminar";

      }
    }

    /**
     * ingresaMaterialData(): ingreso de datos del form y despliegue de tabla de materiales
     * @return string
     */
    private function ingresaMaterialData()
    {
      if( $this->consultas->procesaCuerpoMaterial(   addslashes( $_POST['codigo']), 
                                                     addslashes( $_POST['requerimiento']),
                                                     addslashes( $_POST['id_unidad']),
                                                     addslashes( $_POST['id_insumo']),
                                                     addslashes( $_POST['id_proveedor']),
                                                     addslashes( $_POST['stock']),
                                                     addslashes( $_POST['a_comprar']),
                                                     addslashes( $_POST['precio_unitario'] ),
                                                     addslashes( $_POST['costo_total']),
                                                     addslashes( $_POST['id_item']),
                                                     addslashes( $_POST['comentario'])  ) )
      {
        return $this::notificaciones( 'success',
                                      '<i class="far fa-thumbs-up"></i>', 
                                      'Registro Ingresado' ).$this::tablaMaterialesResumen( $_POST['codigo'] );        
      }
      else{
        return "Error al ingresar";
      }
    }

    /**
     * tablaMaterialesResumen()
     * 
     * @param string codigo
     * @return string
     */
    private function tablaMaterialesResumen( $codigo = null )
    {
      $arr = $this::trTablaMaterialesResumen( $codigo );

      $data = ['###tr###'         => $arr['code'],
               '###total-recs###' => $arr['total-recs'],
               '###suma_total###' => $this::separa_miles(  $arr['suma_total'] ) 
    ];
      return $this::despliegueTemplate( $data, 'tabla-materiales.html' );
    }

    /**
     * trTablaMaterialesResumen()
     * 
     * @param string codigo
     * @param int id_item
     * @return string
     */
    private function trTablaMaterialesResumen( $codigo = null,$id_item = null )
    {
      $code = "";
      $i    = 0;
      
      if( !is_null( $id_item ) )
            $tpl = "tr-materiales2.html";
      else  $tpl = "tr-materiales.html";

      $arr = $this->consultas->listaMateriales( $codigo,null,$id_item );

      $suma_total =0;

      foreach ($arr['process'] as $key => $value) {
        
          $data = ['###requerimiento###'    => $value['requerimiento'] ,
                   '###u_medida###'         => $value['nombreUnidadMedida'],
                   '###insumo###'           => $value['nombreProducto'],
                   '###proveedor###'        => $value['nombreProveedor'],
                   '###stock###'            => $value['stock'],    
                   '###a_comprar###'        => $value['a_comprar'],
                   '###precio_unitario###'  => $this::separa_miles(  $value['precio_unitario'] ),
                   '###costo_total###'      => $this::separa_miles( $value['a_comprar']*$value['precio_unitario'] ),
                   '###item###'             => $value['nombreItemProducto'],
                   '###id###'               => $value['id'],
                   '###codigo###'           => $value['codigo'],
                   '###modal###'            => $this::modal("edita-cuerpo-material-{$value['id']}",
                                                            '<i class="far fa-edit"></i>',
                                                            "Edita Material",
                                                            $this::formEditaMateriales( $value['id'],
                                                                                        $value['codigo'] ) )  ];

          $total =  $value['a_comprar']*$value['precio_unitario'];

          $code .= $this::despliegueTemplate( $data, $tpl );
          $suma_total = $suma_total +$total; 

      }

      $out['code']        = $code;
      $out['suma_total']  = $suma_total; 
      $out['total-recs']  = $arr['total-recs']; 
      $out['code']        = $code; 

      return $out;
    }


    /**
     * sacaStock(): devuelve objeto text con valor del stock del producto
     * @return string
     */
    private function sacaStock()
    {
      $stock = 0;
      $arr = $this->consultas->listaElementos(null,1,null,null,null,null,$_POST['id_insumo']);

      foreach ($arr['process'] as $key => $value) {        
          $stock = $value['stock']; 
      }

      return '<input type="text" class="form-control" name="stock" id="stock" value="'.$stock.'">';
    }

    /**
     * ingresaCabeceraMateriales():
     */
    private function ingresaCabeceraMateriales()
    {
      $codigo ="MU{$this->yo}C{$_POST['id_centro']}-{$this->token}";

      if( $this->consultas->procesaEncabezadoMaterial( htmlentities(addslashes( $_POST['titulo'] ) ) ,
                                                       htmlentities(addslashes( $_POST['id_centro'] )),
                                                       htmlentities(addslashes( $_POST['cantidad_modulos'] )),
                                                       htmlentities(addslashes( $_POST['cantidad_jaulas'] )),
                                                       htmlentities(addslashes( $_POST['calidad'] )),
                                                       htmlentities(addslashes( $_POST['camaras_x_jaula'] )),
                                                       htmlentities(addslashes( $codigo ))
      ) )
      { 
        $arr_destino = $this->consultas->listaDestino(  $_POST['id_centro'] );

        $destino = "";
        foreach ($arr_destino['process'] as $key => $value) {
          # code...
          $destino .= $value['descripcion'];
        }

        $arr_unidades = $this->consultas->unidades();
        $sel_unidades = new select( $arr_unidades['process'],'id','descripcion','id_unidad','Unidades',null,
                                   'z' );

        $arr_items    = $this->consultas->listarItemProductos();
        $sel_items    = new select( $arr_items['process'],'id','descripcion','id_item','Items',null,
                                   'z' );

        $arr_insumos  = $this->consultas->listaElementos();
        $sel_insumos  = new select( $arr_insumos['process'],'codigo','nombre','id_insumo','Insumo',null,
                                   'z' );

        
        $arr_proveedor  = $this->consultas->listaProveedores();
        $sel_proveedor  = new select( $arr_proveedor['process'],'id','descripcion','id_proveedor','Proveedor',null,
                                                              'z' );


        $data = ['###titulo###'           => strtoupper( $_POST['titulo'] ) ,
                 '###codigo###'           => strtoupper( $codigo ) ,
                 '###centro###'           => strtoupper( $destino ) ,
                 '###cantidad-modulos###' => strtoupper( $_POST['cantidad_modulos'] ) ,
                 '###numero-jaulas###'    => strtoupper( $_POST['cantidad_jaulas'] ) ,
                 '###calidad###'          => strtoupper( $_POST['calidad'] ) ,
                 '###camaras_x_jaula###'  => strtoupper( $_POST['camaras_x_jaula'] ),
                 '###sel-medida###'       => $sel_unidades->getCode()  ,
                 '###sel-item###'         => $sel_items->getCode()  ,
                 '###sel-insumo###'       => $sel_insumos->getCode() ,
                 '###sel-prov###'         => $sel_proveedor->getCode() 

      
      ];
        return $this::despliegueTemplate( $data, 'resumen-titulo-materiales.html' );
      }
      else{

        return "Error al ingresar";

      }
    }

    /**
     * formEditaMateriales(): formulario de edicion de lista de materiales
     * @param int id
     * @param string codigo
     * @param string target
     */
    private function formEditaMateriales( $id = null, $codigo =null, $target= null )
    {
      $arr = $this->consultas->listaMateriales( $codigo,$id );
      $code = "";

      foreach ($arr['process'] as $key => $value) {
          
        $arr_unidades = $this->consultas->unidades();
        $sel_unidades = new select( $arr_unidades['process'],'id','descripcion','id_unidad-'.$value['id'],
                                    'Unidades',
                                     $value['u_medida'],
                                     'z' );

        $arr_items    = $this->consultas->listarItemProductos();
        $sel_items    = new select( $arr_items['process'],'id','descripcion','id_item-'.$value['id'],
                                    'Items',
                                    $value['id_item'],
                                   'z' );

        $arr_insumos  = $this->consultas->listaElementos();
        $sel_insumos  = new select( $arr_insumos['process'],'codigo','nombre','id_insumo-'.$value['id'],
                                  'Insumo',
                                   $value['id_insumo'],
                                   'z' );

        $arr_proveedor  = $this->consultas->listaProveedores();
        $sel_proveedor  = new select( $arr_proveedor['process'],'id','descripcion',
                                      'id_proveedor-'.$value['id'],
                                      'Proveedor',
                                      $value['id_proveedor'],
                                      'z' );

          $data = [ '###id###'              => $id,
                    '###codigo###'          => $codigo, 
                    '###target###'          => $target,
                    '###requerimiento###'   => $value['requerimiento'],
                    '###a_comprar###'       => $value['a_comprar'],
                    '###precio_unitario###' => $value['precio_unitario'],
                    '###valor_total###'     => $value['total'],
                    '###stock###'           => $value['stock'],
                    '###select-unidad###'   => $sel_unidades->getCode(),
                    '###select-insumo###'   => $sel_insumos->getCode(),  
                    '###select-proveedor###'=> $sel_proveedor->getCode(),
                    '###select-item###'     => $sel_items->getCode(),
                  
                  ];

          $code .= $this::despliegueTemplate( $data,'form-edita-lista-material.html' );
      }

      return $code;
    }

    /**
     * generarMateriales(): generacion del listado de materiales
     * 
     * @return string 
     */
    private function generarMateriales()
    { 
      $arr      = $this->consultas->listaDestino();
      $select   = new Select(  $arr['process'],'id','descripcion','id_centro','Centro',null,'z'  );

      $data = ['###menu_aux###'           => $this->menu_aux,
                '###buton_id###'          => 'send',
                '###title###'             => strtoupper('Generación de Listas de Material'),
                '###hidden###'            => null,
                '###hidden-codigo###'     => null,
                '###titulo###'            => null,
                '###cantidad-modulos###'  => null, 
                '###cantidad-jaulas###'   => null, 
                '###camaras_x_jaula###'   => null, 
                '###calidad###'           => null, 
                '###select-centro####'    => $select->getCode(),
                '###listado###'           => null,
                '###number###'            => 12,
                '###button-add###'        => null,
                '###modal###'             => null   ];
      return $this::despliegueTemplate( $data, "form-titulo-materiales.html" );  
    
    }

    private function listarMateriales()
    { 
      $arr = $this::trListarMateriales();

      $data=['###tr###'         => $arr['code'],
             '###total-recs###' => $arr['total-recs'],
             '###menu_aux###'   => $this->menu_aux ];


      return $this::despliegueTemplate($data, 'tabla-lista-material.html');
      //return "Modulo en construccion {$this->id}"; 

    }
    
    private function trListarMateriales()
    {
      //listaEncabezadoMaterial
      $code = "";
      $i    =  0;
      $arr  = $this->consultas->listaEncabezadoMaterial();

      foreach ($arr['process'] as $key => $value) 
      {
        $data = ['###id###'               => $value['id'],
                 '###codigo###'           => $value['codigo'] ,
                 '###enc-codigo###'       => base64_encode($value['codigo']),
                 '###titulo###'           => strtoupper(  $value['titulo'] ),
                 '###modulos###'          => $value['modulos'],
                 '###jaulas###'           => $value['jaulas'],
                 '###camaras_x_jaula###'  => $value['camaras_x_jaula'],
                 '###centro###'           => $value['nombreCentro'],
                 '###fecha###'            => $this::arreglaFechas(  $value['fecha_ingreso'] ),
                 '###num###'              => $i+1    
                ];
        $code .= $this::despliegueTemplate( $data, 'tr-lista-material.html' );

        $i++;
      }  

      $out['code']        = $code;
      $out['total-recs']  = $arr['total-recs'];
      $out['sql']         = $arr['sql'];

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
   * modal(): extrae un modal desde una Clase
   *
   *@param string target
   *@param string img
   *@param string title
   *@param string content
   */
  private function modal( $target = null,
                          $img    = null, 
                          $title  = null, 
                          $content = null )
  {
      require_once("modal.class.php");

      try {

        $ob_modal = new Modal($target ,$img , $title , $content );
        return $ob_modal->salida();

      }catch (\Throwable $th)
      {
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