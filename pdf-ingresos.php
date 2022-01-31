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
    include("class/guia-despacho-ingreso.class.php");
	include("config.php");
    
    
    if( $_SESSION['autenticado'] == 1 )
    {
        //Array ( [num_guia] => 20211228081230 [token] => 20211228081230 [fecha] => 28-12-2021 [tipo_documento] => Articulo Nuevo [proveedor] => SOCMA LTDA )
        require_once( "fpdf/fpdf.php" );

        class PDF extends FPDF
        {
            private $num_guia;
            private $token;
            private $fecha;
            private $tipo_documento;
            private $proveedor;    

            public function Header()
            {
                $this->num_guia       = $_GET['num_guia'];
                $this->token          = $_GET['token'];
                $this->fecha          = $_GET['fecha'];
                $this->tipo_documento = $_GET['tipo_documento'];
                $this->proveedor      = $_GET['proveedor'];

                $this->setFont('Arial','',8);
                $this->Cell(45,4, utf8_decode( 'Módulo desarrollado por DYT SOCMA Ltda.' ),0,0);
                $this->Ln();
                $this->Cell(45,4, utf8_decode( '_______________________________________________________________________________________________________' ),0,0);
                $this->Ln(10);
            }

            public function cuerpo()
            {
                $i = 0;
                $ob_guia = new GuiaDespachoIngreso();
                $data    = $ob_guia->dataInsumos( $this->token );
                $suma    = 0;

                $this->setFont('Arial','B',15);
                $this->Cell(45,7, utf8_decode( 'DETALLE DOCUMENTO' ),0,0);
                
                $this->Ln(10);
                $this->setFont('Arial','B',10);			
                $this->Cell(45,7, utf8_decode( 'NUM. DOCUMENTO :' ),0,0);
                $this->setFont('Arial','',10);
                $this->Cell(30,7,$this->num_guia,0,0);
                $this->setFont('Arial','B',10);			
                $this->Cell(40,7, utf8_decode( 'TIPO DOCUMENTO :' ),0,0);
                $this->setFont('Arial','',10);
                $this->Cell(50,7,$this->tipo_documento,0,0);
                $this->setFont('Arial','B',10);
                $this->Cell(15,7, utf8_decode( 'FECHA :' ),0,0);
                $this->setFont('Arial','',10);
                $this->Cell(40,7, $this->fecha  ,0,0);
                $this->Ln();
                $this->setFont('Arial','B',10);			
                $this->Cell(45,7, utf8_decode( 'PROVEEDOR :' ),0,0);
                $this->setFont('Arial','',10);
                $this->Cell(50,7,$this->proveedor,0,0);                
                $this->Ln();
                $this->Ln();
                $this->setFont('Arial','B',11);
                $this->Cell(45,7, utf8_decode( 'LISTA DE INSUMOS' ),0,0);
                $this->Ln();

                $this->setFont('Arial','B',7);
                $this->Cell(6,6, utf8_decode('# '),1,0);
                $this->Cell(15,6, utf8_decode('Código '),1,0);
                $this->Cell(110,6, utf8_decode('Insumo / Producto '),1,0);
                $this->Cell(60,6, utf8_decode('Familia '),1,0);
                $this->Cell(10,6, utf8_decode('Stock '),1,0);
                $this->Cell(15,6, utf8_decode('Cantidad'),1,0);
                $this->Cell(20,6, utf8_decode('Unitario'),1,0);
                $this->Cell(20,6, utf8_decode('Total'),1,0);
                $this->Ln();
                $this->setFont('Arial','',7);

                foreach ($data as $key => $value) {
                    # code...

                    $total = $value['cantidad'] * $value['valor'];

                    $this->Cell(6,6, utf8_decode( $i+1 ),1,0);
                    $this->Cell(15,6, utf8_decode( $value['codigo_final']),1,0);
                    $this->Cell(110,6, utf8_decode($value['nombreInsumo']),1,0);
                    $this->Cell(60,6, utf8_decode($value['familia']),1,0);
                    $this->Cell(10,6, utf8_decode($value['stock']),1,0);
                    $this->Cell(15,6, utf8_decode($value['cantidad']),1,0);
                    $this->Cell(20,6, utf8_decode("$". $this::separa_miles( $value['valor']) ),1,0);
                    $this->Cell(20,6, utf8_decode("$". $this::separa_miles( $total) ),1,0);
                    $this->Ln();

                    $suma = $suma + $total;

                    $i++;
                }
                $this->setFont('Arial','B',7);
                $this->Cell(236,6, utf8_decode('VALOR TOTAL '),1,0);
                $this->setFont('Arial','',7);
                $this->Cell(20,6, utf8_decode("$". $this::separa_miles( $suma) ),1,0);
                $this->Ln();

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

    /**
  	  * separa_miles(), coloca separador de miles en una cadena de caracteres
  	  *
  	  * @param  String num
  	  * @return String
  	  */
      private function separa_miles($num=null){

        return @number_format($num, 0, '', '.');
      }

        }
        #main()    
        $pdf = new PDF();
        $pdf->AliasNbPages();
        $pdf->addPage('L','Letter');
        $pdf->cuerpo();
        $pdf->Output();

    }else{
        echo "NO LOGGED / OUT OF SESSION";
    }
?>