<?php
    session_start();
	
/**********

	Historial de Cambios:
	
	28-enero-2009	Se crea el archivo cir_devoluciones.php
	04 y 05 Mayo	Se corrige codigo existente, se valida usuario y se despliega material en prestamo.
	08 Mayo			Se inicia creacion de archivo de lenguaje y se agregan algunas variables.
	11 Mayo			Se inician validaciones para permitir devolucion y se hace actualizacion de status en tabla Prestamos_det.
	15-Mayo-2009	Se concluye calculo de recargos en atraso de entrega

 **/

	include ("../funcs.inc.php");
	include ("../basic/bd.class.php");
	include ("circulacion.inc.php" );
	
	include_language( "global_menus" );
	include_language( "circ_devoluciones" );

	check_usuario_firmado(); 
	
	$usuario=getsessionvar('usuario');
	
	$id_biblioteca =getsessionvar('id_biblioteca');
	$id_item = read_param( "id_item", -1 );
	
	$rapida = 0;
	
	if( $id_item == -1 )
		$id_usuario = read_param( "id_usuario", 0, 1 ); // fail if don't
	else
	{
		$id_usuario = read_param( "id_usuario", 0 ); // NOT fail, we've ID_ITEM
		$rapida = 1;
	}
	
	$id_prestamo = 0;
	
	$the_action = read_param( "the_action", "" );
	
	$grupo				=	"";
	$nombre				=	"";
	$paterno			=	"";
	$materno			=	"";
		
	$items_actualmente_prestados = 0;
	
	$error = 0;
	$error_message = "";
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );

	$db = new DB();
	
	if( $id_item != -1 )
	{
		$db->Open( "SELECT a.ID_PRESTAMO, b.ID_USUARIO FROM prestamos_det a LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
				   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_ITEM=$id_item and a.STATUS='P'; " );
				   
		if( $db->NextRow() )
		{
			$id_prestamo = $db->row["ID_PRESTAMO"];
			$id_usuario  = $db->row["ID_USUARIO"];
		}
		else
		{
			$db->Close();
			$db->destroy();
			
			// PONER MENSAJE DE QUE EL ITEM NO ESTA PRESTADO
			display_stop_message( "Imposible continuar", "El ITEM no está en préstamo", "gral_elegir_item.php?the_action=devoluciones" );
			/* display_stop_message( $cTitleOrCaption, $singular_name, $buttons=1, $displaynavbar=1, $displaybanner=1, $displaymenu=1 ); */
			
			die( "" );
		}
		
		$db->Close();
	}
	
	if( $the_action == "guardar_devolucion" )
	{
		$fecha_hora_dev = read_param( "fecha_hora_dev", "", 1 ); // fail if don't exists		
		$items = read_param( "items", "", 1 ); // fail if don't exists
		
		$items_para_devolver = split( ":", $items );
				
		$devoluciones_aplicadas = 0;
		
		$TIMESTAMP_FechaHoraDev = convert_humandate_to_unixstyle( $fecha_hora_dev );

		// Convierte la fecha y la hora
		$fecha_hora_dev = date_for_database_updates( $fecha_hora_dev ); // . " " . $save_time;
	
		//
		// loop por cada item que se devolverá
		//
		for( $i=0; $i<count($items_para_devolver); $i++ )
		{
			$datos_item_para_devolver = $items_para_devolver[$i];
			
			$item = split( "@", $items_para_devolver[$i] );
			
			$id_item = $item[0];
			$id_prestamo = $item[1];
			
			$devolucion_retrasada = false;
			$status_devolucion    = "";
			
			$db->Open( "SELECT FECHA_DEVOLUCION_PROGRAMADA, STATUS FROM prestamos_det " .
					   "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PRESTAMO=$id_prestamo and ID_ITEM=$id_item " ); 
					   
			if( $db->NextRow() )
			{
				$status_devolucion  = $db->row["STATUS"];
				
				if( $status_devolucion == "P" )
				{
					$fecha_dev_prog     = dbdate_to_human_format( $db->row["FECHA_DEVOLUCION_PROGRAMADA"], 1 );

					$TIMESTAMP_FechaHoraDEVPROGRAMADA = convert_humandate_to_unixstyle( $fecha_dev_prog );
					
					if( $TIMESTAMP_FechaHoraDev > $TIMESTAMP_FechaHoraDEVPROGRAMADA )
					{
						// Hora excede
						$devolucion_retrasada = true;
					}
				}
			}
					   
			$db->Close();
			
			$sancion_x_retraso_dev = 0;
			
			if( $status_devolucion == "P" )
			{
				//
				// determinar cual sanción se aplicará en una presunta devolución tardía
				//
				$db->Open( "SELECT b.SANCION_X_RETRASO_DEV " . 
						   "FROM cfgusuarios a " .
						   " LEFT JOIN cfgusuarios_grupos b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " . 
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario;" ); 

				if( $db->NextRow() )
				{
					$sancion_x_retraso_dev = $db->row["SANCION_X_RETRASO_DEV"];
				}

				$db->Close();
				
				$aplica_sancion = "N";
				$var4 = 0;
				
				if ( $devolucion_retrasada and $sancion_x_retraso_dev != 0 ) 
				{
					$aplica_sancion = "S";
					//
					// verificar tipo de sancion
					//
					$db->sql = "SELECT TIPO_SANCION, ECONOMICA_SN, ECONOMICA_MONTO_FIJO, ECONOMICA_MONTO_X_DIA, LABOR_SOCIAL_SN, LABOR_SOCIAL_HORAS, ESPECIE_SN " .
							   "FROM CFGSANCIONES " .
							   "WHERE (ID_BIBLIOTECA=$id_biblioteca and TIPO_SANCION=$sancion_x_retraso_dev)";
					
					$db->Open();
					
					$sancion_economica		= "";
					$sancion_labor 		    = "";
					$sancion_especie		= "";
					
					if ($db->NextRow() )
					{
						$sancion_economica		= $db->row["ECONOMICA_SN"];
						$monto_economico		= $db->row["ECONOMICA_MONTO_FIJO"];
						$monto_economico_x_dia	= $db->row["ECONOMICA_MONTO_X_DIA"];
						$sancion_labor			= $db->row["LABOR_SOCIAL_SN"];
						$sancion_horas			= $db->row["LABOR_SOCIAL_HORAS"];
						$sancion_especie 		= $db->row["ESPECIE_SN"];
					}
					
					$db->Close();
					
					if ($sancion_economica == "S" )   // Se impone sancion economica por atraso.
					{
						if( $monto_economico != 0 )
							$var4 = $monto_economico;  // es un monto sin importar el atraso
						else
						{
							// cantidad de atraso misma diariamente
							$time_diff = $TIMESTAMP_FechaHoraDev - $TIMESTAMP_FechaHoraDEVPROGRAMADA;
							$dias_retraso = (int)( $time_diff / (24*60*60) );
							
							$var4 = $dias_retraso * $monto_economico_x_dia;
						}

					} // fin sancion economica
					else if( $sancion_labor == "S" )
					{
					}
					else if( $sancion_especie == "S" )
					{
					}				

				}  // fin compara para sancion
				
				// Actualizar fecha_devolución, status y SANCION_SN en prestamos_det
				// De status prestado a disponible
				$db->sql = "UPDATE prestamos_det SET FECHA_DEVOLUCION='$fecha_hora_dev', STATUS='D', SANCION_SN = '$aplica_sancion' " .
						   "WHERE ID_BIBLIOTECA=$id_biblioteca and ID_PRESTAMO=$id_prestamo and ID_ITEM=$id_item";			
				$db->ExecSQL();
				
				$devoluciones_aplicadas++;
					
				if( $aplica_sancion == "S" )
				{
					//obtener consecutivo de tabla sanciones.
					$db->Open( "SELECT COUNT(*) AS CUANTOS FROM sanciones WHERE ID_BIBLIOTECA=$id_biblioteca;" );

					if( $db->NextRow() )
						$new_id_sancion = $db->Field("CUANTOS") + 1;
					else
						$new_id_sancion = 1;
						
					$db->Close();
					
					$db->sql = "INSERT INTO SANCIONES (ID_BIBLIOTECA, ID_SANCION, ID_USUARIO, TIPO_SANCION, ID_PRESTAMO, ID_ITEM, FECHA_SANCION, FECHA_CUMPLIDA, STATUS_SANCION, " .
							   "    MONTO_SANCION, MONTO_TOTAL ) " . 
							   "VALUES( $id_biblioteca, $new_id_sancion, $id_usuario, $sancion_x_retraso_dev, $id_prestamo, $id_item, '$fecha_hora_dev', NULL, 'N', ";
					$db->sql .= "    $var4, 0 );";
			
					$db->ExecSQL();
				}			
			}
		}
		// end - for
		
		if( $devoluciones_aplicadas > 0 )
		{
			ges_redirect( "circ_devoluciones_end.php?id_usuario=$id_usuario" );
		}
		
		$id_prestamo = 0;
	}
	
	//
	// VALIDAR USUARIO
	// 

	if( $id_usuario != 0 )
	{
		// Identificar los datos personales del usuario de la tabla CFGUSUARIOS, para poder obtener
		// el grupo al que pertenece
		
		$db->Open( " SELECT a.ID_BIBLIOTECA, a.ID_USUARIO, a.USERNAME, a.PATERNO, a.MATERNO, a.NOMBRE, a.ID_GRUPO, a.STATUS, ".
					"   b.NOMBRE_GRUPO, b.MAX_ITEMS_PRESTADOS, b.MAX_DIAS_PRESTAMO " .
					"FROM CFGUSUARIOS a ".
					"  LEFT JOIN CFGUSUARIOS_GRUPOS b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_GRUPO=a.ID_GRUPO) " .
					"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario" );
		
		if( $db->NextRow() )
		{			
			$nombre		= $db->row["NOMBRE"];
			$paterno	= $db->row["PATERNO"];
			$materno	= $db->row["MATERNO"];

			$grupo		= $db->row["ID_GRUPO"];

			//Identidficar los datos del usuario de la tabla CFGUSUARIOS, para poder obtener
			//los dias de prestamos, total de reservaciones, numero max de items, etc.
			$grupo			= $db->row["NOMBRE_GRUPO"];
			$max_items		= $db->row["MAX_ITEMS_PRESTADOS"];
			$max_dias		= $db->row["MAX_DIAS_PRESTAMO"];

			if( $db->row["STATUS"] != 'A' )
			{
				$error = 1;
				$error_message = "$ALERT_WRONG_USER_STATUS";
			}
			
		}
		else
		{
			$error = 1;
			$error_message = "$ALERT_WRONG_USER_NOT_FOUND";
		}
		
		$db->FreeResultset();
		
		if( $error == 0 )
		{
			//
			// VERIFICAR items PRESTAMOS
			//
			// verificar cuantos items tiene ahora mismo prestamos Y SIN DEVOLVER
			//
			$db->Open( "SELECT COUNT(*) AS CUANTOS " .
					   " FROM prestamos_mst a " .
					   "	 LEFT JOIN prestamos_det b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO) " .
					   " WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario) and (b.STATUS='P') " );
			
			//
			// Buscar los préstamos que el usuario tenga
			//
			if( $db->NextRow() )
			{
				$items_actualmente_prestados =  $db->row["CUANTOS"];
			}
			
			$db->FreeResultset();			
		}
	}
	
	// Draw an html head
	include ("../basic/head_handler.php");
	HeadHandler( $LBL_HEADER, "../" );

?>

<SCRIPT language='JavaScript'>

	function devolucion() 
	{ 
		var bError = false;
		var total = js_getElementByName("total_items");
		var chk_obj;
		
		if ( total )
		{
			var items = "";
			var id_item_obj;
			var id_prestamo_obj;
			
			// Agregar los elementos MARCADOS
			for ( var i = 1	; i<=parseInt(total.value); i++ )
			{
				chk_obj = js_getElementByName( "chk_" + i );
				
				if( chk_obj.checked )
				{
					id_item_obj = js_getElementByName( "id_item_" + i );
					id_prestamo_obj = js_getElementByName( "id_prestamo_" + i );
				
					if( items != "" )
						items += ":";
					
					items += id_item_obj.value + "@" + id_prestamo_obj.value;
				}
			}
			
			if( items == "" )
			{
				alert( "<?php echo $MSG_NO_ITEMS_MARKED;?>" );
			}
			else
			{
				if( confirm( octal("<?php echo $MSG_WANT_TO_PROCEED;?>" )) )
				{
					// ya tenemos los ITEM's ID
					document.devolver_form.action = "circ_devoluciones.php";
					document.devolver_form.the_action.value = "guardar_devolucion";
					document.devolver_form.id_usuario.value = "<?php echo $id_usuario;?>";
					document.devolver_form.items.value = items;
					document.devolver_form.method = "POST";
					document.devolver_form.submit();
				}
			}
			
		}
		
	}
	
	window.onload=function()
	{
		prepareInputsForHints();
	}

</SCRIPT>
<STYLE>
	.sp_hint { width: 300px; }
	
	#buttonarea { 
		border: 1px solid red;  
	} 

	#nombre_usuario { 
		display: inline; 
		position: absolute;
		left: 13em;
		width: 45em; 
		border: 1px dotted green; 
		background: transparent;
		padding: 3px;
	}	
	
	#caja_datos {
		width: 140%; 
	}

</STYLE>

<LINK href="../css/screen.css" type=text/css rel=stylesheet>

<body id="home">

<?php
	
	display_global_nav();    // barra de navegación superior

?>

<!-- contenedor principal -->
	
<div id="contenedor"> 

<?php 
		// banner
		display_banner();  
		
		// menu principal
		display_menu('../');

	?>

  <div id="bloque_principal"> 
    <div id="contenido_principal">
	  <div class="caja_datos" id="caja_datos"> 
        
		<h1><?php echo $LBL_HEADER; ?></h1>
        <hr>
        <h4><?php echo $LBL_SUB_HEADER; ?></h4>
		
		<br>
		<div id="caja_datos_login">
		
        <form name="devolver_form" id="devolver_form" class="forma_captura">
          
		  <input type='hidden' class='hidden' id="the_action" name="the_action" value="">
		  <input type='hidden' class='hidden' id="items" name="items" value="">
		  <input type='hidden' class='hidden' id="id_usuario" name="id_usuario" value="">
		  <input type='hidden' class='hidden' id="rapida" name="rapida" value="<?php echo $rapida;?>">
		  
		  <label for="txt_id_usuario"><?php echo $LBL_IDUSUARIO; ?></label>
          
			<!--valida si el boton a btnUsuario_id a sido activado-->
			&nbsp;&nbsp;
			<span id="nombre_usuario" name="nombre_usuario">
				<img src="../images/icons/user.gif">&nbsp;

				<?php 
					echo "<strong>" . $paterno." ".$materno." ".$nombre . "</strong><br> $grupo "; 

					if( $items_actualmente_prestados > 0 )
					{
						echo "&nbsp;<img src='../images/icons/warning.gif'>&nbsp;";
						echo sprintf( $HINT_ITEMS_ALREADY_HAD, $items_actualmente_prestados );
					}

				?>

			</span>		  
			<br>						
			<br>
			<br>			

		<dt>
          <label for="fecha_devolucion"><?php echo $LBL_DATETIME_DEV; ?></label>
		</dt>
		<dd><strong>
			<div style='font-size: 140%;'>
          <?php 
			
			$cur_datetime = current_dateandtime(1);
			
			echo $cur_datetime;
			
			echo "<input type='hidden' class='hidden' value='$cur_datetime' id='fecha_hora_dev' name='fecha_hora_dev'>";

		  ?>
			</div>
		  </strong>
		  <span class="sp_hint">Indique la fecha de hoy, en la cual se registrará la devolución.<span class="hint-pointer">&nbsp;</span></span>
		</dd>

		<br>
          
          <?php
				if( $id_prestamo == 0 )
				{
					// PENDIENTE: Depurar este query = HACERLO eficiente
					//
					// DEVOLUCION LARGA, SE MUESTRAN LOS ITEMS, EL USUARIO DEBE SELECCIONAR
					//
					$db->sql = "SELECT DISTINCT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.ID_USUARIO, b.ID_ITEM, b.FECHA_DEVOLUCION_PROGRAMADA, b.STATUS " .
							   "FROM PRESTAMOS_MST a, PRESTAMOS_DET b " .
							   "WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_USUARIO=$id_usuario) and ".
								   "     (a.ID_PRESTAMO=b.ID_PRESTAMO and a.ID_BIBLIOTECA=$id_biblioteca) and ".
								   "        (b.STATUS='P' and b.ID_BIBLIOTECA=$id_biblioteca) ";
				}
				else
				{
					//
					// DEVOLUCION RÁPIDA CON EL CODIGO DEL ITEM
					//
					$db->sql = "SELECT DISTINCT a.ID_BIBLIOTECA, a.ID_PRESTAMO, a.ID_USUARIO, b.ID_ITEM, b.FECHA_DEVOLUCION_PROGRAMADA, b.STATUS " .
							   "FROM PRESTAMOS_MST a " . 
							   " LEFT JOIN PRESTAMOS_DET b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PRESTAMO=a.ID_PRESTAMO and b.ID_ITEM=$id_item and b.STATUS='P' ) " .
							   "WHERE (a.ID_BIBLIOTECA=$id_biblioteca and a.ID_PRESTAMO=$id_prestamo) ";
				}

				$db->Open();
					
				echo "<br><table border=1 width='100%' bordercolor='#969696' >"; 
				echo "<tr bgcolor='#CCCCCC'>";
				echo "<td align='center' class='cuadricula columna columnaEncabezado' style='padding: 5px;' width='5%'>&nbsp</td>";
				echo "<td align='center' class='cuadricula columna columnaEncabezado' style='padding: 5px;' width='15%'>$LBL_HEADER_COL_1</td>";
				echo "<td align='center' class='cuadricula columna columnaEncabezado' style='padding: 5px;' width='60%'>$LBL_HEADER_COL_2</td>";
				echo "</tr>";
				
				While ( $db->NextRow() )
				{
					$id_prestamo=$db->row["ID_PRESTAMO"];

					$titulo = new TItem_Basic( $id_biblioteca, $db->row["ID_ITEM"], 1 );

					$strchecked = "";
					
					if( $id_prestamo != 0 and $db->row["ID_ITEM"]==$id_item)
						$strchecked = "checked";
					
					echo "\n<tr>";
					echo "<td align='center' class='cuadricula columna'><input name='chk_$db->numRows' id='chk_$db->numRows' $strchecked type='checkbox'></td> ";

					// portada
					echo "<td class='cuadricula columna' width=5% align='center' style='padding: 5px;'>";						
					if( $titulo->cCover != NULL )
					{
						echo "<img src='" . getsessionvar("http_base_dir") ."phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$titulo->nIDTitulo&tipoimagen=PORTADA' width='80'><br>";
					}
						
					echo "</td>";
					
					// titulo
					echo "<td class='cuadricula columna'>";

					// verificar fecha-dev-programada vs. fecha-hora actual
					$TIMESTAMP_FechaHoraACTUAL = convert_humandate_to_unixstyle( $cur_datetime );

					$fecha_dev_prog = dbdate_to_human_format( $titulo->ObtenerFechaDevolucion( $id_prestamo ), 1 ); 
					$TIMESTAMP_FechaHoraDEVPROGRAMADA = convert_humandate_to_unixstyle( $fecha_dev_prog );

					if( $TIMESTAMP_FechaHoraACTUAL > $TIMESTAMP_FechaHoraDEVPROGRAMADA )
					{
						$aTiempoRetraso = $titulo->ObtenerTiempoRetraso( $TIMESTAMP_FechaHoraDEVPROGRAMADA, $TIMESTAMP_FechaHoraACTUAL );
						
						$delayed_time = "";

						if( $aTiempoRetraso["tdias"] > 0 ) 
							$delayed_time .= $aTiempoRetraso["tdias"] . " día(s) ";
						
						if( $aTiempoRetraso["hrs"] > 0 ) 
							$delayed_time .= $aTiempoRetraso["hrs"] . " hora(s) ";

						$delayed_time .= ($aTiempoRetraso["mins"]<=9 ? "0" : "") . $aTiempoRetraso["mins"] . " min(s) ";
						
						// RETRASO TOTAL EN MINUTOS
						// $aTiempoRetraso["tmins"];

						echo "\n<div class='caja_errores'><img src='../images/icons/warning.gif'>&nbsp;$MSG_ITEM_DELAYED $delayed_time </div><br>\n";
					}
					
					echo "<strong><i>" . $titulo->item_id_material . "</i></strong><br>";
					echo "<strong>" . $titulo->cTitle . "</strong><br>";					
					echo $titulo->cAutor . "<br>";
					echo "$LBL_DEV_DUEDATE " . $fecha_dev_prog;
					echo "</td>";	//depliege de titulo
						
					echo "<input class='hidden' type='hidden' id='id_prestamo_$db->numRows' name='id_prestamo_$db->numRows' value='$id_prestamo'>";
					echo "<input class='hidden' type='hidden' id='id_item_$db->numRows' name='id_item_$db->numRows' value='" . $db->row["ID_ITEM"] . "'>";

					$titulo->destroy();
				}  // Fin del While
				
				$db->Close();

				echo "<input class='hidden' type='hidden' id='total_items' name='total_items' value='$db->numRows'>";
				
				echo "</table>";
							
		  ?>
		  
				&nbsp;&nbsp;
				<div align='center'>
					<input type="button" class=boton value="<?php echo $BTN_DEVOLUTION; ?>" name="btnAgregar" id="btnAgregar" onClick="devolucion();">
				</div>
				
          <br>
        </form>
      </div>
	  
      <!-- caja_datos-->
    </div>
	
    <!-- contenido_principal -->
    <?php  display_copyright(); ?>
  </div>
  <br style='clear:both'>
  <!-- bloque_principal-->
</div>
<!-- caja_datos -->
	
</body>

</html>
