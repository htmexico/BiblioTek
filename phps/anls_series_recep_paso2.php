<?php
	session_start();

	/*******
	  Historial de Cambios

	  11 abr 2009: Se crea el archivo PHP para manejar las existencias (copias) de títulos.

     */		

	include( "../funcs.inc.php" ); 

	check_usuario_firmado(); 

	include( "../basic/bd.class.php" );
	include( "../basic/currency.inc.php" );
	include( "../privilegios.inc.php" );
	
	include_language( "global_menus" );
	include_language( "anls_series_recep" ); 	// archivo de idioma

	$id_biblioteca = getsessionvar("id_biblioteca");	
	
	$id_titulo = read_param( "id_titulo", 0, 1 );
	$id_item_created=0;
	$id_item_edited=0;
	$id_items_deleted = 0;
	
	$anio_filter = read_param( "filter_per_year", 0 );
	$mes_filter = read_param( "filter_per_month", 0 );
	$show_missed = read_param( "show_missed", 0 );
	
	if( isset($_GET["id_item_created"] ) )
		$id_item_created = $_GET["id_item_created"];		

	if( isset($_GET["id_item_edited"] ) )
		$id_item_edited = $_GET["id_item_edited"];

	if( isset($_GET["id_items_deleted"] ) )
		$id_items_deleted = $_GET["id_items_deleted"];	

	require_once "marc.php";
	require_once "circulacion.inc.php";
	
	$db = new DB;
	
	$marc_record = new record_MARC21( $id_biblioteca, $db );

	$item = new TItem_Basic( $id_biblioteca, $id_titulo, 0 );

	$portada = $item->cCover;
	$contraportada = $item->cBackCover;

    $marc_titulo = "<img src='../" . $item->cIcon . "'> [" . $item->Material_ShortCode() . "] ". $item->cTitle;
	$marc_autor = $item->cAutor;

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");
	HeadHandler( "$LBL_TITLE_2 $item->cTitle", "../" );	
	
	verificar_privilegio( PRIV_SERIES_RECEPTION, 1 );	
	
	$error_no_es_serie = $item->nIDSerie == 0;
	
	$id_colection = "";
	$descrip_colection = "";
	$frequency = "";
	$descrip_frequency = "";
	
	$item->ObtenerSerie_Info( $id_colection, $descrip_colection, $date_1st_recept, $frequency, $descrip_frequency );
	
?>

<SCRIPT language="JavaScript">

	function activateFilter()
	{
		// activar filtros
		var url = "anls_series_recep_paso2.php?id_titulo=<?php echo $id_titulo;?>";
		
		var objFilterPerYear = js_getElementByName( "cmb_anio_filter" );
		var objFilterPerMonth = js_getElementByName( "cmb_mes_filter" );
		var objChkShowMissed = js_getElementByName( "chkShowMissed" );
		
		if( objFilterPerYear.value != 0 )
			url = url + "&filter_per_year=" + objFilterPerYear.value;
			
		if( objFilterPerMonth.value != 0 )
			url = url + "&filter_per_month=" + objFilterPerMonth.value;
			
		
		if( objChkShowMissed.checked )
			url = url + "&show_missed=1";

		js_ChangeLocation( url );
	}
	
	function create_existence_item( numero, anio, mes, fecha_recep )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "anls_existencia_titulo.php?is_series=1&id_titulo=<?php echo $id_titulo;?>";
		
		if( numero != 0 )
		{
			url += "&numero=" + numero;
		}
		
		if( anio != 0 )
		{
			url += "&anio=" + anio;
		}	

		if( mes != 0 )
		{
			url += "&mes=" + mes;
		}		
		
		if( fecha_recep != "" )
		{
			url += "&date_of_recep=" + fecha_recep;
		}

		js_ProcessActionURL( <?php echo allow_use_of_popups() ? "1" : "0"; ?>, url, "edit_item", "900", "600" );

		window.status='';
	}
	
	function delete_items()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 

		var sel_items = document.getElementsByName("sel_items");
		var url = "anls_existencia_titulo.php?the_action=delete&id_titulo=<?php echo $id_titulo;?>&items=" + sel_items[0].value;

		if( sel_items.length > 0 )
		{
			if( sel_items[0].value != "" )
			{
				if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
					window.open( url, "delete_items", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
				else
					js_ChangeLocation( url );
			}
			else
				alert( "<?php echo $MSG_NO_MARKED_ITEMS;?>" );
		}
	}
	
	function discard_items()
	{
		var nwidth = screen.width;
		var nheight = screen.height; 

		var sel_items = document.getElementsByName("sel_items");
		var url = "anls_descarta_titulos.php?action=discard&id_titulo=<?php echo $id_titulo;?>&items=" + sel_items[0].value;

		if( sel_items.length > 0 )
		{
			if( sel_items[0].value != "" )
			{
				if( <?php echo allow_use_of_popups() ? "1" : "0"; ?> )
					window.open( url, "discard_items", "WIDTH=" + (nwidth-50) + ",HEIGHT=" + (nheight-120) + ",TOP=20,LEFT=30,resizable=yes,scrollbars=yes,status=yes" );
				else
					js_ChangeLocation( url );
			}
			else
				alert( "<?php echo $MSG_NO_MARKED_ITEMS;?>" );
		}
	}
	
	
	function click_CheckBoxes()
	{
		var aObjects = document.getElementsByTagName("input");
		var sel_items = document.getElementsByName("sel_items");
		var i;
		
		if( sel_items.length > 0 )
		{
			sel_items[0].value = "";
			
			for( i = 0; i<aObjects.length; i++ )
				if( aObjects[i].type == "checkbox" )
				{
					if( aObjects[i].checked && aObjects[i].name != "chkShowMissed" )
					{
						if( sel_items[0].value == "" )
							sel_items[0].value = "@";
						else
							sel_items[0].value += ":";
						
						sel_items[0].value += aObjects[i].name.substr(4,15);
					}
				}

					alert( sel_items[0].value );
					
				
			
		}
		
	}
	
	// editar una existencia
	function edit_Existence( id_item )
	{
		var nwidth = screen.width;
		var nheight = screen.height; 
		var url = "anls_existencia_titulo.php?is_series=1&id_titulo=<?php echo $id_titulo;?>&the_action=edit&id_item=" + id_item;
		
		js_ProcessActionURL( 1, url, "edit_item", 900, 590 );

		window.status='';
	}
	

	
</SCRIPT>

<style type="text/css">

#contenido_principal 
{
	display: block;
	width:99%;
}

#info_ejemplares
{
	padding: 5px;
	overflow: auto;
	width: 760px;
	margin: 0px 20px 0px 0px;
}

#buttonArea
{
	margin-bottom: 8px;
}

</style>

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
	display_menu('../'); 
   
 ?>
   <div id="bloque_principal"> 
      <div id="contenido_principal">
		<h1><?php echo $LBL_TITLE_2;?></h1>

		<div style='float:left; width: 760px;'>
			   <a href='gral_vertitulo.php?id_titulo=<?php echo $id_titulo;?>'><?php echo $marc_titulo; ?></a><br>
			   <?php echo $marc_autor; ?><br>
			   <?php echo $LBL_FREQUENCY;?>&nbsp;<em><?php echo $descrip_frequency;?></em><br><br>
		</div>		

		<div style='float: right; overflow: auto;'>
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
					echo "<span>$MSG_NO_IMAGES_AVAIL</span>";
			  
			?>
		</div>	
		
		<br style='clear:both;'>
		  
	   <div id="info_ejemplares" class="caja_datos">

			   <div style='float:left'>
					<div style='display:inline;'><strong><?php echo $LBL_FILTER;?></strong>&nbsp;&nbsp;</div>
					<div style='display:inline;'><?php echo $LBL_FILTER_BY_YEAR;?>
					
						<?php 
							
							// Categoría 10 del Tesauro General
							$db->sql = "SELECT a.SERIES_ANIO " . 
										"FROM acervo_copias a " .
										"WHERE a.ID_BIBLIOTECA = $id_biblioteca and a.ID_TITULO=$id_titulo " .
										" GROUP BY a.SERIES_ANIO ";

							$db->Open();
						
						?>
					
						<select id='cmb_filter' name='cmb_anio_filter' onChange='javascript:activateFilter();'>
							<option value=""><?php echo $LBL_NO_FILTER;?></option>
						
						<?php	
		
							while( $db->NextRow() )
							{ 
								$str_selected = ($anio_filter == $db->row['SERIES_ANIO']) ? "selected" : "";
								
								echo "<option value='" . $db->row['SERIES_ANIO'] . "' $str_selected>" . $db->row["SERIES_ANIO"] . " &nbsp;&nbsp;&nbsp;</option>";
							}
							
							if( $db->numRows == 0)
							{
								if( $date_1st_recept != "" )
								{
									$aInfoDate = decodedate( dbdate_to_human_format( $date_1st_recept ), 0 );
								}
								else
								{	
									$aInfoDate = decodedate( dbdate_to_human_format( getcurdate_human_format() ), 0 );
								}
								
								$anio = $aInfoDate["a"];
								
								$str_selected = ($anio_filter == $anio) ? "selected" : "";
								
								echo "<option value='$anio' $str_selected>$anio</option>";
							}
							
						?>

						</select>
						
						<?php
						  $db->Close();					  
						 ?>
					</div>
					
					<div style='display:inline;'><?php echo $LBL_FILTER_BY_MONTH;?>&nbsp;
					
						<select id='cmb_mes_filter' name='cmb_mes_filter' onChange='javascript:activateFilter();'>
							<option value=""><?php echo $LBL_NO_FILTER;?></option>	
							
							<?php
							
							  for( $i = 0; $i<count($arrayMeses); $i++ )
							  {
								$str_selected = ($mes_filter == $i+1) ? "selected" : "";
								
								echo "<option value='" . ($i+1) . "' $str_selected>" . $arrayMeses[$i] . " &nbsp;</option>";
							  }
							
							 ?>
							
						</select>
					
					</div>
					
					<input type='checkbox' class='checkbox' name='chkShowMissed' id='chkShowMissed' <?php echo ($show_missed==1) ? "checked" : ""; ?> onClick='javascript:activateFilter();'>&nbsp;<?php echo $LBL_SHOW_MISSED_ISSUES;?>
					
			   </div>			
			   
		   <div id="buttonArea" style='float:right'>
			<input class="boton" type="button" value="<?php echo $BTN_CREATE_ITEM; ?>" name="btnCrear" onClick="javascript:create_existence_item(0,0,0,'')"; />
			<input class="boton" type="button" value="<?php echo $BTN_DELETE_ITEM; ?>" name="btnBorrar" onClick="javascript:delete_items()"; />
			<input class="boton" type="button" value="<?php echo $BTN_DISCARD_ITEM; ?>" name="btnDiscard" onClick="javascript:discard_items()"; />
		   </div> 
		   
		   <br style='clear:both;'>
		   
		   <?php 
		   
				if( $error_no_es_serie == 1 )
				{
					echo "<div class='caja_errores'>";
					echo "<img src='../images/icons/warning.gif'>&nbsp;ESTE TITULO NO HA SIDO CATALOGADO COMO UNA PUBLICACION PERIÓDICA.";
					echo "</div><br>";
				}
		   
				if( $id_item_created != 0 ) 
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_CREATED_DONE</strong>";
					echo "</div>";
				}
			  
				if( $id_item_edited != 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$SAVE_DONE</strong>";
					echo "</div>";
				}
				
				if( $id_items_deleted > 0 )
				{
					echo "<div class=caja_info>";
					echo " <strong>$DELETED_DONE ($id_items_deleted)</strong>";
					echo "</div>";
				}			
			  
				$db = new DB();
				
				$db->sql = "SELECT a.*, d.DESCRIPCION AS DESCRIP_CATEGORIA_PRESTAMO, e.DESCRIPCION AS DESCRIP_UBICACION, e.NOTAS_UBICACION " . 
						   "FROM acervo_copias a " . 
						   "  LEFT JOIN cfgbiblioteca b ON (b.ID_BIBLIOTECA=a.ID_BIBLIOTECA) " .
						   "    LEFT JOIN tesauro_terminos_categorias c ON (c.ID_RED=b.ID_RED and c.ID_CATEGORIA=6 and c.ID_TERMINO=a.CATEGORIA_PRESTAMO)" .
						   "     LEFT JOIN tesauro_terminos d ON (d.ID_RED=b.ID_RED and d.ID_TERMINO=a.CATEGORIA_PRESTAMO) " .
						   "            LEFT JOIN cfgubicaciones e ON (e.ID_BIBLIOTECA=a.ID_BIBLIOTECA and e.ID_UBICACION=a.ID_UBICACION) " .
						   "WHERE a.ID_BIBLIOTECA=$id_biblioteca and a.ID_TITULO=$id_titulo ";
						   
				if( $anio_filter != 0 )
					$db->sql .= " and a.SERIES_ANIO=$anio_filter ";
					
				if( $mes_filter != 0 )					
					$db->sql .= " and a.SERIES_MES=$mes_filter ";	   
						   
				$db->sql .= "ORDER BY a.SERIES_ANIO, a.SERIES_MES ";

				$db->Open();					

				if( $show_missed == 0 )
				{
					$info_items = Array();
					
					while( $db->NextRow() ) 
					{
						$info_items[] = Array( "info_month" => $arrayMeses[ $db->row["SERIES_MES"] ], 
												"id_item" => $db->row["ID_ITEM"], 
												  "id_material" => $db->row["ID_MATERIAL"], 
												   "loan_category" => $db->row["DESCRIP_CATEGORIA_PRESTAMO"], 
												     "num_date" => dbdate_to_human_format( $db->row["SERIES_FECHA_PUBLICACION"], 0 ), 
													  "num_date_end" => "",
													   "num_part" => $db->row["NUMERO_PARTE"], 
													     "num_year" => $db->row["SERIES_ANIO"], 
														  "num_month" => $db->row["SERIES_MES"], 
														   "num_special" => $db->row["SERIES_NUMEROESPECIAL"],
													       "title" => $db->row["SERIES_TITULO"], 
															 "subtitle" => $db->row["SERIES_TITULOSECUNDARIO"], 
															  "price" => $db->row["PRECIO_ADQUISICION"], 
															   "status" => $db->row["STATUS"] );
					}

				}
				else
				{
					while( $db->NextRow() )
					{
						if( $db->numRows == 1 )
						{
							$the_date = $db->row["SERIES_FECHA_PUBLICACION"];
							
							if( $the_date == "" )
								$the_date = $db->row["FECHA_RECEPCION"];
							
							$info_items = $item->ObtenerSerie_PrediccionPeriodo( $anio_filter, $mes_filter, 
										$date_1st_recept, $the_date, $db->row["NUMERO_PARTE"], $frequency, $arrayMeses );
						}
						
						$index_in_array = $item->ObtenerSerie_BuscarElemento( $info_items, $frequency, $db->row );

						if( $index_in_array != -1 )
						{
							$info_items[$index_in_array]["id_item"] = $db->row["ID_ITEM"];
							$info_items[$index_in_array]["id_material"] = $db->row["ID_MATERIAL"];
							$info_items[$index_in_array]["loan_category"] = $db->row["DESCRIP_CATEGORIA_PRESTAMO"];
							
							if( $db->row["SERIES_FECHA_PUBLICACION"] != "" )
								$info_items[$index_in_array]["num_date"] = dbdate_to_human_format( $db->row["SERIES_FECHA_PUBLICACION"], 0 );
							
							$info_items[$index_in_array]["num_part"] = $db->row["NUMERO_PARTE"];
							$info_items[$index_in_array]["num_year"] = $db->row["SERIES_ANIO"];
							$info_items[$index_in_array]["num_month"] = $db->row["SERIES_MES"];
							$info_items[$index_in_array]["num_special"] = $db->row["SERIES_NUMEROESPECIAL"];
							
							$info_items[$index_in_array]["title"] = $db->row["SERIES_TITULO"];
							
							$info_items[$index_in_array]["price"] = $db->row["PRECIO_ADQUISICION"];
							$info_items[$index_in_array]["status"] = $db->row["STATUS"];
						}
					}
					
					if( $db->numRows == 0 )
					{
						$info_items = $item->ObtenerSerie_PrediccionPeriodo( $anio_filter, $mes_filter, 
															$date_1st_recept, "", "", $frequency, $arrayMeses );						
					}
				}
				
				$db->Close();
				
				echo "<input type=hidden class=hidden name='sel_items' id='sel_items' value=''>\n\n";
				
				echo "<table>";
				
				for( $i=0; $i<count($info_items); $i++ )
				{
					if( $i == 0 )
					{
						echo "<tr>" .
							  "<td class='cuadricula columna columnaEncabezado' width=15px>&nbsp;</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=55px>$LBL_ID_ITEM</td>" .
							  "<td class='cuadricula columna columnaEncabezado' width=150px'>$LBL_ID_MATERIAL</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=180px>$LBL_LOAN_CATEGORY</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=75px>$LBL_NUMBER_OF_PART</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=80px>$LBL_DATE_OF_RECEP</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=100px>$LBL_YEAR $LBL_MONTH</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_TITLE_OF_COPY</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=100px>$LBL_PRICE</td>" . 
							  "<td class='cuadricula columna columnaEncabezado' width=120px>$LBL_STATUS</td>" . 
							 "</tr>";
					}
													
					$rowedited = "";
					
					$id_item = $info_items[$i]["id_item"];
					
					if( $id_item != "" )
						if( $id_item == $id_item_edited )
							$rowedited = "class='trHilited' ";
							
					$price = "";
					
					if( $info_items[$i]["price"] != "" )
						$price = formato_cantidad($info_items[$i]["price"]);
						
					$error_class = "";
					
					if( $info_items[$i]["id_material"] == "" )
					   $class_hilite = "columnaError";
					else
					{
						$class_hilite = "hilite_odd";
						
						if( $i % 2 == 1 )
						   $class_hilite = "hilite_even";					   
						
					}

					$status = $info_items[$i]["status"];
					   
					$str_id_item = $id_item;
					$str_id_material = "<a href='javascript:edit_Existence($id_item);'>" . $info_items[$i]["id_material"]. "</a>";
					$check_box_input = "<input name='chk_$id_item' id='chk_$id_item' type='checkbox' onClick='javascript:click_CheckBoxes();'>";
					
					if( $id_item == "" )
					{
						$check_box_input = "";
						$str_id_item = "<img src='../images/icons/warning.gif'>";
						$status = "F";
						
						$str_id_material = "<input class='boton' type='button' value='$BTN_CREATE_ITEM' " . 
								"onClick='javascript:create_existence_item( " . $info_items[$i]["num_part"] . ", " . $info_items[$i]["num_year"] . ", " . $info_items[$i]["num_month"] . ", \"" . $info_items[$i]["num_date"] . "\" )'>";
						
					}
					
					$cMes = "";
					
					if( (int) $info_items[$i]["num_month"] >= 1 and (int) $info_items[$i]["num_month"] <= 12 )
						$cMes = substr( $arrayMeses[ $info_items[$i]["num_month"]-1 ],0,3);

					$hilite_on = "";
					$hilite_off = "";					
					
					if( $info_items[$i]["num_special"] == "S" )
					{
						$hilite_on = "<strong>";
						$hilite_off = "</strong>";					
					}					   
					   
					echo "\n<tr class=$class_hilite onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$class_hilite\";'>" . 
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$check_box_input</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on$str_id_item$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on$str_id_material$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $info_items[$i]["loan_category"] . "$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $info_items[$i]["num_part"] . "$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $info_items[$i]["num_date"] . "$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $cMes . "/" . $info_items[$i]["num_year"] . " $hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $info_items[$i]["title"] . "$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $price . "$hilite_off</td> " .
						 " <td class='cuadricula columna " . (($class_hilite=="columnaError") ? $class_hilite : "" ) . "'>$hilite_on" . $marc_record->GetItemStatus($status) . "$hilite_off</td> " .
						 " </tr>";
				}  // end-for
							
				echo "</table>";
				
				if( count($info_items) == 0 )
				{
					echo $MSG_NO_RECORDS_FOUND;
				}				
		  
		   ?>
		
	   </div><!-- - caja datos -->
	   
	</div> <!-- contenido pricipal -->
		
<?php  display_copyright(); ?>
</div><!--bloque principal-->
 </div><!--bloque contenedor-->

<?php

	$marc_record->destroy();
	$item->destroy();
	
	$db->destroy();	
?>
       
</body>
</html>