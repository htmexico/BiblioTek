<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  Guarda/Modifica los archivos anexados en un título
	  
	  23 jun 2009: Se crea el archivo
	  
     */
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "circulacion.inc.php";
	include "catalog.inc.php";
	
	check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	
	include_language( "anls_catalogacion_files" );
	include_language( "gral_files" );
	include_language( "global_menus" );	
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$id_titulo = read_param( "id_titulo", "", 1 );
	$the_action = read_param( "the_action", "", 0 );
	
	$status = 0;
	
	HeadHandler( $LBL_CATALOG_HEADER_1 . ": " . $id_titulo, "../");
	
	include "../privilegios.inc.php";
	
	$digital_permissions = verificar_privilegio( PRIV_ADD_DIGITAL_FILES, 1 );
			
?>

<script type="text/javascript" language="JavaScript">

	function upload()
	{
		var the_input = document.getElementsByName("file_to_upload");
		
		if( the_input.length > 0 ) 
		{
			if( the_input[0].value == "" )
			{
				alert( "<?php echo $ERROR_MSG_INPUTFILE;?>");
				exit;
			}
		}
		
		if( confirm( "<?php echo $WIN_DIALOG_MSG0;?>" ) )
		{
    	    document.f.method = "POST";
    	    document.f.action = "upload.php";
		    document.f.target = "_self";
    	    document.f.submit();
		}
	}
	
	function eliminar_imagen( id_biblioteca, id_titulo, tipo )
	{
		var msg;
		
		if( tipo == "P" )
			msg = "<?php echo $WIN_DIALOG_MSG1;?>";
		else if( tipo == "C" )
			msg = "<?php echo $WIN_DIALOG_MSG2;?>";			
		
		if( confirm( msg ) )
		{
			location.href = "anls_catalogacion_frontpage.php?id_biblioteca="+id_biblioteca+"&id_titulo=" + id_titulo + "&the_action=clear&tipo_imagen="+tipo;
		}
	}

</script>

<STYLE type="text/css"> 


  form.forma_captura label {
	width: 15em;
  }
   
</STYLE>

<body id="home">

<br>
	<!-- contenedor principal -->
	<div id="contenedor" style="width:95%">	
	
		<div id="bloque_principal"> <!-- inicia contenido -->	
				
			 <div id="contenido_principal" style='border: 0px solid red; width: 90%'>

				<br>
				<h1><?php echo $LBL_CATALOG_HEADER_1;?></h1><br>
				
				<p><?php echo "$LBL_CATALOG_INTRO";?></p>
							
				<?php
				
					//
					// SE INFORMA DE LOS RESULTADOS DE LA ULTIMA ACCION
					// DISPLAYS INFO ABOUT THE RESULTS OF LAST ACTION
					//
					if( $status <> 0 )
					{
						// accion de status
						if( $status == 3 )
						{
							echo "<div class='caja_errores'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_ERROR_FRONTCOVER</strong>";
							echo "</div>";
						}
						else if( $status == 4 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_OK_FRONTCOVER</strong>";
							echo "</div>";
							
							echo "<SCRIPT LANGUAGE='javascript'>";
							echo "   window.opener.document.location.reload();";
							echo "</SCRIPT>";
						}						
						else if( $status == 5 )
						{
							echo "<div class='caja_errores'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_ERROR_BACKCOVER</strong>";
							echo "</div>";
						}
						else if( $status == 6 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_OK_BACKCOVER</strong>";
							echo "</div>";
						}
						else if( $status == 10 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_CLEAR_FRONTCOVER</strong>";
							echo "</div>";
						}
						else if( $status == 11 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_CLEAR_BACKCOVER</strong>";
							echo "</div>";
						}						

						echo "<br>";
					}				
				
					$item = new TItem_Basic( $id_biblioteca, $id_titulo );

					echo "<h3>[" . $item->Material_ShortCode() . "] " . $item->cTitle . "</h3>";

					echo "<br>";
					
					//MOSTRAR LOS ARCHIVOS YA ANEXADOS
					$db = new DB( "SELECT * FROM acervo_archivos WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo" );
					
					
					
					// 
					// INICIA MOSTRAR ARCHIVOS
					//
					
					echo "<table width='100%'>";

					
					while( $db->NextRow() )
					{
						if( $db->numRows == 1 )
						{
							echo "<tr><td class='cuadricula columna columnaEncabezado' width='40%'>$LBL_FILE_NAME</td>";
							echo "    <td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_FILE_SIZE</td>";
							echo "    <td class='cuadricula columna columnaEncabezado' width='20%'>$LBL_FILE_TYPE</td>";
							echo "    <td class='cuadricula columna columnaEncabezado' width='15%'></td>";
							echo "</tr>";
						}
						
						$size = ($db->row["FILE_SIZE"] / 1024);
						$size = sprintf("%6.1f", $size );
						
						if( $size > 1024 )
						{
							$size = ($db->row["FILE_SIZE"] / (1024*1024));
							$size = sprintf("%6.2f", $size );
							$size .= " Mb";
						}
						else
							$size .= " Kb";
							
						$url_download_link = "javascript:download_digital_file( \"catalogacion\", $id_titulo, " . $db->row["ID_FILENUM"] . ");"; 
						$url_delete_link = "";
						
						if( $digital_permissions )
							$url_delete_link = "&nbsp;&nbsp;<a href='"."javascript:remove_digital_file( \"$MSG_CONFIRM_REMOVAL\", \"catalogacion\", $id_titulo, " . $db->row["ID_FILENUM"] . ", \"catalogacion_files\");"."'>$LINK_REMOVE_FILE</a>";

						   
						echo "<tr>";
						echo "<td class='columna cuadricula'>" . $db->row["FILE_NAME"] . "</td>";
						echo "<td class='columna cuadricula'>$size</td>";
						echo "<td class='columna cuadricula'>" . obtener_file_info( $db->row["FILE_MIMETYPE"], 2 ) . "</td>";
						echo "<td class='columna cuadricula'><a href='$url_download_link'>$LINK_DOWNLOAD_FILE</a>$url_delete_link</td>";
						echo "</tr>";						
				   
					}
					
					if( $db->numRows == 0 )
					{
						echo "<tr><td>No files</td></tr>";
					}
					
					echo "</table>";
					
					//
					// FIN MOSTRAR ARCHIVOS
					//
					
					echo "<br>";

					// FORMA PARA HACER UPLOAD
					echo "<div class='resaltados'>";
					echo "<H2>$LBL_UPLOAD_NEW_FILE</H2><br>";
					echo "<form enctype='multipart/form-data' name='f' class='forma_captura'>";
					echo "<input type='hidden' class='hidden' name='type' id='type' value='catalogacion'>";
					echo "<input type='hidden' class='hidden' name='id_titulo' id='id_titulo' value='$id_titulo'>";
					echo "<input type='hidden' class='hidden' name='the_action' id='the_action' value='upload'>";
					echo "<input type='hidden' class='hidden' name='url' id='url' value='anls_catalogacion_files.php?id_titulo=$id_titulo'>";

					echo "<label for='file_to_upload'>$LBL_FILE_FOR_UPLOAD</label>&nbsp;";
					echo "<input type='file' name='file_to_upload' size='60' maxlength='150'>";
					echo "<br>";
					
					echo "<label for='file_to_upload'>$LBL_NOTES</label>&nbsp;";
					echo "<input type='text' name='notes' size='73' maxlength='250'>";
					echo "<br><br>";

					echo "<input type='hidden' class='hidden' name='MAX_FILE_SIZE' value='300000'>";
					
						echo "<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
						echo "<input class='boton' type='button' value='$BTN_UPLOAD_FILE' name='btnUpload' id='btnUpload' onClick='javascript:upload();'>";
						echo "</div>";
					
					echo "</div>";
					
					echo '</form>';
				
				 ?>
	
				<br>
				
			</div>
			
			<input class='boton' type='button' value='<?php echo $BTN_CLOSEWIN;?>' name='btnClose' id='btnClose' onClick='javascript:window.close();'>
			<br>
				
		</div>
		<br><br>
	
	</div>

</body>	

</html>