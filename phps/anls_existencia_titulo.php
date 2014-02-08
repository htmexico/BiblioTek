<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  12 abr 2009: Se crea el archivo PHP para editar/crear/eliminar items del acervo
	  12 abr 2009: Se idea/crea el mecanismo para permitir uso de popups en archivos PHP para edición/creación/eliminación
				   en lugar de popups se utilizan REDIRECCIONES lineales
	  23 sep 2009: Se agregan campos para alojar datos de fasciculos de publicaciones periódicas
	  09 dic 2009: Se agrega "status" DISPONIBLE Para USO INTERNO
				   
		PENDIENTES:
		
		- Cambiar la carga de tesauro a un archivo general (funcs.inc.php) (OK  22sep2009)
	  
     */

	include "../funcs.inc.php";
	include "../actions.inc.php";
	include "../basic/bd.class.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 
	check_usuario_empleado();  // acceso solo a empleados

	include_language( "anls_existencia_titulo" );		// archivo de idioma
	
	$is_series = read_param( "is_series", 0 );

	/** INICIO - FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	$id_item = "$LBL_TO_BE_ASIGNED";
	$id_location	= "";
	$id_material    = "";
	
	$id_loan_category = 0;
	$id_physical_st   = 0;
	
	$serie_vol_number   = "0";
	$part_number 		= read_param("numero","");
	$serie_esp_number   = "N";
	$serie_epoch 	    = "0";
	$serie_anio         = read_param("anio","");
	$serie_mes          = read_param("mes", 0);
	$serie_titulo_principal = "";
	$serie_titulo_secundario = "";
	$serie_papel_electronico = "P";
	$serie_date_of_publish = read_param("date_of_recep", getcurdate_human_format() );
	
	$date_of_reception	= read_param("date_of_recep", getcurdate_human_format() );
	
	$cost_adq = 0;
	$id_acquisicion = 0;
	
	$call_number_prefix = "";
	$call_number_class  = "";
	$call_number_book 	= "";
	
	$item_status = "";
	
	$material_adicional = "";
		
	$error = 0;
	
	$action = read_param( "the_action", "", 0 );
	$id_titulo = read_param( "id_titulo", "0", 1 );
		
	$id_red = getsessionvar("id_red");
	$id_biblioteca = getsessionvar("id_biblioteca");

	if( $action == "create_new" )
	{
		// verificar DUPLICIDAD del material
		$id_material			= $_POST["txt_id_material"];
		
		$id_loan_category 		= $_POST["cmb_loan_category"];
		
		$part_number			= $_POST["txt_part_number"];
		$date_of_reception	    = $_POST["txt_date_of_reception"];
		
		if( $date_of_reception == "" )
			$val_date_reception = "NULL";
		else
			$val_date_reception = "'" . date_for_database_updates( $date_of_reception ) . "'";
		
		$date_of_publish = read_param( "txt_date_of_publish", "" );
		
		if( $date_of_publish == "" )
			$val_date_of_publish = "NULL";
		else
			$val_date_of_publish = "'" . date_for_database_updates( $date_of_publish ) . "'";		
		
		$cost_adq = $_POST["txt_cost_adq"];
		
		$id_acquisicion 		= $_POST["txt_id_acquisicion"];
		
		if( $id_acquisicion == "" )
			$id_acquisicion = 0;
		
		$id_location			= $_POST["cmb_item_location"];
		
		$call_number_prefix 	= $_POST["txt_callnumber_prefix"];
		$call_number_class  	= $_POST["txt_callnumber_class"];
		$call_number_book 		= $_POST["txt_callnumber_book"];
		
		$id_physical_st   	   	= $_POST["cmb_physical_st"];
		$item_status 	  		= $_POST["cmb_item_st"];		
		
		$material_adicional     = $_POST["txt_material_adicional"];		
		
		// para series
		$serie_volumen		    = read_param( "txt_vol_number", "" );
		
		if( $serie_volumen == "" )
			$serie_volumen = "0";
		
		$serie_epoch 	         = read_param("cmb_serie_epoca", "" );
		$serie_anio              = read_param("txt_anio","");
		$serie_mes               = read_param("cmb_mes","");
		$serie_titulo_principal  = read_param("txt_serie_titulo_principal","");
		$serie_titulo_secundario = read_param("txt_serie_titulo_secundario","");
		$serie_numero_especial   = isset( $_POST["chk_num_especial"] ) ? "S" : "N";
		$serie_papel_electronico = read_param( "cmb_serie_papel_electronico","");

		$db = new DB( "SELECT COUNT(*) AS CUANTOS FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca and ID_MATERIAL='$id_material'; " );
		
		if ($db->NextRow() ) 
		{
			if( $db->row["CUANTOS"] > 0 )
			{
				$error = 3;  // ID del MATERIAL ya existe
			}
		}
			
		$db->Close();
		
		if( $error == 0 )
		{
			// generar el nuevo ID del item (ejemplar)
			$id_item = 0;
			$db->Open( "SELECT MAX(ID_ITEM) AS MAXID FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca" );
			
			if ($db->NextRow() ) 
				$id_item  = $db->Field("MAXID") + 1;
				
			$db->Close();
			
			$db->sql  = "INSERT INTO acervo_copias ( ID_BIBLIOTECA, ID_ITEM, ID_TITULO, ID_COPIA, CATEGORIA_PRESTAMO,  ";
			$db->sql .= "  ID_MATERIAL, NUMERO_PARTE, SERIES_FECHA_PUBLICACION, FECHA_RECEPCION, PRECIO_ADQUISICION, ID_ADQUISICION, ID_UBICACION, SIGNATURA_PREFIJO, SIGNATURA_CLASE, SIGNATURA_LIBRISTICA, ";
			$db->sql .= "  STATUS, ESTADO_FISICO, MATERIAL_ADICIONAL, SERIES_TITULO, SERIES_TITULOSECUNDARIO, ";
			$db->sql .= "  SERIES_VOLUMEN, SERIES_EPOCA, SERIES_ANIO, SERIES_MES, SERIES_NUMEROESPECIAL, SERIES_PAPEL_ELECTRONICO ) ";
			$db->sql .= " VALUES ( $id_biblioteca, $id_item, $id_titulo, 0, '$id_loan_category', ";
			$db->sql .= " 		   '$id_material', '$part_number', $val_date_of_publish, $val_date_reception, $cost_adq, $id_acquisicion, $id_location, '$call_number_prefix', '$call_number_class', '$call_number_book',";
			$db->sql .= "		   '$item_status', '$id_physical_st', '$material_adicional', '$serie_titulo_principal', '$serie_titulo_secundario',";
			$db->sql .= "		   '$serie_volumen', '$serie_epoch', '$serie_anio', '$serie_mes', '$serie_numero_especial', '$serie_papel_electronico' ) ";
			$db->ExecSQL();
			
			agregar_actividad_de_usuario( ANLS_EXISTENCES, "$COMMENTS_ACTIONS_ADD_COPY", $id_item );
				
			$error = 10;
			$action = "edit";
			
			if( !allow_use_of_popups() )
				ges_redirect( "anls_existencias_paso2.php?id_titulo=$id_titulo&id_item_created=$id_item" );
		}

	}
	else if( $action == "save_changes" )
	{
		$id_item				= $_POST["id_item"];

		$id_location			= $_POST["cmb_item_location"];
		$id_loan_category 		= $_POST["cmb_loan_category"];
		
		$id_material			= $_POST["txt_id_material"];

		$cost_adq 				= $_POST["txt_cost_adq"];
		
		if( $cost_adq == "" )
			$cost_adq = "0";
		
		$id_acquisicion 		= $_POST["txt_id_acquisicion"];

		if( $id_acquisicion == "" )
			$id_acquisicion = "0";		
		
		$part_number			= $_POST["txt_part_number"];

		$date_of_reception	    = $_POST["txt_date_of_reception"];
		
		if( $date_of_reception == "" )
			$val_date_reception = "NULL";
		else
			$val_date_reception = "'" . date_for_database_updates( $date_of_reception ) . "'";
			
		$date_of_publish = read_param( "txt_date_of_publish", "" );
		
		if( $date_of_publish == "" )
			$val_date_of_publish = "NULL";
		else
			$val_date_of_publish = "'" . date_for_database_updates( $date_of_publish ) . "'";		
			
		
		$call_number_prefix 	= $_POST["txt_callnumber_prefix"];
		$call_number_class  	= $_POST["txt_callnumber_class"];
		$call_number_book 		= $_POST["txt_callnumber_book"];

		$id_physical_st   	   	= $_POST["cmb_physical_st"];
		$item_status 	  		= $_POST["cmb_item_st"];

		$material_adicional     = $_POST["txt_material_adicional"];		
		
		// para series
		$serie_volumen		    = read_param( "txt_vol_number", "" );
		
		if( $serie_volumen == "" )
			$serie_volumen = "0";
		
		$serie_epoch 	         = read_param("cmb_serie_epoca", "" );
		$serie_anio              = read_param("txt_anio","");
		$serie_mes               = read_param("cmb_mes","");
		$serie_titulo_principal  = read_param("txt_serie_titulo_principal","");
		$serie_titulo_secundario = read_param("txt_serie_titulo_secundario","");
		$serie_numero_especial   = isset( $_POST["chk_num_especial"] ) ? "S" : "N";
		$serie_papel_electronico = read_param( "cmb_serie_papel_electronico","");	

		$db = new DB;
		
		$db->sql  = "UPDATE acervo_copias SET ID_MATERIAL='$id_material', NUMERO_PARTE='$part_number', ID_ADQUISICION=$id_acquisicion, PRECIO_ADQUISICION=$cost_adq, ID_UBICACION=$id_location, CATEGORIA_PRESTAMO=$id_loan_category,  ";
		$db->sql .= "  SERIES_FECHA_PUBLICACION=$val_date_of_publish, FECHA_RECEPCION=$val_date_reception, SIGNATURA_PREFIJO='$call_number_prefix', SIGNATURA_CLASE='$call_number_class', SIGNATURA_LIBRISTICA='$call_number_book', ";
		$db->sql .= "  STATUS='$item_status', ESTADO_FISICO=$id_physical_st, MATERIAL_ADICIONAL='$material_adicional', ";
		$db->sql .= "  SERIES_TITULO='$serie_titulo_principal', SERIES_TITULOSECUNDARIO='$serie_titulo_secundario', ";
		$db->sql .= "  SERIES_VOLUMEN='$serie_volumen', SERIES_EPOCA='$serie_epoch', SERIES_ANIO='$serie_anio', SERIES_MES='$serie_mes', SERIES_NUMEROESPECIAL='$serie_numero_especial', SERIES_PAPEL_ELECTRONICO='$serie_papel_electronico' ";
		$db->sql .= "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_ITEM=$id_item";
		
		$db->ExecSQL();

		agregar_actividad_de_usuario( ANLS_EXISTENCES, "$COMMENTS_ACTIONS_EDITED_COPY.", $id_item );
		
		$error = 20;
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_existencias_paso2.php?id_titulo=$id_titulo&id_item_edited=$id_item" );

	}
	else if( $action == "edit" )
	{
		if( isset( $_POST["id_item"] ) )
			$id_item = $_POST["id_item"];
		if( isset( $_GET["id_item"] ) )
			$id_item = $_GET["id_item"];		
		
		$db = new DB( "SELECT ID_MATERIAL, ID_UBICACION, CATEGORIA_PRESTAMO, NUMERO_PARTE, FECHA_RECEPCION, ID_ADQUISICION, PRECIO_ADQUISICION," . 
						"SIGNATURA_PREFIJO, SIGNATURA_CLASE, SIGNATURA_LIBRISTICA, STATUS, ESTADO_FISICO, MATERIAL_ADICIONAL, SERIES_VOLUMEN, " . 
						"SERIES_NUMEROESPECIAL, SERIES_EPOCA, SERIES_ANIO, SERIES_MES, SERIES_TITULO, SERIES_TITULOSECUNDARIO, SERIES_PAPEL_ELECTRONICO, " .
						"SERIES_FECHA_PUBLICACION " . 
					  " FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca and ID_ITEM=$id_item" );
		
		if( $db->NextRow() ) 
		{ 		
			$id_material    = $db->Field("ID_MATERIAL");
			$id_acquisicion = $db->Field("ID_ADQUISICION");
			$id_location	= $db->Field("ID_UBICACION");
			$id_loan_category = $db->Field("CATEGORIA_PRESTAMO");
			
			$part_number	= $db->row["NUMERO_PARTE"];
			$date_of_reception= dbdate_to_human_format( $db->row["FECHA_RECEPCION"] );
			
			$serie_date_of_publish= dbdate_to_human_format( $db->row["SERIES_FECHA_PUBLICACION"] );
			
			$cost_adq 			= $db->row["PRECIO_ADQUISICION"];
			
			$call_number_prefix = $db->Field("SIGNATURA_PREFIJO");
			$call_number_class  = $db->Field("SIGNATURA_CLASE");
			$call_number_book 	= $db->Field("SIGNATURA_LIBRISTICA");
			
			$id_physical_st   = $db->Field("ESTADO_FISICO");
			$item_status 	  = $db->Field("STATUS");
			
			$material_adicional = $db->row["MATERIAL_ADICIONAL"];
	
			// datos de serie
			$serie_vol_number        = $db->row["SERIES_VOLUMEN"];
			$serie_esp_number        = $db->row["SERIES_NUMEROESPECIAL"];
			$serie_epoch 	         = $db->row["SERIES_EPOCA"];
		    $serie_anio              = $db->row["SERIES_ANIO"];
			$serie_mes               = $db->row["SERIES_MES"];
			$serie_titulo_principal  = $db->row["SERIES_TITULO"];
			$serie_titulo_secundario = $db->row["SERIES_TITULOSECUNDARIO"];
			$serie_papel_electronico = $db->row["SERIES_PAPEL_ELECTRONICO"];
	
			$action = "save_changes";  // acción derivada natural
		}
		
		$db->destroy();
		
		$error = 0;
	}
	else if( $action == "delete" )
	{
		$items = "";
		$items_borrados = 0;
		
		if( isset($_GET["items"]) )
		{
			$items = $_GET["items"];
			
			// guardar copia para verificaciones
			$copia_items = $items;			
			$copia_items = str_replace( "@", ":", $copia_items ); 
			
			$array_items = split( ":", $copia_items );
			
			$con_error = false;
			
			$db = new DB();
			
			$errores = Array();
			
			// VERIFICACION DE VIABILIDAD DE BORRADO
			for( $i=0; $i<count($array_items); $i++ )
			{
				if( $array_items[$i] != "" )
				{
					// VALIDAR EN DESCARTES
					$db->Open( "SELECT COUNT(*) AS CUANTOS  FROM descartes_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_ITEM=" . $array_items[$i] . ";" );
					
					if( $db->NextRow() )
					{
						if( $db->row["CUANTOS"] > 0 ) 
						{
							$con_error = true;
							$errores[] = Array( "id_item" => $array_items[$i],
												"description_error" => "$LBL_PROBLEM_ON_DISCARDS" );
						}
					}
					
					$db->Close();

					
					// VALIDAR EN PRESTAMOS
					$db->Open( "SELECT COUNT(*) AS CUANTOS  FROM prestamos_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_ITEM=" . $array_items[$i] . ";" );
					
					if( $db->NextRow() )
					{
						if( $db->row["CUANTOS"] > 0 ) 
						{
							$con_error = true;
							$errores[] = Array( "id_item" => $array_items[$i],
												"description_error" => "$LBL_PROBLEM_ON_LOANS" );
						}
					}
					
					$db->Close();
					
					// VALIDAR EN RESERVACIONES
					$db->Open( "SELECT COUNT(*) AS CUANTOS  FROM reservaciones_det WHERE ID_BIBLIOTECA=$id_biblioteca and ID_TITULO=$id_titulo and ID_ITEM=" . $array_items[$i] . ";" );
					
					if( $db->NextRow() )
					{
						if( $db->row["CUANTOS"] > 0 ) 
						{
							$con_error = true;
							$errores[] = Array( "id_item" => $array_items[$i],
												"description_error" => "$LBL_PROBLEM_ON_RESERVAS" );
						}
					}
					
					$db->Close();					
				}
			}
			
			if( $con_error ) 
			{				
				// PONER MENSAJE DE QUE ALGUN ITEM NO PUEDE BORRARSE
				$mess = "";
				
				for( $i=0; $i<count($errores); $i++ )
				{
					$mess .= sprintf( "$ERROR_MSG_ON_DELETE_ITEM", $errores[$i]["description_error"], $errores[$i]["id_item"] ) . ".<br>" ;
				}
				
				display_stop_message( "$HINT_PROBLEM_ON_DELETE", 
					"<strong>$HINT_SHOW_PROBLEM :</strong> <br><br>$mess", "javascript:window.close();", 2, 0, 0, 0 );
				/* display_stop_message( $cTitleOrCaption, $singular_name, $buttons=1, $displaynavbar=1, $displaybanner=1, $displaymenu=1 ); */			
				exit;
			}
			else
			{			
				$items = str_replace( "@", "ID_ITEM=", $items ); // 1st ocurrence
				$items = str_replace( ":", " or ID_ITEM=", $items ); // other ocurrences
				
				$db->ExecSQL( "DELETE FROM acervo_copias WHERE ID_BIBLIOTECA=$id_biblioteca and ($items);" );
				
				$items_borrados = $db->rowsAffected;
				
				$db->Destroy();
				
				agregar_actividad_de_usuario( ANLS_EXISTENCES, "$COMMENTS_ACTIONS_DELETE_ITEM $items" );
				
				$error = 30;
			}
		}
		
		if( !allow_use_of_popups() )
			ges_redirect( "anls_existencias_paso2.php?id_titulo=$id_titulo&id_items_deleted=$items_borrados" );
	}
	else
	{
		$action = "create_new";  // acción por default
	}
	
	/** FIN FUNCIONES DE MODIFICACION/CREACION/BORRADO **/
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( ($action == "create_new") ? $LBL_CREATE_EXIST_V1 : $LBL_CREATE_EXIST_V2, "../");
		
?>

<head>
  <base target="_self">
</head>

<script type="text/javascript" src="../basic/calend.js"></script>
<script type="text/javascript" language="JavaScript">

	function validar( e )
	{
		var error = 0;
		
		if( document.edit_form.txt_id_material.value == "" )		
		{
			error = 1;
			alert( "<?php echo $VALIDA_MSG_2;?>" );
			document.edit_form.txt_id_material.focus();
		}

		if( error == 0 )
		{
			document.edit_form.submit();
			return true;
		}
		else
			return false;
	}
	
	function local_extractNumber(obj, decimalPlaces, allowNegative)
	{
		extractNumber(obj, decimalPlaces, allowNegative);
	}

	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
	   return blockNonNumbers(obj, e, allowDecimal, allowNegative)
	}	
	
	window.onload=function()
	{
		prepareInputsForHints();
	}	
	
</script>

<STYLE>

 #caja_datos1 {
   float: none; 
   width: 750px; 
   }
  
  #buttonarea { position:relative; left:14em; } 
  
form.forma_captura label {
   width: 14em;
}

<?php
	if( allow_use_of_popups() )
		echo "#contenedor { width: 790px; margin-top: 10px; } ";
?>

</STYLE>

<body id="home">

<?php
	// barra de navegación superior
	if( !allow_use_of_popups() )
		display_global_nav();
		
	if( $error == 10 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_CREATED_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 20 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$SAVE_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
	else if( $error == 30 )
	{
		SYNTAX_BEGIN_JavaScript();
		echo "alert('$DELETE_DONE');";
		echo "window.opener.document.location.reload();";
		echo "window.close();";
		SYNTAX_CLOSE_JavaScript();
	}
?>

<!-- contenedor principal -->
<div id="contenedor">

<?php 

	if( !allow_use_of_popups() )
	{
		// banner
		display_banner();  
	   
		// menu principal
		display_menu('../');
	}
   
	// combos

	$items_location = "";
	
	$db = new DB();
	
	$db->Open( "SELECT ID_UBICACION, DESCRIPCION FROM cfgubicaciones WHERE ID_BIBLIOTECA=$id_biblioteca ORDER BY EDIFICIO, PISO, SECCION" );
	
	while( $db->NextRow() )
	{
		$str_selected = "";
		if( $db->Field("ID_UBICACION") == $id_location )
			$str_selected = "SELECTED";
		$items_location .= "<option $str_selected value='" . $db->Field("ID_UBICACION") . "'>" . $db->Field("DESCRIPCION") . "</option>";
	}
	
	$db->Close();

 ?>
 
<div id="bloque_principal"> <!-- inicia contenido -->
 
 <div id="contenido_principal">

	<div class=caja_datos id=caja_datos1 >
		<h2>
		<?php 
			if( $action == "create_new" ) echo $LBL_CREATE_EXIST_V1;
			else						  echo $LBL_CREATE_EXIST_V2;
		?>
		</h2>
		<HR>

		<?php
			if( $error == 2 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_UPDATING_SAVING.</strong>";
				echo "</div>";
			}
			else if( $error == 3 )
			{
				echo "<div class=caja_errores>";
				echo " <strong>$ERROR_ID_MATERIAL_ALREADY_IN_DB.</strong>";
				echo "</div><br>";
			}

		 ?>		

			<form action="anls_existencia_titulo.php" method="post" name="edit_form" id="edit_form" class="forma_captura">
			  <input class=hidden type=hidden name="the_action" id="the_action" value="<?php echo $action;?>">
			  <input class=hidden type=hidden name="id_titulo" id="id_titulo" value="<?php echo $id_titulo; ?>">
			  <input class=hidden type=hidden name="id_item" id="id_item" value="<?php echo $id_item; ?>">
			  
				<label><?php echo $LBL_ID_ITEM;?></label>
				<span class="span_captura"><strong><?php echo "[" . $id_item . "]";?></strong></span>
				<br>			  

				<!-- ITEM LOCATION -->
				<dt>
					<label for="cmb_item_location" id="cmb_item_location"><?php echo $LBL_ITEM_LOCATION;?></label>
				</dt>
				
				<dd>
					<select class="select_captura" name="cmb_item_location" id="cmb_item_location">
						<?php echo $items_location;?>
					</select>				
					
					<span class="sp_hint"><?php echo $HINT_ITEM_LOCATION;?><span class="hint-pointer">&nbsp;</span></span>
					
				</dd>
			  
				<!-- LOAN CATEGORY -->
				<dt>
					<label for="txt_max_dias_prestamo"><?php echo $LBL_LOAN_CATEGORY;?></label>
				</dt>
				<dd>
					<?php
						// categoria 6 
						combo_from_tesauro( "cmb_loan_category", getsessionvar("id_red"), 6, $id_loan_category );
					?>					
					
					<span class="sp_hint"><?php echo $HINT_ITEM_CATEGORY;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<!-- ID DEL ITEM -->
				<dt>
					<label for="txt_id_material"><?php echo $LBL_ID_MATERIAL;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_id_material" id="txt_id_material" value="<?php echo $id_material;?>" size=50 maxlength=50/>
					<span class="sp_hint"><?php echo $HINT_ID_ITEM;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<br><br>
				
				<!-- IDENTIFICADORES UNICOS DE LA COPIA -->
				<!-- VOL NUMBER -->
				<?php if( $is_series == 1) { ?>
					<dt>
						<label for="txt_part_number"><?php echo $LBL_VOL_NUMBER;?></label>
					</dt>
					<dd>
						<input class="campo_captura" type="text" name="txt_vol_number" id="txt_vol_number" value="<?php echo $serie_vol_number;?>" size=10 maxlength=3 onblur="local_extractNumber(this,0,false);" onkeypress="return local_blockNonNumbers(this, event, false, false);">
						<span class="sp_hint"><?php echo $HINT_VOL_NUMBER;?><span class="hint-pointer">&nbsp;</span></span>
					</dd>
				<?php 
					} 
				?>
				
				<!-- PART NUMBER -->
				<dt>
					<label for="txt_part_number"><?php echo $LBL_PART_NUMBER;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_part_number" id="txt_part_number" value="<?php echo $part_number;?>" size="40" maxlength="50" style='display:inline'/>
					
					<?php if( $is_series == 1) { ?>
					&nbsp;&nbsp;&nbsp;<div style='display:inline'><input type='checkbox' class='check' id='chk_num_especial' name='chk_num_especial' <?php echo ($serie_esp_number=="S") ? "checked" : ""; ?>>&nbsp;&nbsp;<?php echo $LBL_SERIE_NUMESP;?></div>
					<?php } ?>
					
					<span class="sp_hint"><?php echo $HINT_PART_NUMBER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>
				
				<!-- DATOS DE UN NUMERO (SERIES) -->
				<?php if( $is_series == 1) { ?>
				<dt>
					<label><?php echo $LBL_SERIE_DATA_ID;?></label>
				</dt>
				<dd>
					<div style='display: inline; border: 0px solid red; width: 100px;'><?php echo $LBL_SERIE_EPOCH;?>

						<select class="select_captura" style="display: inline; float:none; margin-right:5px;" name="cmb_serie_epoca" id="cmb_serie_epoca">
							<option value='0'>--</option>
							<option value='1'>1</option>
							<option value='2'>2</option>
							<option value='3'>3</option>
							<option value='4'>4</option>
							<option value='5'>5</option>
							<option value='6'>6</option>
							<option value='7'>7</option>
							<option value='8'>8</option>
							<option value='9'>9</option>
							<option value='10'>10</option>
						</select>

					</div>
					<div style='display: inline; border: 0px solid blue; width: 200px;'><?php echo $LBL_SERIE_ANIO;?>
						<input class="campo_captura"  type="text" name="txt_anio" id="txt_anio" value="<?php echo $serie_anio;?>" size=9 maxlength=9 style='display: inline; float:none; margin-right:5px;'>&nbsp;&nbsp;
					</div>
					<div style='display: inline; border: 0px solid green; width: 200px; '><?php echo $LBL_SERIE_MES;?>
						<select class="campo_captura" name="cmb_mes" id="cmb_mes">
							<option value='0'>---</option>
							<?php
							
							  for( $i = 0; $i<count($arrayMeses); $i++ )
							  {
								$str_selected = ($serie_mes == $i+1) ? "selected" : "";
								
								echo "<option value='" . ($i+1) . "' $str_selected>" . $arrayMeses[$i] . " &nbsp;</option>";
							  }							
							
							 ?>
						</select>
					</div>
					
					<span class="sp_hint"><?php echo $HINT_SERIE_DATA_ID;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				
				<br><br>

				<!-- TITULO PRINCIPAL -->
				<dt>
					<label for="txt_date_of_reception"><?php echo $LBL_SERIE_MAINTITLE;?></label>
				</dt>
				<dd>
					<input type="text" class="campo_captura" name="txt_serie_titulo_principal" id="txt_serie_titulo_principal" value="<?php echo $serie_titulo_principal;?>" size="80" maxlength="100">
					<span class="sp_hint"><?php echo $HINT_MAIN_TITLE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<!-- TITULO SECUNDARIO -->
				<dt>
					<label for="txt_date_of_reception"><?php echo $LBL_SERIE_ALTTITLE;?></label>
				</dt>
				<dd>
					<input type="text" class="campo_captura" name="txt_serie_titulo_secundario" id="txt_serie_titulo_secundario" value="<?php echo $serie_titulo_secundario;?>" size="80" maxlength="100">
					<span class="sp_hint"><?php echo $HINT_MAIN_ALTTITLE;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				

				<!-- PAPEL O ELECTRONICO -->
				<dt>
					<label for="txt_date_of_reception"><?php echo $LBL_SERIE_PAPEL_ELECTRONICO;?></label>
				</dt>
				<dd>
					<select class='select_captura' name="cmb_serie_papel_electronico" id="cmb_serie_papel_electronico">
						<option value='P'><?php echo $LBL_ITEMS_PAPER;?></option>
						<option value='E'><?php echo $LBL_ITEMS_ELECTRONIC;?></option>
					</select>
				</dd>				

				<!-- FECHA DE PUBLICACION -->
				<dt>
					<label for="txt_date_of_publish"><?php echo $LBL_DATE_OF_PUBLISH;?></label>
				</dt>
				<dd>
					<?php
						colocar_edit_date( "txt_date_of_publish", "$serie_date_of_publish", 0, "" );
					?>				
					<span class="sp_hint"><?php echo $HINT_DATE_PUBLISH;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				

				<?php 
					} 
				?>			 

				<!-- FECHA DE RECEPCION -->
				<dt>
					<label for="txt_date_of_reception"><?php echo $LBL_DATE_OF_RECEPTION;?></label>
				</dt>
				<dd>
					<?php
						colocar_edit_date( "txt_date_of_reception", "$date_of_reception", 0, "" );
					?>
					<span class="sp_hint"><?php echo $HINT_DATE_RECEPTION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<!-- COSTO DE ADQUISICION -->
				<dt>
					<label for="txt_date_of_reception"><?php echo $LBL_COST_ADQUISICION;?></label>
				</dt>
				<dd>
					<input type="text" class="campo_captura" name="txt_cost_adq" id="txt_cost_adq" value="<?php echo $cost_adq;?>" size="15" maxlength="15" onblur="local_extractNumber(this,2,false);" onkeypress="return local_blockNonNumbers(this, event, true, false);">
					<span class="sp_hint"><?php echo $HINT_COST_ACQUISICION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				

				<!--  ID ACQUISICION -->
				<dt>
					<label for="txt_id_acquisicion"><?php echo $LBL_ID_ACQUISICION;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_id_acquisicion" id="txt_id_acquisicion" value="<?php echo $id_acquisicion;?>" size=10 maxlength=10>
					<span class="sp_hint"><?php echo $HINT_ID_ACQUISICION;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<br>

				<!-- CALL NUMBER -->
				<dt>
					<label><?php echo $LBL_CALLNUMBER;?></label>
				</dt>
				<dd>
					<div style='display: inline; border: 0px solid red; width: 100px;'><?php echo $LBL_CALLNUMBER_PREFIX;?>
						<input class="campo_captura" type="text" name="txt_callnumber_prefix" id="txt_callnumber_prefix" value="<?php echo $call_number_prefix;?>" size=5 maxlength=10 style='display: inline; float:none; margin-right:5px;'>&nbsp;
					</div>
					<div style='display: inline; border: 0px solid blue; width: 200px;'><?php echo $LBL_CALLNUMBER_CLASS;?>
						<input class="campo_captura"  type="text" name="txt_callnumber_class" id="txt_callnumber_class" value="<?php echo $call_number_class;?>" size=20 maxlength=50 style='display: inline; float:none; margin-right:5px;'>&nbsp;&nbsp;
					</div>
					<div style='display: inline; border: 0px solid green; width: 200px; '><?php echo $LBL_CALLNUMBER_BOOK;?>
						<input class="captura"  type="text" name="txt_callnumber_book" id="txt_callnumber_book" value="<?php echo $call_number_book;?>" size=20 maxlength=50 style='display: inline; float:none; margin-right:5px;'>
					</div>

					<span class="sp_hint"><?php echo $HINT_CALL_NUMBER;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<dt>
					<label for="txt_max_dias_prestamo"><?php echo $LBL_ITEM_STATUS;?></label>
				</dt>
				<dd>
					<select class="select_captura"  name="cmb_item_st" id="cmb_item_st" >
						<option value="D" <?php echo ( $item_status == "D"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_AVAILABLE;?></option>
						<option value="I" <?php echo ( $item_status == "I"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_AVAILABLE_ONLY_INTERNAL;?></option>
						<option value="P" <?php echo ( $item_status == "P"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_BORROWED;?></option>
						<option value="B" <?php echo ( $item_status == "B"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_BLOCKED;?></option>
						<option value="R" <?php echo ( $item_status == "R"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_RESERVED;?></option>
						<option value="X" <?php echo ( $item_status == "X"? "SELECTED" : ""); ?> ><?php echo $LBL_STATUS_DISABLED;?></option>
					</select>
					<span class="sp_hint"><?php echo $HINT_ITEM_STATUS;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<!-- PHYS ST -->
				<dt>
					<label for="txt_max_dias_prestamo"><?php echo $LBL_ITEM_PHYSICAL_ST;?></label>
				</dt>
				<dd>
					<?php
						// categoria 12 
						combo_from_tesauro( "cmb_physical_st", getsessionvar("id_red"), 12, $id_physical_st, 70 );
					?>
					<span class="sp_hint"><?php echo $HINT_PHYSICAL_ST;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>

				<!-- MAT. ADIT. -->
				<dt>
					<label for="txt_id_acquisicion"><?php echo $LBL_MATERIAL_ADITIONAL;?></label>
				</dt>
				<dd>
					<input class="campo_captura" type="text" name="txt_material_adicional" id="txt_material_adicional" value="<?php echo $material_adicional;?>" size=50 maxlength=100>
					<span class="sp_hint"><?php echo $HINT_MATERIAL_ADITIONAL;?><span class="hint-pointer">&nbsp;</span></span>
				</dd>				

				<br>

			  <div id="buttonarea">
				<input id="btnGuardar" name="btnGuardar" class="boton" type="button" value="<?php echo $BTN_SAVE;?>"  onClick='javascript:validar();'>&nbsp;
				<input id="btnCancelar" name="btnCancelar" class="boton" type="button" value="<?php echo $BTN_CANCEL;?>" onClick='<?php echo back_function();?>'>
			  </div>
			  <br> <!-- for IE -->
			  
			</form>
	  
	</div> <!-- caja_datos --> 

 </div> <!-- contenido_principal -->

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

	<?php  

		if( !allow_use_of_popups() )
			display_copyright(); 
			
	?>

</div><!-- end div contenedor -->

</body>

</html>