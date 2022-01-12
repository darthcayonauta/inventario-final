<?php
class mails
{
	/**
	 * @var String id_tipo_accion
	 * @var String mail
	 */

	private $template;
	private $ruta;
	private $id;
	private $contenido;
	private $mail_target;
	private $ruta_abs;

	function __construct( 	$mail_target	= null,
						  	$contenido 		= null )
	{
		$oConf    			= new config();
		$cfg      			= $oConf->getConfig();
	  	$this->template 	= new template();
   		$this->ruta     	= $cfg['base']['template'];		
		$this->contenido  	= $contenido;
		$this->mail_target  = $mail_target;
		
	}

	/**
	 * enviar(): envio de la data por mail
	 * @return boolean
	 */
	private function enviar()
	{
		$GUSER = 'claudio.guzman@socma.cl';
		$GPWD  = 'Guzman2020';

		global $error;
		$mail = new PHPMailer();  // create a new object
		$mail->IsSMTP(); // enable SMTP
		//$mail->SMTPDebug = 2;  // debugging: 1 = errors and messages, 2 = messages only
		$mail->SMTPAuth = true;  // authentication enabled
		$mail->SMTPSecure = 'tls'; // secure transfer enabled REQUIRED for GMail
		$mail->SMTPAutoTLS = false;
		$mail->Host = 'smtp.gmail.com';
		$mail->Port = 587;

		$mail->Username = $GUSER;
		$mail->Password = $GPWD;
		$mail->SetFrom($GUSER, 'Administrador de Sistemas');
		$mail->Subject = "Recepcion de mensaje ";
		$mail->isHTML(true);

		$mail->Body = $this->contenido;

		//$mail->AddAddress("claudio.guzman@socma.cl");
		$mail->AddAddress($this->mail_target);

		if($mail->Send()) {

			return true;

		} else {

			return false;
		}
	}



	/**
	 * getCode()
	 *
	 * @return boolean
	 */
	public function getCode(){

		return $this->enviar();
	}
}
?>
