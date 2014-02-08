<?php

/*** 
  
 COLOCARLO en el subdominio del cliente
 SOLAMENTE al ejecutar instalaciones reales de clientes

 */

unset( $ACCESS_CFG );

$ACCESS_CFG->id_biblioteca    = 1;
$ACCESS_CFG->codigo_cuenta = "localges";

$ACCESS_CFG->banner = "images/banner_rapido.jpg";

// plantillas que serán buscadas en el directorio del cliente
$ACCESS_CFG->http_public_dir = "http://localhost/biblioges/";

?>