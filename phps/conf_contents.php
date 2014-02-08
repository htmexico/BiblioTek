<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  25 ene 2010: Se crea el archivo anls_contents.php
	  28 ene 2010: Se depurar agregar eventos y ligas.
	  02 feb 2010: Agregar notas
	  
     */
		
	include "../funcs.inc.php";
	include ("../basic/bd.class.php");
	include "../privilegios.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "conf_contents" );

	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( $LBL_TITLE, "../");

	$id_biblioteca = getsessionvar("id_biblioteca");
	$tab = read_param( "tab", 1 );

	$id_resource_created = read_param( "id_resource_created", 0 );
	$id_resource_edited = read_param( "id_resource_edited", 0 );
	$id_resources_deleted = read_param( "id_resources_deleted", 0 );

    $tab = read_param( "tab", 1 );

	if( issetsessionvar("tab_used") )
	{
		if( getsessionvar("tab_used") != 0 )
			$tab = getsessionvar("tab_used");
	}

	$filter = read_param( "filter", "" );
		
	verificar_privilegio( PRIV_CONTENTS, 1 );	

?>

<SCRIPT type="text/javascript" language="JavaScript">

	function verCatalogacion( id_serie, id_titulo )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		
		if( id_titulo == 0 )
			js_ProcessActionURL( 1, "anls_catalogacion_paso2.php?auto_select=1&id_serie=" + id_serie, "create_catalog", (nwidth-50), (nheight-120) );
		else
			js_ProcessActionURL( 1, "anls_catalogacion_paso2.php?id_titulo=" + id_titulo, "ver_catalog", (nwidth-50), (nheight-120) );
			
		window.status='';
	}	
	
	function goReception( id_serie, id_titulo )
	{
		var url = "anls_series_recep_paso2.php?id_titulo=" + id_titulo;
		
		js_ChangeLocation( url );
	}	
	
	function activateFilter( obj )
	{
		var url = "anls_series.php?filter=" + obj.value;
		
		js_ChangeLocation( url );
	}

	/* notas */
	function crear_nota()
	{
		var url = "conf_crearnota.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_notes", 870, 550 );
	}

	function borrar_nota()
	{
		var sel_notes = js_getElementByName("sel_notes");
		
		if( sel_notes )
		{
			if( sel_notes.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE_NOTE;?>") )
				{
					var url = "conf_crearnota.php?the_action=delete&notas=" + sel_notes.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_notes", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_NOTES_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_nota( id_nota )
	{
		var url = "conf_crearnota.php?the_action=edit&id_nota=" + id_nota;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_note", 870, 550 );
	}

	
	/* eventos */
	function crear_evento()
	{
		var url = "conf_crearevento.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_series", 870, 550 );
	}
	
	function borrar_eventos()
	{
		var sel_events = js_getElementByName("sel_events");
		
		if( sel_events )
		{
			if( sel_events.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE_EVENT;?>") )
				{
					var url = "conf_crearevento.php?the_action=delete&eventos=" + sel_events.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_series", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_EVENTS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_evento( id_serie )
	{
		var url = "conf_crearevento.php?the_action=edit&id_evento=" + id_serie;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_serie", 870, 550 );
	}
	
	/***
	  LIGAS
	 **/
	function crear_liga()
	{
		var url = "conf_crearliga.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_link", 900, 550 );
	}
	
	function borrar_liga()
	{
		var sel_links = js_getElementByName("sel_links");
		
		if( sel_links )
		{
			if( sel_links.value != "" )
			{
				if( confirm( "<?php echo $MSG_CONFIRM_BEFORE_DELETE_LINK;?>" ) )
				{
					var url = "conf_crearliga.php?the_action=delete&ligas=" + sel_links.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_links", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_LINKS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_liga( id_liga )
	{
		var url = "conf_crearliga.php?the_action=edit&id_liga=" + id_liga;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_link", 900, 550 );
	}
	 
	/* miscelaneas */ 
	function click_CheckBoxes( tab )
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_value;
		var i;
		var prefix;
		
		// cada tab tendrá su prefijo
		if( tab == 1 )
		{
			sel_value = js_getElementByName("sel_notes");
			prefix = "not";
		}
		if( tab == 2 )
		{
			sel_value = js_getElementByName("sel_events");
			prefix = "evn";
		}
		if( tab == 3 )
		{
			sel_value = js_getElementByName("sel_links");
			prefix = "lnk";
		}
		
		if( sel_value )
		{
			sel_value.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
				{
					if( aObjects[i].checked )
					{
						if( aObjects[i].name.substr(4,3) == prefix )
						{
							if( sel_value.value == "" )
								sel_value.value = "@";
							else
								sel_value.value += ":";
							
							sel_value.value += aObjects[i].name.substr(8,15);
						}
					}
				}
		}

	}	
	
</SCRIPT>

<STYLE>

	#info_general 
	{
		width: 930px;
		margin-top: 10px;
	}  
	
	#buttonArea
	{
		float: none;
		positiona: absolute; 		
		left: 550px;
	}
	
  
</STYLE>

<body id="home">

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

	<div id="info_general" name="info_general">
	
			<UL class="tabset_tabs" style="left:0px; text-align: left;font-size:90%;" name='tabMARCFields' id='tabMARCFields' >
				<LI><a name="tab1_link" href='javascript:changeTab( "info_general", "tab1" );' ><?php echo $LBL_TAB_NOTES;?></a></LI>
				<LI><a name="tab2_link" href='javascript:changeTab( "info_general", "tab2" );'><?php echo $LBL_TAB_EVENTS;?></a></LI>
				<LI><a name="tab3_link" href='javascript:changeTab( "info_general", "tab3" );'><?php echo $LBL_TAB_LINKS;?></a></LI>
			</UL>		

			<?php 
			
			  if( $tab == 1 )
			  {
				SYNTAX_JavaScript( 1, 1, " SetTabLinkActive('tab1_link'); " );
			  }
			  else if( $tab == 2 )
			  {
				SYNTAX_JavaScript( 1, 1, " SetTabLinkActive('tab2_link'); " );
			  }
			  else if( $tab == 3 )
			  {
				SYNTAX_JavaScript( 1, 1, " SetTabLinkActive('tab3_link'); " );
			  }			  
			
			  ?>
		   
			<!--  NOTAS  -->
			
			<DIV id="tab1" name="tab1" class="contenedor_tabs" style="height: 370px;<?php echo($tab<>1) ? "display:none" : "";?>">

			   <div style='float:left'>
					<div style='display:inline;'><strong></strong></div>
					<div style='display:inline;'>
					
						<?php 
							$db = new DB;
						?>							

					</div>
			   </div>			
			
			   <div id="buttonArea" style='float:right;'>
				  <input class="boton" type="button" value="<?php echo $BTN_ADD_NOTE; ?>" name="btnCrearConsulta" onClick="javascript:crear_nota();">
				  <input class="boton" type="button" value="<?php echo $BTN_DELETE_NOTE; ?>" name="btnBorrarConsulta" onClick="javascript:borrar_nota();">
			   </div>
			   
			   <?php 
					
					if( $tab==1 )
					{
						if( $id_resource_created != 0 ) 
						{
							echo "<div class=caja_info>";
							echo " <strong>$SAVE_CREATED_DONE</strong>";
							echo "</div>";
						}
					  
						if( $id_resource_edited != 0 )
						{
							echo "<div class=caja_info>";
							echo " <strong>$SAVE_EDIT_DONE</strong>";
							echo "</div>";
						}
						
						if( $id_resources_deleted > 0 )
						{
							echo "<div class=caja_info>";
							echo " <strong>$DELETE_DONE</strong>";
							echo "</div>";
						}	
					}

					$db->sql = "SELECT a.* " . 
							   "FROM recursos_contenido a " .
							   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=2";
					$db->sql .= "ORDER BY a.ID_RECURSO";
					$db->Open();
					
					$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
					
					echo "<br><br>";
					
					echo "<input type='hidden' class='hidden' name='sel_notes' id='sel_notes' value=''>";
					
					echo "\n\n<table width='915px'>";
					echo "<tr>" .
						 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
						 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_ID_NOTE</td>" .
						 " <td class='cuadricula columna columnaEncabezado' width='300px'>$LBL_SUMMARY</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_TIMEPERIOD</td>" . 
						 "</tr>";
						 
					while( $db->NextRow() ) 
					{ 
						$id_recurso = $db->row["ID_RECURSO"];

						// opciones de las consultas
						$descrip = "";
						
						$hilite_on = "";
						$hilite_off = "";
						
						$rowedited = "";
						
						if( $id_recurso == $id_resource_edited )
							$rowedited = "trHilited";
							
						$rowedited .= $db->class_for_display;
						
						$str_time_period = "";
						
						$desde  = get_str_date( $db->row["PUBLICARSE_DESDE"] );
						$hasta  = get_str_date( $db->row["PUBLICARSE_HASTA"] );
						
						if( $desde == $hasta )
							$str_time_period = $desde;
						else 
						{
							$str_time_period = "Del d&iacute;a $desde al $hasta";
						}
						
						echo "\n<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
							 " <td class='cuadricula columna'><input name='chk_not_$id_recurso' id='chk_not_$id_recurso' type=checkbox onClick='javascript:click_CheckBoxes(1);'></td> " .
							 " <td class='cuadricula columna'>$id_recurso</td> " .
							 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_nota($id_recurso);'>" . $db->row["SUMARIO"]. "</a>$hilite_off</td> " .
							 " <td class='cuadricula columna' align='left'>" . $str_time_period . "</td> " .
							 " </tr>";
					}
					
					echo "</table>";
					
					$db->Close();
					
					if( $db->numRows == 0 )
					{
						echo "<br><br>$LBL_NO_NOTES_FOUND";
					}					
				
			   ?>			   
			
			</DIV>
			
			<!-- EVENTO -->
			
			<DIV id="tab2" name="tab2" class="contenedor_tabs" style="height: 370px;<?php echo($tab<>2) ? "display:none" : "";?>">
						
			   <div style='float:left'>
					<div style='display:inline;'><strong><?php echo $LBL_FILTER_BY_EVENT;?></strong></div>
					<div style='display:inline;'>&nbsp;</div>
			   </div>						
						
			   <div id="buttonArea2" style='float:right;'>
				  <input class="boton" type="button" value="<?php echo $BTN_ADD_EVENT; ?>" name="btnCrearConsulta" onClick="javascript:crear_evento();">
				  <input class="boton" type="button" value="<?php echo $BTN_DELETE_EVENT; ?>" name="btnBorrarConsulta" onClick="javascript:borrar_eventos();">
			   </div>
			
				<?php
				$db->sql =  "SELECT a.*, b.TERMINO AS DESCRIP_EVENTO " . 
						    "FROM recursos_contenido a " .
							" LEFT JOIN tesauro_terminos b ON (b.ID_RED=" . getsessionvar("id_red"). " and b.ID_TERMINO=a.ID_TIPOEVENTO) " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=3 ";
				$db->sql .= "ORDER BY a.ID_RECURSO; ";
				$db->Open();
						   
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<br><br>";
				echo "<input type='hidden' class='hidden' name='sel_events' id='sel_events' value=''>";
				
				echo "<table width=915px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_ID_EVENT</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_SUMMARY</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_TIMEPERIOD</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_EVENT_TYPE</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_EVENT_PROGRAMMED_DATES</td>" . 
					 "</tr>";
				
				require_once( "../basic/currency.inc.php" );
				
				while( $db->NextRow() ) 
				{ 
					$id_evento	  = $db->row["ID_RECURSO"];
					
					// opciones de las consultas
					$descrip = "";
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( 0 )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_evento == $id_resource_edited )
						$rowedited = "trHilited";						
						
					$rowedited .= $db->class_for_display;
					
					$str_time_period = "";
					
					$desde  = get_str_date( $db->row["PUBLICARSE_DESDE"] );
					$hasta  = get_str_date( $db->row["PUBLICARSE_HASTA"] );
					
					if( $desde == $hasta )
						$str_time_period = $desde;
					else 
					{
						$str_time_period = "Del día $desde al $hasta";
					}
					
					$str_schedule;
					
					$desde  = get_str_date( $db->row["FECHA_DESDE"] );
					$hasta  = get_str_date( $db->row["FECHA_HASTA"] );
					
					if( $desde == $hasta )
						$str_schedule = $desde;
					else 
					{
						$str_schedule = "Del día $desde al $hasta";
					}				

					$hora_desde = get_str_onlytime($db->row["HORA_DESDE"]);
					$hora_hasta = get_str_onlytime($db->row["HORA_HASTA"]);
					
					if( $hora_desde == $hora_hasta ) 
						$str_schedule .= " " . $hora_desde;
					else
						$str_schedule .= " " . $hora_desde . "-" . $hora_hasta;
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_evn_$id_evento' id='chk_evn_$id_evento' type=checkbox onClick='javascript:click_CheckBoxes(2);'></td> " .
						 " <td class='cuadricula columna'>$id_evento</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_evento($id_evento);'>" . $db->row["SUMARIO"]. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='left'>" . $str_time_period . "</td> " .
						 " <td class='cuadricula columna' align='left'>" . $db->row["DESCRIP_EVENTO"] . "</td> " .
						 " <td class='cuadricula columna' align='left'>" . $str_schedule . "</td> " .
						 " </tr>";
				}
				
				echo "</table>";
				
				if( $db->numRows == 0 )
				{
					echo "<br><br>$LBL_NO_EVENTS_FOUND";
				}
				
				$db->Close();
				
				?>

			</DIV>		
			
			<!-- LIGAS --> 
			<DIV id="tab3" name="tab3" class="contenedor_tabs" style="height: 370px;<?php echo($tab<>3) ? "display:none" : "";?>">
						
			   <div style='float:left'>
					<div style='display:inline;'></div>
					<div style='display:inline;'>&nbsp;</div>
			   </div>						
						
			   <div id="buttonArea2" style='float:right;'>
				  <input class="boton" type="button" value="<?php echo $BTN_ADD_LINK; ?>" name="btnCrearLiga" onClick="javascript:crear_liga();">
				  <input class="boton" type="button" value="<?php echo $BTN_DELETE_LINK; ?>" name="btnBorrarLiga" onClick="javascript:borrar_liga();">
			   </div>
			   
				<?php
				$db->sql =  "SELECT a.* " . 
						    "FROM recursos_contenido a " .
							"WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TIPORECURSO=1 ";
				$db->sql .= "ORDER BY a.ID_RECURSO; ";
				$db->Open();
						   
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<br><br>";
				echo "<input type='hidden' class='hidden' name='sel_links' id='sel_links' value=''>";
				
				echo "<table width=915px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_ID_EVENT</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_LABEL</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_URL</td>" . 

					 "</tr>";
				
				require_once( "../basic/currency.inc.php" );
				
				while( $db->NextRow() ) 
				{ 
					$id_liga	  = $db->row["ID_RECURSO"];
					
					// opciones de las consultas
					$descrip = "";
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( 0 )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$rowedited = "";
					
					if( $id_liga == $id_resource_edited )
						$rowedited = "trHilited";						
						
					$rowedited .= $db->class_for_display;
					
					$url = trim($db->row["URL"]);
					
					if( substr($url,0,3) == "www" )
					{
						$url = "http://" . $url;
					}
					
					$link_on = "<a href='$url' target='new'>";
					$link_off = "</a>";
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_lnk_$id_liga' id='chk_lnk_$id_liga' type=checkbox onClick='javascript:click_CheckBoxes(3);'></td> " .
						 " <td class='cuadricula columna'>$id_liga</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_liga($id_liga);'>" . $db->row["SUMARIO"]. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='left'>$link_on" . $url . "$link_off</td> " .
						 " </tr>";
				}
				
				echo "</table>";
				
				if( $db->numRows == 0 )
				{
					echo "<br><br>$LBL_NO_LINKS_FOUND";
				}
				
				$db->Close();
				
				$db->destroy();
				
				?>
			   

			</DIV>
						  
	</div> <!-- # info_general --> 	
	
 </div> <!-- contenido_principal -->
 
 <br style='clear:both;'>
 <br>
		<div id=buttonArea>
			<input type=button class=boton value="<?php echo $BTN_GOBACK;?>" onClick='javascript:window.history.back();'>
		</div>			

 <div id="contenido_adicional">
	&nbsp;
  </div> <!-- contenido_adicional -->

</div>
<!-- end div bloque_principal -->

<?php  display_copyright(); ?>

</div><!-- end div contenedor -->

</body>

</html>