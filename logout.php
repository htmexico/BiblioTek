<?php
  session_start();
  include("funcs.inc.php");   
  
  if( isset($_SESSION[ "id_biblioteca" ]) )
  { $id_lib = $_SESSION[ "id_biblioteca" ]; } 
  
   unset( $_SESSION[ "id_biblioteca" ] ); 
   unset( $_SESSION[ "id_red" ] ); 
   unset( $_SESSION[ "id_usuario" ] ); 
   
   unset( $_SESSION[ "last_url" ] );    
   
   unset( $_SESSION[ "usuario" ] ); 
   unset( $_SESSION[ "nombre_usuario" ] ); 
   unset( $_SESSION[ "biblio_firmado" ] ); 
   
   unset( $_SESSION[ "skin" ] ); 
   unset( $_SESSION[ "file_banner" ] ); 
   unset( $_SESSION[ "language_pref" ] ); 
   
   unset( $_SESSION[ "personal_skin" ] );
   
   unset( $_SESSION[ "empleado" ] ); 
   
   unset( $_SESSION[ "pais" ] ); 
   unset( $_SESSION[ "language" ] ); 
   
   unset( $_SESSION[ "usar_popups_transactions" ] );

   unset( $_SESSION[ "init_params" ] ); 

	// en condiciones normales debe apuntar a index.php
	$url = "main.php";
	
	if( isset($id_lib) )
	{
	    unset( $_SESSION["id_lib"] );

		$url .= "?init=1&id_lib=" . $id_lib;
	}
	
	ges_redirect( $url );

?>