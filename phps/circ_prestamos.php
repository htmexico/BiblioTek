<?php
	session_start();

	/**********
	
	28-enero-2009	Se crea el archivo circulacion.php
	25/03/2009		se inicia reconstruccion de interfaz visual
	13/04/2009 		Se u nacomodo de codigo para tener mejor entendimiento del mismo
					se agrego la funcion data_for_database_update
	21-jul-2009:	Se adapta para que reciba id_usuario en lugar de procesar las búsquedas
	21-jul-2009:	Se adapta para eliminar con un icon en lugar de checkboxes
					
	25-nov-2009:	Se agrega loadBlockedItems()
					
		PENDIENTES:

		- Validar que el usuario no supere los días max. de permanencia con un ITEM prestado (VALIDAR fecha)
		- Validar que la aplicación coloque en alguna parte visible las copias bloqueadas para algún usuario.
		
		- VERIFICAR QUE LA FECHA_DEVOLUCION_PROGRAMADA se calcule incluyendo HORA
				
	**********/
 
	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include ("circulacion.inc.php" );
	
	include_language( "global_menus" );
	include_language( "circ_prestamos" );

	check_usuario_firmado(); 
	
	$grupo		= "";
	$nombre_usuario = "";

	$max_dias_prestamo			 = 0;
	$max_items					 = 0;
	$items_actualmente_prestados = 0;
	$id_item					 = 0;
	
	// datos de sesión
	$usuario=getsessionvar('usuario');
	$id_biblioteca =getsessionvar('id_biblioteca');
	
	// fecha del préstamo
	$fechaprestamo = read_param( "fechaprestamo", getcurdate_human_format() );
	$horaprestamo  = read_param( "horaprestamo", current_time() );
	$id_usuario = read_param( "id_usuario", 0, 1 );
	$load_blocked_items = read_param( "load_blocked_items", 0 );
	
	if( strlen( $horaprestamo > 5 ) )
		$horaprestamo = substr( $horaprestamo, 0, 5 );

	$agregar_item = read_param( "agregar_item", 0 );
	
	$imposible_por_restricciones = false;
	
	// banderas de error
	$error = 0;
	$error_message = "";
	
	// banderas para desplegar INFO
	$info = 0;
	$info_message = "";
	
	$db = new DB("");
	
	// 1 = Insertar el PRESTAMO
	$agregarinsert = read_param( "agregar_insert", 0 );
	
	$historial = read_param( "historial", "" );
	
	//
	// codigo para insertar los registros en la base de datos 
	// 
	// INSERT INTO ...
	//
	if( $agregarinsert == 1 )
	{	
		$items_en_prestamo = split( ";", $historial );
		
		$bandera_todos_items_ok = 1;
		
		foreach( $items_en_prestamo as $tmp_item )
		{									
			$tmp_item = str_replace( ".", "#", $tmp_item );
			
			$item_alone = split( "#", $tmp_item );

			$id_item   = $item_alone[0];
			$fecha_dev = $item_alone[1];
			
			//
			// 2da. VALIDACION - Validar si el item está libre para las fechas solicitadas
			//
			$item = new TItem_Basic( $id_biblioteca, $id_item, 1 );
			
			$array_no_usado_aqui = Array();

			if( !$item->VerificarDisponibilidad_X_ITEM( $item->nIDItem, "S", $fechaprestamo, $fecha_dev, $array_no_usado_aqui, 15, 1, 0, $id_usuario ) )
			{
				$bandera_todos_items_ok = 0;
			}
			
			$item->destroy();
		}

		unset( $tmp_item );
		
		if( $bandera_todos_items_ok == 1 )
		{
			// busqueda ultimo registro (ID_PRESTAMO) de la tabla PRESTAMOS_MST para incrementar su valor 
			// y asi no tener registros duplicados
			$db->Open( "SELECT COUNT(*) AS CUANTOS, MAX(ID_PRESTAMO) AS MAXIMO FROM PRESTAMOS_MST WHERE ID_BIBLIOTECA=$id_biblioteca; " );

			if( $db->NextRow() )
			{
				if($db->row["CUANTOS"]==0)
				{ $prestamo=1; }
				else
				{ $prestamo = $db->row["MAXIMO"] + 1; }
			}
			$db->Close();
			// se ha determinado el ID_PRESTAMO que se insertará

			$usuario_presta = getsessionvar( "id_usuario" );

			// se agregan segundos
			$fechaprestamo =  date_for_database_updates( $fechaprestamo ) . " " . $horaprestamo . ":00";

			// se inserta registro
			$db->ExecSQL( "INSERT INTO PRESTAMOS_MST (ID_BIBLIOTECA, ID_PRESTAMO, ID_USUARIO, FECHA_PRESTAMO, USUARIO_PRESTA ) " .
						  " VALUES ( $id_biblioteca, $prestamo, $id_usuario, '$fechaprestamo', $usuario_presta);" );

			foreach( $items_en_prestamo as $tmp_item )
			{
				$tmp_item = str_replace( ".", "#", $tmp_item );

				$item_alone = split( "#", $tmp_item );

				$id_item   = $item_alone[0];
				$fecha_dev = $item_alone[1];

				// convertir la fecha de devolucion y hacer similitud con la hora del prestamo
				$unix_devolucion = convert_humandate_to_unixstyle( $fecha_dev . " " . $horaprestamo . ":00", 1 );			
				$unix_devolucion = $unix_devolucion - (1 * 60);  // Restarle un minuto
				
				$the_date = encodedate_to_human_format( $unix_devolucion, 1 );  // convertir a fecha en formato humano
				$fecha_dev = date_for_database_updates( $the_date );  // pasar a formato de DB
				
				$db->ExecSQL( "INSERT INTO PRESTAMOS_DET (ID_BIBLIOTECA, ID_PRESTAMO, ID_ITEM, FECHA_DEVOLUCION_PROGRAMADA, FECHA_DEVOLUCION, STATUS ) " . 
							  " VALUES( $id_biblioteca, $prestamo, $id_item, '$fecha_dev', NULL, 'P' ); " );
			}
			
			unset( $tmp_item );
			
			$db->destroy();
			
			require_once("../actions.inc.php");
			agregar_actividad_de_usuario( CIRC_LOANS, "", $id_item );
			
			ges_redirect( "circ_prestamos_end.php?id_usuario=$id_usuario&id_prestamo=$prestamo" );
			
		}
		else
		{
			// el status disponible de UN ITEM ha cambiado de ultimo momento
			$error_message = $ALERT_WRONG_STATE_CHANGED;
		}
	}
	
	//
	// VALIDAR USUARIO
	// 

	if( $id_usuario != 0 )
	{	
		$user = new TUser( $id_biblioteca, $id_usuario, $db );
		
		if( $user->NOT_FOUND )
		{
			SYNTAX_JavaScript( 1, 1, "alert( '$ALERT_WRONG_USER_NOT_FOUND' );" );
		}
		else
		{
			if( $user->STATUS == "A" )
			{
				$username = $user->USERNAME;
				$nombre_usuario = $user->NOMBRE_COMPLETO;					
				$grupo = $user->NOMBRE_GRUPO;

				$max_items 			= $user->GRUPO_MAX_ITEMS_PRESTADOS;
				$max_dias_prestamo  = $user->GRUPO_MAX_DIAS_PRESTAMO;
				$items_actualmente_prestados = $user->ObtenerNumItemsPrestados();
				
				$sanciones = 0;
				$restricciones = 0;
				
				$max_renovaciones  = $user->GRUPO_MAX_RENOVACIONES;				
				$dias_renovacion   = $user->GRUPO_DIAS_RENOVACION_DEFAULT;
				$permite_renovacion_con_retraso = $user->GRUPO_PERMITIRRENOVA_CON_RETRASO; //GRUPO_PERMITIRRENOVACIONCONRETRASO;
				
				$items_bloqueados_para_usuario = $user->ObtenerNumItemsBloqueados();
				
				if( $user->GRUPO_MAX_ITEMS_PRESTADOS == 0 )
				{
					$error = 1;
					$error_message = "$ALERT_USER_NOT_ALLOW_LOANS";			
					$imposible_por_restricciones = true;  // RESTRINGIDOS LOS PRESTAMOS
				}
				
				if( $user->GRUPO_PERMITIRPRESTAMOS_CON_SANCIONES == "N" ) 
				{
					$sanciones = $user->ObtenerNumSanciones();
					if( $user->ObtenerNumSanciones() > 0 )
					{
						$error = 1;
						$error_message = "$ALERT_WRONG_USER_SANCTION";
					}
				}
				
				$restricciones = $user->ObtenerNumRestricciones(3);  // 3 = Restricciones sobre prestamos
				
				if( $restricciones > 0 )
				{
					$error = 1;
					$error_message = "$ALERT_WRONG_USER_RESTRICTION";
					$imposible_por_restricciones = true;
				}
				
			}
			else 
			{
				$error = 1;
				echo "<br><div class=caja_info>";
				echo " <strong> $ALERT_WRONG_USER_STATUS </strong>";
				echo "</div>";
			}
		}
		
		$user->destroy();
		
	}
	
	//
	// Esta accion se debe hacer 
	// después de obtener max_dias_prestamo
	//
	if( $load_blocked_items == 1 )
	{
		$fechaprestamo_v2 =  date_for_database_updates( $fechaprestamo ); // . " " . $horaprestamo . ":00";
		
		$fechaprestamo = $fechaprestamo . " " . $horaprestamo . ":00";
		$fecha_devol = sum_days( $fechaprestamo, $max_dias_prestamo );
		
		$db->Open( "SELECT a.ID_TITULO, a.ID_ITEM " .
				   " FROM acervo_copias a " .
				   "WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.BLOQUEADO_PARA_USUARIO=$id_usuario and a.STATUS='B' and ('$fechaprestamo_v2' BETWEEN a.BLOQUEADO_DESDE and a.BLOQUEADO_HASTA) ); " );

		while( $db->NextRow() )
		{
			$id_item = $db->row["ID_ITEM"];

			$historial .= "$id_item.$fecha_devol;";
		}
		
		$db->Close();
		
	}

	$txt_id_material = read_param( "txt_id_material", "" );
	$array_material = explode( ";", $historial );
	
	//print_r( $array_material );

	$limite_alcanzado = 0;

	//
	// verificar si el usuario está agregando un item a su lista de 
	// libros prestados
	//
	if( $agregar_item == 1 )
	{
		if( $txt_id_material != "" )
		{
			if( $error == 0 )
			{
				$fecha_devol = sum_days( $fechaprestamo, $max_dias_prestamo );
			
				// validar que el item solicitado por el usuario se encuentre dentro de acervo_copias
				// y que éste esté en estatus activo para su prestamo
				$item = new TItem_Basic( $id_biblioteca, $txt_id_material, 2 );
				
				if( !$item->NOT_FOUND )
				{
					if( $item->cStatus != "D" )
					{
						//
						// PENDIENTE: falta verificar la fecha de HOY contra Bloqueado_desde y Bloqueado_Hasta
						//
						if( $item->cStatus == "B" and $item->Bloqueado_para_ID_Usuario == $id_usuario )
						{
							$bStatusOK = true;
						}
						else
						{
							$error = 5;
							$error_message = sprintf( $ALERT_WRONG_ITEM_DATES_OP1, "'" . $item->cTitle . "'" );

							$bStatusOK = false;
						}
					}
					else
						$bStatusOK = true;
					
					if( $bStatusOK )
					{
						$error = 0;
						
						// validar que el ID_ITEM del material no esté en el historial previo
						// es decir, evitar la duplicidad
						$array_material = explode( ";", $historial );
						
						foreach( $array_material as $tmp_item )
						{
							if( $tmp_item != "" )
							{
								$tmp_item = str_replace( ".", "#", $tmp_item );
								$item_alone = split( "#", $tmp_item );
								
								if( $item_alone[0] == $item->nIDItem )
								{
									$error = 1;
									$error_message = sprintf( $ALERT_WRONG_DUPLICATION, "'" . $txt_id_material . "'" );
									break;
								}
							}
						}						
						
						if( $error == 0)
						{
							$item_x = $item->nIDItem . "." . $fecha_devol;
							
							$historial .= $item_x;
							
							if( $historial != "" )
								$historial .= ";";
							
							$array_material = explode( ";", $historial );
							
							$txt_id_material = "";
						}
					}
					
				}
				else
				{
					$error = 1;
					$error_message = "$ALERT_WRONG_ITEM_NOT_FOUND";
				}
				
				$item->destroy();
			}
		}
	}
	
	//
	// verificar exceso de items
	//
	if( $error == 0 )
	{
		$count_in_array = 0;
		
		foreach( $array_material as $id_item )
		{
			if( $id_item != "" )
			{	$count_in_array++; }
		}
		
		unset( $id_item );
	
		if( ($count_in_array + $items_actualmente_prestados) >= $max_items )
		{
			$error = 1;
			
			if( $error_message != "" )
				$error_message .= "<br>";

			$error_message .= sprintf( $ALERT_ON_LIMIT_MAX, $max_items );
			
			if( $items_actualmente_prestados > 0 )
			{
				$error_message .= "&nbsp;(<img src='../images/icons/warning.gif'>&nbsp;" . sprintf( $HINT_MAX_ITEMS_ALREADY_HAD, $items_actualmente_prestados ) . ")" ;
			}
			
			$limite_alcanzado = 1;
		}
	}	
	
	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( $LBL_LOAN_HEADER, "../" );	
?>

<script type='text/javascript' src='../basic/calend.js'></script>

<SCRIPT language='JavaScript'>

	var errores_items = [];

	function newOne()
	{
		location.href = "gral_elegir_usuario.php?the_action=prestamos";
	}
	
	function guardarPrestamo()
	{
		var total = js_getElementByName("numero_total_items");
		
		if( !total ) return false;
		
		if( parseInt(total.value) == 0 )
		{
			alert( "<?php echo $ALERT_ERRORS_NOT_ITEMS_IN_LIST;?>" );
			return false;
		}
	
		if( !EsFechaValida( document.agregar_form.fechaprestamo ) )
			alert( "<?php echo $ALERT_WRONG_ERROR_DATE_FORMAT;?>" );
		else
		{
			var bError = false;
			var total = js_getElementByName("numero_total_items");
			
			if( errores_items.length > 0 ) 
			{
				alert( "<?php echo $ALERT_ERRORS_IN_LIST;?>" );
				bError = true;
			}
			
			if ( total && !bError)
			{
				// Agregar los elementos MARCADOS de la bandeja (CHECKED+MODALIDAD+FECHA)
				for( var i = 1	; i<=parseInt(total.value); i++ )
				{
					var fecha_dev = js_getElementByName( "fecha_dev_"+i );
					
					if( !EsFechaValida( fecha_dev ) )
					{
						bError = true;
						alert( "<?php echo $ALERT_WRONG_ERROR_DATE_FORMAT;?>" + " (item "+i+ ")" );
						fecha_dev.focus();
						exit;
					}
				}
			}
			
			if( !bError )
			{				
				if( confirm( octal("<?php echo $MSG_CONFIRM_BEFORE_LOAN;?>") ) )
				{
					location.href = "../phps/circ_prestamos.php?id_usuario=" + document.agregar_form.id_usuario.value + "&agregar_insert=1&" +
									"&fechaprestamo=" + document.agregar_form.fechaprestamo.value +
									"&horaprestamo=" + document.agregar_form.horaprestamo.value +					
									"&historial=" + CreateComplementURL();
				}
			}
		}

	}
	
	// crea la parte complementaria de la URL (para reload)
	// con base en el historial
	function CreateComplementURL()
	{
		var ret = "";
		var total = js_getElementByName("numero_total_items");

		if ( total )
		{
			// Agregar los elementos MARCADOS de la bandeja (CHECKED+MODALIDAD+FECHA)
			for( var i = 1; i<=parseInt(total.value); i++ )
			{
				var id_item = js_getElementByName( "id_item_"+i );
				var fecha_dev = js_getElementByName( "fecha_dev_"+i );

				if( ret != "" )
				   ret += ";";

			    // ID ITEM + FECHA DEVOLUCION
				ret += id_item.value + "." + fecha_dev.value;
			}
		}
		
		return ret;
	}	
	
	function validaMaterial()
	{
		if( document.agregar_form.txt_id_material.value == "" )
		{
			alert("<?php echo $ALERT_WRONG_ADD_ITEM; ?>");
			return false;
		}
		else
		{
			location.href = "circ_prestamos.php?id_usuario=" + document.agregar_form.id_usuario.value + 
					        "&fechaprestamo=" + document.agregar_form.fechaprestamo.value +
							"&horaprestamo=" + document.agregar_form.horaprestamo.value +
							"&agregar_item=1&" +
						    "txt_id_material=" + document.agregar_form.txt_id_material.value + "&historial=" + document.agregar_form.historial.value;
		}
	}
	
	//
	// borra el contenido de material
	// 
	function delete_item( id_item )
	{
		var check;
		var historial = document.agregar_form.historial.value;
		
		var aArray = historial.split(";");
		var xItem;
		var new_historial = "";
		
		for( var i=0; i<aArray.length-1; i++ )
		{
			xItem = aArray[i];
			
			var aArray_Item = xItem.split(".");
			
			if( aArray_Item[0] != id_item )
			{
				// no está marcado continuar
				if( new_historial != "" ) new_historial += ";";
					new_historial += aArray[i];
			}
		}
		
		new_historial += ";";
		
		location.href = "circ_prestamos.php?id_usuario=" + document.agregar_form.id_usuario.value + 
				        "&fechaprestamo=" + document.agregar_form.fechaprestamo.value +
						"&horaprestamo=" + document.agregar_form.horaprestamo.value +
						"&historial=" + new_historial;
	}

	function elegirMaterial()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		window.open( "gral_buscartitulo.php?ver_copias=1", "usuarios", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-330) + ",TOP=50,LEFT=80,resizable=yes,scrollbars=yes,status=yes" );
		window.status='';
	}	
	
	function updateErrorState( obj )
	{
		location.href = "circ_prestamos.php?id_usuario=" + document.agregar_form.id_usuario.value + 
				        "&fechaprestamo=" + document.agregar_form.fechaprestamo.value +
						"&horaprestamo=" + document.agregar_form.horaprestamo.value +
						"&historial=" + CreateComplementURL();
	}
	
	// Sirve para hacer un submit cuando se oprime la tecla enter
	function submit_enter_material( myfield, e )
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
	
		if (keycode == 13)
		{			
			validaMaterial();
			return false;	
		}
		else
			return true;
	}	
	
	window.onload=function()
	{
		prepareInputsForHints();
		
		<?php 	
			if( $id_usuario != 0 )
			{
				if( !$limite_alcanzado and !$imposible_por_restricciones)
					echo "document.agregar_form.txt_id_material.focus();";
			}
		 ?>
		
	}
	
	//
	// 25nov2009
	//
	function loadBlockedItems()
	{
		location.href = "circ_prestamos.php?id_usuario=" + document.agregar_form.id_usuario.value + 
						"&fechaprestamo=" + document.agregar_form.fechaprestamo.value +
						"&horaprestamo=" + document.agregar_form.horaprestamo.value +
						"&load_blocked_items=1"  +
						"&historial=" + CreateComplementURL();	
	}
	
</SCRIPT>

<STYLE>

    .sp_hint { width: 300px; }
	
	#nombre_usuario 
	{ 
		float: none;
		display: block; 
		position: relative;
		width: 45em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
		overflow: auto;
	}	
	
	#lbl_id_title
	{
		position: absolute;
		border: 1px dotted blue; 
		display: inline;
		left: 35em;
		padding: 3px;
	}
	
	#caja_datos {
		width: 140%; 
	}

</STYLE>

<body id="home">
	
	<?php
		display_global_nav();  // barra de navegación superior
	?>
	
	<div id="contenedor" class="contenedor"> 
		<?php 
			display_banner();  // banner			
			display_menu('../'); // menu principal
		?>
		
		<div id="bloque_principal"> 
			<div id="contenido_principal">			    
				<div class="caja_datos" id="caja_datos"> 				
					<H1><?php echo $LBL_LOAN_HEADER; ?> </H1>
					<hr>
					<h4><?php echo $LBL_LOAN_HEADER_SUB; ?></h4>
					
					<?php					
						if( $error != 0 )
						{
							echo "<br><div class='caja_errores'>";
							echo " &nbsp;" . (( $imposible_por_restricciones ) ? "<img src='../images/icons/user_blocked.gif'>" : "" ) . "&nbsp;&nbsp;<strong>$error_message</strong>";
							echo "</div>";
						}
						
						if( $info != 0 )
						{
							echo "<br><div class='caja_info'>";
							echo " <strong>&nbsp;&nbsp;&nbsp;$info_message</strong>";
							echo "</div>";
						}

					 ?>
					
					<br>
					<form name="agregar_form" id="agregar_form" class="forma_captura">

						<!--id_usuario -->
						<input class="hidden" type="hidden" id="id_usuario" name="id_usuario" value="<?php echo $id_usuario; ?>">
					
						<label for="txt_usuario"><strong><?php echo $LBL_ID_USER; ?></strong></label>

						<!--valida si el boton a btnUsuario_id a sido activado-->
						<div id="nombre_usuario" name="nombre_usuario">
							<img src="../images/icons/user.gif">&nbsp;

							<?php
								echo $nombre_usuario . "&nbsp;($username)<br> $grupo (" . sprintf( $HINT_MAX_ITEMS_4_LOAN, $max_items ) . ")"; 

								if( $items_actualmente_prestados > 0 )
								{
									echo "&nbsp;<img src='../images/icons/warning.gif'>&nbsp;";
									echo sprintf( $HINT_MAX_ITEMS_ALREADY_HAD, $items_actualmente_prestados );
								}

								if( $error <> 0 and $error != 5 )
								{
									echo "&nbsp;<br><img src='../images/icons/warning.gif'>&nbsp;$error_message<br>";
								}

								if( $items_bloqueados_para_usuario > 0 )
								{
									echo "&nbsp;<br><img src='../images/icons/item_available.gif'>&nbsp;" . sprintf( "$HINT_ITEMS_BLOCKED", $items_bloqueados_para_usuario ) . "<br>";
								}
								
							?>

						</div>
						<br>
						
						<?php if( !$imposible_por_restricciones ) { ?>
						<dt>
							<label for="fechaprestamo"><strong><?php echo $LBL_DATE_LOAN; ?></strong></label>
						</dt>
						<dd>
							<span><?php echo $fechaprestamo . " " . $horaprestamo;?></span>
							<input type='hidden' class='hidden' value="<?php echo $fechaprestamo;?>" name='fechaprestamo' id='fechaprestamo'>
							<input type='hidden' class='hidden' value="<?php echo $horaprestamo;?>" name='horaprestamo' id='horaprestamo'>
							<span class="sp_hint"><?php echo $HINT_TYPE_CURRENT_DATE;?><span class="hint-pointer">&nbsp;</span></span>
						</dd>
						<br>
						
						<?php } ?>
						
						<?php
						  $show_txt_id_material = 1;
						  $code_disableb_4_material = "";

						  if ($nombre_usuario=="" or $limite_alcanzado==1)
						  {
							$show_txt_id_material = 0; // no mostrar los controles de edición de material
							$code_disableb_4_material = "disabled";
						  }
						 ?>
						 
						 <?php if( !$imposible_por_restricciones ) { ?>
						 
						<dt>
							<label for="txt_id_material"><strong><?php echo $LBL_ID_ITEM; ?></strong></label>
						</dt>
						<dd>
							<input class="campo_captura" <?php echo $code_disableb_4_material; ?> type="text" name="txt_id_material" id="txt_id_material" size=25 maxlength=50 value="<?php echo $txt_id_material;?>" onkeypress="return submit_enter_material(this,event);" style='display:inline;'>
							
							<input class="boton" <?php echo $code_disableb_4_material; ?> type="button" value="<?php echo $BTN_VALIDATE;?>" name="btnMaterial_id" id="btnMaterial_id" onClick="javascript:validaMaterial();">
							<input class="boton" <?php echo $code_disableb_4_material; ?> type="button" value="?" name="btnMaterial" id="btnMaterial" onClick="javascript:elegirMaterial();">

							<?php
							
							  if( $items_bloqueados_para_usuario > 0 and $load_blocked_items==0)
							  {
								echo "<input class='boton' type='button' value='$BTN_LOAD_BLOCKED_ITEMS' name='btnLoadBlockedItems' id='btnLoadBlockedItems' onClick='javascript:loadBlockedItems();' >";
							  }
							
							 ?>
							
							<div id="lbl_id_title" name="lbl_id_title" style='visibility:hidden'>&nbsp;</div>
							<span class="sp_hint"><?php echo $HINT_TYPE_MATERIAL_CODE;?><span class="hint-pointer">&nbsp;</span></span>
						</dd>
						<br>
						
						<?php } ?>
						
						<?php

							// busca libros a imprimir del valor de historial, donde el parametro "historial"
							// lo encontramos en los parametro del explorador que pasamos con POST ó GET

							if( count($array_material) > 1 )
							{
								echo "<table border=1 width='100%'>";
								echo "<tr>";
								echo "<td align='center' class='cuadricula columna columnaEncabezado'></td>";
								echo "<td align='center' class='cuadricula columna columnaEncabezado'>$LBL_TABLE_TITLE0</td>";
								echo "<td align='center' class='cuadricula columna columnaEncabezado'>$LBL_TABLE_TITLE1</td>";
								echo "<td align='center' class='cuadricula columna columnaEncabezado'>$LBL_TABLE_TITLE2</td>";																
								echo "</tr>";

								$i = 0;

								foreach( $array_material as $id_item )
								{
									if( $id_item != "" )
									{
										$array_item = explode( ".", $id_item );

										$i++;
										
										$fecha_dev = $array_item[1];

										// Busqueda de libros que se han ido agregando al parametro historial
										// es un ITEM, es decir una copia directamente
										$item = new TItem_Basic( $id_biblioteca, $array_item[0], 1, $db );

										$error = 0;  // para validar cualquier errr
										
										/* VALIDAR FECHAS */
										$fechas_disponibles = Array();
										$error_fechas = "";
										$disponible_en_fechaprestamo = 0;
										
										if( !$item->VerificarDisponibilidad_X_ITEM( $item->nIDItem, "S", $fechaprestamo, $fecha_dev, $fechas_disponibles, 15, 1,
												0, $id_usuario ) )
										{
											$error = 1;
											
											SYNTAX_JavaScript( 1, 1, " errores_items.push( $i ); "  );
											
											$error_fechas = "";
											
											for( $ij=0; $ij<count($fechas_disponibles); $ij++ )
											{
												if( $error_fechas != "" ) $error_fechas .= ", ";
												
												$error_fechas .= $fechas_disponibles[$ij][1];
												
												if( $fechas_disponibles[$ij][1] == $fechaprestamo ) 
													$disponible_en_fechaprestamo = 1;
											}											
										}	

										unset( $fechas_disponibles );			
										
										$class_x = "columna";
										
										if( $error == 1 )
										   $class_x = "columnaError";
										
										//depliege de libro, checkbox, validaciones
										echo "<tr>";
										echo "<td class='cuadricula $class_x' width='5%' align='center' valign='center'>";
										
										if( $error == 1 )
											echo "<img src='../images/icons/warning.gif'><br>";
										
										echo "<a href='javascript:delete_item( $item->nIDItem );' title='$HINT_DELETE_ITEM'><img src='../images/icons/cut.gif'></a>";
										echo "</td>";

										echo "<td class='cuadricula $class_x' width='10%' align='center' valign='center'>";

										if( $item->cCover != NULL )
										{
											echo "<div style='display:inline;'><img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$item->nIDTitulo&tipoimagen=PORTADA' width='60'\></div>";
										}

										echo "</td>";

										echo "<td class='cuadricula $class_x' width='50%'>";
										
										$error_en_fecha_dev = "";
										
										if( $error == 1 )
										{
											echo "<div class='caja_errores'><img src='../images/icons/warning.gif'>&nbsp;";

											if( $disponible_en_fechaprestamo == 0 )
												echo sprintf( $ALERT_WRONG_ITEM_DATES_OP0, "($fechaprestamo)" ) . "<br>"; 

											if( $error_fechas == "" )
												echo sprintf( $ALERT_WRONG_ITEM_DATES_OP1, "($item->cTitle)" );
											else
											{
												echo sprintf( $ALERT_WRONG_ITEM_DATES_OP2, $error_fechas );
												
												if( $disponible_en_fechaprestamo == 1 )
												{
													$error_en_fecha_dev = "$ALERT_WRONG_DEV_DATE";
												}
											}

											echo "</div><br>";										
										}
										
										echo "$LBL_ITEM_ID:&nbsp;" . $item->item_id_material . "<br>";

										echo "$LBL_CALL_NUMBER:&nbsp;" . $item->CallNumber() . "<br>";

										echo $item->cTitle;

										echo "<input type='hidden' class='hidden' name='id_item_$i' value='$item->nIDItem'></td>";
										echo "<td class='cuadricula $class_x' width='15%' align='center'>";

										$onChange_FechaInError = "";

										if( $error_en_fecha_dev != "" )
										{
											echo "<div class='caja_errores'>";
											echo "<img src='../images/icons/warning.gif'>&nbsp;$error_en_fecha_dev</div><br>";
											$onChange_FechaInError = " onChange='updateErrorState(this);'";
										}
										
										echo "<div style='width:100px; text-align:left;'>";
										colocar_edit_date( "fecha_dev_$i", $fecha_dev, 0, $onChange_FechaInError );
										echo $LBL_BEFORE_TIME . " " . $horaprestamo;
										echo "</div>";
										
										echo "</td></tr>";

										$item->destroy();
										
										unset( $item );
									}
								}
								
								echo "</table>";
								
								echo "<input type='hidden' class='hidden' id='numero_total_items' name='numero_total_items' value='$i'>";
							
								?>
								
								<br>
								&nbsp;&nbsp;
								<input type="button" class=boton value="<?php echo $BTN_ADD_LOAN; ?>" name="btnAgregar" id="btnAgregar" onClick="guardarPrestamo();">
								<br>
								
						<?php
							}
							
							if( $error != 0 )
							{
								echo "<br>";
								if( getsessionvar("empleado") == "S" )
								{
									echo "<div style='margin-left: 13em;'>";
									echo " <div style='float:left'>";
									echo "   <input type='button' class='boton' value='$BTN_CHANGE_USER' name='btnNewOne' id='btnNewOne' onClick='javascript:newOne();'>";
									echo " </div>";
									echo "</div>";
									
									echo "<br>";
								}
								else
								{
									echo "<div style='margin-left: 13em;'>";
									echo " <div style='float:left'>";
									echo "   <input type='button' class='boton' value='$BTN_GOBACK' name='btnNewOne' id='btnNewOne' onClick='javascript:window.history.back();'>";
									echo " </div>";
									echo "</div>";
								}
							}
							
						?>

						<!--historial-->
						<input class="hidden" type="hidden" name="historial" value="<?php echo $historial; ?>" size=80>

						<br>
					</form>
				</div><!-- caja_datos -->
			</div><!-- Contenido principal -->
			<?php  display_copyright();	?>    
		</div><!-- bloque principal -->
	</div>  <!-- contenedor principal -->
</body>

<?php
   $db->destroy();
 ?>

</html>

