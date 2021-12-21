<?php

    include("class/mysqldb.class.php");
    include("class/querys.class.php");
    include("class/template.class.php");
    include("class/codifica.class.php");
    include("class/menu.class.php");
    include("class/principal.class.php");
    include("class/select.class.php");
    include("class/myIp.class.php");
    include("class/seguridad.class.php");
    include("class/utilesmodulo.class.php");
    include("class/guia-despacho-ingreso.class.php");
    include("class/guia-despacho-egreso.class.php");

    include("config.php");

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

    if( $_SESSION['autenticado'] == 1 )
    {

      header('Content-type: application/vnd.ms-excel;charset=iso-8859-15');
      header('Content-Disposition: attachment;filename=excel-guia-despacho-'.$_GET['num_guia'].'.xls');

      if( !isset( $_GET['egreso'] ) )
      {
        $guiaDespachoIngreso = new GuiaDespachoIngreso();        
        echo $guiaDespachoIngreso->tablaExcel( $_GET['token'],$_GET['num_guia'] );     
      }else{

        $ob_egreso = new GuiaDespachoEgreso();        
        echo $ob_egreso->tablaExcelEgreso( $_GET['num_guia'], $_GET['token'] );

      }
   
    }else{

      //print_r( $_SESSION );
      echo "<h1>NO ESTAS LOGUEADO!</h1>";

    }
?>