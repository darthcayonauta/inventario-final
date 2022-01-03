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

	public function procesaCentro(  $descripcion 	= null,
									$id_cliente  	= null,
									$id_estado		= null,
									$id   			= null	)
	{
		if( $id )
		{
			
			$update = "UPDATE centro SET descripcion='{$descripcion}', id_cliente={$id_cliente} WHERE id={$id}";
			if( $this->sql->update( $update ) )
					return true;
			else 	return false;


		}else{

			$insert = "INSERT INTO centro ( descripcion,id_cliente,id_estado ) VALUES ('{$descripcion}','{$id_cliente}',1)";
			if( $this->sql->insert( $insert ) )
					return true;
			else 	return false;
		}
	}


public function cambiaEstadoCentro( $id_centro = null , $id_estado = null )
{
	switch ($id_estado) {
		case 2:
			# code...
			$id_estado = 1;
			break;
		
		case 1:
			# code...
			$id_estado = 2;
			break;	
		default:
			# code...
			break;
	}

	$update = "UPDATE centro SET id_estado={$id_estado} WHERE id={$id_centro}";

	if( $this->sql->update( $update ) )
		 return true;
	else return false;	


}



public function listaCentros( $id = null )
{
	$resto = null;
	
	if( $id )
		$resto = " WHERE centro.id={$id}";	

	$ssql ="SELECT 
			centro.id,	
			centro.descripcion,
			centro.id_estado,
			centro.id_cliente,
			clientes.descripcion AS nombreCliente
			FROM 
			centro
			INNER JOIN clientes ON ( clientes.id = centro.id_cliente)
			{$resto}
	";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function ingresaGuiaDespachoIngreso(  $num_guia     = null,
											 $fecha        = null,
											 $id_proveedor = null,
											 $token        = null,
											 $id_usuario   = null,
											 $id_tipo_documento = null	
											 )
{
	$insert = "INSERT INTO guia_despacho_ingreso( num_guia, fecha, id_proveedor, token, id_estado, id_usuario, id_tipo_documento) VALUES 
	('{$num_guia}','{$fecha}','{$id_proveedor}','{$token}',1,'{$id_usuario}','{$id_tipo_documento}')";
	
	if( $this->sql->insert( $insert ) )
			return true;
	else 	return false;
}

public function ingresaGuiaDespachoEgreso(   $num_guia     = null,
											 $fecha        = null,
											 $id_cliente = null,
											 $rs 		   = null,
											 $token        = null,
											 $id_usuario   = null	
											 )
{
	$insert = "INSERT INTO guia_despacho_egreso( num_guia, fecha, id_cliente, token, id_estado, id_usuario,rs) VALUES 
	('{$num_guia}','{$fecha}','{$id_cliente}','{$token}',1,'{$id_usuario}','{$rs}')";
	
	if( $this->sql->insert( $insert ) )
			return true;
	else 	return false;
}


public function listaGuiaDespachoEgreso($token = null, $num_guia = null, 
										$buscar=null,
										$fecha_inicio = null, 
										$fecha_fin =null)
{
	$resto = null;

	if( $token )
		$resto = "WHERE guia_despacho_egreso.token = '{$token}' ";

	if( $num_guia )
		$resto = "WHERE guia_despacho_egreso.num_guia LIKE '%{$num_guia}%' ";

	if( $buscar )
		$resto = "WHERE guia_despacho_egreso.fecha BETWEEN '{$fecha_inicio}' AND '$fecha_fin'";


	$ssql = "SELECT
				guia_despacho_egreso.id         ,
				guia_despacho_egreso.num_guia   ,
				guia_despacho_egreso.fecha      ,
				guia_despacho_egreso.id_cliente ,
				guia_despacho_egreso.rs         ,
				guia_despacho_egreso.token      ,
				guia_despacho_egreso.id_estado  ,
				guia_despacho_egreso.id_usuario ,
				clientes.descripcion AS nombreCliente,
				usuario.nombres,
				usuario.apaterno
			FROM 
			guia_despacho_egreso
			INNER JOIN clientes ON ( clientes.id = guia_despacho_egreso.id_cliente )
			INNER JOIN usuario ON ( usuario.id = guia_despacho_egreso.id_usuario )
			{$resto}
			ORDER BY guia_despacho_egreso.token DESC
			";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;	

}

public function listaDetalleGuiaDespachoEgreso( $token 			= null, 
												$id_insumo 		= null, 
												$id 			= null
												  )
{	
	$resto = null;

	if( $token  )
		$resto = "WHERE detalle_guia_despacho_egreso.token = '{$token}'";

	if( $id_insumo  )
		$resto = "WHERE detalle_guia_despacho_egreso.id_insumo = '{$id_insumo}'";

	if( $id  )
		$resto = "WHERE detalle_guia_despacho_egreso.id = '{$id}'";

	//if( $buscar )
	//	$resto = "WHERE guia_despacho_ingreso.fecha BETWEEN '{$fechaInicio}' AND '$fechaFin'";

	$ssql = "SELECT 
				detalle_guia_despacho_egreso.id        ,
				detalle_guia_despacho_egreso.id_insumo ,
				detalle_guia_despacho_egreso.num_guia     ,
				detalle_guia_despacho_egreso.token     ,
				detalle_guia_despacho_egreso.cantidad  ,
				detalle_guia_despacho_egreso.valor,
				elemento.nombre AS nombreInsumo, 
				elemento.codigo_final,
				elemento.stock,
				sub_tipo.descripcion AS familia,
				elemento.id AS id_insumo

			FROM		    
				detalle_guia_despacho_egreso 
				INNER JOIN elemento ON ( elemento.id = detalle_guia_despacho_egreso.id_insumo )
				INNER JOIN sub_tipo ON ( elemento.id_sub_tipo = sub_tipo.id)
			{$resto}	
				";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function qGuiaDespachoIngreso( $num_guia = null )
{
	$ssql = "SELECT * FROM guia_despacho_ingreso WHERE num_guia = '{$num_guia}'";


	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function qGuiaDespachoEgreso( $num_guia = null )
{
	$ssql = "SELECT * FROM guia_despacho_egreso WHERE num_guia = '{$num_guia}'";


	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function listaGuiaDespachoIngreso( $token 		= null, 
										  $num_guia 	= null, 
										  $id 			= null ,
										  $buscar 		= null,
										  $fecha_inicio = null,
										  $fecha_fin 	= null )
{

	$resto = null;

	if( $token )
		$resto = "WHERE guia_despacho_ingreso.token = '{$token}' ";

	if( $num_guia )
		$resto = "WHERE guia_despacho_ingreso.num_guia LIKE '%{$num_guia}%' ";

	if( $id )
		$resto = "WHERE guia_despacho_ingreso.id = '{$id}' ";

		if( $buscar )
		$resto = "WHERE guia_despacho_ingreso.fecha BETWEEN '{$fecha_inicio}' AND '$fecha_fin'";


	$ssql = "SELECT 
		  		guia_despacho_ingreso.id           , 
	  			guia_despacho_ingreso.num_guia     , 
	  			guia_despacho_ingreso.fecha        , 
	  			guia_despacho_ingreso.id_proveedor , 
	  			guia_despacho_ingreso.token        , 
	  			guia_despacho_ingreso.id_estado    ,
				guia_despacho_ingreso.id_usuario    , 
				guia_despacho_ingreso.id_tipo_documento,  
				usuario.nombres,
				usuario.apaterno,
				proveedores.descripcion as nombreProveedor,
				tipo_documento.descripcion AS tipo_documento  
			FROM	  
			 guia_despacho_ingreso
			 INNER JOIN  proveedores ON ( proveedores.id = guia_despacho_ingreso.id_proveedor)	
			 INNER JOIN usuario ON ( usuario.id = guia_despacho_ingreso.id_usuario )
			 LEFT JOIN tipo_documento ON (tipo_documento.id = guia_despacho_ingreso.id_tipo_documento)	
			 {$resto}
			ORDER BY  guia_despacho_ingreso.fecha DESC

	 ";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

}

public function maxCodigoImsumo( $id_subtipo = null)
{
	$ssql = "SELECT max(codigo_final) AS max_codigo_final FROM elemento WHERE id_sub_tipo={$id_subtipo}";
	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function procesaDetalleGuiaDespachoIngreso( $id_insumo = null, 
												   $token 	  = null,
												   $cantidad  = null, 
												   $valor 	  = null, 
												   $id 		  = null			 )
{
	if( $id )
	{ 
		$update = "UPDATE detalle_guia_despacho_ingreso 
				   SET cantidad='{$cantidad}', valor='{$valor}' 
				   WHERE id='{$id}'";

		if( $this->sql->update( $update ) )
				return true;
		else 	return false;
	  }
	else{
		
		if( is_null( $cantidad ) )		
				$insert = "INSERT INTO detalle_guia_despacho_ingreso( id_insumo, token) VALUES ('{$id_insumo}','{$token}')";
		else 	$insert = "INSERT INTO detalle_guia_despacho_ingreso( id_insumo, token, cantidad, valor) VALUES ('{$id_insumo}','{$token}', '{$cantidad}', '{$valor}')";
		
		if( $this->sql->insert( $insert ) )
				return true;
		else 	return false;
	}
}

public function maxIdIngresoInsumo( $id_insumo = null )
{
	return $this->sql->select( "SELECT MAX(id) AS maxId from detalle_guia_despacho_ingreso WHERE id_insumo={$id_insumo}" );
}



public function procesaDetalleGuiaDespachoEgreso(  $id_insumo = null, 
												   $token 	  = null,
												   $cantidad  = null, 
												   $valor 	  = null, 
												   $num_guia  = null, 	  
												   $id 		  = null			 )
{
	if( $id )
	{ 
		$update = "UPDATE detalle_guia_despacho_egreso 
				   SET cantidad='{$cantidad}' 
				   WHERE id='{$id}'";

		if( $this->sql->update( $update ) )
				return true;
		else 	return false;
	  }
	else{
		$insert = "INSERT INTO detalle_guia_despacho_egreso( id_insumo, token, num_guia,valor) VALUES ('{$id_insumo}','{$token}','{$num_guia}','{$valor}')";
		if( $this->sql->insert( $insert ) )
				return true;
		else 	return false;

		//return $insert;
	}
}





public function actualizaInsumos( $stock = null, $id = null )
{
	$update = "UPDATE elemento
			   SET stock='{$stock}' 
			   WHERE id='{$id}'";

	if( $this->sql->update( $update ) )
	return true;
	else 	return false;
}


public function listaDetalleGuiaDespachoIngreso( $token = null, $id_insumo = null, $id = null )
{	
	$resto = null;

	if( $token  )
		$resto = "WHERE detalle_guia_despacho_ingreso.token = '{$token}'";

	if( $id_insumo  )
		$resto = "WHERE detalle_guia_despacho_ingreso.id_insumo = '{$id_insumo}'";

		if( $id  )
		$resto = "WHERE detalle_guia_despacho_ingreso.id = '{$id}'";


	$ssql = "SELECT 
				detalle_guia_despacho_ingreso.id        ,
				detalle_guia_despacho_ingreso.id_insumo ,
				detalle_guia_despacho_ingreso.token     ,
				detalle_guia_despacho_ingreso.cantidad  ,
				detalle_guia_despacho_ingreso.valor,
				elemento.nombre AS nombreInsumo, 
				elemento.codigo_final,
				elemento.stock,
				sub_tipo.descripcion AS familia,
				elemento.id AS id_insumo

			FROM		    
				detalle_guia_despacho_ingreso 
				INNER JOIN elemento ON ( elemento.id = detalle_guia_despacho_ingreso.id_insumo )
				INNER JOIN sub_tipo ON ( elemento.id_sub_tipo = sub_tipo.id)
			{$resto}	
				";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}



public function afirmacion()
{
	$ssql = "SELECT * FROM afirmacion";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function procesaRelElemento( $nombre      = null,
									$id_elemento = null,
									$token       = null,									
									$id_usuario  = null,
									$cantidad    = null,
									$id          = null )
{
	if( $id )
	{
	//update
		$update = "";	


		if( $this->sql->update( $update ) )
				return true;
		else 	return false;	
	}
	else{
	//insert	
		$insert = "INSERT INTO rel_elemento( nombre,id_elemento,token,fecha,id_usuario ) VALUES 
				   ('{$nombre}',{$id_elemento},'{$token}','{$this->fecha_hoy}',{$id_usuario} )";

		if( $this->sql->insert( $insert ) )
				return true;
		else 	return false;	

	}
}

public function eliminaElemento(  $id = null  )
{
	$delete = "DELETE FROM elemento WHERE id={$id}";

	if( $this->sql->delete( $delete ) )
		return true;
	else return false;	
}




public function listarItemProductosMateriales( $codigo = null )
{
	$ssql = "SELECT DISTINCT 
				cuerpo_material.id_item,
				item_productos.descripcion AS nombreItemProducto
			from 
				cuerpo_material
			INNER JOIN item_productos ON ( item_productos.id = cuerpo_material.id_item )
			WHERE
				cuerpo_material.codigo = '{$codigo}'
			ORDER BY item_productos.descripcion ";
	
	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;

}


public function eliminaListaMaterial( $codigo = null )
{
	$delete = "DELETE FROM encabezado_material WHERE codigo='{$codigo}'";

	if( $this->sql->delete( $delete ) )
			return true;
	else	return false;
}


public function eliminaMaterial( $id = null, $codigo=null )
{
	$resto = null;
	if( $id  )
		$resto =" WHERE id={$id}";

	if( $codigo )	
		$resto =" WHERE codigo='{$codigo}'";

	$delete = "DELETE FROM cuerpo_material {$resto}";

	if( $this->sql->delete( $delete ) )
			return true;
	else	return false;

}

public function listaMateriales( $codigo = null, $id = null, $id_item = null )
{
	$resto  = null;
	$resto2 = null;

	if( $id_item )
		$resto2 = " AND cuerpo_material.id_item={$id_item}";

	if( $id )
		$resto = " AND cuerpo_material.id={$id}";

	$ssql="SELECT
				cuerpo_material.id                 ,
				cuerpo_material.codigo             ,
				cuerpo_material.requerimiento      ,
				cuerpo_material.u_medida           ,
				cuerpo_material.id_insumo          ,
				cuerpo_material.id_proveedor       ,
				cuerpo_material.stock              ,
				cuerpo_material.a_comprar          ,
				cuerpo_material.precio_unitario    ,
				cuerpo_material.total              ,
				cuerpo_material.id_item            ,
				cuerpo_material.comentario         ,
				cuerpo_material.fecha_ingreso      ,
				cuerpo_material.fecha_modificacion ,
				item_productos.descripcion AS nombreItemProducto,
				unidades.descripcion AS nombreUnidadMedida,
				elemento.nombre AS nombreProducto,
				proveedores.descripcion AS nombreProveedor
			FROM
				cuerpo_material
			INNER JOIN  item_productos ON (item_productos.id = cuerpo_material.id_item)	
			INNER JOIN unidades ON (unidades.id = cuerpo_material.u_medida)
			INNER JOIN elemento ON (elemento.codigo = cuerpo_material.id_insumo)
			INNER JOIN proveedores ON ( proveedores.id = cuerpo_material.id_proveedor )
			WHERE	
				cuerpo_material.codigo = '{$codigo}' {$resto} {$resto2}	
			";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}



public function procesaCuerpoMaterial(  $codigo             = null,
										$requerimiento      = null,
										$u_medida           = null,
										$id_insumo          = null,
										$id_proveedor       = null,
										$stock              = null,
										$a_comprar          = null,
										$precio_unitario    = null,
										$total              = null,
										$id_item            = null,
										$comentario         = null,																				
										$id                 = null )
{
	if( $id )
	{
		$update = "UPDATE cuerpo_material SET requerimiento='{$requerimiento}',
					u_medida = '{$u_medida}',id_insumo='{$id_insumo}',id_proveedor='{$id_proveedor}',
					stock='{$stock}',a_comprar='{$a_comprar}', total='{$total}', id_item='{$id_item}'
					WHERE id={$id}	";

		if( $this->sql->update( $update ) )
				return true;
		else 	return false;
	}else{

		$insert="INSERT INTO cuerpo_material( 	codigo             ,
												requerimiento      ,
												u_medida           ,
												id_insumo          ,
												id_proveedor       ,
												stock              ,
												a_comprar          ,
												precio_unitario    ,
												total              ,
												id_item            ,
												comentario         ,
												fecha_ingreso      ,
												fecha_modificacion ) 
										VALUES( '{$codigo}' ,
												'{$requerimiento}' ,
												'{$u_medida}' ,
												'{$id_insumo}' ,
												'{$id_proveedor}' ,
												'{$stock}' ,
												'{$a_comprar}' ,
												'{$precio_unitario   }' ,
												'{$total}' ,
												'{$id_item}' ,
												'{$comentario}' ,
												'{$this->fecha_hoy}' ,
												'{$this->fecha_hoy}' )";

		if( $this->sql->insert( $insert ) )
				return true;
		else 	return false;

		//return $insert;

	}
}



public function procesaEncabezadoMaterial( $titulo          = null,
										   $centro          = null,
										   $modulos         = null,
										   $jaulas          = null,
										   $calidad         = null,
										   $camaras_x_jaula = null,
										   $codigo          = null,
										   $id              = null)
{
	if( $id )
	{
		$update = "UPDATE encabezado_material SET titulo='{$titulo}',
							centro='{$centro}',
							modulos='{$modulos}',
							jaulas='{$jaulas}',
							calidad='{$calidad}',
							camaras_x_jaula='{$camaras_x_jaula}'
					WHERE id={$id}  ";

		if( $this->sql->update( $update ) )
				return true;
		else 	return false;
 
	//return $update;	
		
	}else{

		$insert = " INSERT INTO encabezado_material (titulo,centro,modulos,jaulas,calidad,camaras_x_jaula,codigo,fecha_ingreso,fecha_update )
		VALUES ('{$titulo}' ,'{$centro}' ,'{$modulos}' ,'{$jaulas}' ,'{$calidad}' ,'{$camaras_x_jaula}','{$codigo}','{$this->fecha_hoy}','{$this->fecha_hoy}' 
				 )";

		if( $this->sql->insert( $insert ) )
				return true;
		else 	return false;

		//return $insert;

	}	
}

public function listaEncabezadoMaterial( $id = null, $codigo=null )
{
	$resto = "";

	if( $id )
		$resto .= "WHERE encabezado_material.id='{$id}'";

	if( $codigo )
		$resto .= "WHERE encabezado_material.codigo='{$codigo}'";	

/*
MariaDB [inventario_older]> describe encabezado_material;
+-----------------+---------+------+-----+---------+----------------+
| Field           | Type    | Null | Key | Default | Extra          |
+-----------------+---------+------+-----+---------+----------------+
| id              | int(11) | NO   | PRI | NULL    | auto_increment |
| titulo          | text    | YES  |     | NULL    |                |
| centro          | int(11) | YES  |     | NULL    |                |
| modulos         | int(11) | YES  |     | NULL    |                |
| jaulas          | int(11) | YES  |     | NULL    |                |
| calidad         | text    | YES  |     | NULL    |                |
| camaras_x_jaula | int(11) | YES  |     | NULL    |                |
| codigo          | text    | YES  |     | NULL    |                |
+-----------------+---------+------+-----+---------+----------------+

*/
	//$ssql ="SELECT * FROM encabezado_material {$resto}";

	$ssql ="SELECT 
				encabezado_material.id ,
				encabezado_material.titulo ,
				encabezado_material.centro ,
				encabezado_material.modulos ,
				encabezado_material.jaulas ,
				encabezado_material.calidad ,
				encabezado_material.camaras_x_jaula ,
				encabezado_material.codigo ,
				encabezado_material.fecha_ingreso ,
				encabezado_material.fecha_update,
				destino.descripcion As nombreCentro
			FROM
				encabezado_material 
			INNER JOIN destino ON (destino.id = encabezado_material.centro)	
				{$resto}
			ORDER BY encabezado_material.fecha_ingreso  DESC

			";


	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}


public function maxListaEncabezadoMaterial()
{
	$ssql = "SELECT MAX(id) as elMayor FROM encabezado_material";
	
	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function actualizaStockElemento( $cantidad = null, $codigo = null )
{
	$update = "UPDATE elemento SET stock={$cantidad} WHERE codigo='{$codigo}'";
	
	if( $this->sql->update( $update ) )
			return true;
	else 
			return 	false;
}



public function actualizaEstadoPreColaboracion( $token = null )
{
	$update = "UPDATE pre_colaboracion SET id_estado=2 WHERE token='{$token}'";

	if( $this->sql->update( $update ) )
		return true;
	else return false;

		
}

public function eliminaColaboracion( $id=null )
{
	$delete = "DELETE FROM pre_colaboracion WHERE id={$id}";

	if( $this->sql->delete( $delete ) )
		return true;
	else return false;
}


public function procesaPreColaboracion( $token 				= null,
										$codigo_producto 	= null,
										$id_cliente 		= null,
										$id_sub_cliente 	= null,
										$id_responsable 	= null,
										$cantidad 			= null,
										$id_unidad 			= null,
										$id 				= null )
{
	if( $id )
	{
		$update ="";

		if( $this->sql->update( $update ) )
					return true;
		else return false;
	}else{

		$insert ="INSERT INTO pre_colaboracion( token,codigo_producto,id_cliente,id_sub_cliente,fecha,id_responsable,cantidad,id_unidad,id_estado )
		VALUES ('{$token}','{$codigo_producto}',{$id_cliente},{$id_sub_cliente},'{$this->fecha_hoy}',{$id_responsable},{$cantidad},{$id_unidad},1)
		";

		$arr = $this::listaPreColaboracion($token,$codigo_producto);

		if( $arr['total-recs']  == 0  )
		{
			if( $this->sql->insert( $insert ) )
						return true;
			else return false;
		}else return false;
	}
}


public function listaPreColaboracion( $token = null,$codigo_producto =null )
{
	$resto = "";

	if( $codigo_producto  )
		$resto .= " AND pre_colaboracion.codigo_producto='{$codigo_producto}'";

	$ssql = "SELECT
					pre_colaboracion.id,
					pre_colaboracion.token,
					pre_colaboracion.codigo_producto,
					pre_colaboracion.id_cliente,
					pre_colaboracion.id_sub_cliente,
					pre_colaboracion.fecha,
					pre_colaboracion.id_responsable,
					pre_colaboracion.id_unidad,
					pre_colaboracion.cantidad,
					elemento.nombre,
					unidades.descripcion AS nombreUnidad,
					clientes.descripcion AS nombreEmpresa,
					sub_cliente.nombres,
					sub_cliente.apaterno,
					sub_cliente.amaterno,
					pre_colaboracion.id_estado
	FROM
	pre_colaboracion
	INNER JOIN elemento ON ( elemento.codigo = pre_colaboracion.codigo_producto )
	INNER JOIN unidades ON ( unidades.id = pre_colaboracion.id_unidad )
	INNER JOIN clientes ON ( clientes.id = pre_colaboracion.id_cliente )
	INNER JOIN sub_cliente ON ( sub_cliente.id = pre_colaboracion.id_sub_cliente )
	WHERE pre_colaboracion.token='{$token}' {$resto}
	";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function listaPreColaboracionDetalle($token = null)
{

	$resto = null;

	if( $token  )
		$resto = " WHERE pre_colaboracion.token='{$token}'";


	$ssql = "SELECT DISTINCT
						clientes.descripcion AS nombreEmpresa,
						sub_cliente.nombres,
						sub_cliente.apaterno,
						sub_cliente.amaterno,
						pre_colaboracion.token,
						pre_colaboracion.fecha,
						pre_colaboracion.id_estado
					FROM
						pre_colaboracion
						INNER JOIN clientes ON ( clientes.id = pre_colaboracion.id_cliente )
						INNER JOIN sub_cliente ON ( sub_cliente.id = pre_colaboracion.id_sub_cliente )
					{$resto}
	";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}





public function procesaAsignaClienteData( $id_destino = null, $id_cliente = null, $id = null  )
{
	if( $id )
	{
		$update ="UPDATE rel_destino_cliente SET id_cliente={$id_cliente} WHERE
							id_destino = {$id_destino}";

		if( $this->sql->update( $update ) )
		 			return true;
		else return false;
	}else{

		$insert ="INSERT INTO rel_destino_cliente( id_destino,id_cliente ) VALUES
    ({$id_destino},{$id_cliente})";

		if( $this->sql->insert( $insert ) )
		 			return true;
		else return false;

	}
}


public function quitarDestinoCliente( $id_destino = null, $id_cliente = null )
{
	$delete = "DELETE FROM rel_destino_cliente
	           WHERE id_destino={$id_destino} AND id_cliente={$id_cliente}";

	if( $this->sql->delete( $delete ) )
				return true;
	else 	return false;
}


public function relDestinoCliente()
{
	$ssql = "SELECT
				destino.id,
				destino.descripcion,
				destino.id_estado,
				rel_destino_cliente.id_cliente

			FROM destino
			left join rel_destino_cliente on( destino.id = rel_destino_cliente.id_destino )
					WHERE destino.id_estado = 1
			ORDER BY descripcion;

	";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}



public function eliminaSubCliente( $id = null )
{
	$update = "UPDATE sub_cliente SET id_estado=2 WHERE id={$id}";

	if( $this->sql->update( $update ) )
			 return true;
	else return false;

}

public function procesaItemProductos( $descripcion = null, $id=null )
{
	if( $id )
	{
		$update = "UPDATE item_productos SET descripcion='{$descripcion}' WHERE id={$id}";

		if( $this->sql->update( $update ) )
				 return true;
		else return false;

	}else{

		$insert = "INSERT INTO item_productos( descripcion,id_estado ) VALUES( '{$descripcion}',1 )";

		if( $this->sql->insert( $insert ) )
				 return true;
		else return false;

	}
}

public function eliminaItemProductos( $id = null )
{
		$update = "UPDATE item_productos SET id_estado=2 WHERE id={$id}";

	if( $this->sql->update( $update ) )
			 return true;
	else return false;
}

public function listarItemProductos( $id = null )
{
  $resto = null;

	if( $id )
		$resto = " AND id={$id}";

	$ssql = "SELECT * FROM item_productos WHERE id_estado = 1 {$resto}
	ORDER by descripcion";

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;



}




public function listaSubClientes( $id = null, $id_cliente = null )
{
	$resto = "";

	if( $id )
	$resto .= " AND sub_cliente.id = {$id}";

	if( $id_cliente )
		$resto .= " AND sub_cliente.id_cliente = {$id_cliente}";

	$ssql = "SELECT
								sub_cliente.id,
								sub_cliente.nombres,
								sub_cliente.apaterno,
								sub_cliente.amaterno,
								sub_cliente.rut,
								sub_cliente.fono,
								sub_cliente.email,
								sub_cliente.id_estado,
								sub_cliente.id_cliente,
								clientes.descripcion
            FROM sub_cliente
						INNER JOIN clientes ON ( sub_cliente.id_cliente = clientes.id )
						WHERE sub_cliente.id_estado =1
						{$resto}
						ORDER BY sub_cliente.apaterno,sub_cliente.amaterno";

			$arr['sql'] 			= $ssql;
			$arr['process']		= $this->sql->select( $ssql );
			$arr['total-recs']= count( $arr['process'] );

		return $arr;
}


public function procesaSubCliente($nombres    = null,
									$apaterno   = null,
									$amaterno   = null,
									$rut        = null,
									$email      = null,
									$fono       = null,
									$id_cliente = null,
									$id         = null)
{
	if( $id )
	{
		//update
		$update ="UPDATE sub_cliente SET nombres  	='{$nombres}',
																		 apaterno 	='{$apaterno}',
																		 amaterno 	='{$amaterno}',
																		 rut		  	='{$rut}',
																		 email    	='{$email}',
																		 fono 			='{$fono}',
																		 id_cliente ='{$id_cliente}'
							WHERE id = '{$id}'
																		 ";

		if( $this->sql->update( $update ) )
			   return true;
		else return false;
	}else{
		//insert
		$insert ="INSERT INTO sub_cliente(nombres,apaterno,amaterno,rut,email,fono,id_cliente ,
		 id_estado)
		VALUES ('{$nombres}','{$apaterno}','{$amaterno}','{$rut}' ,
			      '{$email}','{$fono}','{$id_cliente}' , 1) ";

		if( $this->sql->insert( $insert ) )
				 return true;
		else return false;
	}


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
						  egresos.id_proyecto,
						  egresos.valor,
						  usuario.apaterno			 ,
						  usuario.amaterno       ,
						  usuario.nombres        
						 
					 FROM egresos
					 INNER JOIN usuario ON ( usuario.id = egresos.id_user  )				
					 WHERE
					  egresos.codigo = '{$codigo}'
						ORDER BY egresos.fecha DESC

						";

 		$arr['sql'] 		= $ssql;
 		$arr['process'] 	= $this->sql->select( $ssql );
 		$arr['total-recs'] 	= count( $arr['process'] );

 		return $arr;
  }

  public function maxIdIngresos(  $codigo = null  )
  { return $this->sql->select( "SELECT MAX(id) AS max_id FROM ingresos"); }

  public function lastValorIngresos( $codigo = null  )
  {
	$max_id = "";
	foreach ($this::maxIdIngresos( $codigo ) as $key => $value) {		
		$max_id .= $value['max_id'];
	}
	return $this->sql->select( "SELECT valor FROM ingresos WHERE id = {$max_id}" );
  }







  public function listaEgresos2( $id_proyecto = null )
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
					egresos.id_proyecto		,
					egresos.valor		,
					usuario.apaterno			 ,
					usuario.amaterno       ,
					usuario.nombres        
				
				FROM egresos
				INNER JOIN usuario ON ( usuario.id = egresos.id_user  )
	
				WHERE
				egresos.id_proyecto = '{$id_proyecto}'
				ORDER BY egresos.fecha DESC

						";

 		$arr['sql'] 		= $ssql;
 		$arr['process'] 	= $this->sql->select( $ssql );
 		$arr['total-recs'] 	= count( $arr['process'] );

 		return $arr;
  }





public function guiasDespachoEgreso( $id_proyecto = null   )
{
	if( $id_proyecto  )
		$resto = " AND id_proyecto  LIKE '%{$id_proyecto }%'";
	else $resto = "";

	$ssql = "SELECT DISTINCT id_proyecto,id_estado FROM `egresos` WHERE id_proyecto != '' {$resto}";

	$arr['sql'] 		= $ssql;
	$arr['process'] 	= $this->sql->select( $ssql );
	$arr['total-recs'] 	= count( $arr['process'] );

return $arr;
}

public function guiasDespacho( $num_documento = null )
{	
	if( $num_documento )
		 $resto = " AND num_documento LIKE '%{$num_documento}%'";
	else $resto = "";

	$ssql = "SELECT DISTINCT num_documento,id_estado FROM `ingresos` WHERE num_documento != '' {$resto}";

	$arr['sql'] 		= $ssql;
	$arr['process'] 	= $this->sql->select( $ssql );
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
				ingresos.valor,
				ingresos.guia_despacho,						  
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


		$arr['sql'] 		= $ssql;
		$arr['process'] 	= $this->sql->select( $ssql );
		$arr['total-recs'] 	= count( $arr['process'] );

		return $arr;
 }


 public function listaIngresos2( $guia_despacho = null )
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
				ingresos.valor,
				ingresos.guia_despacho,						  
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
		WHERE ingresos.num_documento = '{$guia_despacho}'
		ORDER BY ingresos.fecha DESC";


		$arr['sql'] 		= $ssql;
		$arr['process'] 	= $this->sql->select( $ssql );
		$arr['total-recs'] 	= count( $arr['process'] );

		return $arr;
 }


 public function ingresaEgresos(  	$codigo     = null ,
									$id_user    = null ,
									$insumo     = null ,
									$id_trabajo = null ,
									$id_cliente = null ,
									$id_destino = null ,
									$cantidad   = null ,
									$comentario = null,
									$id_proyecto = null, $valor = null )
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
										fecha  , 
										id_proyecto, valor     )
						VALUES( '{$codigo}' ,
						        '{$id_user}' ,
						        '{$insumo}' ,
						        '{$id_trabajo}' ,
						        '{$id_cliente}' ,
						        '{$id_destino}' ,
						        '{$cantidad}' ,
						        '{$comentario}' ,
						         1 ,
						        '{$this->fecha_hora_hoy}', 
								'{$id_proyecto}', '{$valor}' )";

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
										$id_user 		= null,
										$cantidad 		= null,
										$tipo_documento = null,
										$num_documento  = null,
										$archivo 		= null, 
										$valor 			= null,
										$guia_despacho  = null  )
{
	$insert=" INSERT INTO ingresos( codigo       ,
	 								id_inventario,
	 								insumo       ,
	 								id_proveedor ,
	 								id_user      ,
									cantidad , fecha, tipo_documento, num_documento,archivo, valor,
									guia_despacho , id_estado    )
						 VALUES(  '{$codigo}'  ,
						          '{$id_inventario}'  ,
						          '{$insumo}'  ,
						          '{$id_proveedor }'  ,
						          '{$id_user}',
								  '{$cantidad}' , '{$this->fecha_hora_hoy}', '{$tipo_documento}','{$num_documento}','{$archivo}', '{$valor}',
								  '{$guia_despacho}' ,1 )";

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

	$ssql = "SELECT * FROM proveedores WHERE id_estado = 1 {$resto} ORDER BY descripcion";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function procesaProveedores( $nombre_proveedor = null, $codigo_proveedor = null,$rut_proveedor = null,  $id = null )
{
	if( $id )
	{
		$update = "UPDATE proveedores SET descripcion='{$nombre_proveedor}',
					codigo_proveedor = '{$codigo_proveedor}',rut_proveedor = '{$rut_proveedor}'	
				  WHERE id={$id}";

		if( $this->sql->update( $update ) )
					return true;
		else 	return false;
	}
	else{

		$insert = "INSERT INTO proveedores( descripcion, id_estado, codigo_proveedor,rut_proveedor )
							VALUES ( '{$nombre_proveedor}', 1 , '{$codigo_proveedor}' ,'{$rut_proveedor}')";

		if( $this->sql->insert( $insert ) )
					return true;
		else 	return false;

	}
}

public function eliminaServicios( $id=null )
{
	$delete = "UPDATE servicios SET id_estado=2 WHERE id={$id}";

	if( $this->sql->update( $delete ) )
			return true;
	else 	return false;	
}


public function listaServicios( $id = null )
{
	$resto = "";

	if( $id )
		$resto = " AND id = {$id}";

	$ssql = "SELECT * FROM servicios WHERE id_estado = 1 {$resto} ORDER BY descripcion";

	$arr['sql'] 	= $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
}

public function procesaServicios( $nombre_proveedor = null, $codigo_proveedor = null,$rut_proveedor = null,  $id = null )
{
	if( $id )
	{
		$update = "UPDATE servicios SET descripcion='{$nombre_proveedor}',
					codigo_proveedor = '{$codigo_proveedor}',rut_proveedor = '{$rut_proveedor}'	
				  WHERE id={$id}";

		if( $this->sql->update( $update ) )
					return true;
		else 	return false;
	}
	else{

		$insert = "INSERT INTO servicios( descripcion, id_estado, codigo_proveedor,rut_proveedor )
							VALUES ( '{$nombre_proveedor}', 1 , '{$codigo_proveedor}' ,'{$rut_proveedor}')";

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


public function listaElementos( $id 				= null,
								$buscar_action 		= null,
								$id_ubicacion   	= null,
								$id_tipo 			= null,
								$id_sububicacion 	= null,
								$id_sub_tipo 		= null,
								$codigo				= null,
								$nombre 			= null 	)
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
							 elemento.codigo_final,
							 elemento.precio,
							 elemento.id_proveedor,
							 ubicacion.descripcion AS nombreUbicacion,
							 sub_ubicacion.descripcion AS nombreSubUbicacion,
							 tipo.descripcion AS nombreTipo,
							 sub_tipo.descripcion AS nombreSubTipo,
							 sub_tipo.codigo AS codigoFamilia,
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

			if( is_null( $codigo ) ||  $codigo=='' )
			{ 		$code = " AND elemento.codigo LIKE '%{$codigo}%'"; }
			else{   $code = " AND elemento.codigo_final LIKE '%{$codigo}%'"; }	


	
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
						elemento.codigo_final,
						elemento.precio,
						elemento.id_proveedor,
						ubicacion.descripcion AS nombreUbicacion,
						sub_ubicacion.descripcion AS nombreSubUbicacion,
						tipo.descripcion AS nombreTipo,
						sub_tipo.descripcion AS nombreSubTipo,
						sub_tipo.codigo AS codigoFamilia,
						unidades.descripcion AS nombreUnidad
					FROM elemento
					left JOIN ubicacion ON ( ubicacion.id = elemento.id_ubicacion )
					left JOIN sub_ubicacion ON ( sub_ubicacion.id = elemento.id_sububicacion )
					left JOIN tipo ON ( tipo.id = elemento.id_tipo )
					left JOIN sub_tipo ON ( sub_tipo.id = elemento.id_sub_tipo )
					left JOIN unidades ON ( unidades.id = elemento.id_unidad )
					WHERE
					elemento.id_estado = 1
					AND elemento.id_ubicacion LIKE '%{$id_ubicacion}%'
					AND elemento.id_tipo LIKE '%{$id_tipo}%'
					AND elemento.id_sububicacion LIKE '%{$id_sububicacion}%'
					{$code}
					AND elemento.id_sub_tipo LIKE '%{$id_sub_tipo}%'
					AND elemento.nombre LIKE '%{$nombre}%'
					ORDER BY  elemento.nombre
					";
	
 		 }

		$arr['sql'] 	= $ssql;
		$arr['process'] = $this->sql->select( $ssql );
		$arr['total-recs'] = count( $arr['process'] );

return $arr;
}

public function procesaElmento(   	$nombre             = null,
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
									$codigo_final       = null,
									$id_proveedor		= null,
									$valor 				= null,	
									$id_afirmacion 		= null,
									$id                 = null )
{
	$arr = $this::listaCodigoFinal( $codigo_final );

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
								id_unidad    	   = '{$id_unidad}' ,
			                    imagen             = '{$imagen}',								
								id_proveedor 	   = '{$id_proveedor}',
								precio 			   = '{$valor}',
								id_afirmacion      = '{$id_afirmacion}',		 			
								fecha_modificacion = '{$this->fecha_hora_hoy}'
			WHERE   id = {$id} ";
				
				if( $this->sql->update( $update ) )
						{
							if( $this::ingresaMirror(   $nombre            ,
														$descripcion       ,
														$stock             ,
														$stock_minimo      ,
														$id_usuario        ,
														$id_tipo           ,
														$id_sub_tipo       ,
														$id_ubicacion      ,
														$id_sububicacion   ,
														$codigo_final            ,
														$imagen            ,
														$id_unidad         ,
														$codigo_final      ,
														$id_proveedor	,														
														$valor ,
														$id_afirmacion				 ) )
								 { $ok = true; }
							else { $ok = false;} 
													
							return true;
						
						}
				else 	{return false;}


			//	return $update;
				
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
							  id_unidad, codigo_final,
							  id_proveedor, precio, id_afirmacion  )
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
							'{$id_unidad}', '{$codigo_final}',
							'{$id_proveedor}', '{$valor}', '{$id_afirmacion}'
							)";

		if( $arr['total-recs'] != 0 )
			return false;
		else{
			if( $this->sql->insert( $insert ) )
					{
						if( $this::ingresaMirror(   $nombre            ,
													$descripcion       ,
													$stock             ,
													$stock_minimo      ,
													$id_usuario        ,
													$id_tipo           ,
													$id_sub_tipo       ,
													$id_ubicacion      ,
													$id_sububicacion   ,
													$codigo_final            ,
													$imagen            ,
													$id_unidad         ,
													$codigo_final      ,
													$id_proveedor	,
													$valor, 		
													$id_afirmacion		 ) )
						     { $ok = true; }
						else { $ok = false;}
						
						
						
						return true;}
			else  	return false;
		}
	}
}

public function ingresaMirror(  $nombre             = null,
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
								$codigo_final       = null,
								$id_proveedor		= null,
								$valor 				= null,
								$id_afirmacion		=null )
{

	$insert = "INSERT INTO mirror( nombre ,
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
							  id_unidad, codigo_final,
							  id_proveedor, precio , id_afirmacion )
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
							'{$id_unidad}', '{$codigo_final}',
							'{$id_proveedor}', '{$valor}', '{$id_afirmacion}'
							)";

		if( $this->sql->insert( $insert ) )
							return true;
					else  	return false;

		//return $insert;

}

public function listaMirror( $codigo_final = null )
{
	$ssql = "SELECT
					mirror.id                 ,
					mirror.nombre             ,
					mirror.descripcion        ,
					mirror.fecha_adquisicion  ,
					mirror.fecha_modificacion ,
					mirror.stock              ,
					mirror.stock_minimo       ,
					mirror.id_usuario         ,
					mirror.id_tipo            ,
					mirror.id_sub_tipo        ,
					mirror.id_estado          ,
					mirror.id_ubicacion       ,
					mirror.id_sububicacion    ,
					mirror.codigo             ,
					mirror.imagen             ,
					mirror.id_unidad          ,
					mirror.tipo_insumo        ,
					mirror.venta              ,
					mirror.precio             ,
					mirror.codigo_final       ,
					mirror.id_proveedor   ,
					proveedores.descripcion AS proveedor,
					usuario.nombres,
					usuario.apaterno,
					usuario.amaterno,
					sub_tipo.descripcion AS familia,
					afirmacion.descripcion AS operativo
				FROM mirror
				INNER JOIN proveedores ON ( proveedores.id  = mirror.id_proveedor )
				INNER JOIN usuario ON ( usuario.id = mirror.id_usuario )
				INNER JOIN sub_tipo ON ( sub_tipo.id = mirror.id_sub_tipo )
				LEFT JOIN afirmacion ON ( afirmacion.id = mirror.id_afirmacion )

				WHERE mirror.codigo_final = {$codigo_final}
				ORDER BY mirror.fecha_modificacion DESC

				";
				

	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] ); 

	return $arr;
}



public function listaCodigoFinal( $codigo_final = null )
{
	$ssql = " SELECT codigo_final FROM elemento WHERE codigo_final = '{$codigo_final}' ";
	
	$arr['sql'] = $ssql;
	$arr['process'] = $this->sql->select( $ssql );
	$arr['total-recs'] = count( $arr['process'] );

	return $arr;
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
					accesos.fecha ,
					accesos.sesion       ,
					accesos.ip           ,
					usuario.nombres      ,
					usuario.apaterno ,
					usuario.amaterno
			FROM accesos
			INNER JOIN usuario ON ( usuario.id = accesos.id_usuario )
			order by accesos.fecha DESC";


	$arr['sql'] 		= $ssql;
	$arr['process'] 	= $this->sql->select( $ssql );
	$arr['total-recs']  = count( $arr['process'] );
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
							sub_tipo.codigo,
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

public function procesaSubTipo( $descripcion = null, $id_tipo=null,
								$codigo = null,  $id=null )
{
	if( $id  )
		{
			$update = "UPDATE sub_tipo SET descripcion='{$descripcion}', id_tipo={$id_tipo},
			           codigo={$codigo}   WHERE id={$id}";
			if( $this->sql->update( $update ) )
				return true;
			else 	return false;
		}
	else{

		$insert = "INSERT INTO sub_tipo( descripcion,id_tipo,codigo,id_estado ) VALUES( '{$descripcion}','{$id_tipo}',{$codigo} ,1)";
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
