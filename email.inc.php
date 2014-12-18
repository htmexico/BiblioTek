<?php

// Configuración de cuentas de email STMP 

unset($EMAIL);

global $EMAIL;

$EMAIL->smtp_host    = "mail.yourdomain.edu";
$EMAIL->smtp_user    = "service@yourdomain.edu";
$EMAIL->smtp_pass    = "";
$EMAIL->smtp_port	 = 25;

$EMAIL->image_background = "http://www.yourdomain.edu/images/logoFondoMail.jpg";
$EMAIL->font_default_css = "font-family:verdana,arial,helvetica; font-size:12px; font-color: black; ";

 ?>