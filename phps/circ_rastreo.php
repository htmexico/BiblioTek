<?php
	session_start();

	/*******
	  Historial de Cambios

	  14 oct 2009: Se crea el archivo PHP para consultar el rastreo

     */		

	include( "../funcs.inc.php" ); 

	check_usuario_firmado(); 

	include( "../basic/bd.class.php" );
	
	include( "../basic/inc.date_functions.php" );
	include( "../basic/class.calendar.php" );	
	
	include_language( "global_menus" );
	include_language( "circ_rastreo" ); 	// archivo de idioma

	$id_biblioteca = getsessionvar("id_biblioteca");	
	
	$id_titulo = read_param( "id_titulo", 0, 1 ); // fail if don't exist

	$db = new DB();
		
	require_once "marc.php";
	require_once "circulacion.inc.php";

	$marc_record = new record_MARC21( $id_biblioteca, $db );
	$item = new TItem_Basic( $id_biblioteca, $id_titulo, 0 );
	
	if( $item->NOT_FOUND )
	{
		$item->destroy();
		$db->destroy();

		ges_redirect( "anls_existencias.php?error_title=$id_titulo" );
	}

	$portada = $item->cCover;
	$contraportada = $item->cBackCover;

    $marc_titulo = "<img src='../" . $item->cIcon . "'> [" . $item->Material_ShortCode() . "] ". $item->cTitle;
	$marc_autor = $item->cAutor;

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_HEADER $item->cTitle", "../" );	

	$item->destroy(); 		

?>

<SCRIPT language="JavaScript">

	function verDetallesDia( id_bib, id_item, fecha, a, m, d, detalles, obj )
	{
		var objGrayed = js_getElementByName( "popUpBlock" );
		
		ShowDiv( "popUpBlock" );
		ResizeFullHeight( "popUpBlock" );
		
		objGrayed.style.left = "1px";
		
		if( ShowDiv( "div_ver_detalles" ) )
		{
			var div_ver_detalles = js_getElementByName( "div_ver_detalles" );
			var nheight;
			var nwidth;
			var scrolledY;
			
			if( self.pageYOffset ) 
			{
				scrolledY = self.pageYOffset;
			} 
			else if( document.documentElement && document.documentElement.scrollTop ) 
			 {
				scrolledY = document.documentElement.scrollTop;
			 } 
			else if( document.body ) 
			 {
				scrolledY = document.body.scrollTop;
			 }			
			
			if( window.innerHeight )
				nheight = window.innerHeight;
			else if( document.documentElement.clientHeight )
				nheight = document.documentElement.clientHeight;
			else if( document.body.clientHeight )
				nheight = document.body.clientHeight;
				
			if( window.innerWidth )
				nwidth = window.innerWidth;
			else if( document.documentElement.clientWidth )
				nwidth = document.documentElement.clientWidth;				
			else if( document.body.clientWidth )
				nwidth = document.body.clientWidth;				
			
			div_ver_detalles.style.position = "absolute";
			div_ver_detalles.style.top  = scrolledY + ((nheight / 2 ) - 150) + "px";
			div_ver_detalles.style.left = ((nwidth / 2 ) - 270) + "px";
			
			// show info
			var div_info_detalles = js_getElementByName( "info_detalles" );
			
			if( div_info_detalles ) 
			{			
				div_info_detalles.innerHTML = "<strong><img src='../images/icons/calendario.png'>&nbsp;Fecha: " + fecha + "</strong><br><br>Detalles:" + detalles;
			}
			
			div_ver_detalles.style.zIndex = 2000; // para que quede arriba de otros
		}
	}
	
	function closeDetalles()
	{
		if( HideDiv( "div_ver_detalles" ) )
		{					
			HideDiv( "popUpBlock" );
		}
	}	

	function HiliteDay( obj )
	{
		obj.style.backgroundColor = "yellow";
	}

	function UnHiliteDay( obj )
	{
		obj.style.backgroundColor = "";
	}	

</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	display: inline;
	float: left;
	width: 82%;
}

#contenido_adicional
{
	display: inline;
	float: right;
	width: 15%;
}

#info_ejemplares
{
	overflow: auto;
	width: 99%;
}

.zero_panel
{
	float: left;
	display: inline;
	width: 100px;
	margin: 2px;
	padding: 2px;
}

.first_panel
{
	float: left;
	display: inline;
	width: 200px;
	margin: 2px;
	padding: 2px;
}

.second_panel
{
	float: left;
	width: 120px;
	margin: 2px;	
	padding: 2px;	
}

.third_panel
{
	float: left;
	width: 35%;
	margin: 2px;	
}

.first_month
{
	float: left;
	width: 32%;
	margin: 2px;
}

.second_month
{
	float: left;
	width: 32%;
	margin: 2px;	
}

.third_month
{
	float: left;
	width: 32%;
	margin: 2px;	
}

	#div_ver_detalles
	{	
		display: none;
		position: absolute;
		background-color: #FCFBD0;
		border: 3px solid gray; 
		color: black;
	
		width: 500px;
		height: 220px;
		
		font-size: 92%;
	}	
	
	#info_detalles
	{
		float:left; 
		width: 400px; 
		text-align: left; 
		font-size: 150%;
	}
	
	#info_portadas
	{
		font-size:85%;
	}

</style>

<body id="home">

<div id='popUpBlock' name='popUpBlock'></div>

	<!--- INICIA POPUP PARA CUMPLIR SANCIÓN -->
	<div class="groupbox" id="div_ver_detalles" name="div_ver_detalles">
		
		<div id='info_detalles' name='info_detalles'>
		</div>
		
		<!-- close icon -->
		<div style="float:right; padding:0px; position: relative; top: -10px; margin:0px;">
			<br>
			<a href="javascript:closeDetalles();"><img src="../images/icons/close_button.gif"></a>
		</div><br>
		<!-- close icon -->
		
		<br style='clear:all'>		
		
	</div> 	   

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
	display_menu('../'); 
   
 ?>
 
   <div id="bloque_principal"> 
      <div id="contenido_principal">
		<h1><?php echo $LBL_SUBTITLE;?></h1>
		
		<div style='float:left;'>
			   <a href='gral_vertitulo.php?id_titulo=<?php echo $id_titulo;?>'><?php echo $marc_titulo; ?></a><br>
			   <?php echo $marc_autor; ?><br><br>
		</div>
		
		<br style='clear:both;'>
		
		<?php
			echo "<div id='showProcessing' name='showProcessing'><img src='../images/icons/wheel-loader_small.gif'>$MSG_PROCESSING_SOMETHING</div>";
			ob_end_flush();		
			
			sleep(1);

			ob_start();  // start buffering echos							
		?>
		
	   <div id="info_ejemplares" class="caja_datos">
	   
		   <?php 
				
				$db->sql = "SELECT a.*, d.DESCRIPCION AS DESCRIP_CATEGORIA_PRESTAMO, e.DESCRIPCION AS DESCRIP_UBICACION, e.NOTAS_UBICACION " . 
						   "FROM acervo_copias a " . 
						   "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						   "    LEFT JOIN tesauro_terminos_categorias c ON (c.ID_RED=b.ID_RED and c.ID_CATEGORIA=6 and c.ID_TERMINO=a.CATEGORIA_PRESTAMO)" .
						   "     LEFT JOIN tesauro_terminos d ON (d.ID_RED=b.ID_RED and d.ID_TERMINO=a.CATEGORIA_PRESTAMO) " .
						   "            LEFT JOIN cfgubicaciones e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_UBICACION=a.ID_UBICACION) " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo " .
						   "ORDER BY a.ID_ITEM ";

				$db->Open();
				
				$fecha_base = getcurdate_human_format();
				$fecha_base_unixstyle = convert_humandate_to_unixstyle( $fecha_base );
				
				$fecha_desde = sum_days( $fecha_base, -30 );
				
				//
				// ajustar primer día
				//
				$fecha_inicial_offset = sum_days( $fecha_desde, -20 );
				$aValues = decodedate( $fecha_inicial_offset );
				
				if( $aValues["d"] > 25 )
				{
					$init_m = $aValues["m"];
					
					// ubicar inicio del siguiente mes (OFFSET)
					do
					{
						$fecha_inicial_offset = sum_days( $fecha_inicial_offset, 1 );
						$fecha_desde = sum_days( $fecha_desde, 1 );
						$aValues = decodedate( $fecha_inicial_offset );
						
					}while( $aValues["m"] == $init_m );
				}
				//
				// ajuster primer día
				//
				
				$fecha_hasta = sum_days( $fecha_base, 30 );
				
				while( $db->NextRow() ) 
				{ 
					$status = $db->Field("STATUS");					
					$status = $marc_record->GetItemStatus($status);
									
					$rowedited = "";
					
					echo "<div class='zero_panel'><strong>$LBL_ID_ITEM</strong>&nbsp;<br>" . $db->row["ID_ITEM"] . "</div>";
					echo "<div class='first_panel'><strong>$LBL_ID_MATERIAL</strong>&nbsp;" . $db->row["ID_MATERIAL"] . "</div>";
					echo "<div class='second_panel'><strong>$LBL_ST_MATERIAL</strong>&nbsp;<br>$status</div>";
					echo "<div class='third_panel'><strong>$LBL_CALLNUMBER</strong>&nbsp;<br>" . $marc_record->ObtenerCallNumber( $db->row["SIGNATURA_PREFIJO"], $db->row["SIGNATURA_CLASE"], $db->row["SIGNATURA_LIBRISTICA"] ) . "</div>";
					echo "<br style='clear:both;'>";

					$titulo = new TItem_Basic( $id_biblioteca, $db->row["ID_ITEM"], 1, $db );

					$array_no_usado_aqui = Array();

					$fechas_analizadas = $titulo->VerificarDisponibilidad_X_ITEM( $db->row["ID_ITEM"], "N", $fecha_desde, $fecha_hasta, $array_no_usado_aqui, 20, 2 );

					$last_month = -1;
					$last_date = "";
					$nmonth = 0;

					$aEventos = Array();
					$aEventosHints = Array();

					for ( $x=0; $x<count($fechas_analizadas); $x++ )
					{
						$aValores = decodedate( $fechas_analizadas[$x]["date"] );
						
						if( $last_month != $aValores["m"] )
						{
							
							if( $last_month != -1 )
							{						
								$nmonth++;
								
								if( $nmonth == 1 )
									echo "<div class='first_month'>";
								else if( $nmonth == 2 )
									echo "<div class='second_month'>";
								else if( $nmonth == 3 )
									echo "<div class='third_month'>";
								else if( $nmonth > 3 )	
									echo "<div>"; // other months
									
								$aValoresLastMonth = decodedate( $last_date );
								
								$c = new calendar( $aValoresLastMonth["d"], (int) $aValoresLastMonth["m"], $aValoresLastMonth["a"], 0 );
								$c->startonmonday = 1;
								$c->events 	     = $aEventos;
								$c->events_hint  = $aEventosHints;
								$c->calendar_width_pxls = 220;
								
								$c->link_mask_substitute = "verDetallesDia( $id_biblioteca, " . $db->row["ID_ITEM"] . ", \"%fecha\", %a, %m, %d, \"%s\", this )";
								
								$c->show( 1, 1, 0, 1 );
								
								unset( $c );  // destroy
								
								echo "</div>\n";
								
								if( $nmonth == 4 ) break;
							}

							$last_month = $aValores["m"];
						}
						
						$aEventos[] 	= $fechas_analizadas[$x]["date"];
						
						if( $fechas_analizadas[$x]["disponible"] == 1 )						
						{
							// 
							$fecha_analizada_unixstyle = convert_humandate_to_unixstyle( $fechas_analizadas[$x]["date"] );

							$aEventosHints[] = Array( "date" => $fechas_analizadas[$x]["date"],
													  "hint" => ($fecha_analizada_unixstyle>=$fecha_base_unixstyle ? "Disponible" : "Estuvo Disponible"),
												      "icon" => ($fecha_analizada_unixstyle>=$fecha_base_unixstyle ? "<img src='../images/icons/item_available.gif'>" : "<img src='../images/icons/item_was_available.gif'>") );
						}
						else if( $fechas_analizadas[$x]["disponible"] == 0 )						
						{
							$info_hint = "";
							$icono = "";
							
							if ($fechas_analizadas[$x]["no_reserva"] != -1 )
							{
								$info_hint .= sprintf( $HINT_ITEM_RESERVED_TO, $fechas_analizadas[$x]["nombre_usuario_reserva"], $fechas_analizadas[$x]["fecha_reserva"] ); 
								
								$icono .= "<img src='../images/icons/item_reserved.gif'>";
							}
								
							if ($fechas_analizadas[$x]["no_prestamo"] != -1 )
							{
								if( $info_hint != "" ) $info_hint .= "<br><br>";
								$info_hint .= sprintf( $HINT_ITEM_ISSUED_BY, $fechas_analizadas[$x]["nombre_usuario_prestamo"], $fechas_analizadas[$x]["fecha_prestamo"] );
								
								if( $fechas_analizadas[$x]["prestamo_vencido"] == "S" )
								{
									if( $fechas_analizadas[$x]["fecha_devolucion_programada"] != "" )
										$info_hint .= "<br>" . sprintf( $HINT_ITEM_HAD_TO_BE_RETURNED, $fechas_analizadas[$x]["fecha_devolucion_programada"] ) . "<br>";
									
									$icono .= "<img src='../images/icons/item_unknown_state.gif'>";
								}
								else
								{
									if( $fechas_analizadas[$x]["fecha_devolucion_programada"] != "" )
										$info_hint .= "<br>" . sprintf( $HINT_ITEM_SHOULD_BE_RETURNED, $fechas_analizadas[$x]["fecha_devolucion_programada"] ) . "<br>";

									$icono .= "<img src='../images/icons/item_not_available.gif'>";
								}
								
							}
							
							$aEventosHints[] = Array( "date" => $fechas_analizadas[$x]["date"],
													  "hint" => $info_hint,
												      "icon" => $icono );
						}
						else
						{
							$aEventosHints[] = Array( "date" => $fechas_analizadas[$x]["date"], 
													  "hint" => "No Especificado",
												      "icon" => "<img src='../images/icons/item_not_available.gif'>" );
						}
						
						$last_date = $fechas_analizadas[$x]["date"];
					}
					
					unset( $array_no_usado_aqui );
					
					$titulo->destroy();
					
					echo "<br style='clear:both;'>";
					echo "<hr>";
					echo "<br style='clear:both;'>";
				}
				
				$db->Close();
				
				if( $db->numRows == 0 )
				{
					echo $MSG_NO_COPIES_FOUND;
				}				
		  
		   ?>
		
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->
	
  <div id="contenido_adicional">
		<div id='info_portadas'>
			<?php 
				if( $portada != NULL )
				{
					$width1 = 70;
					
					if( $contraportada == NULL )
					  $width1 = 130;
					
					echo "<img name='portada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=PORTADA' width='$width1'\>";
				}

				if( $contraportada != NULL )
				{
					echo "<img name='contraportada' src='../phps/image.php?id_biblioteca=$id_biblioteca&id_titulo=$id_titulo&tipoimagen=CONTRAPORTADA' width='70'\>";
				}

				if( $portada == NULL and $contraportada == NULL )
					echo "<span>Sin imagenes disponibles</span>";
			  
				echo "<br><br>";
				echo "<img src='../images/icons/item_available.gif'>&nbsp;$HINT_DESCRIP_ICON1<br>";
				echo "<img src='../images/icons/item_was_available.gif'>&nbsp;$HINT_DESCRIP_ICON2<br>";
				echo "<img src='../images/icons/item_not_available.gif'>&nbsp;$HINT_DESCRIP_ICON3<br>";
				echo "<img src='../images/icons/item_reserved.gif'>&nbsp;$HINT_DESCRIP_ICON4<br>";
				echo "<img src='../images/icons/item_unknown_state.gif'>&nbsp;$HINT_DESCRIP_ICON5<br>";
			  
			?>
		</div>	
  </div>  <!-- contenido_adicional -->
  
  <?php  display_copyright();
  
	// Hide progressing
	SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
	//ob_end_clean() for debugging;  

  ob_end_flush();		

  ?>	
		
 </div><!--bloque principal-->
 </div><!--bloque contenedor-->

<?php

	$marc_record->destroy();
	
	$db->destroy();	
?>
       
</body>
</html>