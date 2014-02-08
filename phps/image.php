<?php
  session_start();
  
	/*******
	  Historial de Cambios
	  
	  23 mar 2009: Se crea el archivo PHP (originalmente como enlace a marc.php para mostrar PORTADAS)
				   Se incorpora en conjunto con la clase ibaseblob.class.php (WebGES)
	  17 nov 2009: Se implementa bd.class.php
     */  

  include "../basic/bd.class.php";

  $id_biblioteca = $_GET["id_biblioteca"];
  $tipoimagen = $_GET["tipoimagen"];
  
  $db = new DB();
  
  if ( $tipoimagen == 'PORTADA' )
  {
	$id_titulo = $_GET["id_titulo"];
	
    $db->Open( "SELECT PORTADA, PORTADA_MIMETYPE FROM acervo_titulos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;" );
  
    if ( $db->NextRow() )
    {
		$image = $db->GetBLOB( $db->row["PORTADA"] ); // $bl_img->data;
		$mimetype = $db->row["PORTADA_MIMETYPE"];

		if( $mimetype == "" ) $mimetype = "image/jpeg";
		   
        Header( "Content-type:" . $mimetype );
        print $image;
    }
	
	$db->Close();
  }
  elseif ( $tipoimagen == 'CONTRAPORTADA' )
  {
  	$id_titulo = $_GET["id_titulo"];

    $db->Open( "SELECT CONTRAPORTADA, CONTRAPORTADA_MIMETYPE FROM acervo_titulos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo;" );
  
    if ( $db->NextRow() )
    {
		$image = $db->GetBLOB( $db->row["CONTRAPORTADA"] );

		$mimetype = $db->row["CONTRAPORTADA_MIMETYPE"];		
		if( $mimetype == "" ) $mimetype = "image/jpeg";

	    Header ("Content-type:" . $mimetype);
        print $image;
		
    }
	
	$db->Close();
  }  
  elseif ( $tipoimagen == 'ALUMNO' )
  {
  	$numeroalumno = $_GET["numeroalumno"];

    $db->Open( "SELECT FOTOGRAFIA FROM alumnos WHERE ID_ESCUELA=$escuela and NUMEROALUMNO=$numeroalumno; " );
  
    $result = db_query ( $sql ) or db_die();

    if ( $row = db_fetch_row( $result ) )
    {
		$image = $db->GetBLOB( $db->row["FOTOGRAFIA"] );
		
		header("Content-type: image/jpeg"); 
		header("Cache-Control: public"); 
		header("Content-Disposition: inline"); 
		header("Accept-Ranges: bytes"); 
		
		print $image;
    }
	
	$db->Close();
  }

  $db->destroy();

?> 
