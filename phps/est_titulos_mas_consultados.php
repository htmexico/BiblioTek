<?php
	session_start();
	/*******
	  Historial de Cambios
	  
	  05 oct 2009: Se crea el para estadística general
     */		
		
	include ("../funcs.inc.php");

	check_usuario_firmado(); 

	include ("../basic/bd.class.php");
	include ("../privilegios.inc.php");
	include ("estadisticas.inc.php");
	include ("circulacion.inc.php");
	
	include_language( "global_menus" );
	include_language( "est_titulos_mas_consultados" ); 	// archivo de idioma

	// Coloca un encabezado HTML <head>
	include ("../basic/head_handler.php");	
	HeadHandler( $LBL_HEADER, "../", 1 );
	
	verificar_privilegio( PRIV_EST_TITLES_MOST_VIEWED, 1 );
	
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
				var url = "est_titulos_mas_consultados.php?desde=" + obj1.value + "&hasta=" + obj2.value;
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

</head>

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
				<div style='float:left; width: auto; margin-right:10px; '>
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
				
				ob_start();  // start buffering echos
	
				$fecha_desde = date_for_database_updates( $fecha_desde ) . " 00:00:00";
				$fecha_hasta = date_for_database_updates( $fecha_hasta ) . " 23:59:59";
				
				$db = new DB;
				
				$array_info_titulos = Array();

				estadisticas_titulos_mas_consultados( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $array_info_titulos );

				// TITULOS MÁS CONSULTADOS
				//echo "<hr>";
				echo "<h1>$LBL_CHAPTER_1</h1>";

				echo "<table width='100%'>";
				echo "<tr>" . 
					 " <td class='columna columnaEncabezado cuadricula' width='10%'>$LBL_MATERIAL</td>" .
					 " <td class='columna columnaEncabezado cuadricula' width='80%'>$LBL_TITLE</td>" .
					 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_HITS</td>" .
					 "</tr>";							 
				
				for( $i=0; $i<count($array_info_titulos); $i++ )
				{ 
					$item = new TItem_Basic( $id_biblioteca, $array_info_titulos[$i]["id_titulo"], 0, $db );
					
					echo "<tr>" . 
						 " <td class='columna cuadricula' valign='top'>" . $array_info_titulos[$i]["marc_code"] . "</td>" .
						 " <td class='columna cuadricula'>" . $item->cTitle . "</td>" .
						 " <td align=center class='columna cuadricula' valign='top'>" . $array_info_titulos[$i]["consultas"] . "</td>" .
						 "</tr>";
							 
					$item->destroy();
					unset( $item );
				}

				echo "</table>";
				
				unset( $array_info_titulos );
				
				echo "<br><br>";
				
				// TEMAS MAS CONSULTADOS
				//echo "<hr>";
				echo "<h1>$LBL_CHAPTER_2</h1>";
				
				$array_info_temas = Array();
				
				estadisticas_temas_mas_consultados( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $array_info_temas );
				
				echo "<table width='100%'>";
				echo "<tr>" . 
					 " <td class='columna columnaEncabezado cuadricula' width='10%'>$LBL_MATERIAL</td>" .
					 " <td class='columna columnaEncabezado cuadricula' width='80%'>$LBL_THEME</td>" .
					 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_HITS</td>" .
					 "</tr>";							 
				
				
				for( $i=0; $i<count($array_info_temas); $i++ )
				{ 
					if( $array_info_temas[$i]["views"] > 2 )
					{
						echo "<tr>" . 
							 " <td class='columna cuadricula'>" . $array_info_temas[$i]["marc_code"] . "</td>" .
							 " <td class='columna cuadricula'>" . $array_info_temas[$i]["theme"] . "</td>" .
							 " <td align=center class='columna cuadricula'>" .  $array_info_temas[$i]["views"] . "</td>" .
							 "</tr>";					
					}
				}				

				echo "</table>";
					
				unset( $array_info_titulos );
				
				
				// AUTORES MAS CONSULTADOS
				echo "<br><br>";
				echo "<h1>$LBL_CHAPTER_3</h1>";
				
				$array_info_authors = Array();
				
				estadisticas_autores_mas_consultados( $db, $id_biblioteca, $fecha_desde, $fecha_hasta, $array_info_authors );
				
				echo "<table width='100%'>";
				echo "<tr>" . 
					 " <td class='columna columnaEncabezado cuadricula' width='10%'>$LBL_MATERIAL</td>" .
					 " <td class='columna columnaEncabezado cuadricula' width='80%'>$LBL_AUTHOR</td>" .
					 " <td align='center' class='columna columnaEncabezado cuadricula' width='10%'>$LBL_HITS</td>" .
					 "</tr>";							 
				
				
				for( $i=0; $i<count($array_info_authors); $i++ )
				{ 
					if( $array_info_authors[$i]["views"] >= 5 )
					{
						echo "<tr>" . 
							 " <td class='columna cuadricula'>" . $array_info_authors[$i]["marc_code"] . "</td>" .
							 " <td class='columna cuadricula'>" . $array_info_authors[$i]["author"] . "</td>" .
							 " <td align=center class='columna cuadricula'>" .  $array_info_authors[$i]["views"] . "</td>" .
							 "</tr>";					
					}
				}				

				echo "</table>";
					
				unset( $array_info_titulos );				
				
				// Hide progressing
				SYNTAX_JavaScript( 1, 1, "HideDiv('showProcessing');" );
				//ob_end_clean() for debugging;
				
				echo "<br><input type='button' class='boton' value='$BTN_PRINT' onClick='javascript:print();'><br>";

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