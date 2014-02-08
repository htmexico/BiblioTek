<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  08 sep 2009: Se crea el para estadística.
	  09 oct 2009: Se agrega resumen de catalogacion; Se perfecciona la actualización de la consulta después de cambiar fechas
	  21-oct-2009: Se agrega columna de usuario que tiene el material.
	  
	  21 jul 2011 : Modifica validador y selector de fechas
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("circulacion.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_prestamos_vencidos" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1 );
	
	verificar_privilegio( PRIV_EST_LOANS_ON_DUE, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$fecha_desde = read_param( "desde", "" );
	$fecha_hasta = read_param( "hasta", "" );

	$filter = read_param( "filter", "" );
	
?>

<SCRIPT type='text/javascript' src='../basic/calend.js'></SCRIPT>

<link rel="stylesheet" href="../basic/calendar/calendar.css">
<script type="text/javascript" language="javascript" src="../basic/calendar/calendar_mx.js"></script>


<SCRIPT language="JavaScript">

	function updateView()
	{
		var error = 0;
		var obj1 = js_getElementByName( "fecha_desde" );
		var obj2 = js_getElementByName( "fecha_hasta" );

		if( !EsFechaValida( obj1 ) || !EsFechaValida( obj2 ) )
		{
			alert( "<?php echo $VALIDA_MSG_DATES_INVALID;?>" );
			
			obj1.focus();
			
			error = 1;
		}
		
		if( error == 0 )
		{
			if( !Validar2Fechas( obj1.value, obj2.value ) )
			{
				alert( "<?php echo $VALIDA_MSG_DATES_WRONG;?>" );
			
				obj1.focus();
				
				error = 1;
			}
		
			if( error == 0 )
			{
				var url = "est_prestamos_vencidos.php?desde=" + obj1.value + "&hasta=" + obj2.value;
				js_ChangeLocation( url );
			}
		}
	}

</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	width: 900px;
	float: none;
}

#info_general 
{
	width: 900px;
}

#buttonArea
{
	margin-bottom: 8px;
}

</style>

</head>

<body id="home">

<?php
  ob_start();   // para mostrar avance en procesamientos 

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
   <div id="bloque_principal" > 
      <div id="contenido_principal">
	   <h1><?php echo $LBL_HEADER;?></h1>
		  
	   <div id="info_general" class="caja_datos">

		   <form id='frm_fechas' name='frm_fechas'>
				<div style='float:left; width: auto; margin-right: 10px;'>
					<?php echo "<span>$LBL_DESDE</span>";
					
					colocar_edit_date_v2( "frm_fechas", "fecha_desde", $fecha_desde, 0, "" ); 
					
					?>
				</div>
				<div style='float:left; width: auto;'>
					<?php echo "<span>$LBL_HASTA</span>";
					
					colocar_edit_date_v2( "frm_fechas", "fecha_hasta", $fecha_hasta, 0, "" ); 
					
					?>				
				</div>
				
				<input type='button' class='boton' value='<?php echo $BTN_UPDATE_VIEW;?>' onClick='javascript:updateView();'>
				
				
		   </form>

		   <?php 
	
				echo "<div id='showProcessing' name='showProcessing'><img src='../images/icons/wheel-loader_small.gif'>$MSG_PROCESSING_SOMETHING</div>";
				ob_end_flush();
				
				sleep(1);

				ob_start();  // start buffering echos				
				echo "<br>";	
	
				$fecha_desde = date_for_database_updates( $fecha_desde ) . " 00:00:00";
				$fecha_hasta = date_for_database_updates( $fecha_hasta ) . " 23:59:59";
				
				$db = new DB;
				
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";				
				
				$db->sql = "SELECT a.ID_PRESTAMO, a.ID_ITEM, a.FECHA_DEVOLUCION_PROGRAMADA, b.FECHA_PRESTAMO, b.ID_USUARIO, b.USUARIO_PRESTA, c.ID_USUARIO, c.PATERNO, c.MATERNO, c.NOMBRE " . 
						   "FROM prestamos_det a " .
						   " LEFT JOIN prestamos_mst b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and a.ID_PRESTAMO=b.ID_PRESTAMO) " . 
						   "    LEFT JOIN cfgusuarios c ON (c.ID_BIBLIOTECA=b.ID_BIBLIOTECA and c.ID_USUARIO=b.ID_USUARIO) " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and (a.FECHA_DEVOLUCION_PROGRAMADA BETWEEN '$fecha_desde' and '$fecha_hasta') and a.STATUS='P' ";
				$db->sql .= "ORDER BY a.FECHA_DEVOLUCION_PROGRAMADA";
				
				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<table width=890px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .					 
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_USUARIOREGISTRO</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_FECHAREGISTRO</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_FECHADEVOLUCION</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='25px'>$LBL_IDITEM</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='40px'>$LBL_TAG_CODE</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='170px'>$LBL_TAG_TITULO</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_RETRASO</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='60px'>$LBL_USUARIO_CON_PRESTAMO</td>" .
					 "</tr>";
				
				$cur_datetime = current_dateandtime(1);
				$TIMESTAMP_FechaHoraACTUAL = convert_humandate_to_unixstyle( $cur_datetime );
				
				while( $db->NextRow() ) 
				{ 
					$txt_fecha_prestamo  = dbdate_to_human_format( $db->row["FECHA_PRESTAMO"], 1, 0 );
					$txt_fecha_devol 	 = dbdate_to_human_format( $db->row["FECHA_DEVOLUCION_PROGRAMADA"], 1, 0 );
					
					$txt_usuario_registro = $db->row["USUARIO_PRESTA"];
					
					$hilite_on = "";
					$hilite_off = "";

					$rowedited = "";

					$rowedited .= $db->class_for_display;

					$item = new TItem_Basic( $id_biblioteca, $db->row["ID_ITEM"], 1, $db );

					$txt_id_material = $item->item_id_material;

					// Obtener la fecha de devolución incluyendo probables RENOVACIONES
					$fecha_dev_prog = dbdate_to_human_format( $item->ObtenerFechaDevolucion( $db->row["ID_PRESTAMO"] ), 1 );
					$TIMESTAMP_FechaHoraDEVPROGRAMADA = convert_humandate_to_unixstyle( $fecha_dev_prog );					

					if( $TIMESTAMP_FechaHoraACTUAL > $TIMESTAMP_FechaHoraDEVPROGRAMADA )
					{
						// Retraso Efectivo
						$aTiempoRetraso = $item->ObtenerTiempoRetraso( $TIMESTAMP_FechaHoraDEVPROGRAMADA, $TIMESTAMP_FechaHoraACTUAL );

						$delayed_time = "";

						if( $aTiempoRetraso["tdias"] > 0 ) 
							$delayed_time .= $aTiempoRetraso["tdias"] . " día(s) ";
						
						if( $aTiempoRetraso["hrs"] > 0 ) 
							$delayed_time .= $aTiempoRetraso["hrs"] . " hora(s) ";

						$delayed_time .= ($aTiempoRetraso["mins"]<=9 ? "0" : "") . $aTiempoRetraso["mins"] . " min(s) ";					

						echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
							 " <td class='cuadricula columna'>" . $db->numRows ."</td> " .						 
							 " <td class='cuadricula columna'>" . $item->ObtenerUsuarioRegistro(1) . "</td> " .
							 " <td class='cuadricula columna'>$hilite_on" . $txt_fecha_prestamo . "</a>$hilite_off</td> " .
							 " <td class='cuadricula columna'>$hilite_on" . $fecha_dev_prog . "</a>$hilite_off</td> " .
							 " <td class='cuadricula columna'>$txt_id_material</td> " .
							 " <td class='cuadricula columna'>" . $item->Material_ShortCode() . "</td> " .
							 " <td class='cuadricula columna'>" . $item->cTitle . "</td> " .
							 " <td align='center' class='cuadricula columna'>" . $delayed_time . "</td> " .
							 " <td align='center' class='cuadricula columna'>" . $db->row["NOMBRE"] . " " . $db->row["PATERNO"] . "</td> " .
							 " </tr>";
					}
					
					$item->destroy();
					unset( $item );
				}
				
				$db->FreeResultset();
				
				echo "</table>";
				
				echo "<br>";		
				
				$db->destroy();
				
				// Hide progressing
				SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
				//ob_end_clean() for debugging;
				
				echo "<br><input type='button' class='boton' value='$BTN_PRINT' onClick='javascript:print();'><br>";				
				
				ob_end_flush();				
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->

<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->
       
</body>
</html>