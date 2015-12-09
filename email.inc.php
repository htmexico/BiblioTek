<?php

// Configuración de cuentas de email STMP 

unset($EMAIL);

global $EMAIL;

$EMAIL = new stdClass();

$EMAIL->smtp_debug = 1;
$EMAIL->smtp_host    = "smtp.mandrillapp.com";
$EMAIL->smtp_user    = "biblioteca@colegiobosques.edu.mx";
$EMAIL->smtp_pass    = "d7ryWqfbpNfa1aMAjY9seg";
$EMAIL->smtp_port	 = 587;

$EMAIL->image_background = "http://www.yourdomain.edu/images/logoFondoMail.jpg";
$EMAIL->font_default_css = "font-family:verdana,arial,helvetica; font-size:12px; font-color: black; ";

 ?>
