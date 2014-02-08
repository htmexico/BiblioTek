<?php
	session_start();

	/**********
		
		Archivo PHP de la aplicación BiblioTEK 
		que permite realizar las reservaciones de títulos
		
		Historial de cambios:
		
		28-enero-2009	Se crea el archivo circulacion.php
		25/03/2009		se inicia reconstruccion de interfaz visual
		13/04/2009 		Se u nacomodo de codigo para tener mejor entendimiento del mismo
						se agrego la funcion data_for_database_update						
		25-jun-2009		Corrección de código		
		17-jul-2009:	Se obtiene código actualizado, usabilidad mejorada.		
		25-jul-2009:    Se aplican parámetros SESS_		
		20-oct-2009:    Se verifican las restricciones.
		21-oct-2009:    Si no hay elementos en la bandeja no mostrar los botones de ocultarla
		
		PENDIENTES:
		
			- Verificar y contabilizar las reservas previas.

	**/
 
	include ( "../funcs.inc.php" );
	include ( "../basic/bd.class.php" );
	include ( "circulacion.inc.php" );
	
	include_language( "global_menus" );
	include_language( "circ_reservaciones" );
	include_language( "anls_consultatitulos" );

	check_usuario_firmado(); 

	$db = new DB();
	
	$grupo			= "";
	$nombre_usuario = "";
	
	$bandera_total				  = 0;
	$bandera_reservado			  = 1;
	$max_dias					  = 0;
	$max_items					  = 0;
	$items_actualmente_reservados = 0;
	$fechareservacion			  = 0;
	$fechadevolucion			  = 0;
	$val_reservacion			  = 1;
	
	$imposible_por_restricciones = false;
	$imposible_por_sanciones = false;
	
	$id_biblioteca = getsessionvar('id_biblioteca');
	
	$id_usuario = read_param( "id_usuario", 0, 1 ); // fail if not exist
	
	if( getsessionvar( "empleado" ) != "S" )
	{
		// no es un empleado
		// verificacion de aseguramiento
		if( $id_usuario != getsessionvar("id_usuario") )
			die( "Llamada incorrecta" );
	}
	
	$save = read_param( "save", 0 );
	$val_reservacion = read_param( "validar_reservacion", 0 );
	
	$error = 0;  // Verificar errores
	$info  = 0;  // Muestra alguna INFO
	
	// variable que en VALOR 1 agrega un ID_TITULO de sesion
	$validate_item = read_param( "validate_item", 0 );
	$delete_item = read_param( "delete_item", 0 );
	
	// este array conservará los items de usuario
	// diferentes a lo que haya en la bandeja
	$array_titulos_x_sesion = Array();

	// agregar los elementos de sesion anteriores (historial)
	$total_x_sesion = read_param( "total_x_sesion", 0 );

	for( $xyz=0; $xyz<$total_x_sesion; $xyz++ )
	{
		$sess_id_titulo = read_param( "SESS_ID_$xyz", "" );
		$sess_modalidad = read_param( "SESS_MD_$xyz", "" );
		$sess_fecha 	= read_param( "SESS_F_$xyz", "" );
		
		$array_titulos_x_sesion[] = Array( "id_titulo" => $sess_id_titulo ,
										   "modalidad" => $sess_modalidad,
										   "fecha_reserva" => $sess_fecha );
	}

	//
	// VALIDAR Y AGREGAR UN ID_TITULO
	//
	if( $validate_item == 1 )
	{	
		// el operador está agregando un título
		// que no existe en la bandeja personal del usuario
		$id_titulo = read_param( "id_titulo", 0, 1 );
		
		$array_titulos_x_sesion[] = Array( "id_titulo" => $id_titulo ,
										   "modalidad" => 0,  // por default EN ESPERA
										   "fecha_reserva" => getcurdate_human_format() );
											   
	}

	// ELIMINAR UN ID_TITULO
	if( $delete_item == 1 )
	{	
		// el operador está borrando un título
		// que ya existe en la sesión
		$id_titulo = read_param( "id_titulo", 0, 1 );
		
		$pos = -1;
		
		for( $ij=0; $ij<count($array_titulos_x_sesion); $ij++ )
		{
			if( $array_titulos_x_sesion[$ij]["id_titulo"] == $id_titulo )
			{
				$pos = $ij;
				break;
			}
		}
		
		if( $pos != -1 )
		{
			array_splice( $array_titulos_x_sesion, $pos, 1 );  // borra la posición
		}
		
		$total_x_sesion = count( $array_titulos_x_sesion );
		
		//echo $total_x_sesion;
	}

	//
	// INSERTAR la reservacion en la base de datos 
	//
	if( $save == 1 )
	{
		$fecha_realizar_reserva = current_dateandtime();  // fecha y hora del servidor
		
		$total_en_bandeja = read_param( "total_en_bandeja", 0, 1 ); // fail if not found
		
		// armar array con todos los elementos
		$array_final_items = Array();
		
		// agregar los elementos marcados de la bandeja
		for( $xyz=0; $xyz < $total_en_bandeja; $xyz++ )
		{
			$id_marked = read_param( "ID_$xyz", 0 );
			$modalidad = read_param( "MD_$xyz", -1 );
			$fecha_reserva = read_param( "F_$xyz", -1 );
			
			if( $id_marked == 1 )
			{
				$id_titulo = read_param( "ID_TITULO_$xyz", 0 );
				
				if( $id_titulo != 0 )
				{
					$array_final_items[] = Array( "id_titulo" => $id_titulo,
												  "modalidad" => $modalidad,
												  "fecha_reserva" => $fecha_reserva,
												  "id_item" => -1 );
				}
			}
		}

		// agregar los elementos de la sesión
		for( $xyz=0; $xyz<$total_x_sesion; $xyz++ )
		{
			$sess_id_titulo = read_param( "SESS_ID_$xyz", "" );
			$sess_modalidad = read_param( "SESS_MD_$xyz", "" );
			$sess_fecha 	= read_param( "SESS_F_$xyz", "" );
			
			$array_final_items[] = Array(  "id_titulo" => $sess_id_titulo ,
										   "modalidad" => $sess_modalidad,
										   "fecha_reserva" => $sess_fecha,
										   "id_item" => -1 );
		}
		
		$max_dias = 0;

		//
		//  CONOCER LOS DIAS DE PRESTAMO QUE PUEDE TENER CADA ITEM
		//  PARA VALIDAR
		$db->Open( " SELECT a.STATUS, b.MAX_RESERVACIONES, b.MAX_DIAS_PRESTAMO " .
					"FROM CFGUSUARIOS a ".
					"  LEFT JOIN CFGUSUARIOS_GRUPOS b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .
					"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario" );

		if( $db->NextRow() )
		{			
			$max_items	= $db->row["MAX_RESERVACIONES"];  // este dato deberá servir para truncar el array de items
			$max_dias	= $db->row["MAX_DIAS_PRESTAMO"];		
		}
		else
		{
			$error = 1;
			$error_message = "$ALERT_WRONG_USER_NOT_FOUND";
		}
		
		$db->FreeResultset();
		
		//
		// VALIDAR LA DISPONIBILIDAD DE CADA UNO DE LOS ITEMS
		// DEPENDIENDO DE LA MODALIDAD (en realidad solo se verifica en modalidad = 1 )
		//
		$bandera_todos_items_ok = 1;
		$id_item_con_error = 0;
		
		for( $xyz=0; $xyz<count($array_final_items); $xyz++ )
		{									
			//
			// 2da. VALIDACION - Validar si el item está libre para las fechas solicitadas
			//
			if( $array_final_items[$xyz]["modalidad"] == 1 )
			{
				// en fecha específica
				$item = new TItem_Basic( $id_biblioteca, $array_final_items[$xyz]["id_titulo"], 0 );  // 0 = POR TITULO
				
				$fechas_disponibles = Array();
				
				// se reservará en esta fecha
				$fecha_desde = $array_final_items[$xyz]["fecha_reserva"];
				$fecha_hasta = sum_days( $fecha_desde, $max_dias );  // agregar n días a la fecha DESDE
				
				$get_item_id = $item->VerificarDisponibilidad_X_TITULO( "N", $fecha_desde, $fecha_hasta, $fechas_disponibles );
				
				if( $get_item_id > 0 )
				{
					$array_final_items[$xyz]["id_item"] = $get_item_id;
				}
				else
				{
					// EL ITEM no está disponible
					$bandera_todos_items_ok = 0;
					$id_item_con_error = $item->cTitle;
				}

				$item->destroy();
				
				unset( $fechas_disponibles );
			}
		}		
		
		// SI TODO VA BIEN
		if( $bandera_todos_items_ok == 1 )
		{
			// OBTENER CONSECUTIVO
			// busqueda ultimo registro tabla RESERVACIONES_MST
			// 
			$db->Open( "SELECT MAX(ID_RESERVACION) AS MAXIMO, COUNT(*) AS CUANTOS ".
					   "FROM reservaciones_mst ".
					   "WHERE ID_BIBLIOTECA=$id_biblioteca;" );
			
			if( $db->NextRow() )
			{			
				if( $db->row["CUANTOS"] = 0 )
					$id_num = 1;
				else
					$id_num = $db->row["MAXIMO"] + 1; 			
			}

			$db->FreeResultset();
			
			$items_inserted = 0;
			
			$db->ExecSQL( "INSERT INTO RESERVACIONES_MST (ID_BIBLIOTECA, ID_RESERVACION, ID_USUARIO, FECHA_RESERVO ) " . 
						  " VALUES( $id_biblioteca, $id_num, $id_usuario, '$fecha_realizar_reserva' ); " );
			
			require_once("../actions.inc.php");
			
			// recorrer el array de items
			foreach( $array_final_items as $tmp_item )
			{
				$fecha_reserva = date_for_database_updates( $tmp_item["fecha_reserva"] );

				// se insertan con status de pendiente
				if( $tmp_item["modalidad"] == 1 )
				{
					$query_insert = "INSERT INTO RESERVACIONES_DET ( ID_BIBLIOTECA, ID_RESERVACION, ID_TITULO, ID_ITEM, FECHA_RESERVACION, TIPO_RESERVACION, STATUS_RESERVACION ) " . 
									"VALUES ( $id_biblioteca, $id_num, " . $tmp_item["id_titulo"] . ", " . $tmp_item["id_item"] . ", '$fecha_reserva', 1, 'P' );";
									
					agregar_actividad_de_usuario( CIRC_RESERVATIONS, "", $tmp_item["id_item"], $tmp_item["id_titulo"] );
				}
				else
				{
					$query_insert = "INSERT INTO RESERVACIONES_DET ( ID_BIBLIOTECA, ID_RESERVACION, ID_TITULO, ID_ITEM, TIPO_RESERVACION, STATUS_RESERVACION ) " . 
									"VALUES ( $id_biblioteca, $id_num, " . $tmp_item["id_titulo"] . ", 0, 0, 'P' );";
									
					agregar_actividad_de_usuario( CIRC_RESERVATIONS, "", 0, $tmp_item["id_titulo"] );
				}

				$db->ExecSQL( $query_insert );

				$items_inserted++;

				// son elementos de la bandeja
				// se deberán eliminar
				// $db->ExecSQL( "DELETE FROM usuarios_bandeja WHERE ID_BIBLIOTECA=$id_biblioteca and ID_USUARIO=$id_usuario and ID_TITULO=$id_titulo;" );
			}


			$db->destroy();
			
			// recorrer los ITEMS de sesión
			if( $items_inserted > 0 )
			{
				ges_redirect( "Location:circ_reservaciones_end.php?id_usuario=$id_usuario&id_reservacion=$id_num" );
			}
		
		}
		else
		{
			// el status disponible de UN ITEM ha cambiado de ultimo momento
			$error = 1;
			$error_message = sprintf( "$ALERT_WRONG_ITEM_RESERVA", $id_item_con_error );
		}		

	}
	
	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_LOAN_HEADER", "../" );
	
?>

<script type='text/javascript' src='../basic/calend.js'></script>
<script type='text/javascript' src='../ajax.js'></script>

<SCRIPT type='text/javascript' language='JavaScript'>

	var items_already_in = new Array();
	var user_max_items_reservados = -1;
	var total_marcados = 0; // items marcados en esta sesión

	var errores_items = new Array();
	
	var ajaxRequest = new Request;
	
	function newOne()
	{
		location.href = "gral_elegir_usuario.php?the_action=reservas";
	}
	
	function verify_keyup_override(myfield,e)
	{
		var keycode;
	   
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;

		if (keycode == 13 ) 
		{
			 updateErrorState();
		}
		else
		{
			verify_keyup(myfield,e); // en calend.js
		}
	}

	// actualizar ITEMS reservados en esta sesión
	function ActualizarItemsReservados()
	{
		var total = js_getElementByName("bandeja_total_items");
		var total_bandeja_marcados = 0;
		
		total_marcados = 0;

		// verificar los elementos MARCADOS de la bandeja 
		for( var i = 0; i<parseInt(total.value); i++ )
		{
			if( (check_element = js_getElementByName( "check_"+i )) )
			{
				if( check_element.checked )
				{ 
					total_marcados++; 
					total_bandeja_marcados++;
				}
			}
		}

		var sesion_totales = js_getElementByName("sesion_total_items");
		var idx;

		// Agregar los elementos RESERVADOS DE LA SESION (ID_TITULO+MODALIDAD+FECHA)
		for( var j = 0; j<parseInt(sesion_totales.value); j++ )
		{
			idx = parseInt(total.value) + j; // indice actual
			if( (check_element = js_getElementByName( "check_"+idx )) )
			{
				if( check_element.checked )
					{ total_marcados++; }
			}
		}		
		
		// COLOCAR EL TOTAL en el DIV
		var div_items_reservados = js_getElementByName( "total_items_reservados" );
		var div_items_reservados_2 = js_getElementByName( "total_items_reservados_2" );
		
		if( div_items_reservados )
		{
			div_items_reservados.innerHTML = total_marcados + "&nbsp;<?php echo $HINTS_ITEMS_MARKED;?>";
			
			if ( total_bandeja_marcados != 0 )
				div_items_reservados.innerHTML = div_items_reservados.innerHTML + " ( " + total_bandeja_marcados + " <?php echo $HINTS_ITEMS_MARKED_BIN;?> ) ";
			
			if ( user_max_items_reservados != -1 )
			{
				var btnSave1 = js_getElementByName( "btnSave1" );
				var btnSave2 = js_getElementByName( "btnSave2" );

				if( total_marcados > user_max_items_reservados )
				{
					// verificar si rebasa el máximo
					div_items_reservados.innerHTML += "<br><?php echo $ALERT_WRONG_MAX_ITEMS_1;?> " + user_max_items_reservados + " <?php echo $ALERT_WRONG_MAX_ITEMS_2;?> " ;
					div_items_reservados.innerHTML += " <img src='../images/icons/warning.gif'>";
					div_items_reservados.className = "caja_errores";

					if( btnSave1 ) 
					{
						btnSave1.disabled = true;
						btnSave1.style.color = "silver";
					}
					
					if( btnSave2 ) 
					{
						btnSave2.disabled = true;
						btnSave2.style.color = "silver";
					}					
					
				}
				else
				{
					div_items_reservados.className = "";
					
					if( btnSave1 ) 
					{
						btnSave1.disabled = false;
						btnSave1.style.color = "";
					}
					if( btnSave2 ) 
					{
						btnSave2.disabled = false;
						btnSave2.style.color = "";
					}					
				}
			}
			
			div_items_reservados_2.className = div_items_reservados.className;
			div_items_reservados_2.innerHTML = div_items_reservados.innerHTML;
		}
	}
	
	//
	// Agrega el ID del TITULO al array de items que se encuentran
	// en pantalla
	//
	function add_item_titulo( id_titulo )
	{
		items_already_in[ items_already_in.length ] = id_titulo;
	}
	
	//
	// Delete a item from the session
	//
	function delete_item_session( id_titulo )
	{
		var id_usuario = js_getElementByName( "id_usuario" );

		if( id_usuario )
		{
			var url = "circ_reservaciones.php?id_usuario=" + id_usuario.value +"&delete_item=1&id_titulo=" + id_titulo;
			
			location.href = url + "&" + CreateComplementURL();
		}
	}	
	
	// 
	// mandar la reservación a guardar
	//
	function guardarReserva()
	{
		if( total_marcados == 0 )
		{
			alert( octal("<?php echo $ALERT_NOITEMS_MARKED;?>") );
		}
		else
		{
			var bError = false;
			var id_usuario = js_getElementByName( "id_usuario" );
			
			if( errores_items.length > 0 ) 
			{
				alert( octal("<?php echo $ALERT_ERRORS_IN_LIST;?>") );
				bError = true;
			}			
			
			if( id_usuario && !bError )
			{
				var url = "circ_reservaciones.php?id_usuario=" + id_usuario.value +"&save=1";
				
				frames.location.href = url + "&" + CreateComplementURL();
			}
		}
	}
		
	//
	// Muestra el area/popup para agregar un ITEM
	//
	function muestraAgregar()
	{
		ShowDiv( "popUpBlock" );

		if( ShowPopupDIV( "div_agregar" ) )
		{
			var div_agregar = js_getElementByName( "div_agregar" );

			var div_resultados_busqueda = js_getElementByName( "resultados_busqueda_embeded" );
			
			if( !div_resultados_busqueda ) return false;

			// Eval code	
			ajaxRequest.submit({
				url : "ajax_funcs.php",
				params : "info=display_library_consult&id_consulta=0",
				xml : false,
				success : function(res, ajaxRequest)
				{
					div_resultados_busqueda.style.display = "block";
					div_resultados_busqueda.innerHTML = res;
				}
			});			

		}
	}
	
	function setIDTITULO( val, descr, icon )
	{
		var txt_id_title = js_getElementByName( "txt_id_title" );

		if( txt_id_title )
		{
			txt_id_title.value = val;
			
			if( validarMaterial() )
			{			
				AgregarID_TITULO();
			}
		}

	}
	
		// 07jun2011
		function search_ajax_catalog( id_lib, id_consulta, path_target, extra_params )
		{
			var error = 0;		
			var obj_searchBy = js_getElementByName( "search_By" );
			var obj_searchFor = js_getElementByName( "search_For" );
				
			if( obj_searchFor.value == "" )		
			{
				error = 1;
				alert( octal("<?php echo $NO_SEARCH_TERMS_ENTERED;?>") );
				
				obj_searchFor.focus();
			}
			
			if( obj_searchBy.value == "" )
			{
				error = 1;
				alert( octal("<?php echo $NO_SEARCH_STYLE_SELECTED;?>") );
			}		
			
			if( error == 0 )
			{
				var div_resultados = js_getElementByName( "embeded_search" );
				
				// Eval code	
				ajaxRequest.submit({
					url : "ajax_funcs.php",
					params : "info=library_search&id_biblioteca=" + id_lib +"&id_consulta=" + id_consulta + "&type=" + obj_searchBy.value + "&search=" + obj_searchFor.value + "&" + extra_params,
					xml : false,
					success : function(res, ajaxRequest)
					{
						div_resultados.style.display = "block";
						div_resultados.innerHTML = res;
					}
				});					

				return true;
			}
			else
				return false;
		}
	

	// cerrar area/popup de Agregar
	function closeAgregar()
	{
		if( HideDiv( "div_agregar" ) )
		{			
			HideDiv( "popUpBlock" );
		}
	}

	//
	// se llama para agregar el ID_TITUTLO
	//
	function AgregarID_TITULO()
	{
		// Crear una URL
		var txt_id_title = js_getElementByName( "txt_id_title" );
		var id_usuario = js_getElementByName( "id_usuario" );
			
		if( id_usuario )
		{			
			var url = "circ_reservaciones.php?id_usuario=" + id_usuario.value +"&validate_item=1&id_titulo="+txt_id_title.value;			
			
			frames.location.href = url + "&" + CreateComplementURL();
		}
	}
		
	function CreateComplementURL()
	{
		var ret = "";
		var total = js_getElementByName("bandeja_total_items");

		if ( total )
		{
			var check_element;
			
			ret += "total_en_bandeja=" + total.value;
			
			// Agregar los elementos MARCADOS de la bandeja (CHECKED+MODALIDAD+FECHA)
			for( var i = 0; i<parseInt(total.value); i++ )
			{
				if( (check_element = js_getElementByName( "check_"+i )) )
				{
					var sel_mode = js_getElementByName( "sel_mode_"+i );
					var fecha_esp = js_getElementByName( "fecha_esp_"+i );
					var id_titulo = js_getElementByName( "id_titulo_"+i );

					if( check_element.checked )
					{
						if( ret != "" )
						   ret += "&";

						// ID CHECADO + ID_TITULO + MODO + FECHA 
						ret += "ID_"+i+"=1&ID_TITULO_"+i+"=" + id_titulo.value + "&MD_"+i+"=" + sel_mode.value + "&F_"+i+"=" + fecha_esp.value;
					}
				}
			}
			
			var sesion_totales = js_getElementByName("sesion_total_items");
			
			if( sesion_totales )
			{
				var check_element;
				var idx;
				var session_checked = 0;

				// se mueve de acá para que al finalizar se alimenten los efectivamente CHECADOS
				//ret += "&total_x_sesion=" + sesion_totales.value;
				
				// Agregar los elementos RESERVADOS DE LA SESION (ID_TITULO+MODALIDAD+FECHA)
				for( var j = 0; j<parseInt(sesion_totales.value); j++ )
				{
					idx = parseInt(total.value) + j; // indice actual
					
					if( (check_element = js_getElementByName( "check_"+idx )) )
					{
						var id_titulo = js_getElementByName( "id_titulo_session_"+idx );
						var sel_mode = js_getElementByName( "sel_mode_"+idx );
						var fecha_esp = js_getElementByName( "fecha_esp_"+idx );
					
						if( check_element.checked )
						{
							if( ret != "" )
							   ret += "&";
							   
							// ID CHECADO + MODO + FECHA
							ret += "SESS_ID_"+j+"=" + id_titulo.value + "&SESS_MD_"+j+"=" + sel_mode.value + "&SESS_F_"+j+"=" + fecha_esp.value;
							
							session_checked++;
						}
						
					}
				}
				
				ret += "&total_x_sesion=" + session_checked;
			}
		}
		
		return ret;
	}	
	
	function findInArrayItems( the_array, expr_to_find )
	{
		var ret = -1;
			
		for( var i = 0; i<the_array.length && ret==-1; i++ )
		{
			if( the_array[i] == expr_to_find )
			{
				ret = i;
			}
		}
		
		return ret;
	}
	
	//
	// valida un número de material
	//
	function validarMaterial()
	{
		var url;
		var obj_id_title = js_getElementByName( "txt_id_title" );
		
		if( obj_id_title.value == "" )
		{  
			alert( octal("<?php echo $ALERT_WRONG_ADD_ITEM; ?>") );  
		}
		else
		{
			if ( findInArrayItems( items_already_in, obj_id_title.value ) != -1 ) 
			{
				alert( octal("<?php echo $ALERT_WRONG_DUPLICATE_ITEM; ?>") );

				var div_error = js_getElementByName( 'div_error_title' );
				
				div_error.style.display    = "none";
				div_error.style.visibility = "hidden";				
				
				return false;
			}
			else
				return true;
		}
	}
	
	function elegirMaterial()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		window.open( "gral_buscartitulo.php", "usuarios", "WIDTH=" + (nwidth-200) + ",HEIGHT=" + (nheight-330) + ",TOP=50,LEFT=80,resizable=yes,scrollbars=yes,status=yes" );
		window.status='';
	}

	function local_blockNonNumbers(obj, e, allowDecimal, allowNegative)
	{
		var keycode;
	
		if (window.event) keycode = window.event.keyCode;
		else if (e) keycode = e.which;
		else return true;
		
		if( keycode == 13 )
		{
			validarMaterial();
			
			return false;
		}
		else
			return blockNonNumbers(obj, e, allowDecimal, allowNegative)
	}	
	
	//
	// Muestra / Oculta los ITEMS que vienen de la bandeja personal del usuario
	//
	function cerrarBandeja()
	{
		var trs_bandeja = document.getElementsByTagName( "TR" );
		var div_property;

		if (navigator.appName == "Microsoft Internet Explorer" ) 
			div_property = "block";
		else
			div_property = "table-row";		

		var btnShowBin = js_getElementByName( "btnBandeja" );
		var btnShowBin2 = js_getElementByName( "btnBandeja2" );
			
		for( var i=0; i<trs_bandeja.length; i++ )
		{			
			if( trs_bandeja[i].className == "tr_bandeja" || trs_bandeja[i].className == "tr_bandeja_marked" )
			{
				if( trs_bandeja[i].style.display == div_property || trs_bandeja[i].style.display == "" )
				{
					trs_bandeja[i].style.display = "none";
					trs_bandeja[i].style.visibility = "hidden";
					
					btnShowBin.value = "<?php echo $BTN_SHOW_BIN; ?>";
					btnShowBin2.value = "<?php echo $BTN_SHOW_BIN; ?>";
				}
				else if( trs_bandeja[i].style.display == "none" )
				{
					trs_bandeja[i].style.display = div_property;
					trs_bandeja[i].style.visibility = "visible";
					
					btnShowBin.value = "<?php echo $BTN_HIDE_BIN; ?>";
					btnShowBin2.value = "<?php echo $BTN_HIDE_BIN; ?>";
				}
			}
		}
		// 
	}
	
	function ocultar_botones_bandeja()
	{
		var btnShowBin = js_getElementByName( "btnBandeja" );
		var btnShowBin2 = js_getElementByName( "btnBandeja2" );	
		
		if( btnShowBin )
		{
			btnShowBin.style.display = "none";
		}
		
		if( btnShowBin2 )
		{
			btnShowBin2.style.display = "none";
		}
	}
	
	//
	// Habilitar un renglón de captura
	//
	function enable_Item( id_row, id )
	{
		var tr_element = document.getElementsByName( id_row );

		if( tr_element.length > 0 )
		{
			var check_element = document.getElementsByName( "check_" + id );
			var sel_mode = document.getElementsByName( "sel_mode_" + id ); 
			var fecha_esp = document.getElementsByName( "fecha_esp_" + id );

			if( check_element[0].checked )
			{
				tr_element[0].className = "tr_bandeja_marked";
				sel_mode[0].disabled = false;
				fecha_esp[0].disabled = false;
			}
			else
			{
				tr_element[0].className = "tr_bandeja";
				sel_mode[0].disabled = true;
				fecha_esp[0].disabled = true;
			}
		}

		ActualizarItemsReservados();
		updateErrorState();
	}

	//
	// Habilitar una fecha de reservación dentro de la lista de ITEMS
	//
	function enable_Specific_Date( id )
	{
		var sel_mode = document.getElementsByName( "sel_mode_" + id ); 
			
		var date_element = document.getElementsByName( "div_fecha_" + id );
			
		if( date_element.length > 0 )
		{
			if( sel_mode[0].value == "1" )
				date_element[0].style.display = "inline";
			else
				date_element[0].style.display = "none";
		}
	}
	
	function updateErrorState( obj )
	{
		location.href = "circ_reservaciones.php?id_usuario=" + document.agregar_form.id_usuario.value + 
						"&historial=" + CreateComplementURL();
	}	
	
</SCRIPT>

<style type="text/css">

	#buttonarea { border: 1px solid red;  } 

	#info_usuario { 
		float: none;
		display: block; 
		position: relative;
		width: 50em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
		overflow: auto;
	}	
	
	#total_items_reservados, #total_items_reservados_2
	{
		display: inline;
		position: absolute;
		border: 1px dotted green;
		margin-left: 20px;
		padding: 2px;
		width: 350px;		
		height: 30px;
	}
	
	#caja_datos {
		width: 130%; 
	}
	
	#contenedor {
		background: #FFF;
	}	
	
	TR.tr_sesion
	{
		background-color: #F6FBFB;
		color: #483E3E;
	}	
	
	TR.tr_bandeja
	{
		/*background: #FFF;*/
		color: gray;
	}
	
	TR.tr_bandeja_marked
	{
		background: #FFF;
		color: black;
	}	
	
	#div_agregar
	{	
		background-color: #FCFBD0;
		color: black;
		border: 3px solid gray; 
	
		width: 900px;
		height: 500px;

		z-Index: 3000;		
	}
	
	#div_descrip_title { 
		display: inline; 
		position: absolute;
		left: 3em;
		width: 80%;
		border: 1px dotted blue; 
		padding: 5px;
	}	

</STYLE>

<BODY id="home">
	
<div id='popUpBlock' name='popUpBlock'></div>	

	<!--- INICIA POPUP AGREGAR -->
	<div class="popup" id="div_agregar" name="div_agregar">
		<input type="hidden" name="txt_id_title" id="txt_id_title" value="">
		
		<!--
		<div style='float:left;'>
			<div class=label style='float:left; padding-top: 3px;'><?php echo $LBL_ID_ITEM; ?>&nbsp;</div>

			<div style='float:right'>
				<input type="text" class="campo_captura" style='display:inline;' name="txt_id_title" id="txt_id_title" size="25" maxlength="50" onblur="extractNumber(this,0,false);" onkeypress="return local_blockNonNumbers(this, event, false, false);">
				
				 <input type="button" class="boton" value="Validar" name="btnValidarTitulo" id="btnValidarTitulo" onClick="javascript:validarMaterial();"> 
			</div>
			
		</div>-->
		
		<!-- close icon -->
		<div style="float:right; padding:0px; position: relative; top: -12px; margin:0px;">
			<a href="javascript:closeAgregar();">[ Cerrar ] <img src="../images/icons/close_button.gif"></a>
		</div>
		<!-- close icon -->
		
		<div style="display:none" id="div_descrip_title" name="div_descrip_title">
			<div align="center" id="lbl_id_title" name="lbl_id_title">&nbsp;</div><br>
			<div align="center">
				<input type="button" class="boton" value="<?php echo $BTN_ADD_THIS_TITLE;?>" name="btnMaterial_id" id="btnMaterial_id" onClick="javascript:AgregarID_TITULO();">
			</div>
		</div>							
		
		<div style='display:none' name="div_error_title" id="div_error_title" class='caja_errores'>
			&nbsp;<img src='../images/icons/warning.gif'>&nbsp;<?php echo $ERROR_MSG_ITEM_NOT_FOUND;?>&nbsp;
		</div>
				
		<div name='resultados_busqueda_embeded' id='resultados_busqueda_embeded' style='display:none'>
		</div>		
		
	</div>
	<!--- FIN POPUP AGREGAR -->
	
	<?php
		display_global_nav();  // barra de navegación superior
	?>
	<div id="contenedor" class="contenedor"> 

		<?php 
			display_banner();  // banner
			display_menu( "../" ); // menu principal
			
			$historial = read_param( "historial", "" );
			$historial_usuario = read_param( "historial_usuario", "" );

			if( $id_usuario != 0 )
			{
				$user = new TUser( $id_biblioteca, $id_usuario );
								
				if( $user->NOT_FOUND )
				{
					SYNTAX_JavaScript( 1, 1, "alert(octal( '$ALERT_WRONG_USER_NOT_FOUND' ));" );
				}
				else				
				{			
					$nombre_usuario = $user->NOMBRE_COMPLETO;
					$grupo 			= $user->NOMBRE_GRUPO;
					
					$max_items = $user->GRUPO_MAX_RESERVACIONES;
					$max_dias  = $user->GRUPO_MAX_DIAS_PRESTAMO;
					
					SYNTAX_JavaScript( 1, 1, "user_max_items_reservados = $max_items; " );

					if( $user->STATUS != "A" )
					{
						SYNTAX_JavaScript( 1, 1, "alert( octal('$ALERT_WRONG_USER_STATUS') );" );
					}
					else
					{
						//
						// VERIFICAR items reservados
						//
						// verificar cuantos items tiene ahora mismo reservados en STATUS (P) PENDIENTE o (E) En espera o (R) Copia Retenida
						//
						$items_actualmente_reservados =  $user->ObtenerNumItemsReservados();
						
						$sanciones = 0;
					
						// Verificar sanciones para USUARIO
						if( $user->GRUPO_PERMITIRRESERVA_CON_SANCIONES == "N" )
						{
							$sanciones = $user->ObtenerNumSanciones();
							
							if( $sanciones > 0 )
							{
								$error = 5;  // EL ERROR 5 se debe disparar cuando
											 // a este usuario se le deban detener las reservaciones
											 // SI CUENTA CON SANCIONES
								$error_message = "$ALERT_WRONG_USER_SANCTION";
								$imposible_por_sanciones = true;
							}
						}
						
						// Verificar restricciones para USUARIO
						$restricciones = $user->ObtenerNumRestricciones(2);
						
						if( $restricciones > 0 )
						{
							$error = 6;
							$error_message = "$ALERT_WRONG_USER_RESTRICTION";
							
							$imposible_por_restricciones = true;
						}
					}
				}
				
				$user->destroy();
			}

		?>

		<div id="bloque_principal"> 
	
			<div id="contenido_principal"> 
				<div class="caja_datos" id="caja_datos"> 
					<H1><?php echo $LBL_LOAN_HEADER; ?></H1>
					<HR>
					<h4><?php echo $LBL_LOAN_HEADER_SUB; ?></h4>
					
					<?php 
					
						if( $error != 0 )
						{
							echo "<br><div class='caja_errores'>";
							echo " &nbsp;" . (( $imposible_por_restricciones or $imposible_por_sanciones ) ? "<img src='../images/icons/user_blocked.gif'>" : "" ) . "&nbsp;&nbsp;<strong>$error_message</strong>";
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
						<label for="txt_id_usuario"><strong><?php echo $LBL_USER; ?></strong></label>
						
						<input type='hidden' class='hidden' name='id_usuario' id='id_usuario' value='<?php echo $id_usuario;?>'>
						
						<div id="info_usuario" name="info_usuario">
							<div style='float:left'>
							   <img src="../images/icons/user.gif">&nbsp;
							       <?php echo "<strong>" . $nombre_usuario . "</strong><br> $grupo (" . sprintf($HINT_MAX_ITEMS_4_RESERVA, $max_items ) . ") "; ?>&nbsp;
								   
								<?php
									if( $items_actualmente_reservados != 0 )
									{
										echo "&nbsp;<img src='../images/icons/warning.gif'>&nbsp;";
										echo sprintf( $HINT_MAX_ITEMS_ALREADY_HAD, $items_actualmente_reservados );
									}
									if( $error <> 0 )
									{
										echo "&nbsp;<br><img src='../images/icons/warning.gif'>&nbsp;$error_message";
									}									

									$curdate = getcurdate_human_format();
								 ?>
							</div>						
						</div>
						
						<?php if( !$imposible_por_restricciones and !($items_actualmente_reservados >= $max_items) ) { ?>
						<br>
						<label for="fecha_prestamo"><strong><?php echo $LBL_DATE_RESERV; ?></strong></label>
						<?php 
							echo $curdate;
						?>
						
						<br><br style='clear:both;'>
						
						<?php } 
						
						$no_hay_elementos_bandeja = false;
						
							if( !($items_actualmente_reservados >= $max_items) and $error == 0 )
							{
								//
								// MENU DE OPCIONES SUPERIORES
								//
								echo "<div style='overflow:auto;'>";
								echo " <div style='float:left;'>";
								echo "   <input type='button' class='boton' value='$BTN_ADD_TITULOS' name='btnAgregar' id='btnAgregar' onClick='javascript:muestraAgregar();'>";
								echo "   <input type='button' class='boton' value='$BTN_SAVE_RESERVA' name='btnSave1' id='btnSave1' onClick='javascript:guardarReserva();'>";								
								echo " </div>";

								echo " <div id='total_items_reservados' name='total_items_reservados'>&nbsp;</div>";

								echo " <div style='float:right'>";
								echo "   <input class='boton' type='button' value='$BTN_HIDE_BIN' name='btnBandeja' id='btnBandeja' onClick='javascript:cerrarBandeja();'>";
								echo " </div>";
								echo "</div>";

								if( $id_usuario != 0 )
								{
									$i = 0;

									// INICIA TABLA TITULOS
									echo "<br><br>";
									echo "<table border=0 width='100%'>";
									echo "<tr>";
									echo " <td class='columna cuadricula columnaEncabezado' width='4%'>&nbsp</td>";
									echo " <td class='columna cuadricula columnaEncabezado' width='5%'>$LBL_TABLE_RESERVE</td>";
									echo " <td colspan=2 class='columna cuadricula columnaEncabezado' align='center'>$LBL_TABLE_TITLE1</td>";
									echo " <td class='columna cuadricula columnaEncabezado' align='center' width='30%'>$LBL_TABLE_TITLE2</td>";
									echo "</tr>";
									
									require_once( "circulacion.inc.php" );
									require_once( "bandeja.inc.php" );
									echo "\n";

									//
									// ELEMENTOS DE 
									// LA BANDEJA 
									//
									
									$db_bandeja = new DB();
									$db_bandeja->Open( "SELECT a.* FROM usuarios_bandeja a WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario" );
													  
									while( $db_bandeja->NextRow() )
									{ 
										$item = new TItem_Basic( $id_biblioteca, $db_bandeja->row["ID_TITULO"] );
										
										$id_checked = read_param( "ID_$i", 0 );
										$fecha_reserva = read_param( "F_$i", $curdate );
										$modalidad = read_param( "MD_$i", 0 );									
										
										$class_tr = "tr_bandeja";
										$class_x = "columna";
										
										if( $id_checked == 1 )
										   $class_tr = "tr_bandeja_marked";
										
										$hay_error = 0;
										$disponible_en_fechareserva = 0;
										$error_fechas = "";
										$error_en_fecha_reserva = "";
										$error_no_copies = 0;
										
										if( $id_checked == 1 )
										{
											if( $modalidad == 1 )
											{
												$fecha_hasta = sum_days( $fecha_reserva, $max_dias );  // agregar n días a la fecha DESDE
												
												$ret = $item->VerificarDisponibilidad_X_TITULO( "N", $fecha_reserva, $fecha_hasta, $fechas_disponibles );
												
												if( $ret == 0 )
												{
													$hay_error = 1;
													
													for( $ij=0; $ij<count($fechas_disponibles); $ij++ )
													{
														if( $error_fechas != "" ) $error_fechas .= ", ";
														
														$error_fechas .= $fechas_disponibles[$ij][1];
														
														if( $fechas_disponibles[$ij][1] == $fecha_reserva ) 
															$disponible_en_fechareserva = 1;
													}											
												}
												else if( $ret == -1 )
												{
													$hay_error = 1;
													$error_no_copies = 1;
												}											
												
												if( $disponible_en_fechareserva == 0 and $ret == 0)
													$error_en_fecha_reserva = "En esta fecha ninguna copia de este item estará disponible.";
											}
											else
											{
												if( $item->ObtenerNumeroCopias("S") == 0 )
												{
													$hay_error = 1;
													$error_no_copies = 1;
												}
											}
										}

										if( $hay_error == 1 )
										   $class_x = "columnaError";										   
										
										echo "\n<!-- ITEM DE BANDEJA $i --> \n";
										echo "<tr class='$class_tr' id='row_$i' name='row_$i' >";
										echo " <td class='$class_x columna cuadricula'><img src='../images/bandeja.png'></td>";
										echo " <td class='$class_x cuadricula' align='center'>";
										
										if( $hay_error == 1 )
											echo "<img src='../images/icons/warning.gif'><br><br>";
										
										echo "<input class='checkbox' type='checkbox' " . (($id_checked==1) ? "checked" : ""). " name='check_$i' onClick='enable_Item(\"row_$i\",$i)'></td>";
										echo " <td class='$class_x cuadricula' align='center'>";
										
										// Verificar si hay portada registrada
										if( $item->cCover != NULL )
										{
											echo "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=" . $db_bandeja->row["ID_TITULO"] . "&tipoimagen=PORTADA' width='50'\><br><br>";
										}									
										
										echo "</td>";

										echo "\n<td class='$class_x cuadricula'>";
										
										if( $hay_error == 1 )
										{
											SYNTAX_JavaScript( 1, 1, " errores_items.push( '" . $db_bandeja->row["ID_TITULO"] . "' ); "  );
											
											echo "<div class='caja_errores'><img src='../images/icons/warning.gif'>&nbsp;";
											
											if( $error_no_copies == 1 )
											{
												echo $ALERT_NO_COPIES;
											}
											else
											{											
												if( $disponible_en_fechareserva == 0 )
												{
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP0, "($fecha_reserva)" ) . "<br>"; 
												}

												if( $error_fechas == "" )
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP1, "($item->cTitle)" );
												else
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP2, $error_fechas );
											}

											echo "</div><br>";										
										}
										
										echo $item->cTitle . 
												"\n<input class='hidden' type='hidden' name='id_titulo_$i' id='id_titulo_$i' value='" . $db_bandeja->row["ID_TITULO"] . "'>";
										echo "</td>";
											
										// MODALIDAD
										echo "<td class='$class_x cuadricula'>";
										echo "&nbsp;<div style='display:inline; '>$LBL_RESERVATION_MODE:&nbsp;</div>";
										echo "<div style='display:inline;'>"; 
										echo " <SELECT style='display:inline; font-size:90%' name='sel_mode_$i' " . (($id_checked==1) ? "" : "disabled"). " onChange='javascript:enable_Specific_Date($i)'>" .
											 "  <OPTION " . (($modalidad==0) ? "selected" : "") . " value='0'>$LBL_MODE_WAITINGLIST</OPTION>" . 
											 "  <OPTION " . (($modalidad==1) ? "selected" : "") . " value='1'>$LBL_MODE_ONCERTAINDATE </OPTION>";
										echo " </SELECT></div><br><br>";
										
										$display_fecha_esp = "style='display:none;'";
										
										if( $modalidad==1 ) 
											$display_fecha_esp = "style='display:visible;'";									
										
										echo "<div $display_fecha_esp id='div_fecha_$i' name='div_fecha_$i'>" .
											 "<div style='float:left;display:inline;'>&nbsp;Reserva en:&nbsp;</div> ";

										colocar_edit_date( "fecha_esp_$i", $fecha_reserva, 0, " onChange='updateErrorState(this);' " ); 

										echo "</div>";

										if( $error_en_fecha_reserva != "" )
										{
											echo "<div class='caja_errores'>";
											echo "<img src='../images/icons/warning.gif'>&nbsp;$error_en_fecha_reserva</div><br>";
										}											 
										
										echo "</td>";

										unset($item);

										echo "</td></tr>\n";
										
										SYNTAX_JavaScript(1, 1, "  add_item_titulo( '" . $db_bandeja->row["ID_TITULO"] . "' ); " );
										
										$i++;
									}
									
									echo "<input type='hidden' class='hidden' id='bandeja_total_items' name='bandeja_total_items' value='$i'>";

									
									
									if( $db_bandeja->numRows == 0 )
									{
										$no_hay_elementos_bandeja = true;
									}
									
									$db_bandeja->Close();
									
									echo "\n";

									//
									// ELEMENTOS PERSONALES
									// PEDIDOS EN RUN-TIME POR EL USUARIO
									//

									//
									// COLOCAR TR's de los items pedidos por el usuario
									// estos items se guardan "array_titulos_x_sesion"
									//

									for( $j = 0; $j<count($array_titulos_x_sesion); $j++ )
									{
										$item_x_sesion = $array_titulos_x_sesion[$j];

										$fecha_reserva = $item_x_sesion["fecha_reserva"];
										
										$item = new TItem_Basic( getsessionvar("id_biblioteca"), $item_x_sesion["id_titulo"] );

										$index_id = $i + $j;
										
										$class_x = "columna";
										
										$hay_error = 0;
										$disponible_en_fechareserva = 0;
										$error_fechas = "";
										$error_en_fecha_reserva = "";
										$error_no_copies = 0;
										
										$fecha_hasta = sum_days( $fecha_reserva, $max_dias );  // agregar n días a la fecha DESDE
										
										$ret = $item->VerificarDisponibilidad_X_TITULO( "N", $fecha_reserva, $fecha_hasta, $fechas_disponibles );
										
										if( !$ret )
										{
											$hay_error = 1;											
											
											for( $ij=0; $ij<count($fechas_disponibles); $ij++ )
											{
												if( $error_fechas != "" ) $error_fechas .= ", ";
												
												$error_fechas .= $fechas_disponibles[$ij][1];
												
												if( $fechas_disponibles[$ij][1] == $fecha_reserva ) 
													$disponible_en_fechareserva = 1;
											}											
										}
										else if( $ret == -1 )
										{
											$hay_error = 1;
											$error_no_copies = 1;
										}

										if( $disponible_en_fechareserva == 0 and $ret == 0)
											$error_en_fecha_reserva = "En esta fecha ninguna copia de este item estará disponible.";
										
										if( $hay_error == 1 )
										   $class_x = "columnaError";										   
										
										echo "\n<!-- ITEM DE SESION $index_id --> \n";
										echo "<tr class='tr_sesion' id='row_$index_id' name='row_$index_id' >";
										echo " <td class='$class_x cuadricula'>&nbsp;<input type='hidden' class='hidden' name='id_titulo_session_$index_id' value='" . $item_x_sesion["id_titulo"] . "'>";
										
										if( $hay_error == 1 )
											echo "<img src='../images/icons/warning.gif'><br><br>";
										
										echo "<a href='javascript:delete_item_session(\"" . $item_x_sesion["id_titulo"] . "\");' title='$HINT_DELETE_TITLE'><img src='../images/icons/cut.gif'></a>";
										echo " </td>";
										echo " <td class='$class_x cuadricula' align='center' width='5%'>" . 
											 "<input " . (($error_no_copies==1) ? "disabled" : "" ) . " type='checkbox' checked name='check_$index_id' onClick='enable_Item(\"row_$i\",$i)'></td>";
										echo " <td class='$class_x cuadricula' align='center'>";
										
										// Verificar si hay portada registrada
										if( $item->cCover != NULL )
										{
											echo "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=" . $item_x_sesion["id_titulo"] . "&tipoimagen=PORTADA' width='50'\><br><br>";
										}											

										echo "</td>";

										echo " <td class='$class_x cuadricula'>"; //$item->cTitle
										
										if( $hay_error == 1 )
										{
											SYNTAX_JavaScript( 1, 1, " errores_items.push( '" . $item_x_sesion["id_titulo"] . "' ); "  );

											echo "<div class='caja_errores'><img src='../images/icons/warning.gif'>&nbsp;";

											if( $error_no_copies == 1 )
											{
												echo $ALERT_NO_COPIES;
											}
											else
											{
												if( $disponible_en_fechareserva == 0 )
												{
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP0, "($fecha_reserva)" ) . "<br>"; 
												}

												if( $error_fechas == "" )
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP1, "($item->cTitle)" );
												else
													echo sprintf( $ALERT_WRONG_ITEM_DATES_OP2, $error_fechas );
											}

											echo "</div><br>";										
										}										
										
										echo $item->cTitle . "<br>";
										echo $item->cAutor;
										echo "</td>";

										// MODALIDAD
										echo "<td class='$class_x cuadricula'>";
										echo "&nbsp;<div style='display:inline; '>$LBL_RESERVATION_MODE:&nbsp;</div>";
										echo "<div style='display:inline;'>"; 
										echo " <SELECT " . (($error_no_copies==1) ? "disabled" : "" ) . " style='display:inline; font-size:90%' name='sel_mode_$index_id' onChange='javascript:enable_Specific_Date($index_id)'>" .
											 "  <OPTION value='0' " . (($item_x_sesion["modalidad"]==0) ? "selected" : "") . ">En lista de espera</OPTION>" . 
											 "  <OPTION value='1' " . (($item_x_sesion["modalidad"]==1) ? "selected" : "") . ">En una fecha específica</OPTION>";
										echo " </SELECT></div><br><br>";
										
										$display_fecha_esp = "style='display:none;'";
										
										if( $item_x_sesion["modalidad"]==1 ) 
											$display_fecha_esp = "style='display:visible;'";
											
										echo "<div $display_fecha_esp id='div_fecha_$index_id' name='div_fecha_$index_id'>" .
											 "<div style='float:left;display:inline;'>&nbsp;Reserva en:&nbsp;</div>";
											 
										colocar_edit_date( "fecha_esp_$index_id", $fecha_reserva, 0, " onKeyUp='verify_keyup_override(this,event);' "  );  // , " onChange='updateErrorState(this);' " 
											 
										echo "</div>";
										echo "</td>";
										
										unset($item);
										
										echo "</tr>\n";
										
										SYNTAX_JavaScript(1, 1, "  add_item_titulo( '" . $item_x_sesion["id_titulo"] . "' ); " );
									}
									
									echo "<input type='hidden' class='hidden' id='sesion_total_items' name='sesion_total_items' value='$j'>";
									
									if( ($i + $j) == 0 )
									{
										// información de SIN items
										echo "<tr>";
										echo " <td class='columna cuadricula' colspan=5 align=center>$ALERT_NOITEMS_TO_SHOW</td>";
										echo "</tr>";
									}
									
									echo "</table>"; // FIN TABLA DE MATERIALES
									
									echo "<br>";
									
									if( ($i + $j) > 1 )
									{
										echo "<div>";
										echo " <div style='float:left'>";
										echo "   <input type='button' class='boton' value='$BTN_ADD_TITULOS' name='btnAgregar2' id='btnAgregar2' onClick='javascript:muestraAgregar();'>";
										echo "   <input type='button' class='boton' value='$BTN_SAVE_RESERVA' name='btnSave2' id='btnSave2' onClick='javascript:guardarReserva();'>";										
										echo " </div>";
										
										echo "   <div id='total_items_reservados_2' name='total_items_reservados_2'>&nbsp;</div>";
										
										echo " <div style='float:right'>";
										echo "   <input class='boton' type='button' value='$BTN_HIDE_BIN' name='btnBandeja2' id='btnBandeja2' onClick='javascript:cerrarBandeja();'>";
										echo " </div>";
										echo "</div>";
										echo "<br>";
									}
								}
								SYNTAX_JavaScript(1, 1, " ActualizarItemsReservados(); " );
							}
							else
							{
								if( $error == 5 )
								{
									echo "<label>&nbsp;</label>";
									
									//echo "<div class='caja_errores'>";
									echo "<strong>&nbsp;<img src='../images/icons/warning.gif'>&nbsp;$error_message</strong>";
									//echo "</div>";									
								}
								else if( $items_actualmente_reservados >= $max_items ) 
								{
									echo "<br>";
									echo "<label>&nbsp;</label><img src='../images/icons/warning.gif'>&nbsp;";
									
									echo $ALERT_NO_MORE_ITEMS;
								}
								
								echo "<br><br style='clear:both;'>";

								if( getsessionvar("empleado") == "S" )
								{
									echo "<div style='margin-left: 13em;'>";
									echo " <div style='float:left'>";
									echo "   <input type='button' class='boton' value='$BTN_CHANGE_USER' name='btnNewOne' id='btnNewOne' onClick='javascript:newOne();'>";
									echo " </div>";
									echo "</div>";
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
							
							echo "<br>";
							
						?>

						<br>
						<br style='clear:both;'>

						<?php
						
						if( $no_hay_elementos_bandeja )
						{
							SYNTAX_JavaScript(1, 1, "ocultar_botones_bandeja(); " );
						}
						
						?>
						
					</form>
				</div><!-- caja_datos -->
				
			</div><!-- Contenido principal -->
			
			<?php  		DisplayChangeNotice();
			

display_copyright();	?>    
		</div><!-- bloque principal -->
	
	</div>  <!-- contenedor principal -->

</BODY>

</html>
