<?php
/**
* @author  Claudio Guzman Herrera
* @version 1.0
*/
class querys
{
	private $fecha_hoy;
	private $fecha_hora_hoy;
	private $error;

	function __construct($sql=null)
	{
		# code...
		if ( !is_null( $sql ) ){
			$this->sql   = $sql;
			$this->error = "Modulo no definido";
		}

		else{

			$oConf     = new config();
		  $cfg       = $oConf->getConfig();
		  $this->sql = new mysqldb( $cfg['base']['dbhost'],
				 												$cfg['base']['dbuser'],
																$cfg['base']['dbpass'],
																$cfg['base']['dbdata'] );

		$this->error = $cfg['base']['error'];
		}

		$this->fecha_hoy 		=  date("Y-m-d");
		$this->fecha_hora_hoy 	=  date("Y-m-d H:i:s");

	}

public function eventosAdmin()
{
		//$ssql = "SELECT * FROM eventos_admin ORDER BY fecha DESC";

		$ssql = "SELECT
						     eventos_admin.descripcion ,
						     eventos_admin.fecha       ,
						     eventos_admin.id_usuario  ,
						     usuario.nombres,
						     usuario.apaterno,
						     usuario.amaterno
						FROM
						    eventos_admin
						    INNER JOIN  usuario ON ( usuario.id = eventos_admin.id_usuario )
						ORDER BY
						eventos_admin.fecha DESC";

		$arr['sql'] 			= $ssql;
		$arr['process']		= $this->sql->select( $ssql );
		$arr['total-recs']= count( $arr['process'] );

	return $arr;

}


public function ingresaEventosAdmin( $descripcion  = null,
																		 $id_usuario   = null )
{
	$insert = "INSERT INTO eventos_admin( descripcion,fecha,id_usuario ) VALUES
						('{$descripcion}','{$this->fecha_hora_hoy}','{$id_usuario}')";

		if( $this->sql->insert( $insert ) )
					return true;
		else	return false;

//return $insert;

}

public function EliminaAlertas()
{
	$delete = "DELETE FROM alertas";

	if( $this->sql->delete( $delete ) )
				return true;
	else	return false;

}

public function ingresaAlertas()
{
	$insert = "INSERT INTO alertas(fecha) VALUES('{$this->fecha_hora_hoy}') ";

	if( $this->sql->insert( $insert ) )
				return true;
	else	return false;

}

public function alertas()
{
	$ssql = "SELECT * FROM alertas";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = 	count( $arr['process'] );

	return $arr;
}


 	public function unidades()
	{
		$ssql = "SELECT * FROM unidades";

		$arr['sql'] 				= $ssql;
		$arr['process'] 		= $this->sql->select( $ssql );
		$arr['total-recs'] 	= count( $arr['process'] );

		return $arr;
	}

	public function listaEgresos( $codigo = null )
  {
 	 $ssql = "SELECT
					      egresos.id         ,
					      egresos.codigo     ,
					      egresos.id_user    ,
					      egresos.insumo     ,
					      egresos.id_trabajo ,
					      egresos.id_cliente ,
					      egresos.id_destino ,
					      egresos.cantidad   ,
					      egresos.comentario ,
					      egresos.id_estado  ,
					      egresos.fecha		,
								usuario.apaterno			 ,
								usuario.amaterno       ,
								usuario.nombres        ,
								trabajos.descripcion AS nombreTrabajo,
								clientes.descripcion AS nombreCliente,
								destino.descripcion AS nombreDestino
					 FROM egresos
					 INNER JOIN usuario ON ( usuario.id = egresos.id_user  )
					 INNER JOIN trabajos ON ( trabajos.id = egresos.id_trabajo  )
					 INNER JOIN clientes ON ( clientes.id = egresos.id_cliente  )
					 INNER JOIN destino ON ( destino.id = egresos.id_destino  )
					 WHERE
					  egresos.codigo = '{$codigo}'
						ORDER BY egresos.fecha DESC

						";

 		$arr['sql'] 				= $ssql;
 		$arr['process'] 		= $this->sql->select( $ssql );
 		$arr['total-recs'] 	= count( $arr['process'] );

 		return $arr;
  }


 public function listaIngresos( $codigo = null )
 {
	 $ssql = "SELECT
					      ingresos.id            ,
					      ingresos.codigo        ,
					      ingresos.id_inventario ,
					      ingresos.insumo        ,
					      ingresos.id_proveedor  ,
					      ingresos.id_user       ,
					      ingresos.fecha         ,
					      ingresos.cantidad			 ,
								ingresos.archivo			 ,
								ingresos.num_documento			 ,
								usuario.apaterno			 ,
								usuario.amaterno       ,
								usuario.nombres        ,
								proveedores.descripcion AS nombreProveedor,
								tipo_documento.descripcion As nombreTipoDocumento
					  FROM ingresos
						INNER JOIN usuario ON ( usuario.id = ingresos.id_user  )
						INNER JOIN proveedores ON ( proveedores.id = ingresos.id_proveedor )
						INNER JOIN tipo_documento ON ( tipo_documento.id = ingresos.tipo_documento )
					 WHERE ingresos.codigo = '{$codigo}'
					 ORDER BY ingresos.fecha DESC";

		$arr['sql'] 				= $ssql;
		$arr['process'] 		= $this->sql->select( $ssql );
		$arr['total-recs'] 	= count( $arr['process'] );

		return $arr;
 }


 public function ingresaEgresos(  $codigo     = null ,
																  $id_user    = null ,
																  $insumo     = null ,
																  $id_trabajo = null ,
																  $id_cliente = null ,
																  $id_destino = null ,
																  $cantidad   = null ,
																  $comentario = null )
 {
	 	$insert= "INSERT INTO egresos(  codigo     ,
						                        id_user    ,
						                        insumo     ,
						                        id_trabajo ,
						                        id_cliente ,
						                        id_destino ,
						                        cantidad   ,
						                        comentario ,
						                        id_estado  ,
						                        fecha      )
						VALUES( '{$codigo}' ,
						        '{$id_user}' ,
						        '{$insumo}' ,
						        '{$id_trabajo}' ,
						        '{$id_cliente}' ,
						        '{$id_destino}' ,
						        '{$cantidad}' ,
						        '{$comentario}' ,
						         1 ,
						        '{$this->fecha_hora_hoy}' )";

		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;
 }


	public function listaDestino( $id = null )
	{
		$resto = "";

		if( $id )
			$resto = " AND id = {$id}";

		$ssql = "SELECT * FROM destino WHERE id_estado = 1 {$resto}";

		$arr['sql'] 	= $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] = count( $arr['process'] );

		return $arr;
	}

	public function procesaDestino( $descripcion = null, $id = null )
	{
		if( $id )
		{
			$update = "UPDATE destino SET descripcion='{$descripcion}'
								WHERE id={$id}";

			if( $this->sql->update( $update ) )
						return true;
			else 	return false;
		}
		else{

			$insert = "INSERT INTO destino( descripcion, id_estado )
								VALUES ( '{$descripcion}', 1 )";

			if( $this->sql->insert( $insert ) )
						return true;
			else 	return false;

		}
	}



	public function listaClientes( $id = null )
	{
		$resto = "";

		if( $id )
			$resto = " AND id = {$id}";

		$ssql = "SELECT * FROM clientes WHERE id_estado = 1 {$resto}";

		$arr['sql'] 	= $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] = count( $arr['process'] );

		return $arr;
	}

	public function procesaClientes( $descripcion = null, $id = null )
	{
		if( $id )
		{
			$update = "UPDATE clientes SET descripcion='{$descripcion}'
								WHERE id={$id}";

			if( $this->sql->update( $update ) )
						return true;
			else 	return false;
		}
		else{

			$insert = "INSERT INTO clientes( descripcion, id_estado )
								VALUES ( '{$descripcion}', 1 )";

			if( $this->sql->insert( $insert ) )
						return true;
			else 	return false;

		}
	}


	public function listaTrabajos( $id = null )
	{
		$resto = "";

		if( $id )
			$resto = " AND id = {$id}";

		$ssql = "SELECT * FROM trabajos WHERE id_estado = 1 {$resto}";

		$arr['sql'] 	= $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] = count( $arr['process'] );

		return $arr;
	}

	public function procesaTrabajos( $descripcion = null, $id = null )
	{
		if( $id )
		{
			$update = "UPDATE trabajos SET descripcion='{$descripcion}'
								WHERE id={$id}";

			if( $this->sql->update( $update ) )
						return true;
			else 	return false;
		}
		else{

			$insert = "INSERT INTO trabajos( descripcion, id_estado )
								VALUES ( '{$descripcion}', 1 )";

			if( $this->sql->insert( $insert ) )
						return true;
			else 	return false;

		}
	}

public function ingresaIngresosData( 	$codigo         = null,
 																			$id_inventario  = null,
 																			$insumo         = null,
 																			$id_proveedor   = null,
 																			$id_user 			  = null,
																			$cantidad 		  = null,
																			$tipo_documento = null,
																			$num_documento  = null,
																			$archivo 				= null )
{
	$insert=" INSERT INTO ingresos( codigo       ,
	 																id_inventario,
	 																insumo       ,
	 																id_proveedor ,
	 																id_user      ,
																	cantidad , fecha, tipo_documento, num_documento,archivo     )
						 VALUES(  '{$codigo}'  ,
						          '{$id_inventario}'  ,
						          '{$insumo}'  ,
						          '{$id_proveedor }'  ,
						          '{$id_user}',
										  '{$cantidad}' , '{$this->fecha_hora_hoy}', '{$tipo_documento}','{$num_documento}','{$archivo}')";

	if( $this->sql->insert( $insert ) )
				return true;
	else 	return false;

//return $insert;

}

public function actualizaStock( $cantidad = null, $id_inventario = null, $resta = null )
{

	$arr = $this::listaElementos( $id_inventario );

	$stock = 0;
	foreach ($arr['process'] as $key => $value) {
		$stock = $value['stock'];
	}

	if( $resta )
				$suma = $stock - $cantidad;
	else 	$suma = $stock + $cantidad;

	$update = "UPDATE elemento SET stock = {$suma},
						fecha_modificacion = '{$this->fecha_hora_hoy}'
	   				WHERE id={$id_inventario}";

	if( $this->sql->update( $update ) )
				return true;
	else 	return false;
}

public function listaProveedores( $id = null )
{
	$resto = "";

	if( $id )
		$resto = " AND id = {$id}";

	$ssql = "SELECT * FROM proveedores WHERE id_estado = 1 {$resto}";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function procesaProveedores( $nombre_proveedor = null, $id = null )
{
	if( $id )
	{
		$update = "UPDATE proveedores SET descripcion='{$nombre_proveedor}'
							WHERE id={$id}";

		if( $this->sql->update( $update ) )
					return true;
		else 	return false;
	}
	else{

		$insert = "INSERT INTO proveedores( descripcion, id_estado )
							VALUES ( '{$nombre_proveedor}', 1 )";

		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;

	}
}



public function criticos()
{
	$ssql = "SELECT
							elemento.id                 ,
							elemento.nombre             ,
							elemento.descripcion        ,
							elemento.fecha_adquisicion  ,
							elemento.fecha_modificacion ,
							elemento.stock              ,
							elemento.stock_minimo       ,
							elemento.id_usuario         ,
							elemento.id_tipo            ,
							elemento.id_sub_tipo        ,
							elemento.id_estado          ,
							elemento.id_ubicacion       ,
							elemento.id_sububicacion    ,
							elemento.codigo             ,
							elemento.imagen             ,
							ubicacion.descripcion AS nombreUbicacion,
							sub_ubicacion.descripcion AS nombreSubUbicacion,
							tipo.descripcion AS nombreTipo,
							sub_tipo.descripcion AS nombreSubTipo
				FROM elemento
				INNER JOIN ubicacion ON ( ubicacion.id = elemento.id_ubicacion )
				INNER JOIN sub_ubicacion ON ( sub_ubicacion.id = elemento.id_sububicacion )
				INNER JOIN tipo ON ( tipo.id = elemento.id_tipo )
				INNER JOIN sub_tipo ON ( sub_tipo.id = elemento.id_sub_tipo )
				WHERE
				elemento.stock < elemento.stock_minimo OR elemento.stock = elemento.stock_minimo

				ORDER BY elemento.nombre
	";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

return $arr;
}


public function listaElementos( $id 							= null,
																$buscar_action 		= null,
																$id_ubicacion   	= null,
																$id_tipo 					= null,
																$id_sububicacion 	= null,
																$id_sub_tipo 			= null,
																$codigo						= null,
																$nombre 					= null 	)
{

 if( is_null( $buscar_action ) )
 {
	 $resto = "";
	 if( $id )
		 $resto .= " AND elemento.id = {$id}";

	 $ssql = "SELECT
							 elemento.id                 ,
							 elemento.nombre             ,
							 elemento.descripcion        ,
							 elemento.fecha_adquisicion  ,
							 elemento.fecha_modificacion ,
							 elemento.stock              ,
							 elemento.stock_minimo       ,
							 elemento.id_usuario         ,
							 elemento.id_tipo            ,
							 elemento.id_sub_tipo        ,
							 elemento.id_estado          ,
							 elemento.id_ubicacion       ,
							 elemento.id_sububicacion    ,
							 elemento.codigo             ,
							 elemento.imagen             ,
							 elemento.id_unidad          ,
							 ubicacion.descripcion AS nombreUbicacion,
							 sub_ubicacion.descripcion AS nombreSubUbicacion,
							 tipo.descripcion AS nombreTipo,
							 sub_tipo.descripcion AS nombreSubTipo,
							 unidades.descripcion AS nombreUnidad
				 FROM elemento
				 INNER JOIN ubicacion ON ( ubicacion.id = elemento.id_ubicacion )
				 INNER JOIN sub_ubicacion ON ( sub_ubicacion.id = elemento.id_sububicacion )
				 INNER JOIN tipo ON ( tipo.id = elemento.id_tipo )
				 INNER JOIN sub_tipo ON ( sub_tipo.id = elemento.id_sub_tipo )
				 INNER JOIN unidades ON ( unidades.id = elemento.id_unidad )
				 WHERE
						 elemento.id_estado = 1
						 {$resto}
				 ORDER BY  elemento.nombre

				 ";
 }else{


	 $ssql = "SELECT
							 elemento.id                 ,
							 elemento.nombre             ,
							 elemento.descripcion        ,
							 elemento.fecha_adquisicion  ,
							 elemento.fecha_modificacion ,
							 elemento.stock              ,
							 elemento.stock_minimo       ,
							 elemento.id_usuario         ,
							 elemento.id_tipo            ,
							 elemento.id_sub_tipo        ,
							 elemento.id_estado          ,
							 elemento.id_ubicacion       ,
							 elemento.id_sububicacion    ,
							 elemento.codigo             ,
							 elemento.imagen             ,
							 ubicacion.descripcion AS nombreUbicacion,
							 sub_ubicacion.descripcion AS nombreSubUbicacion,
							 tipo.descripcion AS nombreTipo,
							 sub_tipo.descripcion AS nombreSubTipo,
							 unidades.descripcion AS nombreUnidad
				 FROM elemento
				 INNER JOIN ubicacion ON ( ubicacion.id = elemento.id_ubicacion )
				 INNER JOIN sub_ubicacion ON ( sub_ubicacion.id = elemento.id_sububicacion )
				 INNER JOIN tipo ON ( tipo.id = elemento.id_tipo )
				 INNER JOIN sub_tipo ON ( sub_tipo.id = elemento.id_sub_tipo )
				 INNER JOIN unidades ON ( unidades.id = elemento.id_unidad )
				 WHERE
						 elemento.id_estado = 1
						 AND elemento.id_ubicacion LIKE '%{$id_ubicacion}%'
						 AND elemento.id_tipo LIKE '%{$id_tipo}%'
						 AND elemento.id_sububicacion LIKE '%{$id_sububicacion}%'
						 AND elemento.id_sub_tipo LIKE '%{$id_sub_tipo}%'
						 AND elemento.codigo LIKE '%{$codigo}%'
						 AND elemento.nombre LIKE '%{$nombre}%'
				 ORDER BY  elemento.nombre

				 ";
 		 }

		$arr['sql'] 	= $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] = count( $arr['process'] );

return $arr;
}

public function procesaElmento(   $nombre             = null,
 																	$descripcion        = null,
 																	$stock              = null,
 																	$stock_minimo       = null,
 																	$id_usuario         = null,
 																	$id_tipo            = null,
 																	$id_sub_tipo        = null,
 																	$id_ubicacion       = null,
 																	$id_sububicacion    = null,
 																	$codigo             = null,
 																	$imagen             = null,
																	$id_unidad          = null,
 																	$id                 = null )
{

	if( $id  )
		{
			$update = "UPDATE elemento SET nombre = '{$nombre}' ,
			                    descripcion        = '{$descripcion}' ,
			                    stock              = '{$stock}' ,
			                    stock_minimo       = '{$stock_minimo}' ,
			                    id_tipo            = '{$id_tipo}' ,
			                    id_sub_tipo        = '{$id_sub_tipo}' ,
			                    id_ubicacion       = '{$id_ubicacion}' ,
			                    id_sububicacion    = '{$id_sububicacion}' ,
													id_unidad    			 = '{$id_unidad}' ,
			                    imagen             = '{$imagen}',
													fecha_modificacion = '{$this->fecha_hora_hoy}'
			WHERE   id = {$id} ";

			if( $this->sql->update( $update ) )
						return true;
			else 	return false;

		}
	else{
						$insert = "INSERT INTO elemento( nombre ,
		                      descripcion        ,
		                      fecha_adquisicion  ,
		                      fecha_modificacion ,
		                      stock              ,
		                      stock_minimo       ,
		                      id_usuario         ,
		                      id_tipo            ,
		                      id_sub_tipo        ,
		                      id_estado          ,
		                      id_ubicacion       ,
		                      id_sububicacion    ,
		                      codigo             ,
		                      imagen             ,
												  id_unidad  )
		            VALUES( '{$nombre}',
		                    '{$descripcion}',
		                    '{$this->fecha_hoy}',
		                    '{$this->fecha_hora_hoy}',
		                    '{$stock}',
		                    '{$stock_minimo}',
		                    '{$id_usuario}',
		                    '{$id_tipo}',
		                    '{$id_sub_tipo}',
		                    1,
		                    '{$id_ubicacion}',
		                    '{$id_sububicacion}',
		                    '{$codigo}',
		                    '{$imagen}',
											  '{$id_unidad}')";

		if( $this->sql->insert( $insert ) )
					return true;
		else  return false;
	}
}

public function listaGemelas( $codigo = null )
{
  $resto = "" ;

	if( $codigo )
		$resto .= " AND gemela.codigo = '{$codigo}' ";

	$ssql = "SELECT
							gemela.id                 ,
							gemela.nombre             ,
							gemela.descripcion        ,
							gemela.fecha_adquisicion  ,
							gemela.fecha_modificacion ,
							gemela.stock              ,
							gemela.stock_minimo       ,
							gemela.id_usuario         ,
							gemela.id_tipo            ,
							gemela.id_sub_tipo        ,
							gemela.id_estado          ,
							gemela.id_ubicacion       ,
							gemela.id_sububicacion    ,
							gemela.codigo             ,
							gemela.imagen             ,
							ubicacion.descripcion AS nombreUbicacion,
							sub_ubicacion.descripcion AS nombreSubUbicacion,
							tipo.descripcion AS nombreTipo,
							sub_tipo.descripcion AS nombreSubTipo,
							usuario.nombres,
							usuario.apaterno,
							usuario.amaterno
				FROM gemela
				INNER JOIN ubicacion ON ( ubicacion.id = gemela.id_ubicacion )
				INNER JOIN sub_ubicacion ON ( sub_ubicacion.id = gemela.id_sububicacion )
				INNER JOIN tipo ON ( tipo.id = gemela.id_tipo )
				INNER JOIN sub_tipo ON ( sub_tipo.id = gemela.id_sub_tipo )
				INNER JOIN usuario ON ( usuario.id = gemela.id_usuario )
				WHERE
						gemela.id_estado = 1
						{$resto}
				ORDER BY gemela.fecha_modificacion DESC";

				$arr['sql'] 	= $ssql;
				$arr['process'] = $this->sql->select( $ssql );
				$arr['total-recs'] = count( $arr['process'] );

				return $arr;

}


//historial
public function procesaGemela($nombre             = null,
 															$descripcion        = null,
 															$stock              = null,
 															$stock_minimo       = null,
 															$id_usuario         = null,
 															$id_tipo            = null,
 															$id_sub_tipo        = null,
 															$id_ubicacion       = null,
 															$id_sububicacion    = null,
 															$codigo             = null,
 															$imagen             = null
 															)
{
	$insert = "INSERT INTO gemela( nombre ,
								descripcion        ,
								fecha_adquisicion  ,
								fecha_modificacion ,
								stock              ,
								stock_minimo       ,
								id_usuario         ,
								id_tipo            ,
								id_sub_tipo        ,
								id_estado          ,
								id_ubicacion       ,
								id_sububicacion    ,
								codigo             ,
								imagen               )
			VALUES( '{$nombre}',
							'{$descripcion}',
							'{$this->fecha_hoy}',
							'{$this->fecha_hora_hoy}',
							'{$stock}',
							'{$stock_minimo}',
							'{$id_usuario}',
							'{$id_tipo}',
							'{$id_sub_tipo}',
							1,
							'{$id_ubicacion}',
							'{$id_sububicacion}',
							'{$codigo}',
							'{$imagen}')";

							if( $this->sql->insert( $insert ) )
										return true;
							else  return false;
}

public function maxElemento()
{
	$ssql = "SELECT max(id) As maxElemento FROM elemento";
	return $this->sql->select( $ssql );
}

public function tipoDocumento()
{
	$ssql = "SELECT * FROM tipo_documento";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );
	return $arr;
}



public function listaAccesos()
{
	$ssql = "SELECT
					accesos.id           ,
					accesos.id_usuario   ,
					accesos.fecha_acceso ,
					accesos.sesion       ,
					accesos.ip           ,
					usuario.nombres      ,
					usuario.apaterno ,
					usuario.amaterno
			FROM accesos
			INNER JOIN usuario ON ( usuario.id = accesos.id_usuario )
			order by accesos.fecha_acceso DESC";


	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );
	return $arr;
}


public function ingresaAccesos( $id_usuario   = null,
																$sesion       = null,
																$ip           = null )
{

$INSERT = "INSERT INTO accesos( id_usuario,fecha, sesion ,ip ) VALUES
			('{$id_usuario}' ,'$this->fecha_hora_hoy' ,'{$sesion}' ,'{$ip}' )";

	if( $this->sql->insert( $INSERT ) )
					return true;
	 		else 	return false;
}


public function procesaArchivos(  	$nombre_archivo = null,
									$descripcion    = null,
									$fecha_subida   = null,
									$subido_por     = null,
									$categoria      = null,
									$ruta 			= null,
									$id_folder 		= null,
									$id_subfolder 	= null,
									$id             = null )
{
	if( $id )
	{ return false; }
	else{

		$insert = "INSERT INTO archivo( nombre_archivo ,
										descripcion    ,
										fecha_subida   ,
										subido_por     ,
										estado         ,
										categoria 	   ,
										ruta		   ,
										id_folder,
										id_subfolder	)
					VALUES ( '{$nombre_archivo}' ,
							 '{$descripcion}',
							 '{$fecha_subida}',
							 '{$subido_por}',
							  1,
							  1,
							'{$ruta}',
							'{$id_folder}',
							'{$id_subfolder}'    							)";

		if( $this->sql->insert( $insert ) )
				return true;
	 	else 	return false;

	// return $insert;
	}
}

public function cambiaEstado( $table = null, $field = null ,$valor = null, $id = null )
{
	$update = " UPDATE {$table} SET {$field} = {$valor} WHERE id = {$id}";

	if( $this->sql->update( $update ) )
					return true;
			 else 	return false;
}



public function cambiaClave( $id = null , $clave = null )
{
	$update = "UPDATE usuario SET clave = password( '{$clave}' )  WHERE id = {$id}";
	if( $this->sql->update( $update ) )
					return true;
			 else 	return false;
}

public function procesaProyecto(  $titulo             = null,
																	$descripcion        = null,
																	$fecha_creacion     = null,
																	$fecha_modificacion = null,
																	$fecha_inicio       = null,
																	$fecha_entrega      = null,
																	$etapa              = null,
																	$estado_etapa       = null,
																	$archivo            = null,
																	$responsable        = null,
																	$codigo             = null,
																	$id_tipo_proyecto   = null,
																	$id_usuario         = null,
																	$id                 = null )
{

	if( $id  )
		{
			$update = "UPDATE proyecto SET
											  titulo             = '{$titulo}' ,
											  descripcion        = '{$descripcion}' ,
											  fecha_modificacion = '{$this->fecha_hoy}' ,
											  fecha_inicio       = '{$fecha_inicio}' ,
											  fecha_entrega      = '{$fecha_entrega}' ,
											  etapa              = '{$etapa}' ,
											  estado_etapa       = '{$estado_etapa}' ,
											  archivo            = '{$archivo}' ,
											  responsable        = '{$responsable}' ,
											  id_tipo_proyecto   = '{$id_tipo_proyecto}'
								 WHERE id = {$id}";

			if( $this->sql->update( $update  ) )
						return true;
			else 	return false;

		}
	else{
		$INSERT = "INSERT INTO proyecto(  titulo             ,
								                      descripcion        ,
								                      fecha_creacion     ,
								                      fecha_modificacion ,
								                      fecha_inicio       ,
								                      fecha_entrega      ,
								                      etapa              ,
								                      estado_etapa       ,
								                      archivo            ,
								                      responsable        ,
								                      codigo             ,
								                      id_tipo_proyecto   ,
								                      id_usuario         )
							VALUES( '{$titulo}',
							        '{$descripcion}',
							        '{$fecha_creacion}',
							        '{$fecha_modificacion}',
							        '{$fecha_inicio}',
							        '{$fecha_entrega}',
							        '{$etapa}',
							        '{$estado_etapa}',
							        '{$archivo}',
							        '{$responsable}',
							        '{$codigo}',
							        '{$id_tipo_proyecto}',
							        '{$id_usuario}' )";

		if( $this->sql->insert( $INSERT ) )
					return true;
		else	return false;
	}
}

public function menu( $tipo_usuario = null )
{
	$ssql = "SELECT * FROM menu WHERE id_tipo_usuario = {$tipo_usuario}";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function sub_menu( $id_menu = null )
{
	$ssql = "SELECT * FROM sub_menu WHERE id_menu = {$id_menu}";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function ubicacion( $id = null )
{
	$resto = "";
	if( $id )
		$resto = " AND id={$id}";

	$ssql = "SELECT * FROM ubicacion  {$resto} order by descripcion";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function procesaSubUbicacion(  $descripcion  = null,
 																			$id_ubicacion = null,
 																			$id           = null  )
{
	if( $id )
	{
		$update = "UPDATE sub_ubicacion SET descripcion='{$descripcion}',
		id_ubicacion = {$id_ubicacion} WHERE id={$id}";

		if( $this->sql->update( $update ) )
					return true;
		else 	return false;
	}
	else{

		$insert = "INSERT INTO sub_ubicacion( descripcion, id_estado, id_ubicacion  )
		VALUES ( '{$descripcion}' , 1 , '{$id_ubicacion}'  ) ";

		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;

	}
}

public function listaSubUbicacion( $id = null , $id_ubicacion = null  )
{
	$resto = "";
	if( $id )
		$resto = " AND sub_ubicacion.id={$id}";

	if( $id_ubicacion )
		$resto = " AND sub_ubicacion.id_ubicacion={$id_ubicacion}";

	//$ssql = "SELECT * FROM sub_ubicacion WHERE id_estado = 1 {$resto}";
$ssql = "SELECT
					 sub_ubicacion.id           ,
					 sub_ubicacion.descripcion  ,
					 sub_ubicacion.id_estado    ,
					 sub_ubicacion.id_ubicacion ,
					 ubicacion.descripcion As nameUbicacion
					 FROM sub_ubicacion
					 INNER JOIN ubicacion ON ( ubicacion.id = sub_ubicacion.id_ubicacion )
					WHERE sub_ubicacion.id_estado = 1
					{$resto}
					ORDER BY sub_ubicacion.descripcion
					 ";


	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

}


public function listaTipo( $id = null )
{
	$resto = "";
	if( $id )
		$resto = " AND id={$id}";

	$ssql = "SELECT * FROM tipo WHERE id_estado = 1 {$resto}";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

}

public function listaSubTipo( $id = null, $id_tipo = null )
{
	$resto = "";
	if( $id )
		$resto = " AND sub_tipo.id={$id}";

  if ($id_tipo) {
  	$resto = " AND sub_tipo.id_tipo={$id_tipo}";
  }


	$ssql = "SELECT
    						sub_tipo.id          ,
    						sub_tipo.descripcion ,
    						sub_tipo.id_tipo     ,
    						sub_tipo.id_estado ,
    						tipo.descripcion As nameTipo
					FROM sub_tipo
					INNER JOIN tipo ON ( tipo.id = sub_tipo.id_tipo )
					WHERE
					sub_tipo.id_estado = 1
					{$resto}
					ORDER BY sub_tipo.descripcion
					";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

}

public function procesaSubTipo( $descripcion = null, $id_tipo=null,$id=null )
{
	if( $id  )
		{
			$update = "UPDATE sub_tipo SET descripcion='{$descripcion}', id_tipo={$id_tipo} WHERE id={$id}";
			if( $this->sql->update( $update ) )
				return true;
			else 	return false;
		}
	else{

		$insert = "INSERT INTO sub_tipo( descripcion,id_tipo,id_estado ) VALUES( '{$descripcion}','{$id_tipo}' ,1)";
		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;

	}
}



public function procesaTipo( $descripcion = null, $id=null )
{
	if( $id  )
		{
			$update = "UPDATE tipo SET descripcion='{$descripcion}' WHERE id={$id}";
			if( $this->sql->update( $update ) )
				return true;
			else 	return false;
		}
	else{

		$insert = "INSERT INTO tipo( descripcion,id_estado ) VALUES( '{$descripcion}' ,1)";
		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;

	}
}

public function listaUsuariosSimple( $id = null )
{
	return $this->sql->select( "SELECT * FROM usuario WHERE id={$id}" );
}

public function listaProyectos( $id 						= null,
																$buscar_action 	= null,
																$codigo 				= null,
																$titulo 				= null,
																$estado_etapa 	= null,
																$fecha_inicio   = null,
																$fecha_entrega  = null	 )
{
	if( is_null( $buscar_action ) )
	{
				$resto = "";
				if( $id )
					$resto .= " WHERE proyecto.id = {$id}";

				$ssql = "SELECT
								      proyecto.id                 ,
								      proyecto.titulo             ,
								      proyecto.descripcion        ,
								      proyecto.fecha_creacion     ,
								      proyecto.fecha_modificacion ,
								      proyecto.fecha_inicio       ,
								      proyecto.fecha_entrega      ,
								      proyecto.etapa              ,
								      proyecto.estado_etapa       ,
								      proyecto.archivo            ,
								      proyecto.responsable        ,
								      proyecto.codigo             ,
								      proyecto.id_tipo_proyecto   ,
								      proyecto.id_usuario         ,
								      usuario.apaterno ,
								      usuario.amaterno,
								      usuario.nombres,
								      tipo_proyecto.descripcion AS nameTipoProyecto,
											etapa.descripcion AS nameEtapa,
											estado_etapa.descripcion as nameEstadoEtapa
								FROM proyecto
								INNER JOIN usuario ON ( proyecto.id_usuario = usuario.id )
								INNER JOIN tipo_proyecto ON ( proyecto.id_tipo_proyecto = tipo_proyecto.id )
		     				INNER JOIN etapa ON ( etapa.id = proyecto.estado_etapa )
								INNER JOIN estado_etapa ON ( estado_etapa.id = proyecto.estado_etapa )
								{$resto}
								ORDER BY proyecto.codigo
								";
			 }else{
				 $resto = "";

				if(  $fecha_inicio !='' &&  $fecha_entrega !=''  )
						$resto .= " AND (proyecto.fecha_inicio <= '{$fecha_inicio}' AND proyecto.fecha_entrega >= '{$fecha_entrega}')
			 								OR   proyecto.fecha_inicio  BETWEEN '{$fecha_inicio}' AND '{$fecha_entrega}'
			 								OR   proyecto.fecha_entrega BETWEEN '{$fecha_inicio}' AND '{$fecha_entrega}' ";


				 $ssql = "SELECT
 								      proyecto.id                 ,
 								      proyecto.titulo             ,
 								      proyecto.descripcion        ,
 								      proyecto.fecha_creacion     ,
 								      proyecto.fecha_modificacion ,
 								      proyecto.fecha_inicio       ,
 								      proyecto.fecha_entrega      ,
 								      proyecto.etapa              ,
 								      proyecto.estado_etapa       ,
 								      proyecto.archivo            ,
 								      proyecto.responsable        ,
 								      proyecto.codigo             ,
 								      proyecto.id_tipo_proyecto   ,
 								      proyecto.id_usuario         ,
 								      usuario.apaterno ,
 								      usuario.amaterno,
 								      usuario.nombres,
 								      tipo_proyecto.descripcion AS nameTipoProyecto,
 											etapa.descripcion AS nameEtapa,
 											estado_etapa.descripcion as nameEstadoEtapa
 								FROM proyecto
 											INNER JOIN usuario ON ( proyecto.id_usuario = usuario.id )
 											INNER JOIN tipo_proyecto ON ( proyecto.id_tipo_proyecto = tipo_proyecto.id )
 		     							INNER JOIN etapa ON ( etapa.id = proyecto.estado_etapa )
 											INNER JOIN estado_etapa ON ( estado_etapa.id = proyecto.estado_etapa )
								WHERE
											proyecto.codigo LIKE '%{$codigo}%' AND
											proyecto.titulo LIKE '%{$titulo}%' AND
											proyecto.estado_etapa LIKE '%{$estado_etapa}%'
 								{$resto}
 								ORDER BY proyecto.codigo
 								";

			 }


		$arr['sql'] = $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] =	count( $arr['process'] );

		return $arr;
}

public function procesaUser( 	$nombres      = null ,
															$apaterno     = null ,
															$amaterno     = null ,
															$login        = null ,
															$clave        = null ,
															$id           = null )
{
	if( $id )
	{
		$update = "UPDATE usuario SET nombres = '{$nombres}',
																	apaterno = '{$apaterno}',
																	amaterno = '{$amaterno}',
																	clave    = PASSWORD('{$clave}')
							WHERE id = {$id}";

		if( $this->sql->update( $update ) )
						return true;
		else 		return false;
		//return false;
	}else{

		$arr = $this::listaUsuarios( $login, null, true );
		if( $arr['total-recs'] > 0 )
			return false;
		else{

			$insert = " INSERT INTO usuario(   nombres     ,
			                        apaterno    ,
			                        amaterno    ,
			                        login       ,
			                        clave       ,
			                        tipo_usuario,
			                        id_estado     )
			 						VALUES 			('{$nombres}'    ,
			                        '{$apaterno}'    ,
			                        '{$amaterno}'    ,
			                        '{$login}'    ,
			                        PASSWORD('{$clave}')    ,
			                        4,
			                        1     )";

			if( $this->sql->insert( $insert  ) )
			 			return true;
			else 	return false;

		}
	}
}


public function listaUsuarios( $email = null , $clave=null, $is_admin = null, $id_usuario = null, $apaterno= null)
{

	$resto = "";
	if( $email  ) $resto  .= " AND usuario.login = '{$email}' ";
	if( $clave )  $resto  .= " AND usuario.clave = password('{$clave}')   ";


	if( !$is_admin )
	{
		$ssql = "SELECT
						usuario.id           ,
						usuario.nombres      ,
						usuario.apaterno     ,
						usuario.amaterno     ,
						usuario.login        ,
						usuario.clave        ,
						usuario.tipo_usuario,
						usuario.id_estado
				FROM usuario
				WHERE usuario.id_estado = 1	{$resto}
				";
	}else{

		$resto = "";
		if( $id_usuario ) $resto .= " AND usuario.id = {$id_usuario}";
		if( $apaterno )   $resto .= " AND usuario.apaterno  LIKE '%{$apaterno}%'  ";
		if( $email  ) $resto  .= " AND usuario.login = '{$email}' ";

		$ssql = "SELECT
						usuario.id           ,
						usuario.nombres      ,
						usuario.apaterno     ,
						usuario.amaterno     ,
						usuario.login        ,
						usuario.clave        ,
						usuario.tipo_usuario,
						usuario.id_estado

				FROM usuario
				WHERE 		usuario.tipo_usuario != 3
							AND usuario.id_estado = 1
				 {$resto}
				ORDER BY usuario.apaterno, usuario.amaterno, usuario.nombres
				";
	}

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

	}
}
?>
