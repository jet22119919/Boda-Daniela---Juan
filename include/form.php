<?php

/*-------------------------------------------------

	Form Processor Plugin
	by SemiColonWeb

---------------------------------------------------*/


/*-------------------------------------------------
	PHPMailer Initialization Files
---------------------------------------------------*/

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'phpmailer/src/Exception.php';
require 'phpmailer/src/PHPMailer.php';
require 'phpmailer/src/SMTP.php';

header('Content-Type: application/json');
/*-------------------------------------------------
	Form Messages
---------------------------------------------------*/
$dataArray = json_decode(file_get_contents('php://input'), true);
$message = array(

	'success'			=> 'Hemos recibido <strong>con éxito</strong> su mensaje y nos pondremos en contacto con usted lo antes posible.',
	'error'				=> 'El correo electrónico <strong>no se pudo</strong> enviar debido a un error inesperado. Por favor, inténtelo de nuevo más tarde.',
	'error_bot'			=> '¡Bot detectado! ¡No se pudo procesar el formulario! ¡Inténtalo de nuevo!',
	'error_unexpected'	=> 'Se produjo un <strong>error inesperado</strong>. Por favor, inténtelo de nuevo más tarde.',
	'captcha_invalid'	=> '¡Captcha no validado! ¡Inténtalo de nuevo!',
	'captcha_error'		=> '¡Captcha no enviado! Inténtalo de nuevo.'

);

//validate recaptcha v3
if(!isset($dataArray['recaptcha-code'])){
    http_response_code ( 400 );
     $arrResult = array ('response'=>'error', 'message' => 'Falta recaptcha');
         echo json_encode($arrResult);
         return false;
 }
 
 if(!validateRecaptchaCode($dataArray['recaptcha-code'])){
     http_response_code ( 400);
     $arrResult = ['response'=>'error', 'message' => 'No eres humano.'];
         echo json_encode($arrResult);
         return false;
 }

 
if (isset($dataArray)){
    $name = $dataArray['contactform_name'];
    $email = $dataArray['contactform_email'];
    $phone = $dataArray['contactform_phone'];
    $message_form = $dataArray['contactform_message'];

    //$phone = isset($dataArray['contactform_phone']) ? $dataArray['contactform_phone'] : '';

    if(!isset($name) || !isset($phone) || !isset($email)){
        http_response_code ( 400 );
        $arrResult = array ('alert'=>'error', 'message' => 'Debes llenar todos los campos requeridos.');
        echo json_encode($arrResult);
        exit;
    }

 /*   http_response_code(200);
	$arrResult = array ('message' => 'Hemos recibido tus datos, en breve nos comunicaremos contigo');
	echo json_encode($arrResult);
    return;   
*/

    
    $mail = new PHPMailer();

    $mail->IsSMTP(); // set mailer to use SMTP

    $mail -> CharSet = "UTF-8";
    $mail->Host = "mail.innovacionambientalmx.com";  // specify main and backup server
    $mail->SMTPAuth = true;     // turn on SMTP authentication
    $mail->Username = "contacto@innovacionambientalmx.com";  // SMTP username
    $mail->Password = '&uiX55n~9xbs'; // SMTP password
    $mail->Port = 465;
    $mail->SMTPSecure = "ssl";


    //Set who the message is to be sent from
    $mail->setFrom("mail.innovacionambientalmx.com", 'Formulario de contacto');
    $mail->addAddress("info@innovacionambientalmx.com", 'Formulario de contacto');
    $mail->AddBCC("info@innovacionambientalmx.com", 'Formulario de contacto');


    // set word wrap to 50 characters
    $mail->WordWrap = 50;
    // set email format to HTML
    $mail->IsHTML(true);
    $mail->Subject = "$name te ha enviado un mensaje desde el formulario de tu pagina web.";
    $mail->Body    = "Nuevo mensaje desde tu sitio web touristiando.com <br/>"."Aqu&iacute; est&aacute;n los detalles:<br/><br/>
                        <b>Nombre</b>:$name<br/>
                        <b>Correo</b>:$email<br/>
                        <b>Celular</b>:$phone<br/>	
                        <b>Mensaje</b>:$message_form<br/>";
    

    if( !$mail->Send() ){
       // echo '{ "alert": "error", "message": "' . $mail->ErrorInfo . '" }'; 
        echo '{ "alert": "error", "message": "' . $message['error_unexpected'] . '" }'; 
        exit;
    }else{
        echo '{ "alert": "success", "message": "' . $message['success'] . '" }';
        exit;
    }

    
}
/*
* Send recaptcha code to google validation
* @param $code string 
* @return bool
*/
function validateRecaptchaCode($code){
   $recaptcha_api_url="https://www.google.com/recaptcha/api/siteverify";
   $secret = "6LfEMD8pAAAAAGbc2D5pCOUcwTQ6X3-mLfByljQN";
  // abrimos la sesión cURL
  $request = curl_init();

  // definimos la URL a la que hacemos la petición
  curl_setopt($request, CURLOPT_URL, $recaptcha_api_url);
  // indicamos el tipo de petición: POST
  curl_setopt($request, CURLOPT_POST, TRUE);
  // definimos cada uno de los parámetros
  curl_setopt($request, CURLOPT_POSTFIELDS, "secret=$secret&response=$code");
  // recibimos la respuesta y la guardamos en una variable
  curl_setopt($request, CURLOPT_RETURNTRANSFER, true);
  $remote_server_output = curl_exec($request);
  // cerramos la sesión cURL
  curl_close ($request);
  //validate request response for valid recaptcha token 
  
  try{
     $response = json_decode($remote_server_output);
     return $response->success && $response->score > 0.5;
  } catch (Exception $error) {
     //TODO: return error message $error->getMessage()
     return false;
  }
  return false;
}

?>