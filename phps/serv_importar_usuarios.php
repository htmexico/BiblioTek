<?php
/***
  Importar Acervo
  
  Solo estará visible cuando a un cliente se le haga migración
  
  Instrucciones para una migraciòn:
  
  - Archivo en XLS con campos como AUTOR, TITULO, EDITORIAL, SERIE, MATERIA, NO_CLASIF, ETIQUETA y 
  	EJEMPLARES: muy importante para cargar existencias, en conjunto con NO_CLASIF o ETIQUETA
  
  - Abrir Access e importar los datos XLS.
  
  - Abrir la tabla y Exportar datos a XML como UTF8. 
  
  
  
 **/
	session_start();
	
	set_time_limit( 120 );
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include "../password.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_system" );

	include "../basic/head_handler.php";    /* colocar TAGS o ETIQUETAS de la parte superior de cada pagina */
	HeadHandler( "Importar Acervo", "../" );   // Coloca incluso el TITULO en la ventana del navegador
	
	$id_biblioteca = getsessionvar("id_biblioteca" );
	
	function calcElapsedTime($time)
	{ // calculate elapsed time (in seconds!)
		$diff = time()-$time;
		$daysDiff = floor($diff/60/60/24);
		$diff -= $daysDiff*60*60*24;
		$hrsDiff = floor($diff/60/60);
		$diff -= $hrsDiff*60*60;
		$minsDiff = floor($diff/60);
		$diff -= $minsDiff*60;
		$secsDiff = $diff;
		
		$str = "";
		
		if( $daysDiff > 0 ) $str .= "$daysDiff Día(s) ";
		if( $hrsDiff > 0 ) $str .= "$hrsDiff Hora(s) ";
		if( $minsDiff > 0 ) $str .= "$minsDiff Minuto(s) ";
		if( $secsDiff > 0 ) $str .= "$secsDiff Segundo(s) ";
		
		if( $diff == 0 )
			$str .= "0 segundos";
		
		return " en $str ";
	}	
	
?>

<script language='javascript'>

	function ShowSyncInfo( msg ) 
	{
		var divID = js_getElementByName( "sync_info" );
		
		if( divID )
		{
			divID.style.display = "inline";
			divID.style.visibility = "visible";
			
			divID.innerHTML = msg;
		}
	}
	
	function GetFile( info, descrip )
	{
		ShowDiv( "screen_block_layer" );
		
		var obj_lbl = js_getElementByName( "lbl_indicator" );
	
		if( obj_lbl )
		{
			obj_lbl.innerHTML = "Datos" + "&nbsp;" + descrip;
		}
		
		var tbl_name = js_getElementByName( "sync_table_name" );
		tbl_name.value = info;
		
		ShowPopupDIV( "popup_importar" );
	}	

	function CloseImportDialog()
	{
		HideDiv( "screen_block_layer" );
		HideDiv( "popup_importar" );		
	}	
	
	function sync_info_go()
	{	
		if( document.sync_file.userfile.value == "" )
			alert( "Para continuar debe seleccionar un archivo para subir y sincronizar." );
		else
		{
			document.sync_file.method = "POST";
			document.sync_file.target = "_self";
			document.sync_file.submit();
		}
	}
	
	function UpdateProgress( msg )
	{
		var divID = js_getElementByName( "progress_info" );
		
		if( divID )
		{
			//divID.style.display = "inline";
			//divID.style.visibility = "visible";
			
			divID.innerHTML = msg + "<br>";
		}		
	}
	
	function MostrarDetallesError()
	{
		var divID = js_getElementByName( "detalles_error" );
		
		if( divID.style.display == "inline" )
			HideDiv( "detalles_error" );
		else
			ShowDiv( "detalles_error" );
	}
	

</script>

<STYLE type="text/css">

 #popup_importar
 {
	position: absolute;
	display: none;

	top:100px; 
	left: 100px;
	
	border: 2px solid black;
	
	width: 680px;
	height: 250px;
	z-Index: 100;
	color: black;
	
	padding: 10px;
	
	text-align: left;
	
	background: white;
 }
 

</STYLE>

<body id="home">

<?php

//	if( isset($_POST["uploadfile"]))
	ob_start();

	$db = new DB("");
	
	
 ?>

<div id='screen_block_layer' name='screen_block_layer' <?php if( isset($_POST["uploadfile"]) ) echo "style='display:block;'";?> ></div>

	<!-- INICIA popup_importar -->
		<div class='groupbox' id='popup_importar' name='popup_importar' >
			<form enctype="multipart/form-data" action="serv_importar_usuarios.php" name="sync_file" id="sync_file" >			
			
				<input type="hidden" name="MAX_FILE_SIZE" value="20000000" />
				<input type="hidden" class="hidden" name="uploadfile" value="YES" >
			
				<h2>Subir Informaci&oacute;n</h2><br>

					  <div style='border:0px solid red;'>
						<span id='lbl_indicator' name='lbl_indicator' style='width:250px; text-align:right; border: 0px solid black;'>&nbsp;</span><br><br>

						<label for='userfile'><strong>Nombre del Archivo</strong></label><br><br>
						<input name="userfile" type="file" maxLength=80 size=80 value="">
						<input type='hidden' class='hidden' id="sync_table_name" name="sync_table_name" value="">
						 
						&nbsp;
						<br><br>
						<input class="boton" type="button" value="Proceder" name="btnBuscar" id="btnBuscar" onClick="javascript:sync_info_go();">&nbsp;
						<input class="boton" type="button" value="Cancelar" name="btnClose" id="btnClose" onClick='javascript:CloseImportDialog();'><br>

					  </div>

				<br>		

			</form>
		
		</div> <!-- popup_importar -->

<?php
  // barra de navegación superior
  display_global_nav();  
 ?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 
   // banner
   display_banner();  
   
   // menu principal
   display_menu( "../" ); 
 ?>
	
	<div id="bloque_principal"> <!-- inicia contenido -->

		<div id="contenido_principal">

			 <div class='caja_mostrar_info'>
			 	<h1><img src='../images/icons/sync.png'>Elija la informaci&oacute;n que deseas subir</h1>
	
			 	<div id='sync_info' name='sync_info' style='display:none'>
			 	</div>
	
				 <br>
				 <p>
					
					<!-- instituci&oacute;n -->
					<h2>Acervo</h2>
					&nbsp;<a href='javascript:GetFile("usuarios","Informacion de usuarios" );'>Archivo de Usuarios</a><br>
					
					<br>
					
					<br>
				 </p>
			 
			 </div>
		
		</div>
		
		<?php display_copyright();?> 

		
	</div> <!-- bloque_principal -->
 
	 <br style='clear:both;'> 

</div><!-- end div contenedor -->

	<?php
				
		if( isset($_POST["uploadfile"]))
		{
		
			if( isset($_POST["uploadfile"]))
			{
				
				echo "<div class='progress_wrapper' id='progressing' name='progressing'>";
				echo "	<div class='progress' id='progress_info' name='progress_info'>";
				echo "     $MSG_PROCESSING_SOMETHING";
				echo " </div>";
				echo "</div>";				
				
				SYNTAX_JavaScript( 1, 1, " ShowDiv( 'screen_block_layer' ); " );
				
				ob_end_flush();			
				
				sleep(1);

				ob_start();  // start buffering echos						
				
			}
		
//require_once( "adm_dbsync_funcs.php" );
			$file_processing = 1;

			if( is_uploaded_file($_FILES['userfile']['tmp_name']) ) 
			{	  
				$nombre_archivo = $_FILES['userfile']['tmp_name'];
				
				if ( eregi('.zip', $_FILES['userfile']['name'] ) ) 
				{
					// archivo ZIP
					$zip = new ZipArchive();
			     
					if( $zip )
					{
						$res = $zip->open( $nombre_archivo );
						
						if( $zip->numFiles > 0 ) 
						{
						    $file_info = $zip->statIndex(0);
						    
						    if( $file_info )
						    {
								require_once( getsessionvar("ss_physical_dir") . "APP_CONFIG.php" );
								
								global $ACCESS_CFG;
								
								$zip->extractTo( getsessionvar("ss_physical_dir") . "custom" );
								
								$nombre_archivo = getsessionvar("ss_physical_dir") . "custom/" . $file_info["name"];
								
								chmod ( $nombre_archivo, 0777 );
						    }
						}
						else
							die( "NO file inside ZIP" );
							
						$zip->Close();
					}
			     
				}
				else
				{
					$new_nombre_archivo = getsessionvar("ss_physical_dir") . "custom/" . $_FILES['userfile']['name'];
					if( move_uploaded_file( $nombre_archivo, $new_nombre_archivo ) )
					{
						$nombre_archivo = $new_nombre_archivo;
						chmod ( $nombre_archivo, 0777 );
					}
				}

				try
				{

					if( filesize( $nombre_archivo ) > 20*(1024*1024) )
					{
						throw new Exception( 'El tamaño del archivo es muy grande (mayor a 20MB). Solicite apoyo a soporte técnico.', 1001 );	
					}
					
					ini_set( "memory_limit" , "128M" );
					ini_set( "output_buffering", "10" );
					
					// Tiempo de inicio
					$starttime = time();
					
					// pdte. mover el archivo al espacio de la instituci&oacute;n			
					//$xml = simplexml_load_string( utf8_decode( file_get_contents($nombre_archivo) ) );
					$xml = simplexml_load_string( file_get_contents($nombre_archivo) );

					if (!is_object($xml))
						throw new Exception("Error en la lectura del archivo XML $nombre_archivo",1001);	

					$db->tag1 = 0;
					//$db->startInsertUpdateTrans();
					$bOk = false;
					
					$error_msg = "";				
				
					if( $_POST["sync_table_name"] == "usuarios" )
					{
											
						$procesando = 0;
						
						foreach ($xml->usuario as $usuario ) 
						{
							//print_r( $recurso );
							
							$verif_MyFile = getsessionvar("ss_physical_dir") . "custom/usr_" . $usuario->Matricula . ".marc";
							
							if( file_exists( $verif_MyFile ) )
							{
								echo "Ya fue migrado $usuario->Matricula <br>";
							}
							else
							{
								$procesando++;
								
						        $db->Open( "SELECT MAX(ID_USUARIO) FROM cfgusuarios WHERE ID_BIBLIOTECA=$id_biblioteca" );
						
						        if( $db->NextRow() )
						        {
						        	$nUsrNum = $db->row["MAX"] + 1;				        
						        }
						        
						        $db->Close();						
								
								$cPassword = generadorPassword(4,10,0,"");
								
								$id_grupo = 2;  // Empleados Medix
								
								$sql = " INSERT INTO cfgusuarios (ID_BIBLIOTECA, ID_GRUPO, ID_USUARIO, STATUS, USERNAME, PASSWRD, PATERNO, MATERNO, NOMBRE, DIRECCION, TELEFONO )";
								$sql.= " VALUES ( $id_biblioteca, $id_grupo, $nUsrNum, 'A', '$usuario->Matricula', '".strtolower(md5($cPassword))."', '$usuario->Paterno', '$usuario->Materno', '$usuario->Nombre', " .
										" '$usuario->Escuela', '$usuario->Telefono' ); ";
								
								//echo $usuario->Matricula . $cPassword;
														
								$db->ExecSQL( $sql );								
								
								$handling = fopen($verif_MyFile, 'x');
								fwrite($handling, $usuario->Nombre_Completo); 
								fclose($handling); 
											
								echo "Username: $usuario->Matricula".", $usuario->Nombre_Completo".", Password $cPassword<br> ";
							}
							
						}		
						
						$descrip_table_name = "Usuarios";
						
						$bOk = true;
					}
					
				}				
				catch(Exception $e)
				{
					//catch exception
					$error_msg = $e->getMessage();
					
					$bOk = false;
				}
				
				unset( $xml );				

				//$cur_datetime = current_db_time( 1 );

			/*	if( $bOk )
				{
					$db->ExecSQL( "INSERT INTO web_log_sync " .
								  " (ID_CUENTA, TIPO_INFO, FECHA, USERNAME, REGISTROS_INSERTADOS,  REGISTROS_ACTUALIZADOS, REGISTROS_BORRADOS ) " .
								  " VALUES ( $id_cuenta, " . _gesToStr($_POST["sync_table_name"]) . ",'" . $cur_datetime . "', " . _gesToStr(getsessionvar("usuario")) . ", $db->inserted_records, $db->updated_records, $db->tag1 ); " );
				}
				**/
				// 
				ob_end_flush();
				//
				
				if( isset($descrip_table_name) )
					$file_sync_done = $descrip_table_name;
				else	
					$file_sync_done = $_POST["sync_table_name"];
				
				$details = "";
				
				//if( $db->inserted_records>0 )
				//	$details = " $db->inserted_records registros insertados";
					
				//if( $db->updated_records>0 )
				//	$details .= " $db->updated_records registros actualizados";
					
				//if( $db->tag1>0 )
				//	$details .= " $db->tag1 registros eliminados";
				
				SYNTAX_JavaScript( 1, 1, " HideDiv('progressing'); " );
				SYNTAX_JavaScript( 1, 1, " HideDiv('screen_block_layer'); " ); 

				if( $bOk )
				{
					// Tiempo que tomó
					$details .= " " . calcElapsedTime( $starttime );
					
					$msg_info = "<div id=info class='caja_info'>&nbsp;Se proces&oacute; informaci&oacute;n de <span class='hilite'>" . $file_sync_done . "</span>&nbsp;$details.</div>";
					
					//$cfgsyscuenta->SaveAction( ACC_ADM_SINCRONIZO_INFO, 0, $_POST["sync_table_name"] . " I" . $db->inserted_records . " U " .$db->updated_records . " D".$db->tag1  );
				}
				else
				{
					$msg_info = "<div id=info class='caja_errores'>&nbsp;Ocurrió un <a href='javascript:MostrarDetallesError();'>error</a> al procesar la informaci&oacute;n de <span class='hilite'>" . $file_sync_done . "</span></div><br>";
					$msg_info .= "<div class='popup' id='detalles_error' name='detalles_error'>$error_msg</div>";

					echo "<embed src='../basic/wrong.wav' hidden=true>";
				}
				
				SYNTAX_JavaScript( 1, 1, " ShowSyncInfo(\"$msg_info\"); " );
				
			} 
			else
			{
				$nombre_archivo = $_FILES['userfile']['name'];
				
				SYNTAX_JavaScript( 1, 1, " closePopupDIV('progressing'); " );
				SYNTAX_JavaScript( 1, 1, " closePopupDIV('popUp'); " );				
				
				$msg_error = "<div id=info class='caja_error'><img src='../images/icons/warning.gif'>&nbsp;No se pudo subir el archivo <span class='hilite'>" . $nombre_archivo . "</span>.</div>";
				SYNTAX_JavaScript( 1, 1, " ShowSyncInfo(\"$msg_error\"); " );
			}
		}

  $db->destroy(); 
 ?>
 
 </body>
  
 </html>
