<?php

class ContentPage
{
	private $consultas;
	private $template;
	private $ruta;
	private $id;
	private $menu;
	private $fecha_hoy;
	private $yo;
	private $id_tipo_user;

	function __construct( $id = null,$yo = null )
	{
		if( !$yo )
			$this->yo = $_SESSION['yo'];
		else {
			$this->yo = $yo;
		}

		$oConf    					= new config();
		$cfg      					= $oConf->getConfig();
		$db       					= new mysqldb(  $cfg['base']['dbhost'],
														$cfg['base']['dbuser'],
														$cfg['base']['dbpass'],
														$cfg['base']['dbdata'] );

		$this->consultas 				= new querys( $db );
		$this->template  				= new template();
		$this->ruta      				= $cfg['base']['template'];
		$this->id 						= $id;
		$this->error 					= $cfg['base']['error'];
		$this->fecha_hoy 				= date("Y-m-d");
		$this->fecha_hora_hoy 			= date("Y-m-d H:i:s");
		$this->nombres          		= $_SESSION['nombres'];
		$this->apaterno         		= $_SESSION['apaterno'];
		$this->amaterno         		= $_SESSION['amaterno'];
		$this->id_tipo_user 			= $_SESSION['tipo_usuario'];
		$this->menu 					= new Menu( $this->yo, $this->id_tipo_user );

	}

	private function control()
	{
		switch ($this->id)
		{
			case 'colaboracion-jefe-produccion':
			case 'listado-colaboracion':
			case 'colaboracion-operario':
			case 'listar-centros':
			case 'crear-centro':
			case 'listar-guia-despacho-egreso':
			case 'crear-guia-despacho-egreso':
			case 'listar-guia-despacho-ingreso':
			case 'crear-guia-despacho-ingreso':
			case 'guia-despacho-egresos':								
			case  'guia-despacho':
			case 'listar-equipos':
			case 'crear-equipos':
			case 'accesos':
			case 'listar-servicio':
			case 'crear-servicio':
			case 'listar-materiales':
			case 'generar-materiales':
			case 'listarColaboraciones':
			case 'generarColaboraciones':
			case 'relDestinoCliente':
			case 'listarItemProductos':
			case 'crearItemProductos':
			case 'ver-subclientes':
			case 'crear-subclientes':
			case 'registro-acciones':
			case 'crear-usuarios':
			case 'lista-destino':
			case 'crear-destino':
			case 'lista-clientes':
			case 'crear-clientes':
			case 'lista-trabajos':
			case 'generar-trabajos':
			case 'crear-proveedores':
			case 'listar-proveedores':
			case 'listar-usuarios':
			case 'inicio':
			case 'crear-sub-ubicaciones':
			case 'listar-sub-ubicaciones':
			case 'crear-subtipos':
			case 'listar-subtipos':
			case 'listar-tipos':
			case 'crear-tipos':
			case 'cambio-clave':
			case 'crearInventario':
			case 'listarInventario':

				return $this::baseHtml();
			break;

			default:
				return $this::baseHtmlError();
			break;
		}
	}

	/**
	 * baseHtml(): carga de los componentes de acuerdo al id capturado y si es que es válido
	 * @return string
	 */
	private function baseHtml()
	{
        if (!isset( $_GET['id']  ))
        {       $content = "CONTENIDO INICIAL DE LA PAGINA  <br>" .$this->id ;
        }else{  $content = $this::importaModulos(); }

			$data = array(	'@@@TITLE'  	=> 'SISTEMA DE INFORMACION DYT SOCMA LIMITADA',
                      		'@@@USER' 		=> utf8_encode( "{$this->nombres} {$this->apaterno} {$this->amaterno}" ),
							'@@@FECHA'  	=> $this::arreglaFechas(  $this->fecha_hoy ),
							'@@@CONTENT' 	=> $content,
							'###tags###'  	=> $this::tags(),
							'@@@MENU'		=> $this->menu->getCode() );

			return $this::despliegueTemplate($data,'inicio-principal.html');
	}

	/**
	 * baseHtmlError(): carga de la pagina de error
	 * @return string
	 */
	private function baseHtmlError()
	{
			$data = array(	'@@@TITLE'  	=> 'SISTEMA DE INFORMACION DYT SOCMA LIMITADA',
                      		'@@@USER' 		=> "{$this->nombres} {$this->apaterno} {$this->amaterno}",
							'@@@FECHA'  	=> $this::arreglaFechas( $this->fecha_hoy ),
							'@@@CONTENT' 	=> "{$this->error} ::: {$this->id}",
							'@@@MENU'		=> $this->menu->getCode() );

			return $this::despliegueTemplate($data,'inicio-principal.html');
	}

	private function tags()
	{
		try {
		require_once( 'inventario.class.php' );
		$ob = new Inventario(); return $ob->tags();
		} catch (\Throwable $th) {
			return "Error de clase {$th}";
		}
	}

    /**
     * importaModulos()
     * @param
     * @return string
     */
    private function importaModulos()
    {
        switch ($this->id) {
			
					case 'colaboracion-jefe-produccion':
						# code...
						return $this::generalCall( 'jefe-produccion.class.php', 'JefeProduccion', $this->id );							
						break;	

					case 'listado-colaboracion':
					case 'colaboracion-operario':
						return $this::generalCall( 'operario-produccion.class.php', 'OperarioProduccion', $this->id );							
						break;

					case 'listar-centros':
					case 'crear-centro':
						return $this::generalCall( 'centros.class.php', 'Centros', $this->id );	
					break;	

					case 'listar-guia-despacho-egreso':
					case 'crear-guia-despacho-egreso':										
						return $this::generalCall( 'guia-despacho-egreso.class.php', 'GuiaDespachoEgreso', $this->id );	
					break;

					case 'listar-guia-despacho-ingreso':
					case 'crear-guia-despacho-ingreso':
						# code...
						return $this::generalCall( 'guia-despacho-ingreso.class.php', 'GuiaDespachoIngreso', $this->id );	
						break;

					case 'listar-equipos':	
					case 'crear-equipos':
						# code...
						return $this::generalCall( 'equipos.class.php', 'Equipos', $this->id );	
						break;

					case 'listar-servicio':
					case 'crear-servicio':
					return $this::generalCall( 'servicios.class.php', 'Servicios', $this->id );
					break;

					case 'listar-materiales':
					case 'generar-materiales':
						return $this::generalCall( 'materiales.class.php', 'Materiales', $this->id );
						break;

					case 'listarColaboraciones':
					case 'generarColaboraciones':

					return $this::generalCall( 'colaboraciones.class.php', 'Colaboraciones', $this->id );
					break;

					case 'listarItemProductos':
					case 'crearItemProductos':
						return $this::generalCall( 'item-productos.class.php', 'ItemProductos', $this->id );
						break;

					case 'relDestinoCliente':
					case 'lista-destino':
					case 'crear-destino':
						return $this::generalCall( 'destino.class.php', 'Destino', $this->id );
						break;

					case 'lista-clientes':
					case 'crear-clientes':
						return $this::generalCall( 'clientes.class.php', 'Clientes', $this->id );
						break;

					case 'lista-trabajos':
					case 'generar-trabajos':
						return $this::generalCall( 'trabajos.class.php', 'Trabajos', $this->id );
						break;

					case 'crear-proveedores':
					case 'listar-proveedores':
						return $this::generalCall( 'proveedores.class.php', 'Proveedores', $this->id );

					case 'guia-despacho-egresos':	
					case 'guia-despacho':	
					case 'registro-acciones':
					case 'inicio':
					case 'crearInventario':
					case 'listarInventario':
						return $this::generalCall( 'inventario.class.php', 'Inventario', $this->id );
					break;

					case 'listar-sub-ubicaciones':
					case 'crear-sub-ubicaciones':
						return $this::generalCall( 'sub-ubicaciones.class.php', 'SubUbicaciones', $this->id );
						break;

					case 'crear-subtipos':
					case 'listar-subtipos':
							return $this::generalCall( 'sub-tipo.class.php', 'SubTipo', $this->id );
							break;

					case 'listar-tipos':
					case 'crear-tipos':
							return $this::generalCall( 'tipo.class.php', 'Tipo', $this->id );
							break;

					case 'accesos':
					case 'crear-usuarios':
					case 'listar-usuarios':
					case 'cambio-clave':
							return $this::generalCall( 'users.class.php', 'Users', $this->id );
							break;

				  	case 'ver-subclientes':
					case 'crear-subclientes':
							return $this::generalCall( 'sub-clientes.class.php', 'SubClientes', $this->id );

					break;
            default:
                # code...
                break;
        }
    }

    /**
     * generalCall()
     * @param string file
     * @param string className
     * @param string idItem
     * @return string
     */
    private function generalCall( $file        = null,
                                  $className   = null,
                                  $idItem      = null   )
    {
        if( require_once( $file ) ) { $ob = new $className($idItem); return $ob->getCode();   }
        else return "error al cargar clase";
    }

    /**
     * arreglaFechas()
     * @param string fecha
     * @return string
     */
    private function arreglaFechas( $fecha = null )
    {
        $div = $this::separa( $fecha , '-'  );

        if( count( $div ) > 0 )
            return "{$div[2]}-{$div[1]}-{$div[0]}";
        else return "Error de Formato";
    }

    /**
     * separa()
     * @param string cadena
     * @param string simbolo
     * @return string
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
	 * getCode(): Salida Pública del metodo control()
	 * @return string
	 *  */  
	public function getCode()
	{
		return $this::control();
	}
}
?>
