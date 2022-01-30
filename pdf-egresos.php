<?php
	header('Cache-Control: no cache');
	//session_cache_limiter('public'); // works too session_start();
	session_cache_limiter('private, must-revalidate');
	session_cache_expire(60);
	define('DURACION_SESION','7200'); //2 horas
	ini_set("session.cookie_lifetime",DURACION_SESION);
	ini_set("session.gc_maxlifetime",DURACION_SESION);
	ini_set("session.save_path","/tmp");
	session_cache_expire(DURACION_SESION);
	session_start();
	session_regenerate_id(true);

	include("class/mysqldb.class.php");
	include("class/querys.class.php");
	include("class/template.class.php");
	include("class/codifica.class.php");
	include("class/menu.class.php");
	include("class/principal.class.php");
	include("class/select.class.php");
	include("class/menu-admin.class.php");
	include("class/myIp.class.php");
	include("class/seguridad.class.php");
	include("class/utilesmodulo.class.php");
    include("class/guia-despacho-egreso.class.php");
	include("config.php");
    

if ($_SESSION['autenticado'] == 1 )
{
	require_once( "fpdf/fpdf.php" );

	class PDF extends FPDF{
		//Array ( [num_guia] => 0101203 [token] => 20220127090235 [rs] => test-01 [fecha] => 2022-01-27 [egreso] => 1 ) some:::3
		private $num_guia;
		private $token;
		private $rs;
		private $fecha;

		public function Header()
		{
			$oConf    = new config();
			$cfg      = $oConf->getConfig();
		  	$db       = new mysqldb( 	$cfg['base']['dbhost'],
										$cfg['base']['dbuser'],
										$cfg['base']['dbpass'],
										$cfg['base']['dbdata'] );
  
		  	$this->consultas = new querys( $db );
			$this->num_guia = $_GET['num_guia'];		
			$this->token 	= $_GET['token'];		
			$this->rs 		= $_GET['rs'];		
			$this->fecha 	= $_GET['fecha'];		
			

			$this->setFont('Arial','',8);
			$this->Cell(45,4, utf8_decode( 'Módulo desarrollado por DYT SOCMA Ltda.' ),0,0);
			$this->Ln();
			$this->Cell(45,4, utf8_decode( '_______________________________________________________________________________________________________' ),0,0);
			$this->Ln(10);
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
		 * sacaCliente(): extrae el nombre del cliente
		 */
		private function sacaCliente()
		{
			$arr 		= $this->consultas->listaGuiaDespachoEgreso( $this->token );
			$cliente	= "";

			foreach ($arr['process'] as $key => $value) {
				$cliente .= $value['nombreCliente'];				
			}

			return $cliente;
		}


		/**
		 * cuerpo(): contenido del pdf
		 */
		public function cuerpo()
		{
			$i = 0;
			$ob_guia = new GuiaDespachoEgreso();
			$data    = $ob_guia->dataInsumosFromEgreso( $this->token );

			$this->setFont('Arial','B',15);
			$this->Cell(45,7, utf8_decode( 'DETALLE GUIA DE DESPACHO / RS' ),0,0);
			
			$this->Ln(10);
			$this->setFont('Arial','B',10);			
			$this->Cell(45,7, utf8_decode( 'GUIA DE DESPACHO :' ),0,0);
			$this->setFont('Arial','',10);
			$this->Cell(30,7,$this->num_guia,0,0);
			$this->setFont('Arial','B',10);			
			$this->Cell(10,7, utf8_decode( 'RS :' ),0,0);
			$this->setFont('Arial','',10);
			$this->Cell(50,7,$this->rs,0,0);
			$this->setFont('Arial','B',10);
			$this->Cell(15,7, utf8_decode( 'FECHA :' ),0,0);
			$this->setFont('Arial','',10);
			$this->Cell(40,7, $this::arreglaFechas(  $this->fecha ) ,0,0);
			$this->Ln();
			$this->setFont('Arial','B',10);			
			$this->Cell(45,7, utf8_decode( 'CLIENTE :' ),0,0);
			$this->setFont('Arial','',10);
			$this->Cell(50,7,$this->sacaCliente(),0,0);
			
			$this->Ln();
			$this->Ln();
			$this->setFont('Arial','B',11);
			$this->Cell(45,7, utf8_decode( 'LISTA DE INSUMOS' ),0,0);
			$this->Ln();
			
			$this->setFont('Arial','B',8);
			$this->Cell(6,6, utf8_decode('# '),1,0);
			$this->Cell(15,6, utf8_decode('Código '),1,0);
			$this->Cell(140,6, utf8_decode('Insumo / Producto '),1,0);
			$this->Cell(60,6, utf8_decode('Familia '),1,0);
			$this->Cell(20,6, utf8_decode('Stock Actual '),1,0);
			$this->Cell(15,6, utf8_decode('Cantidad'),1,0);
			$this->Ln();
			$this->setFont('Arial','',8);

			foreach ($data as $key => $value) {
				
				//$code .= $key."  ".$value['codigo_final']." ".$value['nombreInsumo']    ."<br>";
				$this->Cell(6,6, utf8_decode( $i+1 ),1,0);
				$this->Cell(15,6, utf8_decode($value['codigo_final']),1,0);
				$this->Cell(140,6, utf8_decode($value['nombreInsumo']),1,0);
				$this->Cell(60,6, utf8_decode($value['familia']),1,0);
				$this->Cell(20,6, utf8_decode($value['stock']),1,0);
				$this->Cell(15,6, utf8_decode($value['cantidad']),1,0);
				$this->Ln();

				$i++;
			}
		}

		// Pie de página
		function Footer()
		{
			// Posición: a 1,5 cm del final
			$this->SetY(-15);
			// Arial italic 8
			$this->SetFont('Arial','I',8);
			// Número de página
			$this->Cell(0,10, utf8_decode('Pág.').$this->PageNo().'/{nb}',0,0,'C');
		}
	}

	#main
	$pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->addPage('L','Letter');
    $pdf->cuerpo();
    $pdf->Output();
}
else{
	echo "FUERA DE SESION O NO LOGUEADO";
}
?>