<?php 
	/***
		03-nov-2011:  Se agreg— borrado de archivos
		
	 **/
	session_start();	
	include_once "../funcs.inc.php";
	include_once "../basic/bd.class.php";
	
	check_usuario_firmado(); 

	$id_filenum = read_param( "id_filenum", 0, 1 );
	$type = read_param( "type", "", 1 );
	$sp_action = read_param( "sp_action", "" );
	
	$filename = "";
	$real_filename = "";
	$mimetype = "";
	
	$db = new DB();
	
	if( $type == "catalogacion" )
	{
		$id_biblioteca = getsessionvar( "id_biblioteca" );
		$id_titulo = read_param( "id_titulo", 0, 0 );
		
		if( $id_filenum == 0 )
			$query = "SELECT * FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo " ;
		else
			$query = "SELECT * FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_FILENUM=$id_filenum " ;
			
		$query .= "ORDER BY ID_FILENUM ";
		
		if( $row = db_fetch_row( ($result_file = db_query( $query )) ) )
		{
			$filename	 	= $row['FILE_ENCRYPTED'];
			$real_filename  = $row['FILE_NAME'];
			$mimetype		= $row['FILE_MIMETYPE'];
		}
		
		free_dbquery( $result_file );
		
		if( $sp_action == "remove" )
		{
			$caller = read_param( "caller", "" );
			
			$db->ExecSQL( "DELETE FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_FILENUM=$id_filenum; " );	
			
			unlink( $filename );
			
			if( $caller == "catalogacion_files" )
				ges_redirect( "anls_catalogacion_files.php?id_titulo=$id_titulo" );
			else
				ges_redirect( "gral_vertitulo.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&flag=digital_file_deleted" );
		}
	}
	
	if(!file_exists($filename))
		die("El fichero no existe $real_filename.");

	//Gestionar peticiones especiales de IE si es necesario
	if($_SERVER['HTTP_USER_AGENT']=='contype')
	{
		Header('Content-Type: ' . $mimetype );
		exit;
	}
	
	Header('Content-Type: ' . $mimetype );
	Header('Content-Length: ' . filesize($filename));
 	Header('Content-Disposition: attachment; filename="' . $real_filename . '"'); // $name
	Header('Accept-Ranges: bytes');
	Header('Cache-control: private');
	Header('Pragma: private');
	Header('Title: ' . $real_filename );
		
	ReadFile($filename);

?>
