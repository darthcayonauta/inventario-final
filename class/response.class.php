<?php
include("phpmailer/PHPMailerAutoload.php");
include("class/mails.class.php");

/**
* @author Claudio, cguzmanherr@gmail.com
*/
class response
{
	private $id;
	private $id_user;

	function __construct($id=null,$id_user = null )
	{
		$this->id = $id;
		$this->id_user = $id_user;
	}

	private function cargaModulos(){

		switch ($this->id)
		{
			case 'buscaRecepcion':
			case 'validaTotal':
			case 'validaRecepcion':
			case 'tablaListadoJefeProduccion':
				# code...
				return $this::obtenerContenidoClaseOption('jefe-produccion.class.php','JefeProduccion');
				break;

			case 'tablaListarColaboracion':
			case 'buscaRsMaterials':
			case 'ingresaEncabezadoMaterialRs':
			case 'eliminaRowRecepcion':
			case 'ingresaKartMateriales':
				return $this::obtenerContenidoClaseOption('operario-produccion.class.php','OperarioProduccion');
			break;	

			case 'actualiza-centro':
			case 'edita-estado-centro':
			case 'cambia-estado-centro':
			case 'ingresa-centro':
				return $this::obtenerContenidoClaseOption('centros.class.php','Centros');
			break;	

			case 'ingresaClienteFromEgreso':
			case 'verificaNumGuiaEgreso':
			case 'buscarXfecha':
			case 'buscaLaGuiaEgreso':
			case 'finishEgresos':
			case 'generaGuiaDespachoEgreso':	
				return $this::obtenerContenidoClaseOption('guia-despacho-egreso.class.php','GuiaDespachoEgreso');
			break;

			case 'listarGuiaIngresoPagination':		
			case 'ingresaProveedorFromIngreso':
			case 'verificaNumGuia':
			case 'buscarXfechaIngreso':
			case 'buscaLaGuia':
			case 'finishIngresos':
			case 'generaGuiaDespacho':
				return $this::obtenerContenidoClaseOption('guia-despacho-ingreso.class.php','GuiaDespachoIngreso');
				break;	

			case 'ingresaEquipo1':
				# code...
				return $this::obtenerContenidoClaseOption('equipos.class.php','Equipos');
				break;	

			case 'editaServicioData':
			case 'editaServicio':
			case 'eliminaServicio':
			case 'ingresaServicio':
				return $this::obtenerContenidoClaseOption('servicios.class.php','Servicios');
				break;


			case 'editaCabeceraMateriales':
			case 'detalleListaMaterial':
			case 'editaListaMaterial':
			case 'eliminaListaMaterial':
			case 'editaMaterialData':
			case 'eliminaRegistroCuerpoMaterial':
			case 'ingresaMaterialData':
			case 'sacaStock':
			case 'ingresaCabeceraMateriales':
				# code...
				return $this::obtenerContenidoClaseOption('materiales.class.php','Materiales');
				break;


			case 'modificarInventario':
			case 'verColaboraciones':
			case 'eliminaColaboracion':
			case 'ingresaPreColaboracion':
			return $this::obtenerContenidoClaseOption('colaboraciones.class.php','Colaboraciones');
			break;


			case 'actualizaItemProducto':
			case 'editaItemProductos':
			case 'eliminaItemProductos':
			case 'ingresaItemProducto':
				// code...
				return $this::obtenerContenidoClaseOption('item-productos.class.php','ItemProductos');
				break;

			case 'comboSubClientes':
			case 'actualizaSubCliente':
			case 'editaSubCliente':
			case 'eliminaSubCliente':
			case 'ingresaSubCliente':
				// code...
				return $this::obtenerContenidoClaseOption('sub-clientes.class.php','SubClientes');
				break;

			case 'accesos':	
			case 'cambiaClave':
			case 'actualizaUserData':
			case 'editaUsuario':
			case 'cambiaEstadoUsuario':
			case 'creaUserData':
				return $this::obtenerContenidoClaseOption('users.class.php','Users');
				break;

			case 'cambiaClienteData':
			case 'cambiaAsignacion':
			case 'asignaClienteData':
			case 'asignaCliente':
			case 'quitarAsignacion':
			case 'eliminaDestino':
			case 'editaDestinoData':
			case 'editaDestino':
			case 'ingresaDestino':
				return $this::obtenerContenidoClaseOption('destino.class.php','Destino');
				break;

			case 'eliminaCliente':
			case 'editaClienteData':
			case 'editaCliente':
			case 'ingresaCliente':
				return $this::obtenerContenidoClaseOption('clientes.class.php','Clientes');
				break;

			case 'eliminaTrabajo':
			case 'editaTrabajo':
			case 'editaTrabajoData':
			case 'ingresaTrabajo':
				return $this::obtenerContenidoClaseOption('trabajos.class.php','Trabajos');
				break;


			case 'editaProveedorData':
			case 'editaProveedor':
			case 'eliminaProveedor':
			case 'ingresaProveedor':
				return $this::obtenerContenidoClaseOption('proveedores.class.php','Proveedores');
				break;


				case 'sacaAutoCodigoFinal':
				case 'buscarGuiaDespachoEgreso':
				case 'buscarGuiaDespacho':
				case 'verificaDispCodigo':
				case 'deleteInventario':
				case 'ingresaDataIngreso2':
				case 'ingresa_nombre_destino_from_egreso':
				case 'ingresa_nombre_trabajo_from_egreso':
				case 'ingresaDataEgreso':
				case 'ingresaDataIngreso':
				case 'enviaStockCriticoUnitario':
				case 'buscarInventario':
				case 'editaInventarioData':
				case 'fileForm':
				case 'editarInventario':
				case 'listarInventario':
				case 'ingresaInventarioData':
					return $this::obtenerContenidoClaseOption('inventario.class.php','Inventario');
					break;

				case 'comboSubUbicacion':
				case 'eliminaSubUbicacion':
				case 'editaSububicacionData':
				case 'editaSubUbicacion':
				case 'ingresaSubUbicacion':
					return $this->obtenerContenidoClaseOption('sub-ubicaciones.class.php','SubUbicaciones');
					break;

				case 'buscaFam':
				case 'comboSubTipo':
				case 'editaSubTipoData':
				case 'elimina-subtipo':
				case 'edita-subtipo':
				case 'ingresaSubTipo':
					return $this->obtenerContenidoClaseOption('sub-tipo.class.php','SubTipo');
					break;

			  case 'elimina-tipo':
				case 'editaTipoData':
				case 'edita-tipo':
				case 'ingresaTipo':
					return $this->obtenerContenidoClaseOption('tipo.class.php','Tipo');
					break;

				default:
				# code...
				return "<div class='principal'>MODULO NO DEFINIDO / TIMEOUT DE CARGA</div>";
				break;
		}
	}

/**
 * obtenerContenidoClaseOption(), obtiene un despliegue de resultados de una clase cualquiera para el metodo anterior, Alex aprende a programar
 *
 * @param  String file_class
 * @param  String class
 * @return String
 */
	private function obtenerContenidoClaseOption($file_class=null,$class=null){

	   include($file_class);

	   $obj_class  = new $class( $this->id, $this->id_user);
	   return $obj_class->getCode();

	}

	public function getCode(){

		return $this->cargaModulos();
	}
}
?>
