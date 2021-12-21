<?php
  //$home = getcwd();
  $home = "/home/inventario-v2/public_html";

  require_once("{$home}/phpmailer/PHPMailerAutoload.php");
  require_once("{$home}/class/mails.class.php");
  require_once("{$home}/class/codifica.class.php");
  require_once("{$home}/class/mysqldb.class.php" );
  require_once("{$home}/class/querys.class.php" );
  require_once("{$home}/class/template.class.php" );
  require_once("{$home}/class/inventario.class.php" );
  require_once("{$home}/config.php");

  $ob_inventario = new Inventario();
  echo $ob_inventario->criticos();

  echo getcwd();

 ?>
