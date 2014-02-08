<?php 
  session_start();
  
	/*******
	  Historial de Cambios
	  
	  Heredado de WebGES, se hacen adaptaciones
	  
	  23 jun 2009: Se adapta el archivo
	  
	  PENDIENTES: colocar un archivo language compartido con upload_complete.php
	  
     */  

  include("../funcs.inc.php");
  include "../basic/bd.class.php";
  
  $filename = "";
  
  if( count($_FILES) == 0 )
  {
	  echo "<head><title>Error anexando archivo</title>";
	  echo " <link href='../ee.css' type=text/css rel=stylesheet>";
	  echo	"</head>";
	  echo "<body><center><br><H3>Ocurrió un error al subir el archivo, es posible que el archivo exceda el tamaño permitido.</H3></center></body>";
	   
	  echo "\n<center><br><br>";
      echo "\n<a href='javascript:window.history.back();'><b>Regresar</b></a></center>";
	  exit;
  }
  
  $filename   = $_FILES['file_to_upload']['name'];
  $uploadtype = $_POST['type'];
  $filesize   = $_FILES['file_to_upload']['size'];

  $pathcopyto = getsessionvar("local_base_dir") . "files/";

  if( $uploadtype == "catalogacion" )
    $pathcopyto .= "digital_files/";

  $notes = "";
  if(isset( $_POST['notes'] ) )
    $notes = $_POST['notes'];
  
  if( is_uploaded_file($_FILES['file_to_upload']['tmp_name']) )
  {
    $id_biblioteca = getsessionvar("id_biblioteca");	
	$subido = current_dbdate();
	
	if( $uploadtype == "catalogacion" )
	{
		$id_titulo = read_param( "id_titulo", "", 1 ); // fails if not present
		
		// --- BEGIN --- OBTENER EL ID_FILE CORRESPONDIENTE
		$id_filenum = 0;
		
		$queryidfile = "SELECT MAX(ID_FILENUM) as MAXIMO FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo ";
		$resultidfile = db_query( $queryidfile );

		if( $rowidfile = db_fetch_row($resultidfile) )
		   $id_filenum = $rowidfile["MAXIMO"] + 1;

		free_dbquery( $resultidfile );
		// --- END ---   OBTENER EL ID_FILENUM CORRESPONDIENTE  
		
		$str_idfile = sprintf( "%8d", $id_filenum );
		$str_idfile = str_replace( " ", "0", $str_idfile );  // sustituir los blancos con CEROS
		
		// calcular nombre encriptado
		$newfile = $_FILES['file_to_upload']['tmp_name'];
		$newfile =  $pathcopyto . md5( $str_idfile . "_" . rand(100,20000) . "_" . $subido . "_" . $newfile . "_" ) . ".arch";

		// mover el archivo tmp al real
		if (move_uploaded_file( $_FILES['file_to_upload']['tmp_name'], $newfile ) ) 
		{	   	   
		   chmod( $newfile, 0755 );  // dar privilegios de consulta		
			// insertar en db
			$query1 = "INSERT INTO acervo_archivos (ID_BIBLIOTECA, ID_TITULO, ID_FILENUM, FILE_MIMETYPE, FILE_ENCRYPTED, FILE_NAME, FILE_SIZE, " .
					  "  FILE_DATE_UPLOADED, UPLOADED_BY, FILE_ZIPPED, NOTAS )";
			$query2 = "VALUES ($id_biblioteca, $id_titulo, $id_filenum, '" . $_FILES['file_to_upload']['type'] . "', '$newfile', '$filename', $filesize, " .
					  " '$subido', '" . getsessionvar( "usuario" ) . "', 'N', '$notes' ) ";
			  
			db_query( "$query1 $query2" );  // execute query
			
			db_query( "UPDATE acervo_titulos SET DIGITAL='S' WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo; " );  // execute query

			ges_redirect( "Location:upload_completed.php?filename=$filename&filetype=" . $_FILES['file_to_upload']['type'] . "&url=" . $_POST["url"] );
		}
		
	}
	
  } 
  else 
  {
	  echo "<head><title>Archivo anexado</title>";
	  echo " <link href='../ee.css' type=text/css rel=stylesheet>";
	  echo	"</head>";
	  echo "<body><center><br><H3>Ocurrió un error al subir el archivo " . $_FILES['userfile']['name'] . ", puede ser que el archivo ya exista o que exceda el tamaño permitido.</H3></center></body>";
	   
	  echo "\n<center><br><br>";
      echo "\n<a href='" . $_POST["URL"] . "'><b>Regresar</b></a></center>";
  }

?>
