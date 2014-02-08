<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  08 sep 2009: Se crea el para estadística
	  09 oct 2009: Se agrega resumen de catalogacion; Se perfecciona la actualización de la consulta después de cambiar fechas
	  
	  21 jul 2011 : Modifica validador y selector de fechas
	  
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("circulacion.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_estadistica_catalogacion" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1 );
	
	verificar_privilegio( PRIV_EST_CATALOGING, 1 );
	
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
				var url = "est_estadistica_catalogacion.php?desde=" + obj1.value + "&hasta=" + obj2.value;
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
				
				<div style='float:left; width: auto; margin-right: 10px; '>
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
				
				/* titulos catalogados */
				require_once( "estadisticas.inc.php" );
				
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_2</h1>";
				$tots_catalog = 0;
				$info_catalog = estadisticas_catalogacion( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $tots_catalog );
				
				for( $i=0; $i<count($info_catalog); $i++ )
				{ 
					echo "&nbsp;<div class='mini_bullet'></div><strong>" . $info_catalog[$i]["descripcion"] . "</strong> = " . $info_catalog[$i]["titulos"] . "<br>" ; 
				}
				
				if( count($info_catalog) > 0 )
					echo "<br>";

				echo "&nbsp;&nbsp;<strong>$LBL_TOTAL_CATALOGD</strong>  " . $tots_catalog; 
				echo "<br>";
				echo "<br>";					
				
				echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";				
				
				
				
				$db->sql = "SELECT a.ID_TITULO, a.USUARIO_REGISTRO, a.FECHA_REGISTRO  " . 
						   "FROM acervo_titulos a " .
						   "WHERE ID_BIBLIOTECA=$id_biblioteca and (FECHA_REGISTRO BETWEEN '$fecha_desde' and '$fecha_hasta')";
						   
				if( $filter != "" )
				{
					$db->sql .= " and a.ID_TIPOPERSONA='$filter' ";
				}
						   
				$db->sql .= "ORDER BY a.ID_TITULO";

				$db->Open();
				
				$db->SetClassesForDisplay( "hilite_odd", "hilite_even" );
				
				echo "<table width=890px>";
				echo "<tr>" .
					 " <td class='cuadricula columna columnaEncabezado' width='15px'>&nbsp;</td>" .					 
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_USUARIOREGISTRO</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='50px'>$LBL_FECHAREGISTRO</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='25px'>$LBL_IDITEM</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='150px'>$LBL_TAG_AUTOR</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='40px'>$LBL_TAG_CODE</td>" .
					 " <td class='cuadricula columna columnaEncabezado' width='200px'>$LBL_TAG_TITULO</td>" .
					 " <td align='center' class='cuadricula columna columnaEncabezado' width='50px'>$LBL_COPIES</td>" .
					 "</tr>";
				
				while( $db->NextRow() ) 
				{ 
					$txt_id_titulo	      = $db->row["ID_TITULO"]; 
					$txt_usuario_registro = $db->row["USUARIO_REGISTRO"];
					$txt_fecha_registro   = dbdate_to_human_format( $db->row["FECHA_REGISTRO"], 1, 0 );
					
					$txt_usuaario_registro = 
					
					$hilite_on = "";
					$hilite_off = "";
					
					$rowedited = "";
						
					$rowedited .= $db->class_for_display;
					
					$item = new TItem_Basic( $id_biblioteca, $db->row["ID_TITULO"], 0, $db );
					
					$link_to_author_titles = "anls_ver_titulos_filtrados.php?byauthor=1&filter=\"" . $item->cAutor . "\"";
					
					echo "<tr class='$rowedited' onMouseOver='javascript:this.className=\"hilite_onmouse_enter\";' onMouseOut='javascript:this.className=\"$rowedited\";'>" . 
						 " <td class='cuadricula columna'>" . $db->numRows ."</td> " .						 
						 " <td class='cuadricula columna'>" . $item->ObtenerUsuarioRegistro(1) . "</td> " .
						 " <td class='cuadricula columna'>$hilite_on" . $txt_fecha_registro. "</a>$hilite_off</td> " .
						 " <td class='cuadricula columna'>$txt_id_titulo</td> " .
						 " <td class='cuadricula columna'><a href='$link_to_author_titles'>" . $item->cAutor . "</a></td> " .
						 " <td class='cuadricula columna'>" . $item->Material_ShortCode() . "</td> " .
						 " <td class='cuadricula columna'>" . $item->cTitle . "</td> " .
						 " <td align='center' class='cuadricula columna'>" . $item->ObtenerNumeroCopias() . "</td> " .
						 " </tr>";
						 
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