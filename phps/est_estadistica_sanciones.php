<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  17 nov 2009: Se crea el para estadística de sanciones
	  
	  21 jul 2011 : Modifica validador y selector de fechas 
					Modifica algo de las tablas de presentación 

     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ( "../basic/bd.class.php" );
	include ( "../privilegios.inc.php" );
	include ( "estadisticas.inc.php" );
	include ( "circulacion.inc.php" );
	
	include_language( "global_menus" );
	include_language( "est_estadistica_sanciones" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1);
	
	verificar_privilegio( PRIV_EST_SANCTIONS, 1 );
	
	$id_biblioteca = getsessionvar( "id_biblioteca" );
	
	$fecha_desde = read_param( "desde", "" );
	$fecha_hasta = read_param( "hasta", "" );
	
	setsessionvar( "fecha_desde", $fecha_desde );
	setsessionvar( "fecha_hasta", $fecha_hasta );

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
				var url = "est_estadistica_sanciones.php?desde=" + obj1.value + "&hasta=" + obj2.value;
				js_ChangeLocation( url );
			}
		}
	}

</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	float: none;
	width: 850px;
}

#info_general 
{
	width: 830px;
}

#buttonArea
{
	margin-bottom: 8px;
}

</style>

<body id="home">

<?php
  ob_start();
  
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
					
					colocar_edit_date_v2( "frm_fechas", $fecha_hasta, $fecha_hasta, 0, "" ); 
					
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
				
				ob_start();  // start buffering echos
	
				$fecha_desde = date_for_database_updates( $fecha_desde ) . " 00:00:00";
				$fecha_hasta = date_for_database_updates( $fecha_hasta ) . " 23:59:59";
				
				$db = new DB;
				
				/* sanciones */
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";
				$sanciones = 0;
				$info_sanciones = estadisticas_informes_sanciones( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $sanciones );
				
				echo "<table width='100%'>";

				
				for( $i=0; $i<count($info_sanciones); $i++ )
				{ 
					if( $i == 0 )
					{
						echo "<tr>" . 
							 " <td class='columna columnaEncabezado cuadricula' width='10%'>$LBL_DATE</td>" .
							 " <td class='columna columnaEncabezado cuadricula' width='40%'>$LBL_SANCTION</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='15%'>$LBL_USER</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_ACUMPLISHED</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_DATE_ACUMPLISHED</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='30%'>$LBL_REASON</td>" .
							 "</tr>";									
					}
					
					$fecha          = dbdate_to_human_format( $info_sanciones[$i]["fecha_sancion"], 1, 0 );
					$fecha_cumplida = dbdate_to_human_format( $info_sanciones[$i]["fecha_cumplida"], 0, 0 );
					
					echo "<tr>" . 
						 " <td class='columna cuadricula' valign='top'>" . $fecha . "</td>" .
						 " <td class='columna cuadricula' valign='top'>" . $info_sanciones[$i]["descripcion"] . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . $info_sanciones[$i]["usuario"] . " / " . $info_sanciones[$i]["usuario_grupo"] . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . ICON_DisplayYESNO( $info_sanciones[$i]["cumplida"], 1 ) . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . $fecha_cumplida . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . $info_sanciones[$i]["motivo"] . "</td>" .
						 "</tr>";					
				}
				
				echo "</table>";
				
				if( count($info_sanciones) > 0 )
					echo "<br>";

				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_SANCTIONS</strong>  " . $sanciones; 
				
				// restricciones			
				echo "<br><br>";
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_2</h1>";
				$restricciones = 0;
				$info_restricciones = estadisticas_informes_restricciones( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $restricciones );
				
				echo "<table width='100%'>";
				
				for( $i=0; $i<count($info_restricciones); $i++ )
				{ 
					if( $i == 0 )
					{
						echo "<tr>" . 
							 " <td class='columna columnaEncabezado cuadricula' width='15%'>$LBL_DATE</td>" .
							 " <td class='columna columnaEncabezado cuadricula' width='18%'>$LBL_PERIOD_OF_TIME</td>" .
							 " <td class='columna columnaEncabezado cuadricula' width='25%'>$LBL_RESTRICTION</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='15%'>$LBL_USER</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_CANCELLED</td>" .
							 " <td align='center' class='columna columnaEncabezado cuadricula' width='22%'>$LBL_REASON</td>" .
							 "</tr>";												
					}
					
					$fecha          = dbdate_to_human_format( $info_restricciones[$i]["fecha_registro"], 1, 0 );

					$vigencia  = dbdate_to_human_format( $info_restricciones[$i]["fecha_inicio"], 0, 0 );
					$vigencia  .= " - " . dbdate_to_human_format( $info_restricciones[$i]["fecha_final"], 0, 0 );
					
					echo "<tr>" . 
					     " <td class='columna cuadricula' valign='top'>" . $fecha . "</td>" .
						 " <td class='columna cuadricula' valign='top'>" . $vigencia . "</td>" .
						 " <td class='columna cuadricula' valign='top'>" . $info_restricciones[$i]["descripcion"] . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . $info_restricciones[$i]["usuario"] . " / " . $info_restricciones[$i]["usuario_grupo"] . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . ICON_DisplayYESNO( $info_restricciones[$i]["cancelada"], 1 ) . "</td>" .
						 " <td align='center' class='columna cuadricula' valign='top'>" . $info_restricciones[$i]["motivo"] . "</td>" .
						 "</tr>";
				}
				
				echo "</table>";
				
				if( count($info_restricciones) > 0 )
					echo "<br>";
				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_RESTRICTIONS</strong>  " . $restricciones; 				

				echo "<br><br>";
				
				// Hide progressing
				SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
				//ob_end_clean() for debugging;
				
				echo "<br><br><input type='button' class='boton' value='$BTN_PRINT' onClick='javascript:print();'><br>";
				echo "<br>";

				ob_end_flush();
				
				$db->destroy();
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->

<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->
       
</body>
</html>