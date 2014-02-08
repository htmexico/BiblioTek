<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  Guarda/Modifica las portadas / contraportadas de un libro
	  
	  05 may 2009: Se crea el archivo
	  
	  PENDIENTES: 
	  
	    - Tomar la imagen de un link WEB directamente
		- Colocar portadas default
	  
     */
		
	include "../funcs.inc.php";
	include "../basic/bd.class.php";
	include "circulacion.inc.php";
	include "catalog.inc.php";
	
	check_usuario_firmado(); 

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	
	include_language( "anls_catalogacion_frontpage" );	
	include_language( "global_menus" );	
	
	$id_biblioteca = getsessionvar("id_biblioteca");

	$id_titulo = read_param( "id_titulo", "", 1 );
	$the_action = read_param( "the_action", "", 0 );
	
	$status = 0;
	
	if( $the_action == "upload" )
	{
		
		if( isset($_FILES['file_4_cover']) )
		{
			// archivo portada subido
			// image for frontcover uploaded
			if ( is_uploaded_file($_FILES['file_4_cover']['tmp_name']) ) 
			{
				$bOk = upload_data_from_file( $id_biblioteca, $id_titulo, "P", $_FILES['file_4_cover']['tmp_name'], $_FILES['file_4_cover']['type'] );
							
				if (!$bOk) 
				{
				   // import failed
				   $status = 3;
				} 
				else 
				{	   
				   // import OK
				   $status = 4;
				   
					require_once("../actions.inc.php");		
					agregar_actividad_de_usuario( ANLS_COVERS_ASSIGN, DEF_ACTION_ADD . " COVER {$id_titulo}" );				   
				}
			}
		}
		
		if( isset($_FILES['file_4_backcover']) )
		{
			// archivo contra-portada subido
			// image for backcover uploaded
			if ( is_uploaded_file($_FILES['file_4_backcover']['tmp_name']) ) 
			{
				$bOk = upload_data_from_file( $id_biblioteca, $id_titulo, "C", $_FILES['file_4_backcover']['tmp_name'], $_FILES['file_4_backcover']['type'] );
							
				if (!$bOk) 
				{
				   // import failed
				   $status = 5;
				   
				} 
				else 
				{	   
					// import OK
					require_once("../actions.inc.php");		
					agregar_actividad_de_usuario( ANLS_COVERS_ASSIGN, DEF_ACTION_ADD . " BACKCOVER {$id_titulo}" );
				   
					$status = 6;
				}
			}		
		}
		
	}
	else if( $the_action == "clear" )
	{	
		$tipo_imagen = read_param( "tipo_imagen", "", 1 );
		
		if( $tipo_imagen == "P" )
		{
			db_query("UPDATE acervo_titulos SET PORTADA=NULL, PORTADA_MIMETYPE='' WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo");
			$status  = 10;
			
			require_once("../actions.inc.php");	
			agregar_actividad_de_usuario( ANLS_COVERS_ASSIGN, DEF_ACTION_DEL . " COVER {$id_titulo}" );
		}
		else if( $tipo_imagen == "C" )
		{
			db_query("UPDATE acervo_titulos SET CONTRAPORTADA=NULL, CONTRAPORTADA_MIMETYPE='' WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo");
			$status  = 11;
			
			require_once("../actions.inc.php");	
			agregar_actividad_de_usuario( ANLS_COVERS_ASSIGN, DEF_ACTION_DEL . " BACKCOVER {$id_titulo}" );
		}
		
	}
	
	HeadHandler( $LBL_CATALOG_HEADER_1 . ": " . $id_titulo, "../");
			
?>

<script type="text/javascript" language="JavaScript">

	function upload()
	{
		var the_input = document.getElementsByName("file_4_cover");
		
		if( the_input.length > 0 ) 
		{
			if( the_input[0].value == "" )
			{
				alert( "<?php echo $ERROR_MSG_INPUTFILE;?>");
				exit;
			}
		}
		else
		{
			the_input = document.getElementsByName("file_4_backcover");
			
			if( the_input.length > 0 ) 
			{
				if( the_input[0].value == "" )
				{
					alert( "<?php echo $ERROR_MSG_INPUTFILE;?>");
					exit;
				}
			}			
		}
		
		if( confirm( "<?php echo $WIN_DIALOG_MSG0;?>" ) )
		{
    	    document.f.method = "POST";
    	    document.f.action = "anls_catalogacion_frontpage.php";
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
</STYLE>

<body id="home">

<br>
	<!-- contenedor principal -->
	<div id="contenedor" style="width:95%">	
	
		<div id="bloque_principal"> <!-- inicia contenido -->	
				
			 <div id="contenido_principal">

				<br>
				<h1><?php echo $LBL_CATALOG_HEADER_1;?></h1><br>
				
				<p><?php echo $LBL_CATALOG_INTRO;?></p>
							
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
						if( $status == 4 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_OK_FRONTCOVER</strong>";
							echo "</div>";
							
							echo "<SCRIPT LANGUAGE='javascript'>";
							echo "   window.opener.document.location.reload();";
							echo "</SCRIPT>";
						}						
						if( $status == 5 )
						{
							echo "<div class='caja_errores'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_ERROR_BACKCOVER</strong>";
							echo "</div>";
						}
						if( $status == 6 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_OK_BACKCOVER</strong>";
							echo "</div>";
						}
						if( $status == 10 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_CLEAR_FRONTCOVER</strong>";
							echo "</div>";
						}
						if( $status == 11 )
						{
							echo "<div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$LBL_MSG_CLEAR_BACKCOVER</strong>";
							echo "</div>";
						}						

						
						echo "<br>";
					}				
				
					$item = new TItem_Basic( $id_biblioteca, $id_titulo );
					
					echo "<h3>[" . $item->Material_ShortCode() . "] " . $item->cTitle . ".</h3>";
					
					echo "<br><br>";
					
					// COVER
					echo "<div style='display:inline;float:left;'>";
					if( $item->cCover == NULL )
					{
						echo "<img src='../images/no_cover.png'>";
					}
					else
					{
						echo "<img src='../images/icons/cut.gif'>&nbsp;<a href='javascript:eliminar_imagen($id_biblioteca, $id_titulo,\"P\");'>$LBL_CLEAN</a><br><br>&nbsp;&nbsp;&nbsp;" . 
							 "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=PORTADA' width='100'\>&nbsp;&nbsp;<br>";
					}
					echo "</div>";

					// BACK COVER
					echo "<div style='display:inline; float:left;'>";
					if( $item->cBackCover == NULL )
					{
						echo "<img src='../images/no_backcover.png'>";
					}
					else
					{
						echo "<img src='../images/icons/cut.gif'>&nbsp;<a href='javascript:eliminar_imagen($id_biblioteca, $id_titulo,\"C\");'>$LBL_CLEAN</a><br><br>&nbsp;&nbsp;&nbsp;" . 
							 "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=CONTRAPORTADA' width='100'\>&nbsp;&nbsp;<br>";
						
					}					
					echo "</div>";					
					
					echo "<br clear='both'>";
					echo "<br>";
					
					if( $item->cCover == NULL or $item->cBackCover == NULL )
					{
						echo '<form enctype="multipart/form-data" name="f" class="forma_captura" >';
						echo "<input type='hidden' class='hidden' name='id_titulo' id='id_titulo' value='$id_titulo'>";
						echo "<input type='hidden' class='hidden' name='the_action' id='the_action' value='upload'>";
						
						if( $item->cCover == NULL )
						{
							echo '<label for="txt_id_usuario">' . $LBL_FILE_FOR_COVER. '</label>&nbsp;&nbsp;';
							echo '<input type="file" name="file_4_cover" size=60 maxlength=150>';
							echo '<br><br>';
						}
						else if( $item->cBackCover == NULL )
						{
							echo '<label for="txt_id_usuario">' . $LBL_FILE_FOR_BACKCOVER. '</label>&nbsp;&nbsp;';
							echo '<input type="file" name="file_4_backcover" size=60 maxlength=150>';
							echo '<br><br>';
						}
						
						echo '<input type="hidden" class="hidden" name="MAX_FILE_SIZE" value="100000">';
						
						echo '</form>';
					}
				
				 ?>
	
				<br>
								
				<div>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<?php 
					if( $item->cCover == NULL or $item->cBackCover == NULL )
					{
						echo '<input class="boton" type="button" value="' . $BTN_UPLOAD_IMAGES. '" name="btnUpload" id="btnUpload" onClick="javascript:upload();">&nbsp;&nbsp;&nbsp;';
					}
				?>
				<input class="boton" type="button" value="<?php echo $BTN_CLOSEWIN;?>" name="btnBuscar" id="btnBuscar" onClick="javascript:window.close();"><br>
				</div>
				
			</div>
			
			<br style='clear:both;'>
				
		</div>
		<br><br>
	
	</div>

</body>	

</html>