<?php
	session_start();
	
	/*******
	  Historial de Cambios
	  
	  15 sep 2009: Se crea el archivo anls_series.php
	  22 sep 2009:  Se crea el manejo de suscripciones
	  24 sep 2009:  Se liga la catalogación.
     */
		
	include "../funcs.inc.php";
	include ("../basic/bd.class.php");
	include "../privilegios.inc.php";
	
	include_language( "global_menus" );

	check_usuario_firmado(); 

	include_language( "anls_series" );
	include_language( "anls_suscriptions" );
	
	// Coloca un encabezado HTML <head>
	include "../basic/head_handler.php";
	HeadHandler( $LBL_TITLE, "../");
	
	$id_biblioteca = getsessionvar("id_biblioteca");
	$tab = read_param( "tab", 1 );

	$id_serie_created = read_param( "id_serie_created", 0 );
	$id_serie_edited = read_param( "id_serie_edited", 0 );
	$id_series_deleted = read_param( "id_series_deleted", 0 );
	
	$id_suscrip_created = read_param( "id_suscrip_created", 0 );
	$id_suscrip_edited = read_param( "id_suscrip_edited", 0 );
	$id_suscrip_deleted = read_param( "id_suscrip_deleted", 0 );
	
	if( ($id_suscrip_created != 0) or ($id_suscrip_edited != 0) or ($id_suscrip_deleted != 0) )
	   $tab = 2;

	$filter = read_param( "filter", "" );
		
	verificar_privilegio( PRIV_SERIES, 1 );	
	$priv_catalogacion = verificar_privilegio( PRIV_CATALOGING );
	$priv_series_recep = verificar_privilegio( PRIV_SERIES_RECEPTION );

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

	function crearserie()
	{
		var url = "anls_crearseries.php";
		
		<?php
		
			if( $filter != "" )
			{
				echo "url = url + '?id_coleccion=$filter';";
			}
		
		 ?>
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_series", 870, 550 );
	}
	
	function borrarseries()
	{
		var sel_series = js_getElementByName("sel_series");
		
		if( sel_series )
		{
			if( sel_series.value != "" )
			{
				if( confirm("<?php echo $MSG_CONFIRM_BEFORE_DELETE;?>") )
				{
					var url = "anls_crearseries.php?the_action=delete&series=" + sel_series.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_series", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_SERIES_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_serie( id_serie )
	{
		var url = "anls_crearseries.php?the_action=edit&id_serie=" + id_serie;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_serie", 870, 550 );
	}
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_series = js_getElementByName("sel_series");
		var i;
		
		if( sel_series )
		{
			sel_series.value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
					if( aObjects[i].checked )
					{
						if( sel_series.value == "" )
							sel_series.value = "@";
						else
							sel_series.value += ":";
						
						sel_series.value += aObjects[i].name.substr(4,15);
					}
		}
	}	
	
	/***
	  SUSCRIPCIONES
	 **/
	function crear_suscripcion()
	{
		var url = "anls_crearsuscripcion.php";
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "create_suscrip", 900, 550 );
	}
	
	function borrar_suscripciones()
	{
		var sel_suscrips = js_getElementByName("sel_suscrips");
		
		if( sel_suscrips )
		{
			if( sel_suscrips.value != "" )
			{
				if( confirm( "<?php echo $MSG_CONFIRM_BEFORE_DELETE_V2;?>" ) )
				{
					var url = "anls_crearsuscripcion.php?the_action=delete&suscripciones=" + sel_suscrips.value
					
					js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "delete_suscrips", 350, 350 );
				}
			}
			else
				alert( "<?php echo $MSG_NO_SUSCRIPS_MARKED_TO_DELETE;?>" );

		}
	}
	
	function editar_suscripcion( id_suscripcion )
	{
		var url = "anls_crearsuscripcion.php?the_action=edit&id_suscripcion=" + id_suscripcion;
		
		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_suscrip", 900, 550 );
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
				<LI><a name="tab1_link" href='javascript:changeTab( "info_general", "tab1" );' ><?php echo $LBL_TAB_SERIES;?></a></LI>
				<LI><a name="tab2_link" href='javascript:changeTab( "info_general", "tab2" );'><?php echo $LBL_TAB_SUBSCRIPTIONS;?></a></LI>
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
			  
			  
			
			  ?>
		   
			<DIV id="tab1" name="tab1" class="contenedor_tabs" style="height: 370px;<?php echo($tab==2) ? "display:none" : "";?>">

			   <div style='float:left'>
					<div style='display:inline;'><strong><?php echo $LBL_FILTER;?></strong></div>
					<div style='display:inline;'>
					
						<?php 
						
							$db = new DB;
							
							// Categoría 10 del Tesauro General
							$db->sql = "SELECT a.*, b.ID_TERMINO, b.TERMINO, b.CODIGO_CORTO, b.DESCRIPCION, b.TERMINO_PADRE " . 
										"FROM tesauro_terminos_categorias a " .
										"  LEFT JOIN tesauro_terminos b ON (b.ID_RED=a.ID_RED and b.ID_TERMINO=a.ID_TERMINO) " .
										"WHERE a.ID_RED=" . getsessionvar("id_red") . " and a.ID_CATEGORIA=10 and b.MACROTERMINO='N' " . 
										"ORDER BY b.TERMINO";

							$db->Open();
						
						?>
					
						<select id='cmb_filter' name='cmb_filter' onChange='javascript:activateFilter(this);'>
							<option value=""><?php echo $LBL_NO_FILTER;?></option>";
						
						<?php	
		
							while( $db->NextRow() )
							{ 
								$str_selected = ($filter == $db->row['ID_TERMINO']) ? "selected" : "";
								
								echo "<option value='" . $db->row['ID_TERMINO'] . "' $str_selected>" . $db->row["TERMINO"] . " &nbsp;&nbsp;&nbsp;</option>";							
							}
							
						?>

						</select>
						
						<?php
						
						  $db->Close();					  
						 ?>
					</div>
			   </div>			
			
			   <div id="buttonArea" style='float:right;'>
				  <input class="boton" type="button" value="<?php echo $BTN_ADD_SERIES; ?>" name="btnCrearConsulta" onClick="javascript:crearserie();">
				  <input class="boton" type="button" value="<?php echo $BTN_DELETE_SERIES; ?>" name="btnBorrarConsulta" onClick="javascript:borrarseries();">
			   </div>
			   
			   <?php 
					
					if( $tab==1 )
					{
						if( $id_serie_created != 0 ) 
						{
							echo "<div class=caja_info>";
							echo " <strong>$SAVE_CREATED_DONE</strong>";
							echo "</div>";
						}
					  
						if( $id_serie_edited != 0 )
						{
							echo "<div class=caja_info>";
							echo " <strong>$SAVE_EDIT_DONE</strong>";
							echo "</div>";
						}
						
						if( $id_series_deleted > 0 )
						{
							echo "<div class=caja_info>";
							echo " <strong>$DELETE_DONE</strong>";
							echo "</div>";
						}	
					}

					$db->sql = "SELECT a.ID_SERIE, a.ID_TITULO, a.NOMBRE, a.PAPEL, a.ELECTRONICO, a.ID_SUSCRIPCION, b.TERMINO AS DESCRIP_PERIODICITY, c.TERMINO AS DESCRIP_COLECTION " . 
							   "FROM series a " .
							   " LEFT JOIN tesauro_terminos b ON (b.ID_RED=" . getsessionvar("id_red"). " and b.ID_TERMINO=a.ID_PERIODICIDAD) " .
							   "  LEFT JOIN tesauro_terminos c ON (c.ID_RED=" . getsessionvar("id_red"). " and c.ID_TERMINO=a.ID_COLECCION) " .
							   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
							   
					if( $filter != "" )
						$db->sql .= " and a.ID_COLECCION=$filter ";
							   
					$db->sql .= "ORDER BY a.ID_SERIE";

					$db->Open();
					
					$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
					
					echo "<input type=hidden class=hidden name='sel_consultas' id='sel_consultas' value=''>";
					echo "<br><br>";
					
					echo "<input type='hidden' class='hidden' name='sel_series' id='sel_series' value=''>";
					
					echo "\n\n<table width='915px'>";
					echo "<tr>" .
						 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
						 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_ID_SERIE</td>" .
						 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_NAME_SERIE</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='100px'>$LBL_PERIODICITY</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='80px'>$LBL_COLECTION</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='60px' align='center'>$LBL_ON_PAPER</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='55px' align='center'>$LBL_ON_ELECTRONIC</td>" .
						 " <td class='cuadricula columna columnaEncabezado' width='100px' align='center'>$LBL_CATALOG</td>" . 
						 " <td class='cuadricula columna columnaEncabezado' width='100px' align='center'>$LBL_RECEPTION</td>" . 
						 "</tr>";
					
					while( $db->NextRow() ) 
					{ 
						$id_serie   = $db->row["ID_SERIE"];
						
						if( $db->row["ID_TITULO"] == 0 )
							$id_titulo  = 0;
						else
							$id_titulo  = $db->row["ID_TITULO"];
						
						// opciones de las consultas
						$descrip = "";
						
						$hilite_on = "";
						$hilite_off = "";
						
/**						if( $db->row["SERIES_NUMEROESPECIAL"] == "S" )
						{
							$hilite_on = "<strong>";
							$hilite_off = "</strong>";					
						} **/
						
						$rowedited = "";
						
						if( $id_serie == $id_serie_edited )
							$rowedited = "trHilited";
							
						$rowedited .= $db->class_for_display;
						
						$caption_catalog = "$BTN_CREATE_CATALOG";
						
						if( $id_titulo != 0 )
							$caption_catalog = "$BTN_VIEW_CATALOG";
						
						echo "\n<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
							 " <td class='cuadricula columna'><input name='chk_$id_serie' id='chk_$id_serie' type=checkbox onClick='javascript:click_CheckBoxes();'></td> " .
							 " <td class='cuadricula columna'>$id_serie</td> " .
							 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_serie($id_serie);'>" . $db->row["NOMBRE"]. "</a>$hilite_off</td> " .
							 " <td class='cuadricula columna' align='left'>" . $db->row["DESCRIP_PERIODICITY"] . "</td> " .
							 " <td class='cuadricula columna' align='left'>" . $db->row["DESCRIP_COLECTION"] . "</td> " .
							 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["PAPEL"],true) . "</td> " .
							 " <td class='cuadricula columna' align='center'>" . ICON_DisplayYESNO($db->row["ELECTRONICO"],true) . "</td> " .
							 " <td class='cuadricula columna' align='center'>" . 
							 (($priv_catalogacion==1) ? " <input type='button' class='boton' value='$caption_catalog' onClick='javascript:verCatalogacion($id_serie,$id_titulo);'>" : "--" ) .
							 "  </td>" .
							 " <td class='cuadricula columna' align='center'>" . 
							 (($priv_series_recep==1 and $db->row["ID_TITULO"] != 0 ) ? " <input type='button' class='boton' value='$BTN_RECEPT_SERIES' onClick='javascript:goReception($id_serie,$id_titulo);'>" : "--" ) .
							 "  </td>" .							 
							 " </tr>";
					}
					
					$db->Close();
					
					echo "</table>";
				
			   ?>			   
			
			</DIV>
			
			<DIV id="tab2" name="tab2" class="contenedor_tabs" style="height: 370px;<?php echo($tab==1) ? "display:none" : "";?>">
						
			   <div id="buttonArea2" style='float:right;'>
				  <input class="boton" type="button" value="<?php echo $BTN_ADD_SUSCRIPTIONS; ?>" name="btnCrearConsulta" onClick="javascript:crear_suscripcion();">
				  <input class="boton" type="button" value="<?php echo $BTN_DELETE_SUSCRIPTIONS; ?>" name="btnBorrarConsulta" onClick="javascript:borrar_suscripciones();">
			   </div>
			
				<?php
				
				$db->sql = "SELECT a.*, b.APELLIDOS, b.NOMBRES " . 
						   "FROM suscripciones a " .
						   " LEFT JOIN personas b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA and b.ID_PERSONA=a.ID_PROVEEDOR) " . 
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca ";
						   
				//if( $filter_suscrip != "" )
					//$db->sql .= " and a.ID_COLECCION=$filter ";
						   
				$db->sql .= "ORDER BY a.ID_SUSCRIPCION";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<input type=hidden class=hidden name='sel_consultas' id='sel_consultas' value=''>";
				echo "<br><br>";
				
				echo "<input type='hidden' class='hidden' name='sel_suscrips' id='sel_suscrips' value=''>";
				
				echo "<table width=915px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='20px'>$LBL_ID_SUSCRIP</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_NAME_SUPPLIER</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_DATE_INIT</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px'>$LBL_DATE_END</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='50px' align='center'>$LBL_PRICE_SUSCRIP</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='65px' align='center'>$LBL_PRICE_PAPER</td>" . 
					 " <td class='cuadricula columna columnaEncabezado' width='70px' align='center'>$LBL_PRICE_ELECTRONIC</td>" .
					 "</tr>";
				
				require_once( "../basic/currency.inc.php" );
				
				while( $db->NextRow() ) 
				{ 
					$id_suscripcion	  = $db->row["ID_SUSCRIPCION"];
					
					// opciones de las consultas
					$descrip = "";
					
					$hilite_on = "";
					$hilite_off = "";
					
					if( $db->row["STATUS_SUSCRIPCION"] == "A" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}
					
					$nombre_proveedor = $db->row["APELLIDOS"];
					
					$rowedited = "";
					
					if( $id_suscripcion == $id_suscrip_edited )
						$rowedited = "trHilited";
						
					$rowedited .= $db->class_for_display;
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'><input name='chk_$id_suscripcion' id='chk_$id_suscripcion' type=checkbox onClick='javascript:click_CheckBoxes_v2();'></td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_suscripcion($id_suscripcion);'>$id_suscripcion</a>$hilite_off</td> " .
						 " <td class='cuadricula columna'>$hilite_on<a href='javascript:editar_suscripcion($id_suscripcion);'>$nombre_proveedor</a>$hilite_off</td> " .
						 " <td class='cuadricula columna' align='left'>" . dbdate_to_human_format( $db->row["FECHA_INICIAL"] ) . "</td> " .
						 " <td class='cuadricula columna' align='left'>" . dbdate_to_human_format( $db->row["FECHA_FINAL"] ) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . formato_cantidad($db->row["PRECIO_SUSCRIPCION"]) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . formato_cantidad($db->row["PRECIO_PAPEL"]) . "</td> " .
						 " <td class='cuadricula columna' align='center'>" . formato_cantidad($db->row["PRECIO_ELECTRONICO"]) . "</td> " . 
						 " </tr>";
				}
				
				echo "</table>";
				
				if( $db->numRows == 0 )
				{
					echo "NO SUSCRIPCIONES";
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