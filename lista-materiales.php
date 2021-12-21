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
	include("config.php");
	
    if( $_SESSION['autenticado'] == 1   )
    {

    require_once( "fpdf/fpdf.php" );
    
    class PDF extends FPDF{

        private $image;
        private $consultas;
        private $codigo;

        public function Header()
        {
            $this->codigo = base64_decode( $_GET['codigo']);
            $this->imagen  = "img/logo-socma-2.png"; 

            $oConf              = new config();
            $cfg                = $oConf->getConfig();

            $db                 = new mysqldb( $cfg['base']['dbhost'],
                                               $cfg['base']['dbuser'],
                                               $cfg['base']['dbpass'],
                                               $cfg['base']['dbdata'] );
  
           $this->consultas 	= new querys( $db );

            $this->setFont('Arial','B',15);
            $this->Cell(10);
            $this->Cell(20,7, utf8_decode( 'LISTAS DE MATERIALES - DYT SOCMA LIMITADA' ),0,0);
            $this->Ln();
            $this->Ln();
        }

        public function cuerpo()
        {
            // $this->Image( $this->imagen, 35,15, 45  );
            
            $crr = $this->consultas->listaEncabezadoMaterial( null,$this->codigo );
            
            $this->setFont('Arial','B',13);
            $this->Cell(10);
            $this->Cell(20,7, utf8_decode( '1. ENCABEZADO' ),0,0);
            
     


            foreach ($crr['process'] as $key => $val) 
            {
                    $this->setFont('Arial','B',10);
                    $this->Ln(9);
                    $this->Cell(10);
                    $this->Cell(20,7, utf8_decode( 'Título' ),1,0); 
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['titulo'] ) ),1,0); 
                    $this->setFont('Arial','B',10);
                    $this->Cell(20,7, utf8_decode( 'Código' ),1,0);
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $this->codigo ) ),1,0); 
                    $this->setFont('Arial','B',10);
                    $this->Cell(40,7, utf8_decode( 'Centro / Lugar' ),1,0);
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['nombreCentro'] ) ),1,0);
                    $this->Ln(); 
                    $this->Cell(10);
                    $this->setFont('Arial','B',10);
                    $this->Cell(20,7, utf8_decode( '# Módulos' ),1,0); 
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['modulos'] ) ),1,0); 
                    $this->setFont('Arial','B',10);
                    $this->Cell(20,7, utf8_decode( '# Jaulas' ),1,0); 
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['jaulas'] ) ),1,0); 
                    $this->setFont('Arial','B',10);
                    $this->Cell(40,7, utf8_decode( 'Calidad' ),1,0); 
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['calidad'] ) ),1,0); 
                    $this->Ln(); 
                    $this->Cell(10);
                    $this->setFont('Arial','B',10);
                    $this->Cell(20,7, utf8_decode( 'Cam/Jaula' ),1,0); 
                    $this->setFont('Arial','',10);
                    $this->Cell(70,7, utf8_decode(  strtoupper( $val['camaras_x_jaula'] ) ),1,0); 

           } 
           $this->Ln();
           $this->Ln();
           $this->setFont('Arial','B',13);
           $this->Cell(10);
           $this->Cell(20,7, utf8_decode( '2. DETALLE' ),0,0);
           
        
         

            $this->Ln(8);
            $this->setFont('Arial','B',10);
            $arr   = $this->consultas->listarItemProductosMateriales( $this->codigo );
            $final = 0;

            foreach ($arr['process'] as $key => $value) {
                $this->Cell(10);
                $this->Cell(20,7, utf8_decode( $value['nombreItemProducto'] ),0,0);
                $this->Ln();
                $this->Cell(10);
                $this->Cell(15,7,'Req.',1,0);
                $this->Cell(20,7,'U. Medida',1,0);
                $this->Cell(110,7,'Insumo',1,0);
                $this->Cell(70,7,'Proveedor',1,0);
                $this->Cell(20,7,'Stock',1,0);
                $this->Cell(20,7,'A Comprar',1,0);
                $this->Cell(35,7,'Precio Unitario',1,0);
                $this->Cell(35,7,'Total',1,0);
                $this->Ln();
                

                //vamos a hacer algo feo, pero efectivo
                $brr = $this->consultas->listaMateriales( $this->codigo,null,$value['id_item'] );
                
                $suma = 0;
                foreach ($brr['process'] as $key => $v) 
                {
                    $total = $v['a_comprar']*$v['precio_unitario'];

                    $this->setFont('Arial','',10);
                    $this->Cell(10);
                    $this->Cell(15,7,$v['requerimiento'],1,0);
                    $this->Cell(20,7,$v['nombreUnidadMedida'],1,0);
                    $this->Cell(110,7,utf8_decode(  $v['nombreProducto'] ),1,0);
                    $this->Cell(70,7,utf8_decode(  $v['nombreProveedor'] ),1,0);
                    $this->Cell(20,7,$v['stock'],1,0);
                    $this->Cell(20,7,$this::separa_miles( $v['a_comprar'] ),1,0);
                    $this->Cell(35,7,' $ '.$this::separa_miles( $v['precio_unitario'] ),1,0);
                    $this->Cell(35,7,' $ '.$this::separa_miles(  $total ) ,1,0);
                    $this->Ln();

                    $suma = $suma + $total;
                }
                $this->setFont('Arial','B',10);
                $this->Cell(10);
                $this->Cell(290,7,'Sub Total',1,0);
                $this->Cell(35,7,' $ '.$this::separa_miles(  $suma ) ,1,0);
                $this->Ln();$this->Ln();
                $final = $final + $suma;    

            }

            
            $this->setFont('Arial','B',13);
            $this->Cell(10);
            $this->Cell(290,7,'Total',1,0);
            $this->Cell(35,7,' $ '.$this::separa_miles(  $final ) ,1,0);
            $this->Ln();$this->Ln();

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

    //main()
    $pdf = new PDF();
    $pdf->AliasNbPages();
    $pdf->addPage('L','Legal');
    $pdf->cuerpo();
    $pdf->Output();

}else{

    echo "FUERA DE SESION O NO LOGUEADO";

}
?>