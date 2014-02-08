<?php
/*****
 **/
 // Archivo de configuración global de la aplicación
 // no debe incluir ninguna cosa personalizada para ningun cliente

	setsessionvar( "server_public_url", "http://vaiojels" );  // quitar slash al final

	setsessionvar( "mp3_in_flash", true ); 

	setsessionvar( "user_header302_relocation", false );   // ajusta parametros al hacer relocations
														// aparentemente algunas versiones de PHP no lo necesita

	if( isset( $_SERVER["HTTP_HOST"] ) )
	{ 
		$http_base_dir = dirname("http://" . $_SERVER["HTTP_HOST"] . $_SERVER['PHP_SELF']);
		if( strpos( $http_base_dir, "/phps" ) != 0 )  $http_base_dir = substr( $http_base_dir, 0, strlen($http_base_dir)-5 );
		if( strpos( $http_base_dir, "/phps/" ) != 0 )  $http_base_dir = substr( $http_base_dir, 0, strlen($http_base_dir)-6 );
		$http_base_dir .= "/";
		setsessionvar("http_base_dir", $http_base_dir );
		 
		$local_base_dir = dirname( $_SERVER['SCRIPT_FILENAME'] );
		if( strpos( $local_base_dir, "/phps" ) != 0 )  $local_base_dir = substr( $local_base_dir, 0, strlen($local_base_dir)-5 );
		if( strpos( $local_base_dir, "/phps/" ) != 0 )  $local_base_dir = substr( $local_base_dir, 0, strlen($local_base_dir)-6 );
		 
		$local_base_dir .= "/";
		setsessionvar("local_base_dir", $local_base_dir );

	}

?>