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
	
	set_time_limit( 0 );
		
	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
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
	
	//include "clase_config.inc.php";  		// incluir clase cuenta	
	//$cfgsyscuenta = new TConfigSys( $id_cuenta, $db );		

/*
	$sql = "SELECT a.ID_BIBLIOTECA, a.ID_TITULO, a.ID_DESCRIPTOR, a.ID_CAMPO, a.CODIGO, a.SUBCODIGO, a.ID1, a.ID2, a.VALOR " .
			" FROM ACERVO_CATALOGACION a " .
			"WHERE a.ID_BIBLIOTECA=5 and VALOR <> '' and (ID_CAMPO <> '$$$' and ID_CAMPO<>'008' and ID_CAMPO<>'001' and ID_CAMPO<>'005') ";
			
	$db->Open( $sql );
	
	while( $db->NextRow() )
	{
		$valor_save = $db->row["VALOR"];
		
		if( strlen($valor_save) > 5)
		{
			//echo $valor_save . "<br>";
			if( utf8_decode( $valor_save ) <> $valor_save )
			{
				echo utf8_decode( $valor_save ) . "<br>";
				
				$sql_update = "UPDATE acervo_catalogacion SET VALOR='".utf8_decode( $valor_save ) ."' " . 
								" WHERE ID_BIBLIOTECA=5 and ID_TITULO=" . $db->row["ID_TITULO"] . " and ID_DESCRIPTOR=" . $db->row["ID_DESCRIPTOR"];
								
				//$db->ExecCommand( $sql_update );
				ibase_query( $db->db_link, $sql_update );
				
			}
		}
	}
	
	$db->Close();
	*/
	
 ?>

<div id='screen_block_layer' name='screen_block_layer' <?php if( isset($_POST["uploadfile"]) ) echo "style='display:block;'";?> ></div>

	<!-- INICIA popup_importar -->
		<div class='groupbox' id='popup_importar' name='popup_importar' >
			<form enctype="multipart/form-data" action="gral_importar_acervo.php" name="sync_file" id="sync_file" >			
			
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
					&nbsp;<a href='javascript:GetFile("acervo","Informacion del acervo institucional" );'>Acervo de Recursos Bibliotecarios</a><br>
					
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
					
					ini_set( "display_errors", "on" );
					
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
				
					if( $_POST["sync_table_name"] == "acervo" )
					{
						$tesauro_id_categoria_prestamo = "";  // tesauro 6
						$tesauro_id_estado_fisico = "0"; // tesauro 12		
						
						// categoria 6
						$query_categoria_prestamo = "SELECT a.ID_RED, a.ID_TERMINO, a.ID_CATEGORIA, b.DESCRIPCION " .
													"FROM TESAURO_TERMINOS_CATEGORIAS a	" .
													"  LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
													"where a.id_red=".getsessionvar("id_red")." and a.id_categoria=6 ORDER BY a.ID_TERMINO; ";
								
						$db->Open( $query_categoria_prestamo );
						
						if( $db->NextRow() )
						{
							$tesauro_id_categoria_prestamo = $db->row["ID_TERMINO"];
						}
						
						$db->Close();
									
						// 	categoria 12
						$query_categoria_prestamo = "SELECT a.ID_RED, a.ID_TERMINO, a.ID_CATEGORIA, b.DESCRIPCION " .
													"FROM TESAURO_TERMINOS_CATEGORIAS a	" .
													"  LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
													"where a.id_red=".getsessionvar("id_red")." and a.id_categoria=12 ORDER BY a.ID_TERMINO; ";
								
						$db->Open( $query_categoria_prestamo );
						
						if( $db->NextRow() )
						{
							$tesauro_id_estado_fisico = $db->row["ID_TERMINO"];
						}
						
						$db->Close();

						
						require_once "iso2709.inc.php";	
						require_once "marc.php";				
						
						$procesando = 0;
						
						foreach ($xml->record as $recurso ) 
						{
							//print_r( $recurso );
							
							$verif_MyFile = getsessionvar("ss_physical_dir") . "custom/arch_" . $recurso->ID . ".marc";
							
							if( file_exists( $verif_MyFile ) )
							{
								echo "Ya fue migrado $recurso->ID <br>";
							}
							else
							{
								$procesando++;
								
								$marc_record = new record_MARC21( $id_biblioteca, $db );
								$marc_record->AgregarCampo( "$$$", false, true );					
								
								if( isset($recurso->AUTOR) )  // Marc 100
								{
									$objCampo = $marc_record->AgregarCampo( "100", false, true );
									$objCampo->cValor = ""; // $recurso->AUTOR;
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$objCampo->AgregarIdentificador( 1, "", "", "1" );
									
									$recurso->AUTOR = str_replace( " & ", " _Y_ ", $recurso->AUTOR );
									$recurso->AUTOR = utf8_decode( $recurso->AUTOR );
									$recurso->AUTOR = str_replace( " _Y_ ", " &amp; ", $recurso->AUTOR );
									
									// xx - para que el sistema no cargue las descripciones de subcampos
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->AUTOR, "", "" );
								}
								
								if( isset($recurso->TITULO) )  // Marc 245
								{
									$objCampo = $marc_record->AgregarCampo( "245", false, true );
									$objCampo->cValor = ""; // $recurso->TITULO;
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$objCampo->AgregarIdentificador( 1, "", "", "1" );
	
									$recurso->TITULO = str_replace( " & ", " _Y_ ", $recurso->TITULO );
									$recurso->TITULO = utf8_decode( $recurso->TITULO  );
									$recurso->TITULO = str_replace( " _Y_ ", " &amp; ", $recurso->TITULO );
	
									// xx - para que el sistema no cargue las descripciones de subcampos								
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->TITULO, "", "" );
								}
								
								if( isset($recurso->NO_CLASIF) )  // Marc 084  --  Clasificaciones Locales Informales
								{
									$objCampo = $marc_record->AgregarCampo( "084", false, true );
									$objCampo->cValor = ""; // $recurso->NO_CLASIF;
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->NO_CLASIF = utf8_decode( $recurso->NO_CLASIF  );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->NO_CLASIF, "", "" );
								}
								if( isset($recurso->ETIQUETA) )  // Marc 084  --  Clasificaciones Locales Informales
								{
									$objCampo = $marc_record->AgregarCampo( "084", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->ETIQUETA = utf8_decode( $recurso->ETIQUETA  );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->ETIQUETA, "", "" );
								}
								
								if( isset($recurso->EDICION) )  // Marc 250  --  Mencion de Edicion
								{
									$objCampo = $marc_record->AgregarCampo( "250", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->EDICION = utf8_decode( $recurso->EDICION  );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->EDICION, "", "" );
								}
								
								if( isset($recurso->EDITORIAL) )  // Marc 260 $b  --  Nombre de la Editorial
								{
									$objCampo = $marc_record->AgregarCampo( "260", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->EDITORIAL = str_replace( " & ", " _Y_ ", $recurso->EDITORIAL );
									$recurso->EDITORIAL = utf8_decode( $recurso->EDITORIAL  );
									$recurso->EDITORIAL = str_replace( " _Y_ ", " &amp; ", $recurso->EDITORIAL );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$b', "xx", "xx", $recurso->EDITORIAL, "", "" );
								}
								
								if( isset($recurso->MATERIA) )  // Marc 650  --  Entradas Secundarias Terminos Tematicos
								{
									$objCampo = $marc_record->AgregarCampo( "650", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->MATERIA = utf8_decode( $recurso->MATERIA  );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->MATERIA, "", "" );
								}

								if( isset($recurso->SERIE) )  // Marc 440  --  Mención de Serie
								{
									$objCampo = $marc_record->AgregarCampo( "440", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->SERIE = utf8_decode( $recurso->SERIE );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->SERIE, "", "" );
								}
								
								if( isset($recurso->COLECCION) )  // Marc 440  -- Colección
								{
									$objCampo = $marc_record->AgregarCampo( "440", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->COLECCION = str_replace( " & ", " _Y_ ", $recurso->COLECCION );
									$recurso->COLECCION = utf8_decode( $recurso->COLECCION );
									$recurso->COLECCION = str_replace( " _Y_ ", " &amp; ", $recurso->COLECCION );		
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $recurso->COLECCION, "", "" );
								}								
								
								if( isset( $recurso->No_Adquisicion) )
								{
									// Numeros de Adquisicion
									$objCampo = $marc_record->AgregarCampo( "037", false, true );
									$objCampo->cValor = "";
									$objCampo->ID	  = $objCampo->ObtenerID();
									
									$recurso->No_Adquisicion = utf8_decode( $recurso->No_Adquisicion );
									
									// xx - para que el sistema no cargue las descripciones de subcampos						
									$objSubCampo = $objCampo->AgregarSubCampo( '$b', "xx", "xx", $recurso->No_Adquisicion, "", "" );									
								}
							
								if( isset($recurso->MARC) )  // Diferentes etiquetas MARC concatenadas y separadas segun sea el caso con |
								{
									
									$recurso->MARC = str_replace( " & ", " _Y_ ", $recurso->MARC );
									$recurso->MARC = utf8_decode( $recurso->MARC  );
									$recurso->MARC = str_replace( " _Y_ ", " &amp; ", $recurso->MARC );
																		
									$myArray = explode('¦', $recurso->MARC );
																	
									for( $x=0; $x < count($myArray); $x++ )
									{
										$str_info = $myArray[$x];
										
										if( $str_info != "" )
										{
											$id_campo_num = substr( $str_info, 0, 3 );
											$info_campo = substr( $str_info, 3, strlen($str_info) );
											
											if( $marc_record->BuscarCampo( $id_campo_num) == NULL )
											{										
												$objCampo = $marc_record->AgregarCampo( $id_campo_num, false, true );
												$objCampo->cValor = "";
												$objCampo->ID	  = $objCampo->ObtenerID();
		
												$info_campo = str_replace( "Ì", "", $info_campo );
		
												$objSubCampo = $objCampo->AgregarSubCampo( '$a', "xx", "xx", $info_campo, "", "" );
											}
										}
									}

								}							
							
								$tipomaterial = "a";
								
								if( isset($recurso->TIPO_ITEM) )
								{
									if( $recurso->TIPO_ITEM == "CD" or $recurso->TIPO_ITEM == "AUDIO")
										$tipomaterial ="j";
									if( $recurso->TIPO_ITEM == "DVD" or  $recurso->TIPO_ITEM == "VIDEO" )
										$tipomaterial ="g";
									if( $recurso->TIPO_ITEM == "MATERIAL" )
										$tipomaterial ="n";  // otros
								}
								
								$marc_record->SetTipoMaterial( $tipomaterial );
									
								$marc_record->FTipoRegistro		  	 = $tipomaterial;     // dt
								$marc_record->FEstadoRegistro 		 = "n"; // nuevo
								$marc_record->FNivelBibliografico	 = "m";
								$marc_record->FTipoControl			 = "#"; // 
					
								$marc_record->FCodigoEsquemaChars	 = "#"; // PENDIENTE REVISAR
								$marc_record->FConteoIndicadores	 = "2"; // 2 CARACTERES PARA ESPECIFICAR INDICADORES
								$marc_record->FConteoCodigoSubcampo  = "2"; // 2 CARACTERES PARA ESPECIFICAR LOS CODIGO DE SUBCAMPOS
					
								$marc_record->FDireccionBase_Datos   = 0;  // ba
					
								$marc_record->FNivelCodificacion     = "5";
								$marc_record->FFormaCatalogacion     = "u";
								$marc_record->FNivelRegistro_Recurso = "#";
					
								$marc_record->FMapaEntradas 		 = 0;  // em
								
								// genera el registro MARC según ISO2709
								// esto mismo genera la cabecera
								$marc_record->CreateRecord_ISO2709( false );
								
								$marc_record->AgregarRegistro( getsessionvar("id_usuario") );
								
								// Agregar campo por campo
								foreach( $marc_record->aCamposMarc as $value )
								{
									$objCampo = $value[1];
						
									$marc_record->GuardarElementosSubCampos( $objCampo );
								}
								
								$marc_record->FinalizarGuardado();
								
								// caso particular
								/*if( !isset($recurso->ETIQUETA) and isset($recurso->NO_CLASIF))
								{	
									if( $recurso->NO_CLASIF != "" )
									{
										$recurso->ETIQUETA = $recurso->NO_CLASIF;
										$recurso->EJEMPLARES = 1;
									}
								}**/
								// caso particular - borrar despues
								
								$ejemplares = 0;
								
								if( isset($recurso->EJEMPLARES) )
								{
									$ejemplares = $recurso->EJEMPLARES;
								}
								else
								{
									$ejemplares = 1;	
								}
								
								echo $ejemplares . "<br>";
								
								// usar existencias o EJEMPLARES
								//
								for( $x=1; $x<=$ejemplares; $x++ )
								{
									$num_copia = "";
									
									if( isset($recurso->ETIQUETA) )
										$num_copia = $recurso->ETIQUETA;
									else if( isset($recurso->NO_CLASIF) )
										$num_copia = $recurso->NO_CLASIF;
									else if( isset($recurso->NO_CONTROL_EJEMPLARES) )
										$num_copia = $recurso->NO_CONTROL_EJEMPLARES;										
									
									if( $num_copia != "" )
									{
										$ejemplares_info = explode( ";", $num_copia );
										
										for( $ndx_Ejemplar=0; $ndx_Ejemplar < count($ejemplares_info); $ndx_Ejemplar++ )
										{
											$num_copia = trim($ejemplares_info[$ndx_Ejemplar]);
											
											// generar el nuevo ID del item (ejemplar)
											$id_item = 0;
											$db->Open( "SELECT MAX(ID_ITEM) AS MAXID FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca" );
											
											if ($db->NextRow() ) 
												$id_item  = $db->Field("MAXID") + 1;
												
											$db->Close();
											
											$fecha = $recurso->FECHA;
		
											$id_location = "NULL";  
											$fecha_recepcion = "NULL";
											
											if( isset($recurso->FECHA) )
												$fecha = $recurso->FECHA;  //  
												
											if( isset($recurso->UBICACION) )
												$id_location = $recurso->UBICACION;
												
											$item_status = "D";
											
											$db->sql  = "INSERT INTO acervo_copias ( ID_BIBLIOTECA, ID_ITEM, ID_TITULO, ID_COPIA, CATEGORIA_PRESTAMO,  ";
											$db->sql .= "  ID_MATERIAL, NUMERO_PARTE, SERIES_FECHA_PUBLICACION, FECHA_RECEPCION, PRECIO_ADQUISICION, ID_ADQUISICION, ID_UBICACION, SIGNATURA_PREFIJO, SIGNATURA_CLASE, SIGNATURA_LIBRISTICA, ";
											$db->sql .= "  STATUS, ESTADO_FISICO, MATERIAL_ADICIONAL, SERIES_TITULO, SERIES_TITULOSECUNDARIO, ";
											$db->sql .= "  SERIES_VOLUMEN, SERIES_EPOCA, SERIES_ANIO, SERIES_MES, SERIES_NUMEROESPECIAL, SERIES_PAPEL_ELECTRONICO ) ";
											$db->sql .= " VALUES ( $id_biblioteca, $id_item, $marc_record->nIDTitulo, 1, $tesauro_id_categoria_prestamo, ";
											$db->sql .= "  		    '$num_copia', '$x', null, $fecha_recepcion, 0, 0, $id_location, '', '', '', ";
											$db->sql .= "		      '$item_status', $tesauro_id_estado_fisico, '', '', '',";
											$db->sql .= " 			  null, '', '', '', '', '' ) ";
											$db->ExecSQL();
											
											echo $num_copia . "<br>";
										}
										
									}
								}
								
								$contenido = $marc_record->CreateRecord_ISO2709( false );
															
								$handling = fopen($verif_MyFile, 'x');
								fwrite($handling, $contenido); 
								fclose($handling); 
											
								echo "Se migró $recurso->Id $recurso->TITULO<br> ";
								
								$marc_record->destroy();
								
								//if( $procesando > 10 )
								//	break;
							}
							
						}		
						
						$descrip_table_name = "Acervo Bibliográfico";
						
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
